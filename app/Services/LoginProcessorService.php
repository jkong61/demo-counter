<?php

namespace App\Services;

use App\Models\Counter;
use App\Models\CounterUser;
use App\Models\LoginSession;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Exception;
use UnexpectedValueException;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Collection;

class LoginProcessorService {

    private static $date_today;
    private static $next_day;
    private static $counter_instances;
    private static $counter_user_instances;

    public static function ProcessLoginRequest(array $data_array, $branch) : bool {

        // DB::enableQueryLog();

        // Initialize the date_today for the class
        self::$date_today = CarbonImmutable::today()->startOfDay();
        // self::$date_today = Carbon::createMidnightDate(2019, 8, 31); // DEBUG ONLY
        self::$next_day = self::$date_today->endOfDay();
        self::$counter_instances = Counter::select('id','number','branch_id')->get();
        self::$counter_user_instances = CounterUser::select('id','number','branch_id')->get();

        if (count($data_array) > 0) {
            try {
                $remapped_input_sessions_for_ORM = self::processInputDataJsonForORM($data_array, $branch);
            } catch (InvalidFormatException $e) {
                throw new InvalidFormatException("Invalid Format.", 1, $e);
            } catch (Exception $e) {
                throw $e;
            }

            // Early return if there are no available sessions for today.
            if(count($remapped_input_sessions_for_ORM) <= 0) {
                return False;
            }

            // Collect all sessions for "today's day"
            // Filter DB login sessions for those that are already have a logout time, as they are already completed and does not require updates.
            $all_login_sessions = LoginSession::whereBetween('login_date_time',
            [self::$date_today->timestamp, self::$next_day->timestamp])
            ->orWhere(function ($query) {
                $query->whereBetween ('logout_date_time', [self::$date_today->timestamp, self::$next_day->timestamp])
                ->whereNull('login_date_time');
            })
            ->get();
            // dd($all_login_sessions);

            // Filter input data against DB data to ensure no duplicates from input data
            $filtered_input_data_against_existing_db = $remapped_input_sessions_for_ORM->reject(function ($session) use ($all_login_sessions, $branch) {
                return !is_null($all_login_sessions
                        ->where('counter_id','=', $session['counter_id'])
                        ->where('counter_user_id','=', $session['counter_user_id'])
                        ->where('branch_id','=',$branch->id)
                        ->where('login_date_time','=', $session['login_date_time'])
                        ->whereNotNull('logout_date_time')
                        ->first()
                );
            });

            // dd($filtered_input_data_against_existing_db);
            // dd($all_login_sessions); // SHOULD STILL BE UNCHANGED AT THIS POINT

            $nulled_login_sessions = $all_login_sessions->whereNull('logout_date_time');

            // Update the older login events first to check if some have already logged out.
            $filtered_input_data_against_existing_db->each(function ($session) use ($nulled_login_sessions, $branch){
                $null_login = $nulled_login_sessions
                    ->where('counter_id','=', $session['counter_id'])
                    ->where('counter_user_id','=', $session['counter_user_id'])
                    ->where('branch_id','=',$branch->id)
                    ->where('login_date_time','=', $session['login_date_time']);
                $possible_model = $null_login->first();
                
                if(is_null($possible_model)) {
                    // If such model is not found, create a new instance

                    // Check if there is a preceding logged in session for the current counter,
                    // then update it's logout time with the the login time (logout time if login is NULL) of the incoming model
                    // Does not matter who is the counter user
                    $preceding_session = $nulled_login_sessions
                        ->where('counter_id','=', $session['counter_id'])
                        ->sortByDesc('login_date_time');

                    // There are possibly duplicate (very few) records from testing, but not likely during real deployment (just extra steps)
                    if(count($preceding_session) > 0) {
                        $preceding_session->each(function ($p_session) use ($session) {
                            $p_session->logout_date_time = $session['login_date_time'] ?? $session['logout_date_time'];
                            $p_session->save();    
                        });
                    }

                    // This calls created event (because of NEW model) on LoginSession Model Observer, (causes counter side-effects)
                    $login_session = new LoginSession();
                    $login_session->login_date_time = $session['login_date_time'] ?? null;
                    $login_session->logout_date_time = $session['logout_date_time'] ?? null;
                    $login_session->counter_id = $session['counter_id'];
                    $login_session->counter_user_id = $session['counter_user_id'];
                    $login_session->temp_counter_user_number = $session['temp_counter_user_number'];
                    $login_session->branch_id = $branch->id;
                    $login_session->save();

                } else {
                    // Else update the possible model's logout_date_time
                    // This calls updated event on LoginSession Model Observer, (causes counter side-effects)
                    $possible_model->logout_date_time = $session['logout_date_time'];
                    $possible_model->save();
                }
            });

            // dd(DB::getQueryLog());

            return True;
        }
        return False;
    }

    private static function getCarbonTime($date, $time) : int {
        return Carbon::createFromFormat('Y-m-d H:i:s', sprintf('%s %s', $date, $time))->timestamp;
    }

    private static function processInputDataJsonForORM($input_array, $branch) : Collection {
        // Used to filter
        $filtered_sessions = array_filter($input_array, function ($session) {
            return Carbon::parse($session->DATE)->toDateString() == self::$date_today->toDateString();
        });

        // Should return empty collection if filtered sessions is empty
        return collect(array_map(function ($session) use ($branch) {

            if(boolval(intval($session->USER)) && boolval(intval($session->COUNTER))) {
                $session->USER = intval($session->USER);
                $session->COUNTER = intval($session->COUNTER);
            } else {
                // Typically because the input data is non-numeric
                throw new InvalidFormatException("Invalid Counter or User format.");
            }

            // These processes is to remap the branch instance and specific numbers to the database's own internal ID
            // Check if there are any counter users that match the same number and branch
            $counter_user = self::$counter_user_instances->first(function ($user) use ($session, $branch) {
                return $user->number == $session->USER && $user->branch_id == $branch->id;
            });

            // If none is found, reassign to a temporary number
            if(is_null($counter_user)){
                $temporary_counter_user_number = $session->USER;
                // throw new UnexpectedValueException(sprintf("Counter User Number %s from %s does not exist.", $session->USER, $branch->name));
            }

            // Check if there is a counter with the same details from the branch
            $counter = self::$counter_instances->first(function ($counter) use ($session, $branch) {
                return $counter->number == $session->COUNTER && $counter->branch_id == $branch->id;
            });
            // If none is found, create a new counter for that branch
            if(is_null($counter)) {
                $counter = new Counter();
                $counter->number = $session->COUNTER;
                $counter->branch_id = $branch->id;
                $counter->save();
                $counter->refresh();
                
                // Refresh the collection to ensure collection is up to date
                self::$counter_instances = Counter::select('id','number','branch_id')->get();
            }

            $session->DATE = Carbon::parse($session->DATE)->toDateString();
            $session->TIMEIN = empty($session->TIMEIN) ? null : self::getCarbonTime($session->DATE, $session->TIMEIN);
            $session->TIMEOUT = empty($session->TIMEOUT) ? null : self::getCarbonTime($session->DATE, $session->TIMEOUT);

            return [
                'login_date_time' => $session->TIMEIN,
                'logout_date_time' => $session->TIMEOUT,
                'counter_id' => $counter->id,
                'counter_user_id' => is_null($counter_user) ? null : $counter_user->id,
                'temp_counter_user_number' => $temporary_counter_user_number ?? null
            ];
        }, $filtered_sessions));
    }

    // Used to reprocess null logins at end of day
    public static function remapTemporaryCounterUserNumbersToIds()
    {
        $date_today = CarbonImmutable::today()->startOfDay();
        $next_day = $date_today->endOfDay();

        $all_login_sessions = LoginSession::whereBetween('login_date_time',
        [$date_today->timestamp, $next_day->timestamp])
        ->whereNull('counter_user_id')
        ->whereNotNull('temp_counter_user_number')
        ->get();

        $all_login_sessions->each(function ($login_session) {
            $login_session->remapTemporaryCounterUser();
            $login_session->save();
        });
    }
}

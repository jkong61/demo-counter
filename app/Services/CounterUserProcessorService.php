<?php

namespace App\Services;

use App\Models\CounterUser;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Exception;

class CounterUserProcessorService {

    public static function ProcessCounterUserRequest(array $data_array, $branch_instance) : bool {  

        // get all the users for the specific branch
        $user_instances = CounterUser::withTrashed()->where('branch_id','=',$branch_instance->id)->get();

        if (count($data_array) > 0) {
            try {
                $remapped_users_for_ORM = self::processInputDataJsonForORM($data_array, $branch_instance);
            } catch (InvalidFormatException $e) {
                throw $e;  
            } catch (Exception $e) {
                throw $e;
            }

            // DBase script will not return deleted records
            // Partition both the data and update, create or remove instances where necessary
            $user_instances->each(function ($user) use ($remapped_users_for_ORM) {
                // $ORM_data = $remapped_users_for_ORM->where('number', $user->number)->where('branch_id', $user->branch_id)->first();
                $ORM_data = $remapped_users_for_ORM->first(function ($orm_user) use ($user) {
                    return $orm_user['number'] == $user->number && $orm_user['branch_id'] == $user->branch_id;
                });

                if($ORM_data){
                    $user->number = $ORM_data['number'];
                    $user->name = $ORM_data['name'];
                    $user->sname = $ORM_data['sname'];
                    $user->date_joined = $ORM_data['date_joined'];
                    $user->last_updated = $ORM_data['last_updated'];
                    $user->position = $ORM_data['position'];
                    $user->is_active = $ORM_data['is_active'];
                    $user->branch_id = $ORM_data['branch_id'];
                    if ($user->trashed())
                    {
                        $user->restore();
                    }
                    $user->save();
                } else {
                    $user->delete();
                }
            });

            // Create those that are not within user instances
            $user_instances->fresh();
            $missing_new_users = $remapped_users_for_ORM->filter(function ($user) use ($user_instances){
                // return is_null($user_instances->where('number', $user['number'])->where('branch_id', $user['branch_id'])->first());
                return is_null($user_instances->first(function ($orm_user) use ($user) {
                    return $orm_user->number == $user['number'] && $orm_user->branch_id == $user['branch_id'];
                }));
            });
            $missing_new_users->each(function ($user) {
                $counter_user = new CounterUser();
                $counter_user->number = $user['number'];
                $counter_user->name = $user['name'];
                $counter_user->sname = $user['sname'];
                $counter_user->date_joined = $user['date_joined'];
                $counter_user->last_updated = $user['last_updated'];
                $counter_user->position = $user['position'];
                $counter_user->is_active = $user['is_active'];
                $counter_user->branch_id = $user['branch_id'];
                $counter_user->save();
            });

            // CounterUser::upsert($remapped_users_for_ORM,
            // ['id'], ['name','sname','date_joined','last_updated','position','is_active','branch_id']);

            return True;
        }
        return False;
    }

    private static function processInputDataJsonForORM($input_array, $branch_instance) {
        return collect(array_map(function ($session) use ($branch_instance) {

            if(boolval(intval($session->USERID))) {
                $session->USERID = intval($session->USERID);
            } else {
                throw new InvalidFormatException("Invalid Format");
            }
            $session->DATE = Carbon::parse($session->DATE)->toDateString();

            // There are instances from dBASE file where last updated date is set at 0001-01-01
            // The we use the CREATED/JOINEDDATE date as the LAST UPDATE date.
            $session->LASTUPDATE = Carbon::parse($session->LASTUPDATE) < Carbon::createFromDate(1990,1,1) 
            ? $session->DATE 
            : Carbon::parse($session->LASTUPDATE)->toDateString();

            return [
                'number' => $session->USERID,
                'name' => $session->NAME,
                'sname' => $session->SNAME,
                'date_joined' => $session->DATE,
                'last_updated' => $session->LASTUPDATE,
                'position' => $session->POSITION,
                'is_active' => $session->ACTIVE,
                'branch_id' => $branch_instance->id
            ];
        }, $input_array));
    }
}
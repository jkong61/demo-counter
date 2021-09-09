<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Counter;
use App\Models\Feedback;
use App\Models\LoginSession;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FeedbackProcessorService {
    public static function ProcessFeedbackRequest(Request $request) : bool {
        $login_session = null;

        // Need to standardize the requests input here.
        $validationArray = [
            'rating' => 'required|numeric|min:1|max:5',
            'description' => 'nullable',
            'branch_name' => 'required|string|max:2|min:2|exists:branches,name',
            'counter_number' => 'required|numeric',
        ];
        $request->validate($validationArray);
        try 
        {
            $feedback_time = CarbonImmutable::now()->timestamp;
            $branch = Branch::getIdByName($request->get('branch_name'));
            if(is_null($branch))
            {
                throw new Exception('Branch does not exist.');
            }
            $possible_active_counter = Counter::getIdByBranchIdAndNumber($branch->id, $request->get('counter_number'));

            if (is_null($possible_active_counter)) 
            {
                // Counter was not found, must always have a counter number
                throw new Exception('Counter does not exist.');
            }

            $feedback = new Feedback();
            $feedback->description = $request->get('description') ?? null;
            $feedback->rating = $request->get('rating');
            $feedback->feedback_time_submission = $feedback_time;
            $feedback->counter_id = $possible_active_counter->id;

            $login_session =  LoginSession::where('counter_id','=', $possible_active_counter->id)
            ->where('login_date_time','<=', $feedback_time)
            ->where('logout_date_time','>=', $feedback_time)
            ->first();

            if (is_null($login_session))
            {
                $login_session =  LoginSession::where('counter_id','=', $possible_active_counter->id)
                ->where('login_date_time','<=', $feedback_time)
                ->whereNull('logout_date_time')
                ->select('*', DB::raw("ABS($feedback_time - CAST(login_date_time AS SIGNED)) AS margin"))
                // get the session closest to the feedback time with the margin within 24hrs , 86400 seconds
                ->having('margin','<', 86400)
                ->orderBy('margin')
                ->first();
            }

            // Would be saved as either null or as a possible model
            $feedback->login_session_id = is_null($login_session) ? null : $login_session->id;
            $feedback->save();
        }
        catch (ValidationException $e)
        {
            throw $e;
        } 
        catch (Exception $e)
        {
            throw $e;
        }
        return True;
    }

    public static function ProcessNullSessionFeedbacks() : void {
        Log::info("Imma process them null feedbacks now.");

        // echo("Hello");

        $date_today = CarbonImmutable::now();
        $feedbacks_collection = Feedback::whereBetween('feedback_time_submission',[$date_today->startOfDay()->timestamp, $date_today->endOfDay()->timestamp])
        ->whereNull('login_session_id')
        ->whereNotNull('counter_id')
        ->get();

        $feedbacks_collection->each(function ($feedback) {
            $login_session =  LoginSession::where('counter_id','=', $feedback->counter_id)
            ->where('login_date_time','<=', $feedback->feedback_time_submission)
            ->where('logout_date_time','>=', $feedback->feedback_time_submission)
            ->first();

            if(is_null($login_session)){
                $login_session =  LoginSession::where('counter_id','=', $feedback->counter_id)
                ->where('login_date_time','<=', $feedback->feedback_time_submission)
                ->whereNull('logout_date_time')
                ->select('*', DB::raw("ABS($feedback->feedback_time_submission - CAST(login_date_time AS SIGNED)) AS margin"))
                // get the session closest to the feedback time with the margin within 24hrs , 86400 seconds
                ->having('margin','<', 86400)
                ->orderBy('margin')
                ->first();
            }

            $feedback->login_session_id = $login_session->id ?? null;
            $feedback->save();
        });
    }
}
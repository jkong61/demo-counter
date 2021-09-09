<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Counter;
use App\Models\CounterUser;
use App\Models\Feedback;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class OverviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $validated = $request->validate([
            'selection' => 'nullable|in:counter,user',
            'ordering' => 'nullable|boolean',
            'branch' => 'nullable|numeric|exists:branches,id',
            'identifier' => 'nullable|numeric|max:255'
        ]);

        if(isset($validated['selection']) && $validated['selection'] == 'user'){
            $models_query = CounterUser::select("id","name","sname",'number','branch_id')
                ->with('branch')
                ->withAvg(['feedback' => function ($query) {
                    $query->join('sales','feedback.id','=','sales.feedback_id');
                }],'rating')
                ->withCount(['feedback' => function ($query) {
                    $query->join('sales','feedback.id','=','sales.feedback_id');
                }])->groupBy('id');
        } else {
            $models_query = Counter::select("id","number","branch_id")
                ->with('branch')
                ->withAvg(['feedback' => function ($query) {
                    $query->join('sales','feedback.id','=','sales.feedback_id');
                }],'rating')
                ->withCount(['feedback' => function ($query) {
                    $query->join('sales','feedback.id','=','sales.feedback_id');
                }])
                ->groupBy('id');
        }

        if(isset($validated['ordering']) && $validated['ordering']) {
            $ordering = 'desc';
        } else {
            $ordering = 'asc';
        }

        $models_query = $models_query->orderBy(
            isset($validated['selection']) && $validated['selection'] == 'user' 
            ? 'id' : 'number',
            $ordering);

        if(isset($validated['identifier'])){
            $models_query = $models_query->where(
                'number',
                'like',
                "%".$validated['identifier']."%"
            );
        }

        if(isset($validated['branch'])){
            $models_query = $models_query->where('branch_id','=', $validated['branch']);
        }

        $models = $models_query->having('feedback_avg_rating','!=','null')->paginate(10);

        $feedback_collection = Feedback::select('rating')->join('sales','feedback.id','=','sales.feedback_id')->get();
        $ratings = [
            "rating_group" => $feedback_collection->sort()->groupBy('rating', true),
            "total_feedback" => $feedback_collection->count()
        ];

        $branches = Branch::all('id','name');
        return view('overview.index')->with(compact('models','ratings','branches'));
    }

    public function view_user(Request $request, CounterUser $user)
    {
        $validated = $request->validate([
            'rating' => 'nullable|numeric|min:1|max:5',
            'feedback_date_start' => 'nullable|date|required_with:feedback_date_end|before:feedback_date_end',
            'feedback_date_end' => 'nullable|date|required_with:feedback_date_start|after:feedback_date_start',
            'identifier' => 'nullable|numeric|max:255'
        ]);

        $feedback_query = Feedback::with(['loginsession.counter','loginsession.counter.branch'])
            ->join("login_sessions","login_sessions.id", "=", "feedback.login_session_id")
            ->join('sales','feedback.id','=','sales.feedback_id')
            ->select("feedback.*", "login_sessions.counter_user_id as laravel_through_key")
            ->where("login_sessions.counter_user_id", "=", $user->id);

        if (isset($validated['feedback_date_start']) && isset($validated['feedback_date_end'])){
            $feedback_request_start_date = CarbonImmutable::createFromFormat('Y-m-d', ($validated['feedback_date_start']));
            $request_date_start_timestamp = $feedback_request_start_date->startOfDay()->timestamp;
            $feedback_request_end_date = CarbonImmutable::createFromFormat('Y-m-d', ($validated['feedback_date_end']));
            $request_date_end_timestamp = $feedback_request_end_date->endOfDay()->timestamp;

            $feedback_query = $feedback_query->whereBetween('feedback_time_submission', [$request_date_start_timestamp, $request_date_end_timestamp]);
        }

        if(isset($validated['rating'])){
            $feedback_query = $feedback_query->where('rating','=',$validated['rating']);
        }

        if(isset($validated['identifier'])){
            $feedback_query = $feedback_query->whereHas('loginsession.counter', function ($query) use ($validated) {
                $query->where('number', 'like', "%".$validated['identifier']."%");
            });
        }

        // $feedback_count = $user->feedback->count();
        // $feedback_average = $user->feedback->avg('rating');

        $feedback_count = $user->loadCount(['feedback' => function ($query) {
            $query
            ->join('sales','feedback.id','=','sales.feedback_id');
        }]);
        $feedback_average = $user->loadAvg(['feedback' => function ($query) {
            $query ->join('sales','feedback.id','=','sales.feedback_id');
        }], 'rating');

        $feedbacks = $feedback_query->paginate(10);

        return view('overview.model', [
            'feedbacks' => $feedbacks,
            'model' => $user,
            'count' => $feedback_count->feedback_count,
            'average' => $feedback_average->feedback_avg_rating
        ]);
    }

    public function view_counter(Request $request, Counter $counter)
    {
        //
        $validated = $request->validate([
            'rating' => 'nullable|numeric|min:1|max:5',
            'feedback_date_start' => 'nullable|date|required_with:feedback_date_end|before:feedback_date_end',
            'feedback_date_end' => 'nullable|date|required_with:feedback_date_start|after:feedback_date_start',
            'identifier' => 'nullable|numeric|max:255'
        ]);

        $feedback_query = Feedback::with(['loginsession.counter_user'])
            ->join("login_sessions","login_sessions.id", "=", "feedback.login_session_id")
            ->join('sales','feedback.id','=','sales.feedback_id')
            ->select("feedback.*", "login_sessions.counter_id as laravel_through_key")
            ->where("login_sessions.counter_id", "=", $counter->id);

        if (isset($validated['feedback_date_start']) && isset($validated['feedback_date_end'])){
            $feedback_request_start_date = CarbonImmutable::createFromFormat('Y-m-d', ($validated['feedback_date_start']));
            $request_date_start_timestamp = $feedback_request_start_date->startOfDay()->timestamp;
            $feedback_request_end_date = CarbonImmutable::createFromFormat('Y-m-d', ($validated['feedback_date_end']));
            $request_date_end_timestamp = $feedback_request_end_date->endOfDay()->timestamp;

            $feedback_query = $feedback_query->whereBetween('feedback_time_submission', [$request_date_start_timestamp, $request_date_end_timestamp]);
        }

        if(isset($validated['rating'])){
            $feedback_query = $feedback_query->where('rating','=',$validated['rating']);
        }

        if(isset($validated['identifier'])){
            $feedback_query = $feedback_query->whereHas('loginsession.counter_user', function ($query) use ($validated) {
                $query->where('number', 'like', "%".$validated['identifier']."%");
            });
        }

        // $feedback_count = $counter->feedback->count();
        $feedback_count = $counter->loadCount(['feedback' => function ($query) {
            $query
            ->join('sales','feedback.id','=','sales.feedback_id');
        }]);
        $feedback_average = $counter->loadAvg(['feedback' => function ($query) {
            $query ->join('sales','feedback.id','=','sales.feedback_id');
        }], 'rating');
        // $feedback_average = $counter->feedback->avg('rating');

        $feedbacks = $feedback_query->paginate(10);

        return view('overview.model', [
            'feedbacks' => $feedbacks,
            'model' => $counter,
            'count' => $feedback_count->feedback_count,
            'average' => $feedback_average->feedback_avg_rating
        ]);
    }
}

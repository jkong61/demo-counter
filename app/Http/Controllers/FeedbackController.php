<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Services\FeedbackProcessorService;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'rating' => 'nullable|numeric|min:1|max:5',
            'feedback_date_start' => 'nullable|date|required_with:feedback_date_end|before:feedback_date_end',
            'feedback_date_end' => 'nullable|date|required_with:feedback_date_start|after:feedback_date_start',
        ]);

        // Selective Eager Loading Eloquent
        $feedback_query = Feedback::with(['loginsession.counter_user','counter'])
            ->join('sales','feedback.id','=','sales.feedback_id')
            ->orderBy('feedback.id', 'DESC');

        if(isset($validated['rating'])){
            $feedback_query = $feedback_query->where('rating','=',$validated['rating']);
        }

        if (isset($validated['feedback_date_start']) && isset($validated['feedback_date_end'])){
            $feedback_request_start_date = CarbonImmutable::createFromFormat('Y-m-d', ($validated['feedback_date_start']));
            $request_date_start_timestamp = $feedback_request_start_date->startOfDay()->timestamp;
            $feedback_request_end_date = CarbonImmutable::createFromFormat('Y-m-d', ($validated['feedback_date_end']));
            $request_date_end_timestamp = $feedback_request_end_date->endOfDay()->timestamp;

            $feedback_query = $feedback_query->whereBetween('feedback_time_submission', [$request_date_start_timestamp, $request_date_end_timestamp]);
        }

        $feedbacks = $feedback_query->paginate(10);

        return view('feedback.index-feedback')->with(compact('feedbacks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        Log::info('Feedback creation request coming in from '.$request->ip());
        try {
            return FeedbackProcessorService::ProcessFeedbackRequest($request) 
            ? response()->json(['message' => Response::HTTP_OK], 200)
            : response()->json(['message' => Response::HTTP_BAD_REQUEST], 400);
            
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 400);
        } catch (Exception $e) {
            // return response()->json(['message' => Response::HTTP_UNPROCESSABLE_ENTITY], 422);
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function show(Feedback $feedback)
    {
        //
        $feedback = $feedback->load('loginsession.counter_user','counter');
        return response()->json($feedback, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function edit(Feedback $feedback)
    {
        //
        return view('feedback.edit-feedback')->with(compact('feedback'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Feedback $feedback)
    {
        //
        $validationArray = [
            'rating' => 'required|numeric|min:1|max:5',
            'description' => 'nullable|max:512'
        ];

        $request->validate($validationArray);

        $feedback->description = $request->get('description') ?? null;
        $feedback->rating = $request->get('rating');

        $feedback->save();

        return redirect()->route('feedback.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function destroy(Feedback $feedback)
    {
        // dd($feedback);
        return redirect()->route('feedback.index');
    }
}

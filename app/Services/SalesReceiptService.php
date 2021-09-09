<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\CounterUser;
use App\Models\Feedback;
use App\Models\Sale;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Collection;

class SalesReceiptService {

    public function handleSalesReceipts($json_array, Branch $branch_instance)
    {
        $remapped_sales_for_ORM = $this->processInputDataJsonForORM($json_array, $branch_instance);

        $remapped_sales_for_ORM->each(function ($sale_data) {
            $sale = new Sale([
                'receipt_number' => $sale_data['receipt_number'],
                'sale_time' => $sale_data['sale_time'],
                'branch_id' => $sale_data['branch_id'],
                'counter_user_id' => $sale_data['counter_user_id'],
                'temp_counter_user_number' => $sale_data['temp_counter_user_number'],
            ]);
            $sale->save();
        });

        return True;
    }

    // Internal function to process JSON into PHP associative array
    private function processInputDataJsonForORM($input_array, Branch $branch_instance) : Collection 
    {
        $counter_user_instances = CounterUser::select('id','number','branch_id')->get();

        return collect(array_map(function ($sale) use ($branch_instance, $counter_user_instances) {
            if(!boolval($receipt_num = intval($sale->RECEIPTNUM)))
            {
                throw new InvalidFormatException("Invalid Receipt Number format.");
            }

            if(!boolval($user_number = intval($sale->USER)))
            {
                throw new InvalidFormatException("Invalid Counter User Number format.");
            }

            $sale->RECEIPTNUM = $receipt_num;

            $counter_user = $counter_user_instances->first(function ($user) use ($user_number, $branch_instance) {
                return $user->number == $user_number && $user->branch_id == $branch_instance->id;
            });

            if(is_null($counter_user)){
                $temporary_counter_user_number = $user_number;
            }

            return [
                'receipt_number' => $sale->RECEIPTNUM,
                'sale_time' => Carbon::parse($sale->TIME)->toDateString(),
                'branch_id' => $branch_instance->id,
                'counter_user_id' => is_null($counter_user) ? null : $counter_user->id,
                'temp_counter_user_number' => $temporary_counter_user_number ?? null
            ];

        }, $input_array));
    }

    public static function processSalesToFeedbacksAtEndOfDay()
    {
        // Filter the sales by the day
        $date_today = CarbonImmutable::today()->startOfDay();
        $next_day = $date_today->endOfDay();
        $all_sales_for_day = Sale::whereBetween('sale_time', [$date_today->timestamp, $next_day->timestamp])->get();

        // Process all sales dates with temporary counter user numbers, will be skipped if there are none
        $sales_with_temp_users = $all_sales_for_day
        ->whereNull('counter_user_id');

        $sales_with_temp_users->each(function ($sale) 
        {
            $sale->remapTemporaryCounterUser();
            $sale->save();
        });

        // Process each sale entry and map them to a feedback entry if available
        $sales_without_feedbacks = $all_sales_for_day->whereNull('feedback_id');

        // Filter those feedbacks with sales information, because 1 sale can only have 1 feedback
        $feedbacks_collection = Feedback::whereBetween('feedback_time_submission',
        [$date_today->startOfDay()->timestamp, $date_today->endOfDay()->timestamp])
        ->get();

        $sales_without_feedbacks->each(function ($sale) use (&$feedbacks_collection, $date_today) {
            // Find a feedback from the collection, filter the collection again by the user user id
            // and the time within 2 minutes of the sale time (starting and ending)
            $local_filtered_feedback_collection = $feedbacks_collection
                ->whereNull('sale')
                ->where('loginsession.counter_user_id', $sale->counter_user_id)
                ->whereBetween('feedback_time_submission', [
                    $sale->sale_time - 60,
                    $sale->sale_time + 60,
                ]);

            // var_dump($local_filtered_feedback_collection);

            // Get the time that is closest to the sale time and associate that first one with the sale time
            $feedback_indexes_with_time_margin = $local_filtered_feedback_collection->map(function ($feedback, $key) use ($sale) {
                return [
                    'index' => $key,
                    'margin' => abs($feedback->feedback_time_submission - $sale->sale_time)
                ];
            })->sortBy('margin');

            $feedback_index_with_smallest_time_margin = $feedback_indexes_with_time_margin->first();

            if (!is_null($feedback_index_with_smallest_time_margin))
            {
                $closest_feedback = $local_filtered_feedback_collection[$feedback_index_with_smallest_time_margin['index']];
                $sale->feedback()->associate($closest_feedback);
                $sale->save();

                $feedbacks_collection = Feedback::whereBetween('feedback_time_submission',
                [$date_today->startOfDay()->timestamp, $date_today->endOfDay()->timestamp])
                ->get();
                // ->filter(function ($feedback) {
                //     return is_null($feedback->sale);
                // });
                // $feedbacks_collection = $feedbacks_collection->fresh();
                // $feedbacks_collection = $feedbacks_collection->filter(function ($feedback) {
                //     return is_null($feedback->sale);
                // });
            }
        });
    }
}
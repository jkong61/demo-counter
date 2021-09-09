<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Counter;
use App\Models\CounterUser;
use App\Models\Feedback;
use App\Models\LoginSession;
use App\Models\Sale;
use App\Services\SalesReceiptService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class SalesReceiptsFeatureTest extends TestCase
{
    use WithoutMiddleware;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_sales_remap_function()
    {
        $this->seedDB();

        $counter_user = CounterUser::getIdFromNumberBranch(101, Branch::first()->id);
        $sale = Sale::factory()->create([
            'temp_counter_user_number' => 101,
            'branch_id' => Branch::first()
        ]);

        $sale->remapTemporaryCounterUser();
        $sale->save();

        $this->assertEquals($counter_user->id, $sale->counter_user_id);
        $this->assertNull($sale->temp_counter_user_number);
    }

    public function test_sales_fields_on_first_create()
    {
        $this->seedDB();

        // This user 101 should already exist during initial seeding
        $expected_temp_counter_user_number = 101;
        $sale = Sale::factory()->create([
            'temp_counter_user_number' => $expected_temp_counter_user_number,
            'branch_id' => Branch::first()
        ]);

        $this->assertNotNull($sale->temp_counter_user_number);
        $this->assertEquals($expected_temp_counter_user_number, $sale->temp_counter_user_number);
        $this->assertNull($sale->counter_user_id);
    }

    public function test_sales_endpoint_controller_misconfigured_data()
    {
        $response = $this->postJson('/api/salesreceipt', [
            'data' => "giberrighs",
            'branch' => 'hello'
        ]);

        // Unprocessable data because of wrong format
        $response->assertStatus(422);
    }

    public function test_sales_endpoint_controller_nonexistant_branch_data()
    {
        $response = $this->postJson('/api/salesreceipt', [
            'data' => "[
                    {
                      \"RECEIPTNUM\": \"010160941\",
                      \"ITEMCOUNT\": \"1\",
                      \"TIME\": \"2021-08-26 08:22:16\",
                      \"USER\": \"384\"
                    }
                  ]",
            'branch' => 'hello'
        ]);

        // Unprocessable data because of wrong format
        $response->assertStatus(422);
    }

    public function test_sales_endpoint_controller_wrong_data_formatting()
    {
        $this->seedDB();
        $response = $this->postJson('/api/salesreceipt', [
            'data' => "[
                    {
                      \"RECEIPTNUM\": \"010160941\",
                      \"RECEIPTNUM\": \"1\",
                      \"RECEIPTNUM\": \"2021-08-26 08:22:16\"
                    }
                  ]",
            'branch' => Branch::first()->name
        ]);

        // Unprocessable data because of wrong format
        $response->assertStatus(422);
    }

    public function test_sales_endpoint_controller_existant_branch_and_correct_data_single()
    {
        $this->seedDB();

        $response = $this->postJson('/api/salesreceipt', [
            'data' => "[
                    {
                      \"RECEIPTNUM\": \"010160941\",
                      \"ITEMCOUNT\": \"1\",
                      \"TIME\": \"2021-08-26 08:22:16\",
                      \"USER\": \"384\"
                    }
                  ]",
            'branch' => Branch::first()->name
        ]);

        $response->assertStatus(200);
    }

    public function test_sales_endpoint_controller_with_correct_data_multiple_data()
    {
        $this->seedDB();

        $response = $this->postJson('/api/salesreceipt', [
            'data' => "[
                      {
                        \"RECEIPTNUM\": \"010160941\",
                        \"ITEMCOUNT\": \"1\",
                        \"TIME\": \"2021-08-26 08:22:16\",
                        \"USER\": \"384\"
                      },
                      {
                        \"RECEIPTNUM\": \"011227685\",
                        \"ITEMCOUNT\": \"1\",
                        \"TIME\": \"2021-08-26 08:22:37\",
                        \"USER\": \"364\"
                      },
                      {
                        \"RECEIPTNUM\": \"011227686\",
                        \"ITEMCOUNT\": \"1\",
                        \"TIME\": \"2021-08-26 08:23:21\",
                        \"USER\": \"364\"
                      },
                      {
                        \"RECEIPTNUM\": \"010160942\",
                        \"ITEMCOUNT\": \"1\",
                        \"TIME\": \"2021-08-26 08:26:19\",
                        \"USER\": \"384\"
                      },
                      {
                        \"RECEIPTNUM\": \"010160943\",
                        \"ITEMCOUNT\": \"1\",
                        \"TIME\": \"2021-08-26 08:26:50\",
                        \"USER\": \"384\"
                      }
                    ]",
            'branch' => Branch::first()->name
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('sales', 5);
    }

    public function test_sales_relationship_with_feedback_and_feedback_relationship_with_sales()
    {
        $this->seedDB();
        $date_today = CarbonImmutable::today();
        $branch = Branch::first();
        $counter_user = CounterUser::getIdFromNumberBranch(101, $branch->id);

        $counter_one = Counter::factory()->create([
            'number' => 1,
            'branch_id' => $branch->id
        ]);

        $login_session_counter_one = LoginSession::factory()->create([
          'login_date_time' => $date_today->addHour()->timestamp,
          'logout_date_time' => $date_today->addHours(2)->timestamp,
          'counter_id' => $counter_one->id,
          'counter_user_id' => CounterUser::all()->random(),
          'branch_id' => $branch
      ]);

        $feedback_counter_one_1 = Feedback::factory()->create([
            'rating' => 4,
            'description' => 'Excellent!',
            'feedback_time_submission' => $date_today->timestamp,
            'counter_id' => $counter_one->id
        ]);

        $sale = Sale::factory()->create([
            'counter_user_id' => $counter_user->id,
            'branch_id' => $branch->id,
            'feedback_id' => $feedback_counter_one_1->id
        ]);

        // Assert that the model relationships are working
        $this->assertTrue($sale->feedback->is($feedback_counter_one_1));
        $this->assertTrue($feedback_counter_one_1->sale->is($sale));
    }

    public function test_sales_processing_mapping_feedback_function()
    {
        $this->seedDB();

        $branch = Branch::first();
        $counter_user = CounterUser::all()->random();
        $date_today = CarbonImmutable::now();

        $counter_one = Counter::factory()->create([
            'number' => 1,
            'branch_id' => $branch->id
        ]);

        $login_session_counter_one = LoginSession::factory()->create([
            'login_date_time' => $date_today->subMinute(10)->timestamp,
            'logout_date_time' => $date_today->addHours(2)->timestamp,
            'counter_id' => $counter_one->id,
            'counter_user_id' => $counter_user,
            'branch_id' => $branch
        ]);

        $feedback_counter_one_1 = Feedback::factory()->create([
            'rating' => 4,
            'description' => 'Excellent!',
            'feedback_time_submission' => $date_today->timestamp,
            'counter_id' => $counter_one->id,
            'login_session_id' => $login_session_counter_one
        ]);

        $feedback_counter_one_2 = Feedback::factory()->create([
            'rating' => 2,
            'description' => 'Kinda Excellent?',
            'feedback_time_submission' => $date_today->addMinute()->timestamp,
            'counter_id' => $counter_one->id,
            'login_session_id' => $login_session_counter_one
        ]);

        // Should be closer to feedback_counter_one_1 
        $sale_one = Sale::factory()->create([
            'counter_user_id' => $counter_user,
            'branch_id' => $branch->id,
            'sale_time' => $date_today->timestamp
        ]);

        // Should be closer to feedback_counter_one_2 
        $sale_two = Sale::factory()->create([
            'counter_user_id' => $counter_user,
            'branch_id' => $branch->id,
            'sale_time' => $date_today->addSeconds(45)->timestamp
        ]);

        SalesReceiptService::processSalesToFeedbacksAtEndOfDay();

        $sale_one->refresh();
        $sale_two->refresh();

        $this->assertTrue($sale_one->feedback->is($feedback_counter_one_1));
        $this->assertTrue($sale_two->feedback->is($feedback_counter_one_2));
    }

    public function test_sales_where_no_feedback_time_within_time_frame()
    {
        $this->seedDB();

        $branch = Branch::first();
        $counter_user = CounterUser::all()->random();
        $date_today = CarbonImmutable::now();

        $counter_one = Counter::factory()->create([
            'number' => 1,
            'branch_id' => $branch->id
        ]);

        $login_session_counter_one = LoginSession::factory()->create([
            'login_date_time' => $date_today->subMinute(10)->timestamp,
            'logout_date_time' => $date_today->addHours(2)->timestamp,
            'counter_id' => $counter_one->id,
            'counter_user_id' => $counter_user,
            'branch_id' => $branch
        ]);

        $feedback_counter_one_1 = Feedback::factory()->create([
            'rating' => 4,
            'description' => 'Excellent!',
            'feedback_time_submission' => $date_today->timestamp,
            'counter_id' => $counter_one->id,
            'login_session_id' => $login_session_counter_one
        ]);

        $sale_one = Sale::factory()->create([
            'counter_user_id' => $counter_user,
            'branch_id' => $branch->id,
            'sale_time' => $date_today->addHour()->timestamp
        ]);

        SalesReceiptService::processSalesToFeedbacksAtEndOfDay();

        // Should be not be assigned because 1 hour away from the window
        $sale_one->refresh();
        $this->assertNull($sale_one->feedback);
    }

    public function test_sales_where_feedback_no_login_sessions()
    {
        $this->seedDB();

        $branch = Branch::first();
        $counter_user = CounterUser::all()->random();
        $date_today = CarbonImmutable::now();

        $counter_one = Counter::factory()->create([
            'number' => 1,
            'branch_id' => $branch->id
        ]);

        $login_session_counter_one = LoginSession::factory()->create([
            'login_date_time' => $date_today->subMinute(10)->timestamp,
            'logout_date_time' => $date_today->addHours(2)->timestamp,
            'counter_id' => $counter_one->id,
            'counter_user_id' => $counter_user,
            'branch_id' => $branch
        ]);

        $feedback_counter_one_1 = Feedback::factory()->create([
            'rating' => 4,
            'description' => 'Excellent!',
            'feedback_time_submission' => $date_today->timestamp,
            'counter_id' => $counter_one->id,
            // 'login_session_id' => $login_session_counter_one
        ]);

        $feedback_counter_one_2 = Feedback::factory()->create([
            'rating' => 3,
            'description' => 'Excellent-ish',
            'feedback_time_submission' => $date_today->subMinute()->timestamp,
            'counter_id' => $counter_one->id,
            // 'login_session_id' => $login_session_counter_one
        ]);

        $sale_one = Sale::factory()->create([
            'counter_user_id' => $counter_user,
            'branch_id' => $branch->id,
            'sale_time' => $date_today->timestamp
        ]);

        SalesReceiptService::processSalesToFeedbacksAtEndOfDay();

        // Should be null because feedback has no login data/session
        $sale_one->refresh();
        $this->assertNull($sale_one->feedback);

    }

    public function test_sales_with_possible_duplicate_assignments()
    {
        $this->seedDB();

        $branch = Branch::first();
        $counter_user = CounterUser::all()->random();
        $date_today = CarbonImmutable::now();

        $counter_one = Counter::factory()->create([
            'number' => 1,
            'branch_id' => $branch->id
        ]);

        $login_session_counter_one = LoginSession::factory()->create([
            'login_date_time' => $date_today->subMinute(10)->timestamp,
            'logout_date_time' => $date_today->addHours(2)->timestamp,
            'counter_id' => $counter_one->id,
            'counter_user_id' => $counter_user,
            'branch_id' => $branch
        ]);

        $feedback_counter_one_3 = Feedback::factory()->create([
            'rating' => 5,
            'description' => 'Excellent!',
            'feedback_time_submission' => $date_today->subSeconds(15)->timestamp,
            'counter_id' => $counter_one->id,
            'login_session_id' => $login_session_counter_one
        ]);

        $feedback_counter_one_4 = Feedback::factory()->create([
            'rating' => 5,
            'description' => 'Excellent!',
            'feedback_time_submission' => $date_today->addSeconds(100)->timestamp,
            'counter_id' => $counter_one->id,
            'login_session_id' => $login_session_counter_one
        ]);

        $sale_two = Sale::factory()->create([
            'counter_user_id' => $counter_user,
            'branch_id' => $branch->id,
            'sale_time' => $date_today->addSeconds(15)->timestamp
        ]);

        $sale_three = Sale::factory()->create([
            'counter_user_id' => $counter_user,
            'branch_id' => $branch->id,
            'sale_time' => $date_today->addSeconds(30)->timestamp
        ]);

        $sale_four = Sale::factory()->create([
            'counter_user_id' => $counter_user,
            'branch_id' => $branch->id,
            'sale_time' => $date_today->addSeconds(120)->timestamp,
            // 'feedback_id' => $feedback_counter_one_4
        ]);

        SalesReceiptService::processSalesToFeedbacksAtEndOfDay();

        $sale_two->refresh();
        $this->assertTrue($sale_two->feedback->is($feedback_counter_one_3));

        $sale_three->refresh();
        $this->assertNull($sale_three->feedback);

        $sale_four->refresh();
        $this->assertTrue($sale_four->feedback->is($feedback_counter_one_4));
    }
}

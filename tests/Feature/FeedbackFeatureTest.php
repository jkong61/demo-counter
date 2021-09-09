<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Counter;
use App\Models\CounterUser;
use App\Models\Feedback;
use App\Models\LoginSession;
use App\Services\FeedbackProcessorService;
use App\Services\LoginProcessorService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class FeedbackFeatureTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_feedback_api_creation_if_no_counter_in_db()
    {
        $this->seedDB();

        $branch = Branch::first();

        $response = $this->post('/api/feedback',[
            'rating' => 4,
            'description' => 'Very good',
            'counter_number' => 1,
            'branch_name' => $branch->name
        ]);
        $response->assertSeeText(['Counter']);
        $response->assertStatus(422);
    }

    public function test_feedback_api_creation_with_missing_branch_in_db_but_correct_length()
    {
        $this->seedDB();

        $response = $this->post('/api/feedback',[
            'rating' => 4,
            'description' => 'Very good',
            'counter_number' => 1,
            'branch_name' => "HD"
        ]);

        // Should be caught in validator
        $response->assertSeeText(['branch','invalid']);
        $response->assertStatus(400);
    }

    public function test_feedback_api_creation_with_correct_counter_in_db()
    {
        $this->seedDB();

        $branch = Branch::first();

        Counter::factory(1)->create([
            'number' => 1,
            'branch_id' => $branch->id
        ]);

        $response = $this->post('/api/feedback',[
            'rating' => 4,
            'description' => 'Very good',
            'counter_number' => 1,
            'branch_name' => $branch->name
        ]);
        $response->assertStatus(200);
    }

    public function test_feedback_data_input_with_branch_name_less_than_2_chars()
    {
        $this->seedDB();

        $branch = Branch::first();
        Counter::factory()->create([
            'number' => 1,
            'branch_id' => $branch->id
        ]);

        // Technically tests a branch id that is not available in DB
        $response = $this->postJson('/api/feedback',[
            'rating' => 4,
            'description' => 'Very good',
            'counter_number' => 1,
            'branch_name' => "V"
        ]);

        // Should be caught in validator
        $response->assertStatus(400);
        $response->assertSeeText(['branch_name','least','2 characters']);
    }

    public function test_feedback_data_input_with_branch_name_more_than_2_chars()
    {
        $this->seedDB();

        $branch = Branch::first();
        Counter::factory()->create([
            'number' => 1,
            'branch_id' => $branch->id
        ]);

        // Technically tests a branch id that is not available in DB
        $response = $this->postJson('/api/feedback',[
            'rating' => 4,
            'description' => 'Very good',
            'counter_number' => 1,
            'branch_name' => "Veeeee"
        ]);

        // Should be caught in validator
        $response->assertStatus(400);
        $response->assertSeeText(['branch_name','greater','2 characters']);
    }

    public function test_feedback_with_null_login_session()
    {
        $this->seedDB();

        $date_today = CarbonImmutable::today();
        $branch = Branch::first();
        $counter = Counter::factory()->create([
            'number' => 1,
            'branch_id' => $branch->id
        ]);

        $feedback = Feedback::factory()->create([
            'rating' => 4,
            'description' => 'Excellent!',
            'feedback_time_submission' => $date_today->timestamp,
            'counter_id' => $counter->id
        ]);

        $this->assertNull($feedback->loginsession);

        // Login session should be outside the window of feedback above
        $login_session = LoginSession::factory()->create([
            'login_date_time' => $date_today->addMinutes(30)->timestamp,
            'logout_date_time' => $date_today->addMinutes(55)->timestamp,
            'counter_id' => $counter->id,
            'counter_user_id' => CounterUser::first(),
            'branch_id' => $branch
        ]);

        FeedbackProcessorService::ProcessNullSessionFeedbacks();

        $this->assertNull($feedback->loginsession);

        // Login session within the window of the feedback
        $login_session = LoginSession::factory()->create([
            'login_date_time' => $date_today->subMinutes(10)->timestamp,
            'logout_date_time' => $date_today->addMinutes(55)->timestamp,
            'counter_id' => $counter->id,
            'counter_user_id' => CounterUser::all()->random(),
            'branch_id' => $branch
        ]);

        $this->assertDatabaseCount('login_sessions', 2);

        FeedbackProcessorService::ProcessNullSessionFeedbacks();
        $feedback->refresh();
        $this->assertNotNull($feedback->loginsession);
        $this->assertEquals($login_session->id, $feedback->loginsession->id);
    }

    public function test_feedback_closest_login_time()
    {
        $this->seedDB();

        $date_today = CarbonImmutable::today();
        $branch = Branch::first();
        $counter = Counter::factory()->create([
            'number' => 1,
            'branch_id' => $branch->id
        ]);

        $feedback = Feedback::factory()->create([
            'rating' => 4,
            'description' => 'Excellent!',
            'feedback_time_submission' => $date_today->timestamp,
            'counter_id' => $counter->id
        ]);

        $login_session_closer = LoginSession::factory()->create([
            'login_date_time' => $date_today->subMinutes(10)->timestamp,
            'logout_date_time' => null,
            'counter_id' => $counter->id,
            'counter_user_id' => CounterUser::all()->random(),
            'branch_id' => $branch
        ]);

        $login_session_furthur = LoginSession::factory()->create([
            'login_date_time' => $date_today->subMinutes(30)->timestamp,
            'logout_date_time' => null,
            'counter_id' => $counter->id,
            'counter_user_id' => CounterUser::all()->random(),
            'branch_id' => $branch
        ]);

        $login_session_not_within_window = LoginSession::factory()->create([
            'login_date_time' => $date_today->addMinutes(30)->timestamp,
            'logout_date_time' => null,
            'counter_id' => $counter->id,
            'counter_user_id' => CounterUser::all()->random(),
            'branch_id' => $branch
        ]);

        $this->assertDatabaseCount('login_sessions', 3);

        FeedbackProcessorService::ProcessNullSessionFeedbacks();
        $feedback->refresh();

        $this->assertNotNull($feedback->loginsession);
        $this->assertNotEquals($login_session_not_within_window->id, $feedback->loginsession->id);
        $this->assertNotEquals($login_session_furthur->id, $feedback->loginsession->id);
        $this->assertEquals($login_session_closer->id, $feedback->loginsession->id);
    }

    public function test_multiple_feedback_login_time()
    {
        $this->seedDB();

        $date_today = CarbonImmutable::today();
        $branch = Branch::first();
        $counter_one = Counter::factory()->create([
            'number' => 1,
            'branch_id' => $branch->id
        ]);

        $counter_two = Counter::factory()->create([
            'number' => 2,
            'branch_id' => $branch->id
        ]);

        $feedback_counter_one_1 = Feedback::factory()->create([
            'rating' => 4,
            'description' => 'Excellent!',
            'feedback_time_submission' => $date_today->timestamp,
            'counter_id' => $counter_one->id
        ]);

        $feedback_counter_one_2 = Feedback::factory()->create([
            'rating' => 2,
            'description' => 'Not so good',
            'feedback_time_submission' => $date_today->addSeconds(10)->timestamp,
            'counter_id' => $counter_one->id
        ]);

        $feedback_counter_two_1 = Feedback::factory()->create([
            'rating' => 4,
            'description' => 'Excellent!',
            'feedback_time_submission' => $date_today->subSeconds(10)->timestamp,
            'counter_id' => $counter_two->id
        ]);

        $feedback_counter_two_2 = Feedback::factory()->create([
            'rating' => 2,
            'description' => 'Not so good',
            'feedback_time_submission' => $date_today->addSeconds(15)->timestamp,
            'counter_id' => $counter_two->id
        ]);

        $this->assertDatabaseCount('feedback', 4);

        $login_session_counter_one = LoginSession::factory()->create([
            'login_date_time' => $date_today->subMinutes(10)->timestamp,
            'logout_date_time' => $date_today->addMinutes(10)->timestamp,
            'counter_id' => $counter_one->id,
            'counter_user_id' => CounterUser::all()->random(),
            'branch_id' => $branch
        ]);

        FeedbackProcessorService::ProcessNullSessionFeedbacks();
        $feedback_counter_one_1->refresh();
        $feedback_counter_one_2->refresh();
        $feedback_counter_two_1->refresh();
        $feedback_counter_two_2->refresh();

        $this->assertNotNull($feedback_counter_one_1->loginsession);
        $this->assertNotNull($feedback_counter_one_2->loginsession);
        $this->assertNull($feedback_counter_two_1->loginsession);
        $this->assertNull($feedback_counter_two_2->loginsession);

        $this->assertEquals($login_session_counter_one->id, $feedback_counter_one_1->login_session_id);
        $this->assertEquals($login_session_counter_one->id, $feedback_counter_one_2->login_session_id);

    }

    public function test_feedback_submission_with_available_login_session()
    {
        $this->seedDB();

        $date_today = CarbonImmutable::now();
        $branch = Branch::first();
        $counter_one = Counter::factory()->create([
            'number' => 1,
            'branch_id' => $branch->id
        ]);

        $login_session_counter_one = LoginSession::factory()->create([
            'login_date_time' => $date_today->subMinutes(10)->timestamp,
            'logout_date_time' => $date_today->addMinutes(10)->timestamp,
            'counter_id' => $counter_one->id,
            'counter_user_id' => CounterUser::all()->random(),
            'branch_id' => $branch
        ]);

        $response = $this->post('/api/feedback',[
            'rating' => 4,
            'description' => 'Very good',
            'counter_number' => 1,
            'branch_name' => $branch->name
        ]);

        $response->assertStatus(200);
        $this->assertNotNull(Feedback::all()->first()->loginsession);
        $this->assertEquals($login_session_counter_one->counter_id, Feedback::all()->first()->counter_id);
        $this->assertEquals($login_session_counter_one->id, Feedback::all()->first()->login_session_id);
    }

    public function test_feedback_submission_with_no_available_login_session()
    {
        $this->seedDB();

        $branch = Branch::first();
        $counter_one = Counter::factory()->create([
            'number' => 1,
            'branch_id' => $branch->id
        ]);

        $branch = Branch::first();

        $response = $this->post('/api/feedback',[
            'rating' => 4,
            'description' => 'Very good',
            'counter_number' => 1,
            'branch_name' => $branch->name
        ]);

        $response->assertStatus(200);
        $this->assertNull(Feedback::all()->first()->login_session_id);
    }

    public function test_feedback_submission_where_login_session_is_out_of_range()
    {
        $this->seedDB();

        $date_today = CarbonImmutable::now();
        $branch = Branch::first();
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

        $response = $this->post('/api/feedback',[
            'rating' => 4,
            'description' => 'Very good',
            'counter_number' => 1,
            'branch_name' => $branch->name
        ]);

        $response->assertStatus(200);
        $this->assertNull(Feedback::all()->first()->loginsession);
    }

    public function test_feedback_submission_where_login_session_of_non_existing_counter_user()
    {
        $this->seedDB();

        $date_today = CarbonImmutable::now();
        $branch = Branch::first();
        $counter_one = Counter::factory()->create([
            'number' => 1,
            'branch_id' => $branch->id
        ]);

        $expected_temp_number = 200;
        $login_session_counter_one = LoginSession::factory()->create([
            'login_date_time' => $date_today->subMinutes(10)->timestamp,
            'logout_date_time' => $date_today->addMinutes(10)->timestamp,
            'counter_id' => $counter_one->id,
            'counter_user_id' => null,
            'branch_id' => $branch,
            'temp_counter_user_number' => $expected_temp_number
        ]);

        $response = $this->post('/api/feedback',[
            'rating' => 4,
            'description' => 'Very good',
            'counter_number' => $counter_one->number,
            'branch_name' => $branch->name
        ]);

        $response->assertStatus(200);
        $feedback = Feedback::first();

        $this->assertTrue($feedback->loginsession->is($login_session_counter_one));
        $this->assertNull($feedback->loginsession->counter_user_id);
        $this->assertEquals($expected_temp_number ,$feedback->loginsession->temp_counter_user_number);

        $counter_user = CounterUser::factory(1)->create(
            ['number' => $expected_temp_number, 'branch_id' => $branch]
        )->first();
        LoginProcessorService::remapTemporaryCounterUserNumbersToIds();
        
        $feedback->refresh();
        $this->assertNotNull($feedback->loginsession->counter_user_id);
        $this->assertNull($feedback->loginsession->temp_counter_user_number);
        $this->assertEquals($counter_user->id ,$feedback->loginsession->counter_user_id);

    }
}

<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Counter;
use App\Models\CounterUser;
use App\Models\LoginSession;
use App\Services\LoginProcessorService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class LoginSessionFeatureTest extends TestCase
{
    use WithoutMiddleware;

    private $login_session_table_name = "login_sessions";
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_login_session_not_current_date()
    {
        $this->seedDB();

        $response = $this->post("/api/loginsession", [
            "data" => "[{\"USER\":\"101\",\"DATE\":\"2021-07-31T00:00:00\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"001\"}]",
            "branch" => "FM"
        ]);
        $response->assertStatus(204);
        $this->assertDatabaseCount($this->login_session_table_name, 0);
    }

    public function test_login_session_creation_with_current_date()
    {
        $this->seedDB();

        $date_today = Carbon::today()->startOfDay();
        $date_string = $date_today->toIso8601String();
        $response = $this->post("/api/loginsession", [
            "data" => "[{\"USER\":\"101\",\"DATE\":\"$date_string\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"001\"}]",
            "branch" => "FM"
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount($this->login_session_table_name, 1);

        $response = $this->post("/api/loginsession", [
            "data" => "[{\"USER\":\"101\",\"DATE\":\"$date_string\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":\"21:48:04\",\"COUNTER\":\"001\"}]",
            "branch" => "FM"
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseCount($this->login_session_table_name, 1);

        $response = $this->post("/api/loginsession", [
            "data" => "[
                {\"USER\":\"101\",\"DATE\":\"$date_string\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":\"21:48:04\",\"COUNTER\":\"001\"},
                {\"USER\":\"102\",\"DATE\":\"$date_string\",\"TIMEIN\":\"20:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"002\"}
            ]",
            "branch" => "FM"
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseCount($this->login_session_table_name, 2);

        $response = $this->post("/api/loginsession", [
            "data" => "[
                {\"USER\":\"101\",\"DATE\":\"$date_string\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":\"21:48:04\",\"COUNTER\":\"001\"},
                {\"USER\":\"102\",\"DATE\":\"$date_string\",\"TIMEIN\":\"20:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"002\"},
                {\"USER\":\"103\",\"DATE\":\"$date_string\",\"TIMEIN\":\"21:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"003\"}
            ]",
            "branch" => "FM"
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseCount($this->login_session_table_name, 3);
    }

    public function test_insertion_login_session_from_different_branch()
    {
        $this->seedDB();

        $date_today = Carbon::today()->startOfDay();
        $date_string = $date_today->toIso8601String();
        $response = $this->post("/api/loginsession", [
            "data" => "[
                {\"USER\":\"101\",\"DATE\":\"$date_string\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":\"21:48:04\",\"COUNTER\":\"001\"},
                {\"USER\":\"102\",\"DATE\":\"$date_string\",\"TIMEIN\":\"20:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"002\"},
                {\"USER\":\"103\",\"DATE\":\"$date_string\",\"TIMEIN\":\"21:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"003\"}
            ]",
            "branch" => "FM"
        ]);
        $response->assertStatus(200);

        $this->seedDB("MF");
        $response = $this->post("/api/loginsession", [
            "data" => "[
                {\"USER\":\"101\",\"DATE\":\"$date_string\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":\"21:48:04\",\"COUNTER\":\"001\"},
                {\"USER\":\"102\",\"DATE\":\"$date_string\",\"TIMEIN\":\"20:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"002\"},
                {\"USER\":\"103\",\"DATE\":\"$date_string\",\"TIMEIN\":\"21:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"003\"}
            ]",
            "branch" => "MF"
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseCount($this->login_session_table_name, 6);
        $this->assertDatabaseCount('counters', 6);
    }

    public function test_insertion_login_session_where_counter_did_not_exist()
    {
        $this->seedDB();

        $date_today = Carbon::today()->startOfDay();
        $date_string = $date_today->toIso8601String();
        $response = $this->post("/api/loginsession", [
            "data" => "[
                {\"USER\":\"101\",\"DATE\":\"$date_string\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":\"21:48:04\",\"COUNTER\":\"001\"},
                {\"USER\":\"102\",\"DATE\":\"$date_string\",\"TIMEIN\":\"20:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"002\"},
                {\"USER\":\"103\",\"DATE\":\"$date_string\",\"TIMEIN\":\"21:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"003\"}
            ]",
            "branch" => "FM"
        ]);
        $response->assertStatus(200);

        /**
         * Edge case where counter does not exist (not created), and will create 3 more of the same counter due to a bug in 
         * LoginProcessorService collection instance not refreshed after adding new counter
         */
        $this->seedDB("MF");
        $response = $this->post("/api/loginsession", [
            "data" => "[
                {\"USER\":\"101\",\"DATE\":\"$date_string\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":\"21:48:04\",\"COUNTER\":\"001\"},
                {\"USER\":\"102\",\"DATE\":\"$date_string\",\"TIMEIN\":\"20:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"001\"},
                {\"USER\":\"103\",\"DATE\":\"$date_string\",\"TIMEIN\":\"21:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"001\"}
            ]",
            "branch" => "MF"
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseCount('counters', 4);
    }

    public function test_insertion_login_session_where_user_wrong_format_should_fail()
    {
        $this->seedDB();

        $date_today = Carbon::today()->startOfDay();
        $date_string = $date_today->toIso8601String();

        // User ID should only numerics
        $response = $this->post("/api/loginsession", [
            "data" => "[
                {\"USER\":\"abc1234\",\"DATE\":\"$date_string\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":\"21:48:04\",\"COUNTER\":\"001\"}
            ]",
            "branch" => "FM"
        ]);

        $response->assertStatus(400);
    }

    public function test_insertion_login_session_where_counter_wrong_format_should_fail()
    {
        $this->seedDB();

        $date_today = Carbon::today()->startOfDay();
        $date_string = $date_today->toIso8601String();

        // Counter number should only numerics
        $response = $this->post("/api/loginsession", [
            "data" => "[
                {\"USER\":\"102\",\"DATE\":\"$date_string\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":\"21:48:04\",\"COUNTER\":\"abc123\"}
            ]",
            "branch" => "FM"
        ]);

        $response->assertStatus(400);
    }

    public function test_insertion_login_session_where_user_does_not_exist_should_temporary_store_number()
    {
        $this->seedDB();

        $date_today = Carbon::today()->startOfDay();
        $date_string = $date_today->toIso8601String();

        // User ID 9999999 does not exist
        $response = $this->post("/api/loginsession", [
            "data" => "[
                {\"USER\":\"9999999\",\"DATE\":\"$date_string\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":\"21:48:04\",\"COUNTER\":\"001\"}
            ]",
            "branch" => "FM"
        ]);

        // Technically should go through, no errors are expected to pop up
        $response->assertStatus(200);

        // Check for the login session data
        $login_session = LoginSession::first();

        $this->assertNull($login_session->counter_user_id);
        $this->assertEquals(9999999 ,$login_session->temp_counter_user_number);
    }

    public function test_login_session_remap_function()
    {
        $this->seedDB();

        $date_today = CarbonImmutable::today();

        $branch = Branch::first();
        $counter = Counter::factory()->create([
            'number' => 1,
            'branch_id' => $branch->id
        ]);

        $random_counter_user = CounterUser::all()->random();

        $login_session = LoginSession::factory()->create([
            'login_date_time' => $date_today->subMinutes(10)->timestamp,
            'logout_date_time' => $date_today->addMinutes(10)->timestamp,
            'counter_id' => $counter->id,
            'counter_user_id' => null,
            'branch_id' => $branch,
            'temp_counter_user_number' => $random_counter_user->number
        ]);

        $login_session->remapTemporaryCounterUser();
        $login_session->save();

        $this->assertEquals($random_counter_user->id, $login_session->counter_user_id);
        $this->assertNull($login_session->temp_counter_user_number);
    }

    public function test_insertion_login_session_where_counter_user_added_later()
    {
        $this->seedDB();

        $date_today = Carbon::today()->startOfDay();
        $date_string = $date_today->toIso8601String();
        $branch = Branch::first();

        // User ID 105 does not exist yet
        $this->post("/api/loginsession", [
            "data" => "[
                {\"USER\":\"105\",\"DATE\":\"$date_string\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":\"21:48:04\",\"COUNTER\":\"001\"}
            ]",
            "branch" => "FM"
        ]);

        $login_session = LoginSession::first();
        $this->assertNull($login_session->counter_user_id);
        $this->assertEquals(105 ,$login_session->temp_counter_user_number);

        $counter_user = CounterUser::factory(1)->create(
            ['number' => 105, 'branch_id' => $branch]
        )->first();
        LoginProcessorService::remapTemporaryCounterUserNumbersToIds();
        
        $login_session->refresh();
        $this->assertNull($login_session->temp_counter_user_number);
        $this->assertNotNull($login_session->counter_user_id);
        $this->assertEquals($counter_user->id ,$login_session->counter_user_id);
    }

}

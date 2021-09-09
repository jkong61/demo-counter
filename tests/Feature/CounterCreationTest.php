<?php

namespace Tests\Feature;

use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class CounterCreationTest extends TestCase
{
    use WithoutMiddleware;

    private $counter_table_name = "counters";

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_counter_not_created_because_login_session_not_current_date()
    {
        $this->post("/api/loginsession", [
            "data" => "[{\"USER\":\"101\",\"DATE\":\"2021-07-31T00:00:00\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"001\"}]",
            "branch" => "FM"
        ]);
        $this->assertDatabaseCount($this->counter_table_name, 0);
    }

    public function test_private_seeding_db()
    {
        $this->seedDB();

        $this->assertDatabaseCount('branches', 1);
        $this->assertDatabaseCount('counter_users', 3);
    }

    public function test_counter_created_because_login_correct_today_date()
    {
        $branch_name = "FM";
        $this->seedDB();

        $date_today = Carbon::today()->startOfDay();
        $date_string = $date_today->toIso8601String();
        $response = $this->post("/api/loginsession", [
            "data" => "[{\"USER\":\"101\",\"DATE\":\"$date_string\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"001\"}]",
            "branch" => $branch_name
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount($this->counter_table_name, 1);

        // Asserts that there is a counter with number 1
        $this->assertDatabaseHas($this->counter_table_name, [
            "number" => 1,
            "branch_id" => Branch::first()->id
        ]);
    }

    public function test_counter_created_with_multiple_json()
    {
        $branch_name = "FM";
        $this->seedDB();

        $date_today = Carbon::today()->startOfDay();
        $date_string = $date_today->toIso8601String();
        $response = $this->postJson("/api/loginsession", [
            "data" => "[
                {\"USER\":\"101\",\"DATE\":\"$date_string\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"001\"},
                {\"USER\":\"102\",\"DATE\":\"$date_string\",\"TIMEIN\":\"20:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"002\"},
                {\"USER\":\"103\",\"DATE\":\"$date_string\",\"TIMEIN\":\"21:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"003\"}
            ]",
            "branch" => $branch_name
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount($this->counter_table_name, 3);

        $response = $this->postJson("/api/loginsession", [
            "data" => "[{\"USER\":\"101\",\"DATE\":\"$date_string\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":\"21:48:04\",\"COUNTER\":\"001\"}]",
            "branch" => $branch_name
        ]);
        $response->assertStatus(200);
        $this->assertDatabaseCount($this->counter_table_name, 3);
    }
}

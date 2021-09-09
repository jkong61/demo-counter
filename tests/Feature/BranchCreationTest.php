<?php

namespace Tests\Feature;

use App\Models\Branch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class BranchCreationTest extends TestCase
{
    use WithoutMiddleware;

    private $branch_table_name = "branches";
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_branch_creation_error_422_from_api_where_data_field_empty()
    {
        $this->assertDatabaseCount($this->branch_table_name, 0);

        $response = $this->postJson("/api/loginsession", [
            "data" => "",
            "branch" => "FM"
        ]);

        $response->assertStatus(422);
    }

    public function test_branch_creation_where_data_field_correct_format() 
    {
        $this->assertDatabaseCount($this->branch_table_name, 0);

        $this->post("/api/loginsession", [
            "data" => "[{\"USER\":\"101\",\"DATE\":\"2021-07-31T00:00:00\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"001\"}]",
            "branch" => "FM"
        ]);

        $this->assertDatabaseCount($this->branch_table_name, 1);

        $this->assertDatabaseHas($this->branch_table_name,['name' => "FM"]);
    }

    public function test_branch_not_created_if_branch_exists_in_db()
    {
        $branch_name = "FM";
        Branch::factory()->create([
            "name" => $branch_name
        ]);

        $this->assertDatabaseCount($this->branch_table_name, 1);

        $this->post("/api/loginsession", [
            "data" => "[{\"USER\":\"101\",\"DATE\":\"2021-07-31T00:00:00\",\"TIMEIN\":\"19:48:04\",\"TIMEOUT\":null,\"COUNTER\":\"001\"}]",
            "branch" => $branch_name
        ]);

        $this->assertDatabaseCount($this->branch_table_name, 1);
        $this->assertDatabaseHas($this->branch_table_name, [
            "name" => $branch_name,
        ]);
    }
}

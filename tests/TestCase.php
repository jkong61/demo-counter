<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Database\Eloquent\Factories\Sequence;
use App\Models\Branch;
use App\Models\CounterUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTransactions;
    // use RefreshDatabase;

    protected function seedDB(string $branch_name = "FM")
    {
        $branch = Branch::factory()->create([
            "name" => $branch_name
        ]);
        CounterUser::factory()->count(3)->state(new Sequence(
            ['number' => 101, 'branch_id' => $branch],
            ['number' => 102, 'branch_id' => $branch],
            ['number' => 103, 'branch_id' => $branch],
        ))->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Branch::factory()->count(6)->state(new Sequence(
            ['name' => 'FM'],
            ['name' => 'MA'],
            ['name' => 'KN'],
            ['name' => 'BP'],
            ['name' => 'MP'],
            ['name' => 'TN']
        ))->create();
    }
}

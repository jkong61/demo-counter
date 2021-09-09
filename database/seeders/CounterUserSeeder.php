<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CounterUser;


class CounterUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        CounterUser::factory()->count(201)->create();
    }
}

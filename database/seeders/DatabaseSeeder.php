<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory(1)->create();
        if (App::environment('local')) {
            $this->call([
                BranchSeeder::class,
                CounterUserSeeder::class,
                CounterSeeder::class,
                LoginSessionSeeder::class,
                FeedbackSeeder::class
            ]);
        } else {
            $this->call([
                BranchSeeder::class,
            ]);
        }
    }
}

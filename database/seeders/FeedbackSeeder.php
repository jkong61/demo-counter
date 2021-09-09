<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\LoginSession;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Feedback::factory(20)->state(new Sequence(
            fn ($sequence) => ['login_session_id' => LoginSession::all()->random()],
        ))->create();
    }
}

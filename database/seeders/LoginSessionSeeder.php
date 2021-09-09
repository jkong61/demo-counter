<?php

namespace Database\Seeders;

use App\Models\LoginSession;
use Illuminate\Database\Seeder;

class LoginSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        LoginSession::factory()->count(15)->create();
    }
}

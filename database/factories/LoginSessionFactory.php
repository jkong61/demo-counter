<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\LoginSession;
use App\Models\CounterUser;
use App\Models\Counter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\CarbonImmutable;

class LoginSessionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LoginSession::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $required_date = CarbonImmutable::today()->startOfDay();
        $nextday_date = $required_date->endOfDay();
        $supposed_login_time = $this->faker->numberBetween($min = $required_date->timestamp, $max = $nextday_date->timestamp);
        $supposed_logout_time = $supposed_login_time + $this->faker->numberBetween($min = 1000, $max = 9000);
        
        return [
            'login_date_time' => $supposed_login_time,
            'logout_date_time' => (bool)random_int(0, 1) ? $supposed_logout_time : null,
            'counter_id' => Counter::inRandomOrder()->get()->random(),
            'counter_user_id' => CounterUser::inRandomOrder()->get()->random(),
            'branch_id' => Branch::first()
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\Counter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedbackFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Feedback::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $counter = Counter::inRandomOrder()->get()->random();

        return [
            //
            'description' => $this->faker->sentence(30),
            'rating' => $this->faker->numberBetween($min = 1, $max = 5),
            'feedback_time_submission' => Carbon::now()->timestamp,
            'login_session_id' => null,
            'counter_id' => $counter->id
        ];
    }
}

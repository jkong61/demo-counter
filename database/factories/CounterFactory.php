<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Counter;
use Illuminate\Database\Eloquent\Factories\Factory;

class CounterFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Counter::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
            'number' => $this->faker->unique->numberBetween(1, 12),
            'branch_id' => Branch::first()
        ];
    }
}

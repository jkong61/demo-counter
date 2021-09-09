<?php

namespace Database\Factories;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Sale::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'receipt_number' => $this->faker->numberBetween($min = 1000000, $max = 200000),
            'sale_time' => Carbon::now()->timestamp,
        ];
    }
}

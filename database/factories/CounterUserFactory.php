<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\CounterUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class CounterUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CounterUser::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
            'id' => $this->faker->unique()->numberBetween($min = 200, $max = 400),
            'name' => $this->faker->name(),
            'sname' => $this->faker->firstName(),
            'position' => $this->faker->randomElement([
                'Cashier',
                'Manager',
                'Inventory',
                'Accounts',
                'Warehouse'
            ]),
            'number' => $this->faker->numberBetween($min = 100, $max = 1000), 
            'branch_id' => Branch::first()
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bill>
 */
class StatementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'accountId' => fake()->randomNumber(5, true),
            'visitId' => fake()->randomNumber(5, true),
            'accountClass' => \Str::random(10),
            'attendingPhysician' => fake()->name(),
            'serviceDate' => fake()->date(),

            'totalCharges' => fake()->randomFloat(2, 20, 100000),
            'totalPayments' => fake()->randomFloat(2, 20, 100000),
            'totalBalance' => fake()->randomFloat(2, 20, 100000),

            'totalPages' => 1,
        ];
    }
}

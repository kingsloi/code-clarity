<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Charge>
 */
class ChargeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'pageId' => \App\Models\Page::all()->random()->id,

            'date' => fake()->date(),
            'revCode' => fake()->randomNumber(5, true),
            'procedureCode' => fake()->randomNumber(5, true),
            'description' => fake()->words(3, true),
            'qty' => fake()->randomDigit(),
            'amount' => fake()->randomFloat(2, 20, 100000)
        ];
    }
}

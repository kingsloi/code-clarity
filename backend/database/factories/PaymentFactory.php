<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
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
            'description' => fake()->words(3, true),
            'amount' => fake()->randomFloat(2, 20, 100000)
        ];
    }
}

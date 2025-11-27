<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CopyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'barcode' => 'BC-' . $this->faker->unique()->ean13(),
            'status' => $this->faker->randomElement(['available', 'borrowed', 'damaged', 'lost']),
            'condition' => $this->faker->randomElement(['excellent', 'good', 'fair', 'poor']),
        ];
    }
}

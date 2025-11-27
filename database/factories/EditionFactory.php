<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class EditionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'isbn' => $this->faker->isbn13(),
            'publication_date' => $this->faker->dateTime(),
            'publisher' => $this->faker->company(),
            'language' => $this->faker->randomElement(['en', 'es', 'fr', 'de', 'it']),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'isbn' => $this->faker->isbn13(),
            'description' => $this->faker->paragraphs(2, true),
            'publication_year' => $this->faker->year(),
            'authors' => [
                $this->faker->name(),
                $this->faker->name(),
            ],
        ];
    }
}

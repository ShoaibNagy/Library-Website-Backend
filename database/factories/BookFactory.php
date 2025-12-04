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
            'author_id' => \App\Models\Author::factory(),
            'category_id' => \App\Models\Category::factory(),
            'total_copies' => 5,
            'available_copies' => 5,
            'publication_year' => $this->faker->year(),
            'description' => $this->faker->paragraph(),
        ];
    }
}

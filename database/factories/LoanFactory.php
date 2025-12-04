<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    public function definition(): array
    {
        $checkoutDate = $this->faker->dateTimeBetween('-30 days', 'now');
        $dueDate = (clone $checkoutDate)->modify('+14 days');

        return [
            'user_id' => \App\Models\User::factory(),
            'book_id' => \App\Models\Book::factory(),
            'borrowed_at' => $checkoutDate,
            'due_date' => $dueDate,
            'returned_at' => $this->faker->optional(0.7)->dateTimeBetween($checkoutDate, 'now'),
            'status' => $this->faker->randomElement(['borrowed', 'returned', 'overdue']),
        ];
    }
}

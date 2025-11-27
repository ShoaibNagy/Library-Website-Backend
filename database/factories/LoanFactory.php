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
            'checkout_date' => $checkoutDate,
            'due_date' => $dueDate,
            'return_date' => $this->faker->optional(0.7)->dateTimeBetween($checkoutDate, 'now'),
            'status' => $this->faker->randomElement(['active', 'returned', 'overdue']),
        ];
    }
}

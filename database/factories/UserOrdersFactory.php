<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserOrders>
 */
class UserOrdersFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'time_service' => $this->faker->time('H:i'),
            'date_service' => $this->faker->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'rejected']),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }
}

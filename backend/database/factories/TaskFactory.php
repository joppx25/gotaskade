<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'description' => fake()->sentence(),
            'is_completed' => false,
            'task_date' => now()->format('Y-m-d'),
            'sort_order' => 0,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'is_completed' => true,
        ]);
    }

    public function forDate(string $date): static
    {
        return $this->state(fn () => [
            'task_date' => $date,
        ]);
    }
}

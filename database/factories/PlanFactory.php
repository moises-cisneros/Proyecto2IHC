<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->sentence(3),
            'organizador' => $this->faker->name(),
            'fecha_limite' => now()->addDays(rand(1, 30)),
            'estado' => 'pendiente',
            'user_id' => User::factory(),
        ];
    }

    public function confirmado(): static
    {
        return $this->state(['estado' => 'confirmado']);
    }

    public function cancelado(): static
    {
        return $this->state(['estado' => 'cancelado']);
    }

    public function vencido(): static
    {
        return $this->state(['fecha_limite' => now()->subDays(rand(1, 30))]);
    }
}

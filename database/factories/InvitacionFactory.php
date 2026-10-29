<?php

namespace Database\Factories;

use App\Models\Invitacion;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invitacion>
 */
class InvitacionFactory extends Factory
{
    protected $model = Invitacion::class;

    public function definition(): array
    {
        return [
            'plan_id' => Plan::factory(),
            'email' => $this->faker->unique()->safeEmail(),
            'token' => Str::random(40),
            'estado' => 'pendiente',
            'user_id' => null,
        ];
    }

    public function aceptada(?User $user = null): static
    {
        return $this->state(fn () => [
            'estado' => 'aceptada',
            'user_id' => $user?->id ?? User::factory(),
        ]);
    }

    public function pendiente(): static
    {
        return $this->state(['estado' => 'pendiente']);
    }

    public function rechazada(): static
    {
        return $this->state(['estado' => 'rechazada']);
    }
}

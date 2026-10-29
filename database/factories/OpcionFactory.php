<?php

namespace Database\Factories;

use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Opcion>
 */
class OpcionFactory extends Factory
{
    protected $model = Opcion::class;

    public function definition(): array
    {
        $tipo = $this->faker->randomElement(['fecha', 'lugar']);

        return [
            'plan_id' => Plan::factory(),
            'user_id' => User::factory(),
            'tipo' => $tipo,
            'valor' => $tipo === 'fecha'
                ? now()->addDays(rand(1, 30))->format('Y-m-d')
                : $this->faker->city(),
            'descripcion' => $this->faker->optional()->sentence(),
        ];
    }

    public function fecha(?string $fecha = null): static
    {
        return $this->state(fn () => [
            'tipo' => 'fecha',
            'valor' => $fecha ?? now()->addDays(7)->format('Y-m-d'),
        ]);
    }

    public function lugar(?string $lugar = null): static
    {
        return $this->state(fn () => [
            'tipo' => 'lugar',
            'valor' => $lugar ?? 'Playa El Agua',
        ]);
    }
}

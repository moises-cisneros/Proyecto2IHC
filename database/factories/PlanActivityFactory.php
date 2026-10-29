<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\PlanActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlanActivity>
 */
class PlanActivityFactory extends Factory
{
    protected $model = PlanActivity::class;

    public function definition(): array
    {
        return [
            'plan_id' => Plan::factory(),
            'user_id' => User::factory(),
            'accion' => $this->faker->randomElement(['creacion', 'actualizacion', 'cambio_estado']),
            'descripcion' => $this->faker->sentence(),
            'estado_resultante' => $this->faker->randomElement(['pendiente', 'confirmado', 'cancelado']),
        ];
    }
}

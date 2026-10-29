<?php

namespace Database\Factories;

use App\Models\Opcion;
use App\Models\User;
use App\Models\Voto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voto>
 */
class VotoFactory extends Factory
{
    protected $model = Voto::class;

    public function definition(): array
    {
        return [
            'opcion_id' => Opcion::factory(),
            'user_id' => User::factory(),
        ];
    }
}

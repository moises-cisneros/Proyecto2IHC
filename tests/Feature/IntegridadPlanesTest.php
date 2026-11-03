<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegridadPlanesTest extends TestCase
{
    use RefreshDatabase;

    public function test_relacion_user_planes_retorna_planes_creados(): void
    {
        $usuario = User::factory()->create();

        $plan1 = Plan::create([
            'nombre' => 'Cena de equipo',
            'organizador' => $usuario->name,
            'fecha_limite' => now()->addDays(5)->toDateString(),
            'user_id' => $usuario->id,
            'estado' => 'pendiente',
        ]);

        $plan2 = Plan::create([
            'nombre' => 'Paseo dominical',
            'organizador' => $usuario->name,
            'fecha_limite' => now()->addDays(8)->toDateString(),
            'user_id' => $usuario->id,
            'estado' => 'pendiente',
        ]);

        $this->assertCount(2, $usuario->planes);
        $this->assertTrue($usuario->planes->contains($plan1));
        $this->assertTrue($usuario->planes->contains($plan2));
    }

    public function test_actualizacion_plan_mantiene_organizador_original_inmutable(): void
    {
        $creador = User::factory()->create(['name' => 'Organizador Original']);
        $plan = Plan::create([
            'nombre' => 'Reunión de Diseño',
            'organizador' => $creador->name,
            'fecha_limite' => now()->addDays(3)->toDateString(),
            'user_id' => $creador->id,
            'estado' => 'pendiente',
        ]);

        $respuesta = $this->actingAs($creador)->put(route('planes.actualizar', $plan), [
            'nombre' => 'Reunión de Diseño Actualizada',
            'organizador' => 'Suplantador Ilegal',
            'fecha_limite' => now()->addDays(6)->toDateString(),
        ]);

        $respuesta->assertRedirect(route('planes.listar'));
        $plan->refresh();

        $this->assertEquals('Reunión de Diseño Actualizada', $plan->nombre);
        $this->assertEquals('Organizador Original', $plan->organizador);
    }

    public function test_plan_cancelado_no_puede_reactivarse_a_pendiente_ni_confirmado(): void
    {
        $creador = User::factory()->create();
        $plan = Plan::create([
            'nombre' => 'Plan Cancelado',
            'organizador' => $creador->name,
            'fecha_limite' => now()->addDays(2)->toDateString(),
            'user_id' => $creador->id,
            'estado' => 'cancelado',
        ]);

        // Intento hacia pendiente
        $resPendiente = $this->actingAs($creador)->put(route('planes.estado', $plan), [
            'estado' => 'pendiente',
        ]);
        $resPendiente->assertSessionHas('error');
        $this->assertEquals('cancelado', $plan->fresh()->estado);

        // Intento hacia confirmado
        $resConfirmado = $this->actingAs($creador)->put(route('planes.estado', $plan), [
            'estado' => 'confirmado',
        ]);
        $resConfirmado->assertSessionHas('error');
        $this->assertEquals('cancelado', $plan->fresh()->estado);
    }

    public function test_usuario_ajeno_no_puede_actualizar_plan_403(): void
    {
        $creador = User::factory()->create();
        $intruso = User::factory()->create();

        $plan = Plan::create([
            'nombre' => 'Plan Privado',
            'organizador' => $creador->name,
            'fecha_limite' => now()->addDays(5)->toDateString(),
            'user_id' => $creador->id,
            'estado' => 'pendiente',
        ]);

        $respuesta = $this->actingAs($intruso)->put(route('planes.actualizar', $plan), [
            'nombre' => 'Modificación No Autorizada',
            'fecha_limite' => now()->addDays(10)->toDateString(),
        ]);

        $respuesta->assertForbidden();
        $this->assertEquals('Plan Privado', $plan->fresh()->nombre);
    }
}

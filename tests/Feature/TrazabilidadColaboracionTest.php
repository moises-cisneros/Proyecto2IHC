<?php

namespace Tests\Feature;

use App\Models\Opcion;
use App\Models\Plan;
use App\Models\PlanActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrazabilidadColaboracionTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitacion_registra_actividad_en_historial(): void
    {
        $creador = User::factory()->create();
        $plan = Plan::create([
            'nombre' => 'Cena Aniversario',
            'organizador' => $creador->name,
            'fecha_limite' => now()->addDays(4)->toDateString(),
            'user_id' => $creador->id,
            'estado' => 'pendiente',
        ]);

        $respuesta = $this->actingAs($creador)->post(route('planes.invitar', $plan), [
            'email' => 'invitado@planazo.test',
        ]);

        $respuesta->assertRedirect();
        $this->assertDatabaseHas('plan_activities', [
            'plan_id' => $plan->id,
            'user_id' => $creador->id,
            'accion' => 'invitacion',
            'descripcion' => 'Invitación enviada a invitado@planazo.test',
        ]);
    }

    public function test_propuesta_de_opcion_registra_actividad_en_historial(): void
    {
        $creador = User::factory()->create(['name' => 'Carlos']);
        $plan = Plan::create([
            'nombre' => 'Viaje Playa',
            'organizador' => $creador->name,
            'fecha_limite' => now()->addDays(7)->toDateString(),
            'user_id' => $creador->id,
            'estado' => 'pendiente',
        ]);

        $respuesta = $this->actingAs($creador)->post(route('planes.opciones.guardar', $plan), [
            'tipo' => 'lugar',
            'valor' => 'Playa Blanca',
            'descripcion' => 'Hermosa playa al sur',
        ]);

        $respuesta->assertRedirect();
        $this->assertDatabaseHas('plan_activities', [
            'plan_id' => $plan->id,
            'user_id' => $creador->id,
            'accion' => 'propuesta',
            'descripcion' => 'Carlos propuso una nueva opción de lugar: Playa Blanca',
        ]);
    }

    public function test_voto_registra_actividad_en_historial(): void
    {
        $creador = User::factory()->create(['name' => 'Lucía']);
        $plan = Plan::create([
            'nombre' => 'Asado Familiar',
            'organizador' => $creador->name,
            'fecha_limite' => now()->addDays(5)->toDateString(),
            'user_id' => $creador->id,
            'estado' => 'pendiente',
        ]);

        $opcion = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $creador->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-01',
        ]);

        $respuesta = $this->actingAs($creador)->post(route('opciones.votar', $opcion));

        $respuesta->assertRedirect();
        $this->assertDatabaseHas('plan_activities', [
            'plan_id' => $plan->id,
            'user_id' => $creador->id,
            'accion' => 'voto',
            'descripcion' => 'Lucía votó por la opción 2026-12-01',
        ]);
    }

    public function test_usuario_no_autenticado_es_redirigido_al_intentar_ver_historial_302(): void
    {
        $respuesta = $this->get(route('historial.index'));
        $respuesta->assertRedirect(route('login'));
    }
}

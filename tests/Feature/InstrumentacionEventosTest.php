<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstrumentacionEventosTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: Test de confirmación de plan disparando y persistiendo el evento plan_confirmado.
     */
    public function test_confirmacion_de_plan_dispara_y_persiste_evento_plan_confirmado(): void
    {
        $organizador = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
            'fecha_limite' => now()->addDays(3),
        ]);

        $opcionFecha = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-20T20:00',
        ]);

        $response = $this->actingAs($organizador)->post(route('planes.confirmar', $plan), [
            'propuesta_fecha_id' => $opcionFecha->id,
            'propuesta_lugar_id' => null,
            'motivo_desempate' => null,
        ]);

        $response->assertRedirect(route('planes.detalle', $plan));

        $this->assertDatabaseHas('eventos', [
            'evento' => 'plan_confirmado',
        ]);
    }

    /**
     * Caso 2: Test de emisión de voto disparando y persistiendo voto_emitido.
     */
    public function test_emision_de_voto_dispara_y_persiste_evento_voto_emitido(): void
    {
        $organizador = User::factory()->create();
        $votante = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
            'fecha_limite' => now()->addDays(3),
        ]);

        // Agregar al votante como participante
        $plan->invitaciones()->create([
            'email' => $votante->email,
            'user_id' => $votante->id,
            'estado' => 'aceptada',
            'token' => 'token_de_prueba_123',
        ]);

        $opcion = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'lugar',
            'valor' => 'Cafetería Central',
        ]);

        $response = $this->actingAs($votante)->post(route('opciones.votar', $opcion));
        $response->assertStatus(302);

        $this->assertDatabaseHas('eventos', [
            'evento' => 'voto_emitido',
        ]);

        $evento = Evento::where('evento', 'voto_emitido')->first();
        $this->assertNotNull($evento);
        $this->assertEquals($opcion->id, $evento->props['opcion_id']);
        $this->assertEquals('lugar', $evento->props['tipo']);
    }

    /**
     * Caso 3: Test de acceso exitoso (200 OK) a /analitica en entorno local/testing.
     */
    public function test_acceso_exitoso_a_analitica_en_entorno_local_o_testing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('analitica.index'));

        $response->assertStatus(200);
        $response->assertViewIs('analitica.index');
        $response->assertViewHas('metricas');
        $response->assertViewHas('ultimosEventos');
    }

    /**
     * Caso 4 (Seguridad): Test negativo verificando que /analitica arroja 403 Forbidden en entorno de producción simulado.
     */
    public function test_acceso_denegado_403_a_analitica_en_entorno_de_produccion(): void
    {
        $user = User::factory()->create();

        // Simular entorno de producción
        $this->app['env'] = 'production';

        $response = $this->actingAs($user)->get(route('analitica.index'));

        $response->assertStatus(403);
    }
}

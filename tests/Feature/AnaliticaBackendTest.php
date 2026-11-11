<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use App\Models\Voto;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnaliticaBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_no_autenticado_es_redirigido_al_intentar_registrar_evento(): void
    {
        $response = $this->postJson(route('eventos.guardar'), [
            'evento' => 'plan_creacion_iniciada',
            'props' => ['seccion' => 'creados'],
        ]);

        $response->assertStatus(401);
    }

    public function test_se_puede_registrar_evento_plan_creacion_iniciada_con_props(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('eventos.guardar'), [
            'evento' => 'plan_creacion_iniciada',
            'props' => [
                'desde_seccion' => 'creados',
                'tiene_planes_previos' => true,
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'evento' => [
                    'evento' => 'plan_creacion_iniciada',
                ],
            ]);

        $this->assertDatabaseHas('eventos', [
            'evento' => 'plan_creacion_iniciada',
        ]);

        $evento = Evento::first();
        $this->assertNotNull($evento);
        $this->assertEquals('creados', $evento->props['desde_seccion']);
        $this->assertTrue($evento->props['tiene_planes_previos']);
    }

    public function test_se_puede_registrar_evento_plan_guardado_con_props(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('eventos.guardar'), [
            'evento' => 'plan_guardado',
            'props' => [
                'con_fecha_limite' => true,
                'longitud_nombre' => 15,
            ],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('eventos', [
            'evento' => 'plan_guardado',
        ]);
    }

    public function test_se_puede_registrar_evento_plan_confirmado_con_props(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('eventos.guardar'), [
            'evento' => 'plan_confirmado',
            'props' => [
                'con_desempate' => false,
                'opciones_totales' => 3,
            ],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('eventos', [
            'evento' => 'plan_confirmado',
        ]);
    }

    public function test_guardar_plan_desde_controlador_registra_evento_plan_guardado(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('planes.guardar'), [
            'nombre' => 'Cena de Fin de Semestre',
            'fecha_limite' => now()->addDays(5)->format('Y-m-d'),
        ])->assertRedirect(route('planes.listar'));

        $this->assertDatabaseHas('eventos', [
            'evento' => 'plan_guardado',
        ]);
    }

    public function test_confirmar_plan_desde_controlador_registra_evento_plan_confirmado(): void
    {
        $organizador = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
            'fecha_limite' => now()->addDays(2),
        ]);

        $opcionFecha = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-15T10:00',
        ]);

        Voto::create([
            'opcion_id' => $opcionFecha->id,
            'user_id' => $organizador->id,
        ]);

        $this->actingAs($organizador)->post(route('planes.confirmar', $plan), [
            'propuesta_fecha_id' => $opcionFecha->id,
            'propuesta_lugar_id' => null,
            'motivo_desempate' => null,
        ])->assertRedirect(route('planes.detalle', $plan));

        $this->assertDatabaseHas('eventos', [
            'evento' => 'plan_confirmado',
        ]);
    }

    public function test_fechas_de_eventos_se_formatean_en_zona_horaria_boliviana(): void
    {
        // 2026-11-10 20:00:00 UTC corresponde a 2026-11-10 16:00:00 en America/La_Paz (-04:00)
        Carbon::setTestNow(Carbon::create(2026, 11, 10, 20, 0, 0, 'UTC'));

        $evento = Evento::registrar('plan_creacion_iniciada', [
            'canal' => 'web',
        ]);

        $this->assertEquals('2026-11-10 16:00:00', $evento->fecha_bolivia);
        $this->assertEquals('16:00:00', $evento->hora_bolivia);

        Carbon::setTestNow();
    }

    public function test_endpoint_eventos_rechaza_payload_invalido_con_422(): void
    {
        $user = User::factory()->create();

        // Sin evento
        $response = $this->actingAs($user)->postJson(route('eventos.guardar'), [
            'props' => ['foo' => 'bar'],
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['evento']);

        // Formato de evento no snake_case (contiene espacios o mayúsculas)
        $responseInvalido = $this->actingAs($user)->postJson(route('eventos.guardar'), [
            'evento' => 'Evento Invalido Con Espacios',
        ]);
        $responseInvalido->assertStatus(422)
            ->assertJsonValidationErrors(['evento']);
    }

    public function test_modelo_evento_calcula_metricas_del_embudo_correctamente(): void
    {
        // 10 inicios, 6 guardados, 3 confirmados -> conversión 30.0%
        for ($i = 0; $i < 10; $i++) {
            Evento::registrar('plan_creacion_iniciada', ['paso' => 1]);
        }
        for ($i = 0; $i < 6; $i++) {
            Evento::registrar('plan_guardado', ['paso' => 2]);
        }
        for ($i = 0; $i < 3; $i++) {
            Evento::registrar('plan_confirmado', ['paso' => 3]);
        }

        $metricas = Evento::metricasEmbudo();

        $this->assertEquals(10, $metricas['iniciados']);
        $this->assertEquals(6, $metricas['guardados']);
        $this->assertEquals(3, $metricas['confirmados']);
        $this->assertEquals(30.0, $metricas['tasa_conversion']);
        $this->assertEquals(19, $metricas['total_eventos']);
    }
}

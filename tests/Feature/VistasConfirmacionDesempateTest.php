<?php

namespace Tests\Feature;

use App\Models\Invitacion;
use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use App\Models\Voto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VistasConfirmacionDesempateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: Organizador autenticado visualiza botón «Cerrar votación y confirmar» y modal en sala abierta.
     */
    public function test_organizador_autenticado_en_sala_abierta_visualiza_boton_cierre_y_modal(): void
    {
        $organizador = User::factory()->create();

        $plan = Plan::create([
            'user_id' => $organizador->id,
            'nombre' => 'Cena de Fin de Anio',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(5),
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($organizador)->get(route('planes.detalle', $plan));

        $response->assertStatus(200);
        $response->assertSee('Cerrar votación y confirmar');
        $response->assertSee('id="modal-confirmar-plan"', false);
        $response->assertSee('Confirmar Plan y Cerrar Votación');
    }

    /**
     * Caso 2: Participante común autenticado no visualiza botón de cierre ni modal de desempate.
     */
    public function test_participante_comun_autenticado_en_sala_abierta_no_visualiza_boton_de_cierre_ni_modal(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::create([
            'user_id' => $organizador->id,
            'nombre' => 'Paseo en Bicicleta',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(3),
            'estado' => 'pendiente',
        ]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'estado' => 'aceptada',
        ]);

        $response = $this->actingAs($participante)->get(route('planes.detalle', $plan));

        $response->assertStatus(200);
        $response->assertDontSee('Cerrar votación y confirmar');
        $response->assertDontSee('id="modal-confirmar-plan"', false);
    }

    /**
     * Caso 3: Vista del listado renderiza acceso accesible a la sala sin romper layout.
     */
    public function test_vista_listado_renderiza_acceso_accesible_a_la_sala_para_organizador(): void
    {
        $organizador = User::factory()->create();

        $plan = Plan::create([
            'user_id' => $organizador->id,
            'nombre' => 'Reunion Estrategica',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(7),
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($organizador)->get(route('planes.listar'));

        $response->assertStatus(200);
        $response->assertSee(route('planes.detalle', $plan));
        $response->assertSee('Entrar a la sala de votación');
        $response->assertSee('Reunion Estrategica');
    }

    /**
     * Caso 4: Sala confirmada renderiza veredicto publico y desactiva votacion.
     */
    public function test_sala_confirmada_renderiza_veredicto_publico_y_desactiva_votacion(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::create([
            'user_id' => $organizador->id,
            'nombre' => 'Hackathon Anual',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(4),
            'estado' => 'pendiente',
        ]);

        $opcionFecha = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-11-20T09:00',
        ]);

        $opcionLugar = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'lugar',
            'valor' => 'Campus Tecnologico',
        ]);

        $plan->update([
            'estado' => 'confirmado',
            'propuesta_fecha_ganadora_id' => $opcionFecha->id,
            'propuesta_lugar_ganadora_id' => $opcionLugar->id,
            'motivo_desempate' => 'Mayor disponibilidad de salas de conferencia',
            'fecha_confirmacion' => now(),
        ]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'estado' => 'aceptada',
        ]);

        $response = $this->actingAs($participante)->get(route('planes.detalle', $plan));

        $response->assertStatus(200);
        $response->assertSee('¡Plan confirmado! Deliberación y votación finalizadas.');
        $response->assertSee('Campus Tecnologico');
        $response->assertSee('Mayor disponibilidad de salas de conferencia');
        $response->assertSee('Votación cerrada (plan confirmado)');
        $response->assertDontSee('+ Proponer opción');
    }
}

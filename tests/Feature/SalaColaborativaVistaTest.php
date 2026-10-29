<?php

namespace Tests\Feature;

use App\Models\Invitacion;
use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use App\Models\Voto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalaColaborativaVistaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: La sala colaborativa renderiza para el organizador y para participantes con invitación aceptada.
     */
    public function test_vista_detalle_renderiza_correctamente_para_creador_y_participante(): void
    {
        $creador = User::factory()->create(['name' => 'Carlos Organizador']);
        $participante = User::factory()->create(['name' => 'Ana Participante']);
        $ajeno = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id'     => $creador->id,
            'nombre'      => 'Viaje a la Costa',
            'organizador' => $creador->name,
        ]);

        Invitacion::factory()->aceptada($participante)->create([
            'plan_id' => $plan->id,
            'email'   => $participante->email,
        ]);

        // Creador tiene acceso 200 OK
        $responseCreador = $this->actingAs($creador)->get(route('planes.detalle', $plan));
        $responseCreador->assertStatus(200);
        $responseCreador->assertSee('Viaje a la Costa');

        // Participante aceptado tiene acceso 200 OK
        $responseParticipante = $this->actingAs($participante)->get(route('planes.detalle', $plan));
        $responseParticipante->assertStatus(200);
        $responseParticipante->assertSee('Viaje a la Costa');

        // Usuario ajeno recibe 403 Forbidden
        $responseAjeno = $this->actingAs($ajeno)->get(route('planes.detalle', $plan));
        $responseAjeno->assertStatus(403);
    }

    /**
     * Caso 2: El parcial _cabecera muestra título, badge de estado, fecha límite y organizador.
     */
    public function test_parcial_cabecera_muestra_titulo_badge_estado_fecha_limite_y_organizador(): void
    {
        $creador = User::factory()->create(['name' => 'Elena Vega']);
        $plan = Plan::factory()->create([
            'user_id'      => $creador->id,
            'nombre'       => 'Cena de Aniversario',
            'organizador'  => 'Elena Vega',
            'estado'       => 'pendiente',
            'fecha_limite' => '2026-12-24',
        ]);

        $response = $this->actingAs($creador)->get(route('planes.detalle', $plan));

        $response->assertStatus(200);
        $response->assertSee('Cena de Aniversario');
        $response->assertSee('Pendiente');
        $response->assertSee('Elena Vega');
        $response->assertSee('24/12/2026');
        $response->assertSee('Volver a Mis Planes');
    }

    /**
     * Caso 3: El parcial _participantes lista colaboradores con sus roles y botón accesible "Invitar".
     */
    public function test_parcial_participantes_muestra_roles_y_boton_invitar_para_organizador(): void
    {
        $creador = User::factory()->create(['name' => 'Marcos Creador']);
        $participante = User::factory()->create(['name' => 'Lucía Colaboradora']);
        $plan = Plan::factory()->create([
            'user_id'     => $creador->id,
            'organizador' => $creador->name,
        ]);

        Invitacion::factory()->aceptada($participante)->create([
            'plan_id' => $plan->id,
            'email'   => $participante->email,
        ]);

        // Consulta del creador: ve ambos colaboradores y el botón "Invitar"
        $responseCreador = $this->actingAs($creador)->get(route('planes.detalle', $plan));
        $responseCreador->assertStatus(200);
        $responseCreador->assertSee('Organizador');
        $responseCreador->assertSee('Participante');
        $responseCreador->assertSee('Lucía Colaboradora');
        $responseCreador->assertSee('Invitar colaborador');
        $responseCreador->assertSee('modal-invitar');

        // Consulta del participante: no ve el botón de invitar (exclusivo del creador)
        $responseParticipante = $this->actingAs($participante)->get(route('planes.detalle', $plan));
        $responseParticipante->assertStatus(200);
        $responseParticipante->assertDontSee('+ Invitar colaborador');
    }

    /**
     * Caso 4: El parcial _sala_votacion muestra pestañas semánticas de Hick y tarjetas con contador de votos.
     */
    public function test_parcial_sala_votacion_muestra_pestanas_hick_y_tarjetas_con_votos(): void
    {
        $creador = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $creador->id]);

        $opcionFecha = Opcion::factory()->create([
            'plan_id' => $plan->id,
            'user_id' => $creador->id,
            'tipo'    => 'fecha',
            'valor'   => 'Sábado 15 de Noviembre 20:00',
        ]);

        $opcionLugar = Opcion::factory()->create([
            'plan_id' => $plan->id,
            'user_id' => $creador->id,
            'tipo'    => 'lugar',
            'valor'   => 'Terraza Bella Vista',
        ]);

        // Emitir un voto en la fecha
        Voto::factory()->create([
            'opcion_id' => $opcionFecha->id,
            'user_id'   => $creador->id,
        ]);

        // Ver pestaña por defecto (fechas)
        $responseFechas = $this->actingAs($creador)->get(route('planes.detalle', ['plan' => $plan, 'tab' => 'fechas']));
        $responseFechas->assertStatus(200);
        $responseFechas->assertSee('Fechas propuestas');
        $responseFechas->assertSee('Lugares propuestos');
        $responseFechas->assertSee('Sábado 15 de Noviembre 20:00');
        $responseFechas->assertSee('1 voto');

        // Ver pestaña de lugares
        $responseLugares = $this->actingAs($creador)->get(route('planes.detalle', ['plan' => $plan, 'tab' => 'lugares']));
        $responseLugares->assertStatus(200);
        $responseLugares->assertSee('Terraza Bella Vista');
        $responseLugares->assertSee('0 votos');
    }

    /**
     * Caso 5: Los botones cumplen con las dimensiones Fitts (mínimo 44px) y separación antagónica.
     */
    public function test_botones_cumplen_dimensiones_fitts_minimo_44px_y_separacion_antagonica(): void
    {
        $creador = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $creador->id]);

        $response = $this->actingAs($creador)->get(route('planes.detalle', $plan));

        $response->assertStatus(200);
        // Dimensión táctil mínima según Ley de Fitts y WCAG (44px)
        $response->assertSee('min-height: 44px');
        // Separación antagónica mínima de 12px entre botones de acción y cancelación
        $response->assertSee('gap: 12px');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Invitacion;
use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalaColaborativaCerradaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: Verifica que la sala colaborativa renderiza el banner de cierre y deshabilita
     * la votación cuando el plan se encuentra confirmado.
     */
    public function test_sala_colaborativa_muestra_banner_de_cierre_en_plan_confirmado(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::create([
            'user_id' => $organizador->id,
            'nombre' => 'Viaje a la Montaña',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(5),
            'estado' => 'confirmado',
        ]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'token' => 'token-participante-aceptado',
            'estado' => 'aceptada',
        ]);

        $opcion = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-11-20T10:00',
        ]);

        $response = $this->actingAs($participante)->get(route('planes.detalle', $plan));

        $response->assertStatus(200);
        $response->assertSee('Votación finalizada.');
        $response->assertSee('Este plan ha sido confirmado y la deliberación se encuentra cerrada.');
        $response->assertSee('Votación cerrada (plan confirmado)');
        $response->assertDontSee('Votar por esta opción');
    }

    /**
     * Caso 2: Verifica que el botón y modal de proponer nueva opción no se muestra en planes confirmados.
     */
    public function test_boton_proponer_opcion_no_se_muestra_en_plan_confirmado(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::create([
            'user_id' => $organizador->id,
            'nombre' => 'Cena de Aniversario',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(3),
            'estado' => 'confirmado',
        ]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'token' => 'token-participante-aniv',
            'estado' => 'aceptada',
        ]);

        // Verificación tanto para el organizador como para el participante
        $responseOrg = $this->actingAs($organizador)->get(route('planes.detalle', $plan));
        $responseOrg->assertStatus(200);
        $responseOrg->assertDontSee('+ Proponer opción');
        $responseOrg->assertDontSee('modal-proponer-opcion');

        $responsePart = $this->actingAs($participante)->get(route('planes.detalle', $plan));
        $responsePart->assertStatus(200);
        $responsePart->assertDontSee('+ Proponer opción');
        $responsePart->assertDontSee('modal-proponer-opcion');
    }

    /**
     * Caso 3: Verifica que el formulario/botón de invitación de colaboradores permanece disponible
     * en planes confirmados (para sumar asistentes al evento acordado) pero se oculta en cancelados.
     */
    public function test_formulario_invitacion_disponible_en_plan_confirmado_pero_oculto_en_cancelado(): void
    {
        $organizador = User::factory()->create();

        $planConfirmado = Plan::create([
            'user_id' => $organizador->id,
            'nombre' => 'Concierto de Rock',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(10),
            'estado' => 'confirmado',
        ]);

        $planCancelado = Plan::create([
            'user_id' => $organizador->id,
            'nombre' => 'Paseo al Zoológico',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(10),
            'estado' => 'cancelado',
        ]);

        // En plan confirmado: el organizador puede invitar colaboradores
        $responseConfirmado = $this->actingAs($organizador)->get(route('planes.detalle', $planConfirmado));
        $responseConfirmado->assertStatus(200);
        $responseConfirmado->assertSee('+ Invitar colaborador');
        $responseConfirmado->assertSee('modal-invitar');

        // En plan cancelado: el organizador no puede invitar colaboradores
        $responseCancelado = $this->actingAs($organizador)->get(route('planes.detalle', $planCancelado));
        $responseCancelado->assertStatus(200);
        $responseCancelado->assertDontSee('+ Invitar colaborador');
        $responseCancelado->assertDontSee('modal-invitar');
    }

    /**
     * Caso 4 (Seguridad / Directivas Can): Verifica que el listado de planes evalúa las acciones
     * mediante directivas @can:
     * - El organizador ve botones y rutas de confirmación/cancelación, edición y eliminación.
     * - Un participante ve el plan en su listado y el enlace "Ver sala", pero NO ve controles
     *   de edición ni eliminación (menor privilegio).
     */
    public function test_listado_de_planes_evalua_acciones_mediante_directivas_can(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $planPendiente = Plan::create([
            'user_id' => $organizador->id,
            'nombre' => 'Pícnic en el Parque',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(7),
            'estado' => 'pendiente',
        ]);

        Invitacion::create([
            'plan_id' => $planPendiente->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'token' => 'token-part-directivas-can',
            'estado' => 'aceptada',
        ]);

        // Visión del organizador: ve acciones administrativas protegidas por directivas can
        $responseOrg = $this->actingAs($organizador)->get(route('planes.listar'));
        $responseOrg->assertStatus(200);
        $responseOrg->assertSee('Editar plan');
        $responseOrg->assertSee('Eliminar plan');
        $responseOrg->assertSee(route('planes.estado', $planPendiente));
        $responseOrg->assertSee(route('planes.eliminar', $planPendiente));

        // Visión del participante: ve el plan y "Ver sala", pero NO controles ni clases de edición/eliminación
        $responsePart = $this->actingAs($participante)->get(route('planes.listar'));
        $responsePart->assertStatus(200);
        $responsePart->assertSee('Pícnic en el Parque');
        $responsePart->assertSee('Ver sala');
        $responsePart->assertDontSee('Editar plan');
        $responsePart->assertDontSee('Eliminar plan');
        $responsePart->assertDontSee('boton-editar');
        $responsePart->assertDontSee('boton-rojo');
        $responsePart->assertDontSee('data-title="Eliminar plan"');
        $responsePart->assertDontSee('name="estado"');
    }
}

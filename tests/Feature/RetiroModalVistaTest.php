<?php

namespace Tests\Feature;

use App\Models\Gasto;
use App\Models\GastoDivision;
use App\Models\Invitacion;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetiroModalVistaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: Participante sin deuda visualiza botón de retiro y modal habilitado para retirarse.
     */
    public function test_participante_sin_deuda_visualiza_boton_y_modal_habilitado(): void
    {
        $organizador = User::factory()->create(['name' => 'Carlos']);
        $participante = User::factory()->create(['name' => 'Ana']);

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
        ]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'estado' => 'aceptada',
            'token' => 'tok_test_1',
        ]);

        $response = $this->actingAs($participante)->get(route('planes.detalle', $plan));

        $response->assertStatus(200);
        $response->assertSee('Retirarme del plan');
        $response->assertSee('id="modal-retirarse"', false);
        $response->assertSee('Anulación automática de votos');
        $response->assertSee('Mantener plan');
        $response->assertSee('Sí, retirarme del plan');
        $response->assertDontSee('tienes deudas pendientes');
    }

    /**
     * Caso 2: Participante con deuda visualiza banner de bloqueo y botón destructivo deshabilitado.
     */
    public function test_participante_con_deuda_visualiza_banner_de_bloqueo_y_boton_deshabilitado(): void
    {
        $organizador = User::factory()->create(['name' => 'Carlos']);
        $participante = User::factory()->create(['name' => 'Ana']);

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
        ]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'estado' => 'aceptada',
            'token' => 'tok_test_1',
        ]);

        // Organizador pagó un gasto de 100 Bs dividido donde el participante adeuda 50 Bs
        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Alquiler de cabaña',
            'monto_total' => 100.00,
            'tipo_division' => 'personalizada',
        ]);

        GastoDivision::create([
            'gasto_id' => $gasto->id,
            'user_id' => $organizador->id,
            'monto_asignado' => 50.00,
        ]);

        GastoDivision::create([
            'gasto_id' => $gasto->id,
            'user_id' => $participante->id,
            'monto_asignado' => 50.00,
        ]);

        $response = $this->actingAs($participante)->get(route('planes.detalle', $plan));

        $response->assertStatus(200);
        $response->assertSee('Retirarme del plan');
        $response->assertSee('id="modal-retirarse"', false);
        $response->assertSee('No puedes retirarte: tienes deudas pendientes');
        $response->assertSee('Bs 50.00');
        $response->assertSee('Ir a Gastos y Liquidación');
        $response->assertSee('disabled', false);
    }

    /**
     * Caso 3: Organizador del plan no visualiza el botón de retiro en la sala.
     */
    public function test_organizador_no_visualiza_boton_de_retiro(): void
    {
        $organizador = User::factory()->create(['name' => 'Carlos']);

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($organizador)->get(route('planes.detalle', $plan));

        $response->assertStatus(200);
        $response->assertDontSee('class="boton boton-claro boton-retirarse"', false);
        $response->assertDontSee('id="modal-retirarse"', false);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Gasto;
use App\Models\GastoDivision;
use App\Models\Invitacion;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GastosVistasTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: La vista de la sala renderiza la pestaña de Gastos y Liquidación y su contenido para participantes.
     */
    public function test_vista_sala_renderiza_pestana_gastos_para_participantes(): void
    {
        $organizador = User::factory()->create(['name' => 'Organizador Lucas']);
        $participante = User::factory()->create(['name' => 'Participante Ana']);

        $plan = Plan::create([
            'nombre' => 'Viaje al Sur',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(5)->toDateString(),
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'token' => 'token-test-vista-1',
            'estado' => 'aceptada',
        ]);

        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Alquiler de Cabaña',
            'monto_total' => 120.00,
            'tipo_division' => 'equitativa',
        ]);

        GastoDivision::create([
            'gasto_id' => $gasto->id,
            'user_id' => $organizador->id,
            'monto_asignado' => 60.00,
        ]);

        GastoDivision::create([
            'gasto_id' => $gasto->id,
            'user_id' => $participante->id,
            'monto_asignado' => 60.00,
        ]);

        // Consulta accediendo a la pestaña de gastos
        $response = $this->actingAs($participante)->get(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));

        $response->assertOk();
        $response->assertSee('Gastos y Liquidación');
        $response->assertSee('Alquiler de Cabaña');
        $response->assertSee('$120.00');
        $response->assertSee('Total Gastado en el Plan');
    }

    /**
     * Caso 2: Los botones de confirmar y rechazar pago solo son visibles para el receptor del pago.
     */
    public function test_botones_confirmar_pago_solo_visibles_para_el_receptor_autenticado(): void
    {
        $deudor = User::factory()->create(['name' => 'Deudor Carlos']);
        $receptor = User::factory()->create(['name' => 'Receptor Diana']);
        $tercero = User::factory()->create(['name' => 'Observador Pedro']);

        $plan = Plan::create([
            'nombre' => 'Picnic en el Parque',
            'organizador' => $receptor->name,
            'fecha_limite' => now()->addDays(2)->toDateString(),
            'user_id' => $receptor->id,
            'estado' => 'pendiente',
        ]);

        foreach ([$deudor, $tercero] as $u) {
            Invitacion::create([
                'plan_id' => $plan->id,
                'user_id' => $u->id,
                'email' => $u->email,
                'token' => 'token-pago-view-' . $u->id,
                'estado' => 'aceptada',
            ]);
        }

        $pago = Pago::create([
            'plan_id' => $plan->id,
            'deudor_id' => $deudor->id,
            'receptor_id' => $receptor->id,
            'monto' => 45.00,
            'estado' => 'pendiente',
        ]);

        // 1. El receptor ve el botón de confirmar y de rechazar
        $responseReceptor = $this->actingAs($receptor)->get(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));
        $responseReceptor->assertOk();
        $responseReceptor->assertSee('Confirmar pago');
        $responseReceptor->assertSee('Rechazar');

        // 2. El deudor NO ve los botones de confirmar/rechazar (solo ve estado de espera)
        $responseDeudor = $this->actingAs($deudor)->get(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));
        $responseDeudor->assertOk();
        $responseDeudor->assertDontSee('Confirmar pago');
        $responseDeudor->assertSee('Esperando confirmación del receptor');

        // 3. Un tercero tampoco ve los botones de confirmación
        $responseTercero = $this->actingAs($tercero)->get(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));
        $responseTercero->assertOk();
        $responseTercero->assertDontSee('Confirmar pago');
    }
}

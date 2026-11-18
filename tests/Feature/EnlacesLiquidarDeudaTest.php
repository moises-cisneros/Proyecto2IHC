<?php

namespace Tests\Feature;

use App\Models\Gasto;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Los enlaces «Liquidar» de la Salida Honesta deben abrir la pestaña de gastos.
 * La sala solo lee el parámetro `tab`; `pestana` caía en la pestaña de fechas.
 */
class EnlacesLiquidarDeudaTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: Plan, 1: User, 2: User} plan confirmado donde el participante debe 50 al organizador */
    private function planConDeuda(): array
    {
        $organizador = User::factory()->create();
        $deudor = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'confirmado',
        ]);
        $plan->invitaciones()->create([
            'email' => $deudor->email,
            'user_id' => $deudor->id,
            'estado' => 'aceptada',
            'token' => 'tok-liquidar-'.$plan->id,
        ]);

        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Cena',
            'monto_total' => 100.00,
            'tipo_division' => 'equitativo',
        ]);
        $gasto->divisiones()->createMany([
            ['user_id' => $organizador->id, 'monto_asignado' => 50.00],
            ['user_id' => $deudor->id, 'monto_asignado' => 50.00],
        ]);

        return [$plan, $organizador, $deudor];
    }

    public function test_mi_cuenta_enlaza_liquidar_a_la_pestana_de_gastos(): void
    {
        [$plan, , $deudor] = $this->planConDeuda();

        $this->actingAs($deudor)->get(route('perfil.datos'))
            ->assertOk()
            ->assertSee('tab=gastos', false)
            ->assertDontSee('pestana=', false);
    }

    public function test_listado_enlaza_la_resolucion_de_deuda_a_la_pestana_de_gastos(): void
    {
        [, $organizador] = $this->planConDeuda();

        $this->actingAs($organizador)->get(route('planes.listar'))
            ->assertOk()
            ->assertDontSee('pestana=', false);
    }

    public function test_bloqueo_de_eliminacion_sugiere_la_accion_en_la_pestana_de_gastos(): void
    {
        [$plan] = $this->planConDeuda();

        $bloqueos = $plan->validarBloqueosEliminacion();

        $this->assertStringContainsString('tab=gastos', $bloqueos['deudas_pendientes_gastos']['url_accion']);
        $this->assertStringNotContainsString('pestana', $bloqueos['deudas_pendientes_gastos']['url_accion']);
    }

    public function test_la_sala_abre_la_pestana_de_gastos_con_el_parametro_tab(): void
    {
        [$plan, , $deudor] = $this->planConDeuda();

        $this->actingAs($deudor)->get(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']))
            ->assertOk()
            ->assertSee('id="modal-crear-pago"', false);
    }
}

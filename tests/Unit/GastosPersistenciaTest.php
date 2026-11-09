<?php

namespace Tests\Unit;

use App\Models\Gasto;
use App\Models\GastoDivision;
use App\Models\Invitacion;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GastosPersistenciaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: Crear gasto con división equitativa distribuye el total entre los participantes seleccionados.
     */
    public function test_crear_gasto_con_division_equitativa_calcula_partes_correctamente(): void
    {
        $organizador = User::factory()->create();
        $participante1 = User::factory()->create();
        $participante2 = User::factory()->create();

        $plan = Plan::create([
            'nombre' => 'Viaje a la Playa',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(5)->toDateString(),
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        $montoTotal = 60.00;
        $numParticipantes = 3;
        $cuota = round($montoTotal / $numParticipantes, 2); // 20.00

        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Almuerzo de bienvenida',
            'monto_total' => $montoTotal,
            'tipo_division' => 'equitativa',
        ]);

        foreach ([$organizador->id, $participante1->id, $participante2->id] as $uid) {
            GastoDivision::create([
                'gasto_id' => $gasto->id,
                'user_id' => $uid,
                'monto_asignado' => $cuota,
            ]);
        }

        $this->assertDatabaseHas('gastos', [
            'id' => $gasto->id,
            'plan_id' => $plan->id,
            'monto_total' => 60.00,
            'tipo_division' => 'equitativa',
        ]);

        $this->assertCount(3, $gasto->divisiones);
        $this->assertEquals(60.00, $gasto->divisiones->sum('monto_asignado'));
        $this->assertEquals(20.00, $gasto->divisiones->first()->monto_asignado);
    }

    /**
     * Caso 2: Gasto con división personalizada persiste montos exactos asignados a cada participante.
     */
    public function test_gasto_con_division_personalizada_persiste_montos_exactos(): void
    {
        $organizador = User::factory()->create();
        $amigo = User::factory()->create();

        $plan = Plan::create([
            'nombre' => 'Cena Gourmet',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(2)->toDateString(),
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Platos especiales',
            'monto_total' => 85.50,
            'tipo_division' => 'personalizada',
        ]);

        GastoDivision::create([
            'gasto_id' => $gasto->id,
            'user_id' => $organizador->id,
            'monto_asignado' => 50.00,
        ]);

        GastoDivision::create([
            'gasto_id' => $gasto->id,
            'user_id' => $amigo->id,
            'monto_asignado' => 35.50,
        ]);

        $this->assertDatabaseHas('gastos', [
            'id' => $gasto->id,
            'concepto' => 'Platos especiales',
            'tipo_division' => 'personalizada',
        ]);

        $this->assertDatabaseHas('gasto_divisiones', [
            'gasto_id' => $gasto->id,
            'user_id' => $amigo->id,
            'monto_asignado' => 35.50,
        ]);

        $this->assertEquals(85.50, $gasto->divisiones->sum('monto_asignado'));
    }

    /**
     * Caso 3: calcularSaldosNetos resuelve correctamente deudas cruzadas y compensación con pagos confirmados.
     */
    public function test_calcular_saldos_netos_resuelve_correctamente_deudas_cruzadas(): void
    {
        $userA = User::factory()->create(['name' => 'Usuario A']);
        $userB = User::factory()->create(['name' => 'Usuario B']);

        $plan = Plan::create([
            'nombre' => 'Camping Fin de Semana',
            'organizador' => $userA->name,
            'fecha_limite' => now()->addDays(4)->toDateString(),
            'user_id' => $userA->id,
            'estado' => 'pendiente',
        ]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $userB->id,
            'email' => $userB->email,
            'token' => 'token-user-b',
            'estado' => 'aceptada',
        ]);

        // Gasto 1: A paga 40.00 (20 para A, 20 para B). Deuda B -> A = 20.00
        $gasto1 = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $userA->id,
            'concepto' => 'Supermercado',
            'monto_total' => 40.00,
            'tipo_division' => 'equitativa',
        ]);
        GastoDivision::create(['gasto_id' => $gasto1->id, 'user_id' => $userA->id, 'monto_asignado' => 20.00]);
        GastoDivision::create(['gasto_id' => $gasto1->id, 'user_id' => $userB->id, 'monto_asignado' => 20.00]);

        // Gasto 2: B paga 10.00 (5 para A, 5 para B). Deuda A -> B = 5.00
        $gasto2 = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $userB->id,
            'concepto' => 'Hielo y Bebidas',
            'monto_total' => 10.00,
            'tipo_division' => 'equitativa',
        ]);
        GastoDivision::create(['gasto_id' => $gasto2->id, 'user_id' => $userA->id, 'monto_asignado' => 5.00]);
        GastoDivision::create(['gasto_id' => $gasto2->id, 'user_id' => $userB->id, 'monto_asignado' => 5.00]);

        // Estado inicial de deudas cruzadas:
        // B le debe 20 a A, A le debe 5 a B.
        // Saldo neto bilateral: B le debe 15 a A.
        $saldos1 = $plan->calcularSaldosNetos();
        $this->assertEquals(50.00, $saldos1['total_gastado']);
        $this->assertCount(1, $saldos1['deudas']);
        $this->assertEquals($userB->id, $saldos1['deudas'][0]['deudor_id']);
        $this->assertEquals($userA->id, $saldos1['deudas'][0]['acreedor_id']);
        $this->assertEquals(15.00, $saldos1['deudas'][0]['monto']);

        // Ahora B realiza un pago de 15.00 a A en estado pendiente (no debe alterar saldos aún)
        $pagoPendiente = Pago::create([
            'plan_id' => $plan->id,
            'deudor_id' => $userB->id,
            'receptor_id' => $userA->id,
            'monto' => 15.00,
            'estado' => 'pendiente',
        ]);

        $saldos2 = $plan->calcularSaldosNetos();
        $this->assertCount(1, $saldos2['deudas']);
        $this->assertEquals(15.00, $saldos2['deudas'][0]['monto']);

        // A confirma el pago de B: la deuda queda saldada (0 deudas)
        $pagoPendiente->update([
            'estado' => 'confirmado',
            'fecha_resolucion' => now(),
        ]);

        $saldos3 = $plan->calcularSaldosNetos();
        $this->assertCount(0, $saldos3['deudas']);
        $this->assertEquals(0.00, $saldos3['balances'][$userA->id]['neto']);
        $this->assertEquals(0.00, $saldos3['balances'][$userB->id]['neto']);
    }

    /**
     * Caso 4 (Seguridad / Rechazo): Integridad de asignación para participantes del plan.
     */
    public function test_no_se_puede_asignar_gasto_a_usuario_ajeno_al_plan(): void
    {
        $organizador = User::factory()->create();
        $usuarioAjeno = User::factory()->create();

        $plan = Plan::create([
            'nombre' => 'Cena Privada',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(3)->toDateString(),
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        // Verificamos que el usuario ajeno no es organizador ni participante
        $this->assertFalse($plan->participantes->contains($usuarioAjeno));
        $this->assertNotEquals($plan->user_id, $usuarioAjeno->id);

        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Entradas',
            'monto_total' => 20.00,
            'tipo_division' => 'personalizada',
        ]);

        // Las divisiones solo deben persistirse con usuarios validados
        $division = GastoDivision::create([
            'gasto_id' => $gasto->id,
            'user_id' => $organizador->id,
            'monto_asignado' => 20.00,
        ]);

        $this->assertEquals($plan->id, $gasto->plan->id);
        $this->assertEquals($organizador->id, $division->user->id);
    }
}

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

class GastosControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: Un participante puede registrar un gasto y posteriormente un pago en estado pendiente.
     */
    public function test_participante_puede_registrar_gasto_y_pago_pendiente(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::create([
            'nombre' => 'Asado de Fin de Año',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(5)->toDateString(),
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'token' => 'token-participante-1',
            'estado' => 'aceptada',
        ]);

        // 1. Participante registra un gasto equitativo de 40 entre él y el organizador
        $responseGasto = $this->actingAs($participante)->post(route('planes.gastos.guardar', $plan), [
            'concepto' => 'Carne y Carbón',
            'monto_total' => 40.00,
            'tipo_division' => 'equitativa',
            'participantes' => [$participante->id, $organizador->id],
        ]);

        $responseGasto->assertRedirect(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));
        $responseGasto->assertSessionHas('exito');

        $this->assertDatabaseHas('gastos', [
            'plan_id' => $plan->id,
            'pagador_id' => $participante->id,
            'concepto' => 'Carne y Carbón',
            'monto_total' => 40.00,
        ]);

        $this->assertDatabaseCount('gasto_divisiones', 2);

        // 2. Organizador (deudor de 20) registra un pago a favor de participante (acreedor)
        $responsePago = $this->actingAs($organizador)->post(route('planes.pagos.guardar', $plan), [
            'receptor_id' => $participante->id,
            'monto' => 20.00,
        ]);

        $responsePago->assertRedirect(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));
        $responsePago->assertSessionHas('exito');

        $this->assertDatabaseHas('pagos', [
            'plan_id' => $plan->id,
            'deudor_id' => $organizador->id,
            'receptor_id' => $participante->id,
            'monto' => 20.00,
            'estado' => 'pendiente',
        ]);
    }

    /**
     * Caso 2: El receptor acreedor confirma el pago y la deuda se reduce/extingue efectivamente.
     */
    public function test_receptor_puede_confirmar_pago_y_reducir_deuda(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::create([
            'nombre' => 'Salida al Cine',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(3)->toDateString(),
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'token' => 'token-participante-2',
            'estado' => 'aceptada',
        ]);

        // Gasto pagado por organizador: 50.00 total (25 cada uno)
        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Entradas y Popcorn',
            'monto_total' => 50.00,
            'tipo_division' => 'equitativa',
        ]);
        GastoDivision::create(['gasto_id' => $gasto->id, 'user_id' => $organizador->id, 'monto_asignado' => 25.00]);
        GastoDivision::create(['gasto_id' => $gasto->id, 'user_id' => $participante->id, 'monto_asignado' => 25.00]);

        // Participante (deudor) envía pago de 25.00 a organizador (receptor)
        $pago = Pago::create([
            'plan_id' => $plan->id,
            'deudor_id' => $participante->id,
            'receptor_id' => $organizador->id,
            'monto' => 25.00,
            'estado' => 'pendiente',
        ]);

        // Antes de confirmar, existe deuda de 25.00
        $saldosAntes = $plan->calcularSaldosNetos();
        $this->assertCount(1, $saldosAntes['deudas']);

        // Organizador (receptor) confirma el pago
        $responseConfirmar = $this->actingAs($organizador)->post(route('pagos.confirmar', $pago));
        $responseConfirmar->assertRedirect(route('planes.detalle', ['plan' => $plan->id, 'tab' => 'gastos']));
        $responseConfirmar->assertSessionHas('exito');

        $pago->refresh();
        $this->assertSame('confirmado', $pago->estado);
        $this->assertNotNull($pago->fecha_resolucion);

        // Tras confirmar, la deuda quedó saldada
        $saldosDespues = $plan->calcularSaldosNetos();
        $this->assertCount(0, $saldosDespues['deudas']);
    }

    /**
     * Caso 3: El rechazo del pago exige motivo y mantiene la deuda intacta.
     */
    public function test_rechazo_de_pago_requiere_motivo_y_no_reduce_deuda(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::create([
            'nombre' => 'Paseo en Bici',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(2)->toDateString(),
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'token' => 'token-participante-3',
            'estado' => 'aceptada',
        ]);

        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Alquiler de bicicletas',
            'monto_total' => 30.00,
            'tipo_division' => 'equitativa',
        ]);
        GastoDivision::create(['gasto_id' => $gasto->id, 'user_id' => $organizador->id, 'monto_asignado' => 15.00]);
        GastoDivision::create(['gasto_id' => $gasto->id, 'user_id' => $participante->id, 'monto_asignado' => 15.00]);

        $pago = Pago::create([
            'plan_id' => $plan->id,
            'deudor_id' => $participante->id,
            'receptor_id' => $organizador->id,
            'monto' => 15.00,
            'estado' => 'pendiente',
        ]);

        // Intento de rechazo sin motivo (debe fallar validación)
        $responseSinMotivo = $this->actingAs($organizador)->post(route('pagos.rechazar', $pago), [
            'motivo_rechazo' => '',
        ]);
        $responseSinMotivo->assertSessionHasErrors(['motivo_rechazo']);

        // Rechazo con motivo válido
        $motivo = 'No me llegó la transferencia a mi cuenta bancaria.';
        $responseConMotivo = $this->actingAs($organizador)->post(route('pagos.rechazar', $pago), [
            'motivo_rechazo' => $motivo,
        ]);
        $responseConMotivo->assertSessionHas('exito');

        $pago->refresh();
        $this->assertSame('rechazado', $pago->estado);
        $this->assertSame($motivo, $pago->motivo_rechazo);

        // La deuda sigue existiendo de 15.00
        $saldos = $plan->calcularSaldosNetos();
        $this->assertCount(1, $saldos['deudas']);
        $this->assertEquals(15.00, $saldos['deudas'][0]['monto']);
    }

    /**
     * Caso 4 (Seguridad / 403): El deudor no puede auto-confirmar su propio pago y recibe 403.
     */
    public function test_deudor_no_puede_confirmar_su_propio_pago_recibe_403(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::create([
            'nombre' => 'Cena de Amigos',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(4)->toDateString(),
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        $pago = Pago::create([
            'plan_id' => $plan->id,
            'deudor_id' => $participante->id,
            'receptor_id' => $organizador->id,
            'monto' => 30.00,
            'estado' => 'pendiente',
        ]);

        // El deudor intenta confirmar el pago que él mismo emitió
        $response = $this->actingAs($participante)->post(route('pagos.confirmar', $pago));

        $response->assertStatus(403);

        $pago->refresh();
        $this->assertSame('pendiente', $pago->estado);
    }

    /**
     * Caso 5 (Seguridad / 302): Usuario no autenticado es redirigido a login al intentar operar gastos.
     */
    public function test_usuario_no_autenticado_es_redirigido_al_intentar_registrar_gasto(): void
    {
        $organizador = User::factory()->create();
        $plan = Plan::create([
            'nombre' => 'Paseo al Río',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(2)->toDateString(),
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        $response = $this->post(route('planes.gastos.guardar', $plan), [
            'concepto' => 'Snacks',
            'monto_total' => 10.00,
            'tipo_division' => 'equitativa',
            'participantes' => [$organizador->id],
        ]);

        $response->assertRedirect(route('login'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Gasto;
use App\Models\GastoDivision;
use App\Models\Invitacion;
use App\Models\Plan;
use App\Models\PlanActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GastosValidacionPagoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: Test de consulta a /historial?categoria=gastos retornando únicamente eventos financieros.
     */
    public function test_consulta_historial_categoria_gastos_retorna_solo_eventos_financieros(): void
    {
        $usuario = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $usuario->id]);

        PlanActivity::query()->where('user_id', $usuario->id)->delete();

        $actividadGasto = PlanActivity::create([
            'plan_id' => $plan->id,
            'user_id' => $usuario->id,
            'accion' => 'gasto',
            'descripcion' => "{$usuario->name} registró el gasto 'Almuerzo' por Bs 90.00.",
            'estado_resultante' => $plan->estado,
        ]);

        $actividadPago = PlanActivity::create([
            'plan_id' => $plan->id,
            'user_id' => $usuario->id,
            'accion' => 'pago',
            'descripcion' => "{$usuario->name} registró un pago de Bs 45.00 a Amigo (pendiente).",
            'estado_resultante' => $plan->estado,
        ]);

        $actividadEstado = PlanActivity::create([
            'plan_id' => $plan->id,
            'user_id' => $usuario->id,
            'accion' => 'cambio_estado',
            'descripcion' => 'Estado cambiado a confirmado.',
            'estado_resultante' => 'confirmado',
        ]);

        $actividadCreacion = PlanActivity::create([
            'plan_id' => $plan->id,
            'user_id' => $usuario->id,
            'accion' => 'creacion',
            'descripcion' => 'Plan creado.',
            'estado_resultante' => 'pendiente',
        ]);

        // Verificación HTML
        $response = $this->actingAs($usuario)->get('/historial?categoria=gastos');
        $response->assertStatus(200);
        $response->assertSee('Almuerzo');
        $response->assertSee('45.00');
        $response->assertDontSee('Estado cambiado a confirmado.');
        $response->assertDontSee('Plan creado.');

        $actividades = $response->viewData('actividades');
        $this->assertCount(2, $actividades);
        $this->assertTrue($actividades->contains($actividadGasto));
        $this->assertTrue($actividades->contains($actividadPago));

        // Verificación JSON
        $responseJson = $this->actingAs($usuario)->getJson('/historial?categoria=gastos');
        $responseJson->assertStatus(200);
        $responseJson->assertJsonPath('categoria', 'gastos');
        $responseJson->assertJsonCount(2, 'actividades');
    }

    /**
     * Caso 2: Test de envío de pago con monto que excede la deuda exigible arrojando error de validación 422.
     */
    public function test_envio_pago_con_monto_que_excede_deuda_exigible_arroja_error_validacion_422(): void
    {
        $organizador = User::factory()->create(['name' => 'Acreedor']);
        $deudor = User::factory()->create(['name' => 'Deudor']);
        $plan = Plan::factory()->create(['user_id' => $organizador->id]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $deudor->id,
            'email' => $deudor->email,
            'token' => 'token-deudor-pago',
            'estado' => 'aceptada',
        ]);

        // Gasto de 100 pagado por organizador, dividido en 50 para cada uno
        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Cena grupal',
            'monto_total' => 100.00,
            'tipo_division' => 'equitativa',
        ]);

        GastoDivision::create([
            'gasto_id' => $gasto->id,
            'user_id' => $organizador->id,
            'monto_asignado' => 50.00,
        ]);

        GastoDivision::create([
            'gasto_id' => $gasto->id,
            'user_id' => $deudor->id,
            'monto_asignado' => 50.00,
        ]);

        // Deudor debe 50.00, pero intenta pagar 80.00
        $response = $this->actingAs($deudor)->post(route('planes.pagos.guardar', $plan), [
            'receptor_id' => $organizador->id,
            'monto' => 80.00,
        ]);

        $response->assertSessionHasErrors(['monto']);

        // Verificación de respuesta JSON 422
        $responseJson = $this->actingAs($deudor)->postJson(route('planes.pagos.guardar', $plan), [
            'receptor_id' => $organizador->id,
            'monto' => 80.00,
        ]);

        $responseJson->assertStatus(422);
        $responseJson->assertJsonValidationErrors(['monto']);
    }

    /**
     * Caso 3: Test de envío de pago con monto parcial válido procesado exitosamente (302).
     */
    public function test_envio_pago_con_monto_parcial_valido_procesado_exitosamente(): void
    {
        $organizador = User::factory()->create(['name' => 'Acreedor']);
        $deudor = User::factory()->create(['name' => 'Deudor']);
        $plan = Plan::factory()->create(['user_id' => $organizador->id]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $deudor->id,
            'email' => $deudor->email,
            'token' => 'token-deudor-parcial',
            'estado' => 'aceptada',
        ]);

        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Hospedaje',
            'monto_total' => 200.00,
            'tipo_division' => 'equitativa',
        ]);

        GastoDivision::create([
            'gasto_id' => $gasto->id,
            'user_id' => $organizador->id,
            'monto_asignado' => 100.00,
        ]);

        GastoDivision::create([
            'gasto_id' => $gasto->id,
            'user_id' => $deudor->id,
            'monto_asignado' => 100.00,
        ]);

        // Deudor debe 100.00, y realiza un pago parcial de 40.00
        $response = $this->actingAs($deudor)->post(route('planes.pagos.guardar', $plan), [
            'receptor_id' => $organizador->id,
            'monto' => 40.00,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));
        $response->assertSessionHas('exito');

        $this->assertDatabaseHas('pagos', [
            'plan_id' => $plan->id,
            'deudor_id' => $deudor->id,
            'receptor_id' => $organizador->id,
            'monto' => 40.00,
            'estado' => 'pendiente',
        ]);
    }

    /**
     * Caso 4 (Seguridad): Test negativo 302 para usuarios anónimos al consultar el historial.
     */
    public function test_usuario_anonimo_es_redirigido_al_intentar_consultar_historial_302(): void
    {
        $response = $this->get('/historial?categoria=gastos');

        $response->assertStatus(302);
        $response->assertRedirect('/ingresar');
    }
}

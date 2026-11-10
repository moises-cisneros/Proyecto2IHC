<?php

namespace Tests\Feature;

use App\Models\Invitacion;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ConciliacionComprobantePipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_flujo_completo_con_comprobante_coincidente_avanza_a_verificado(): void
    {
        Storage::fake('public');

        $organizador = User::factory()->create(['name' => 'Organizador']);
        $deudor = User::factory()->create(['name' => 'Deudor']);
        $plan = Plan::factory()->create(['user_id' => $organizador->id]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $deudor->id,
            'email' => $deudor->email,
            'token' => 'token-deudor-pipeline',
            'estado' => 'aceptada',
        ]);

        $fixturePath = base_path('tests/fixtures/comprobantes/comprobante_coincidente.png');
        $this->assertFileExists($fixturePath);

        $uploadedFile = new UploadedFile(
            $fixturePath,
            'comprobante_coincidente.png',
            'image/png',
            null,
            true
        );

        // 1. Deudor registra pago adjuntando el comprobante coincidente
        $responsePago = $this->actingAs($deudor)->post(route('planes.pagos.guardar', $plan), [
            'receptor_id' => $organizador->id,
            'monto' => 100.00,
            'comprobante' => $uploadedFile,
        ]);

        $responsePago->assertRedirect(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));

        $pago = $plan->pagos()->first();
        $this->assertNotNull($pago);
        $this->assertEquals('pendiente', $pago->estado);
        $this->assertEquals('verificado', $pago->estado_escaneo);
        $this->assertEquals(100.00, (float) $pago->monto_detectado);
        $this->assertTrue($pago->esEscaneoVerificado());

        // 2. Acreedor confirma el pago
        $responseConfirmar = $this->actingAs($organizador)->post(route('pagos.confirmar', $pago));
        $responseConfirmar->assertRedirect(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));

        $pago->refresh();
        $this->assertEquals('confirmado', $pago->estado);
        $this->assertTrue($pago->esConfirmado());
    }

    public function test_flujo_con_comprobante_discrepante_genera_alerta_para_acreedor(): void
    {
        Storage::fake('public');

        $organizador = User::factory()->create(['name' => 'Acreedor']);
        $deudor = User::factory()->create(['name' => 'Deudor']);
        $plan = Plan::factory()->create(['user_id' => $organizador->id]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $deudor->id,
            'email' => $deudor->email,
            'token' => 'token-pipeline-discrep',
            'estado' => 'aceptada',
        ]);

        $fixturePath = base_path('tests/fixtures/comprobantes/comprobante_discrepante.png');
        $uploadedFile = new UploadedFile(
            $fixturePath,
            'comprobante_discrepante.png',
            'image/png',
            null,
            true
        );

        // 1. Deudor registra pago por 100 pero comprobante es por 35.50
        $responsePago = $this->actingAs($deudor)->post(route('planes.pagos.guardar', $plan), [
            'receptor_id' => $organizador->id,
            'monto' => 100.00,
            'comprobante' => $uploadedFile,
        ]);

        $responsePago->assertRedirect(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));

        $pago = $plan->pagos()->first();
        $this->assertNotNull($pago);
        $this->assertEquals('discrepante', $pago->estado_escaneo);
        $this->assertTrue($pago->esEscaneoDiscrepante());
        $this->assertEquals(35.50, (float) $pago->monto_detectado);

        // 2. Acreedor rechaza el pago con motivo obligatorio
        $responseRechazar = $this->actingAs($organizador)->post(route('pagos.rechazar', $pago), [
            'motivo_rechazo' => 'Discrepancia en el monto: la transferencia recibida fue de Bs 35.50.',
        ]);

        $responseRechazar->assertRedirect();
        $pago->refresh();
        $this->assertEquals('rechazado', $pago->estado);
        $this->assertTrue($pago->esRechazado());
        $this->assertEquals('Discrepancia en el monto: la transferencia recibida fue de Bs 35.50.', $pago->motivo_rechazo);
    }

    public function test_flujo_con_comprobante_invalido_mantiene_estado_sin_verificar(): void
    {
        Storage::fake('public');

        $organizador = User::factory()->create();
        $deudor = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $organizador->id]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $deudor->id,
            'email' => $deudor->email,
            'token' => 'token-pipeline-inv',
            'estado' => 'aceptada',
        ]);

        $fixturePath = base_path('tests/fixtures/comprobantes/comprobante_invalido.png');
        $uploadedFile = new UploadedFile(
            $fixturePath,
            'comprobante_invalido.png',
            'image/png',
            null,
            true
        );

        $responsePago = $this->actingAs($deudor)->post(route('planes.pagos.guardar', $plan), [
            'receptor_id' => $organizador->id,
            'monto' => 50.00,
            'comprobante' => $uploadedFile,
        ]);

        $responsePago->assertRedirect();

        $pago = $plan->pagos()->first();
        $this->assertNotNull($pago);
        $this->assertEquals('invalido', $pago->estado_escaneo);
        $this->assertTrue($pago->esEscaneoInvalido());
    }

    public function test_pago_con_comprobante_no_puede_ser_confirmado_por_el_mismo_deudor_403(): void
    {
        $organizador = User::factory()->create();
        $deudor = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $organizador->id]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $deudor->id,
            'email' => $deudor->email,
            'token' => 'token-pipeline-403',
            'estado' => 'aceptada',
        ]);

        $pago = $plan->pagos()->create([
            'deudor_id' => $deudor->id,
            'receptor_id' => $organizador->id,
            'monto' => 80.00,
            'estado' => 'pendiente',
            'estado_escaneo' => 'verificado',
        ]);

        $response = $this->actingAs($deudor)->post(route('pagos.confirmar', $pago));
        $response->assertStatus(403);
    }
}

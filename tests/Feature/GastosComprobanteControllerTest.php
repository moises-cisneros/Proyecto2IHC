<?php

namespace Tests\Feature;

use App\Models\Invitacion;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GastosComprobanteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_pago_con_comprobante_valido_ejecuta_escaneo_y_asocia_archivo(): void
    {
        Storage::fake('public');

        $creador = User::factory()->create();
        $deudor = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $creador->id]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $deudor->id,
            'email' => $deudor->email,
            'token' => 'token-deudor-1',
            'estado' => 'aceptada',
        ]);

        $comprobante = UploadedFile::fake()->image('comprobante_transferencia_valido.png', 600, 800);

        $response = $this->actingAs($deudor)->post(route('planes.pagos.guardar', $plan), [
            'receptor_id' => $creador->id,
            'monto' => 100.00,
            'comprobante' => $comprobante,
        ]);

        $response->assertRedirect(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));
        $response->assertSessionHas('exito');

        $this->assertDatabaseHas('pagos', [
            'plan_id' => $plan->id,
            'deudor_id' => $deudor->id,
            'receptor_id' => $creador->id,
            'monto' => 100.00,
            'estado' => 'pendiente',
            'banco_detectado' => 'Banco Unión',
            'estado_escaneo' => 'verificado',
        ]);

        $pago = $plan->pagos()->first();
        $this->assertNotNull($pago->comprobante_path);
        Storage::disk('public')->assertExists($pago->comprobante_path);
    }

    public function test_store_pago_rechaza_archivos_no_imagen_o_mayores_al_limite(): void
    {
        Storage::fake('public');

        $creador = User::factory()->create();
        $deudor = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $creador->id]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $deudor->id,
            'email' => $deudor->email,
            'token' => 'token-deudor-2',
            'estado' => 'aceptada',
        ]);

        $archivoTexto = UploadedFile::fake()->create('documento.txt', 100, 'text/plain');

        $response = $this->actingAs($deudor)->post(route('planes.pagos.guardar', $plan), [
            'receptor_id' => $creador->id,
            'monto' => 50.00,
            'comprobante' => $archivoTexto,
        ]);

        $response->assertSessionHasErrors(['comprobante']);

        $archivoPesado = UploadedFile::fake()->image('pesado.png')->size(5000); // 5MB > 4096KB

        $responsePesado = $this->actingAs($deudor)->post(route('planes.pagos.guardar', $plan), [
            'receptor_id' => $creador->id,
            'monto' => 50.00,
            'comprobante' => $archivoPesado,
        ]);

        $responsePesado->assertSessionHasErrors(['comprobante']);
    }

    public function test_usuario_puede_actualizar_sus_datos_de_cobro_y_subir_qr(): void
    {
        Storage::fake('public');

        $usuario = User::factory()->create();
        $qrFile = UploadedFile::fake()->image('mi_qr_cobro.png', 300, 300);

        $response = $this->actingAs($usuario)->put(route('perfil.cobro.actualizar'), [
            'alias_pago' => 'juan.perez.bnb',
            'cbu_pago' => '10000088992211',
            'qr_pago' => $qrFile,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('exito');

        $usuario->refresh();
        $this->assertEquals('juan.perez.bnb', $usuario->alias_pago);
        $this->assertEquals('10000088992211', $usuario->cbu_pago);
        $this->assertNotNull($usuario->qr_pago_path);
        Storage::disk('public')->assertExists($usuario->qr_pago_path);
    }

    public function test_usuario_no_autenticado_es_redirigido_al_intentar_actualizar_cobro_302(): void
    {
        $response = $this->put(route('perfil.cobro.actualizar'), [
            'alias_pago' => 'hacker.alias',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_usuario_no_participante_recibe_403_al_intentar_registrar_pago_con_comprobante(): void
    {
        $creador = User::factory()->create();
        $receptor = User::factory()->create();
        $usuarioAjeno = User::factory()->create();

        $plan = Plan::factory()->create(['user_id' => $creador->id]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $receptor->id,
            'email' => $receptor->email,
            'token' => 'token-receptor-1',
            'estado' => 'aceptada',
        ]);

        $response = $this->actingAs($usuarioAjeno)->post(route('planes.pagos.guardar', $plan), [
            'receptor_id' => $receptor->id,
            'monto' => 20.00,
        ]);

        $response->assertStatus(403);
    }
}

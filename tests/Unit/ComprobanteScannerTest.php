<?php

namespace Tests\Unit;

use App\Contracts\ComprobanteScannerInterface;
use App\DTOs\ComprobanteScanResult;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\User;
use App\Services\Comprobante\FakeComprobanteScanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprobanteScannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_fake_scanner_extrae_datos_correctamente_de_fixture_valido(): void
    {
        $scanner = new FakeComprobanteScanner;
        $resultado = $scanner->scan('comprobante_transferencia_valido.png');

        $this->assertTrue($resultado->esComprobante);
        $this->assertEquals(100.00, $resultado->monto);
        $this->assertEquals('Banco Unión', $resultado->banco);
        $this->assertNotEmpty($resultado->referencia);
        $this->assertGreaterThanOrEqual(0.9, $resultado->confianza);
        $this->assertEquals('verificado', $resultado->determinarEstadoEscaneo(100.00));
    }

    public function test_fake_scanner_detecta_discrepancia_de_monto(): void
    {
        $scanner = new FakeComprobanteScanner;
        $resultado = $scanner->scan('comprobante_monto_discrepante.jpg');

        $this->assertTrue($resultado->esComprobante);
        $this->assertEquals(35.50, $resultado->monto);
        $this->assertFalse($resultado->coincideConMonto(100.00));
        $this->assertEquals('discrepante', $resultado->determinarEstadoEscaneo(100.00));
    }

    public function test_fake_scanner_maneja_imagen_invalida_o_ilegible(): void
    {
        $scanner = new FakeComprobanteScanner;
        $resultado = $scanner->scan('foto_meme_invalido.png');

        $this->assertFalse($resultado->esComprobante);
        $this->assertNull($resultado->monto);
        $this->assertNotNull($resultado->mensajeError);
        $this->assertEquals('invalido', $resultado->determinarEstadoEscaneo(100.00));
    }

    public function test_dto_comprobante_scan_result_evalua_coincidencia_con_tolerancia(): void
    {
        $resultado = new ComprobanteScanResult(
            esComprobante: true,
            monto: 99.98,
            banco: 'Banco Sol',
            referencia: 'REF-7711',
            confianza: 0.95
        );

        $this->assertTrue($resultado->coincideConMonto(100.00, 0.05));
        $this->assertFalse($resultado->coincideConMonto(100.00, 0.01));
        $this->assertEquals('verificado', $resultado->determinarEstadoEscaneo(100.00));
    }

    public function test_modelo_pago_persiste_metadatos_de_escaneo_y_evalua_helpers(): void
    {
        $creador = User::factory()->create();
        $deudor = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $creador->id]);

        $pago = Pago::create([
            'plan_id' => $plan->id,
            'deudor_id' => $deudor->id,
            'receptor_id' => $creador->id,
            'monto' => 150.00,
            'comprobante_path' => 'comprobantes/recibo_123.jpg',
            'monto_detectado' => 150.00,
            'banco_detectado' => 'Banco FIE',
            'estado_escaneo' => 'verificado',
            'confianza_escaneo' => 0.96,
            'detalles_escaneo' => ['nro_operacion' => 'OP-9871'],
            'estado' => 'pendiente',
        ]);

        $this->assertTrue($pago->tieneComprobante());
        $this->assertTrue($pago->esEscaneoVerificado());
        $this->assertFalse($pago->esEscaneoDiscrepante());
        $this->assertFalse($pago->esEscaneoInvalido());
        $this->assertEquals('verificado', $pago->estado_escaneo);
        $this->assertEquals(150.00, (float) $pago->monto_detectado);
        $this->assertEquals('OP-9871', $pago->detalles_escaneo['nro_operacion']);
    }

    public function test_modelo_user_persiste_datos_de_cobro_y_evalua_helper(): void
    {
        $usuario = User::factory()->create([
            'alias_pago' => 'juan.perez.qr',
            'cbu_pago' => '10000012345678',
            'qr_pago_path' => 'qrs/usuario_1.png',
        ]);

        $this->assertTrue($usuario->tieneDatosCobro());
        $this->assertEquals('juan.perez.qr', $usuario->alias_pago);
        $this->assertEquals('10000012345678', $usuario->cbu_pago);

        $usuarioSinCobro = User::factory()->create([
            'alias_pago' => null,
            'cbu_pago' => null,
            'qr_pago_path' => null,
        ]);

        $this->assertFalse($usuarioSinCobro->tieneDatosCobro());
    }

    public function test_service_container_resuelve_interfaz_de_escaneo(): void
    {
        $resolved = app(ComprobanteScannerInterface::class);
        $this->assertInstanceOf(ComprobanteScannerInterface::class, $resolved);
    }
}

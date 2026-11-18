<?php

namespace Tests\Unit;

use App\Contracts\ComprobanteScannerInterface;
use App\DTOs\ComprobanteScanResult;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\User;
use App\Services\Comprobante\GeminiComprobanteScanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ComprobanteScannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_gemini_scanner_aplica_plan_de_contingencia_si_falta_api_key(): void
    {
        $scanner = new GeminiComprobanteScanner(apiKey: '');
        $resultado = $scanner->scan('archivo_cualquiera.png');

        $this->assertFalse($resultado->esComprobante);
        $this->assertEquals(0.0, $resultado->confianza);
        $this->assertStringContainsString('Servicio de escaneo no configurado', $resultado->mensajeError);
        $this->assertEquals('invalido', $resultado->determinarEstadoEscaneo(100.00));
    }

    public function test_gemini_scanner_aplica_plan_de_contingencia_si_archivo_no_existe(): void
    {
        $scanner = new GeminiComprobanteScanner(apiKey: 'dummy-key');
        $resultado = $scanner->scan('ruta/inexistente/comprobante.png');

        $this->assertFalse($resultado->esComprobante);
        $this->assertStringContainsString('no existe en disco', $resultado->mensajeError);
        $this->assertEquals('invalido', $resultado->determinarEstadoEscaneo(100.00));
    }

    public function test_gemini_scanner_extrae_datos_correctamente_con_respuesta_valida(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'es_comprobante' => true,
                                        'monto' => 100.00,
                                        'banco' => 'Banco Unión',
                                        'referencia' => 'OP-998877',
                                        'confianza' => 0.95,
                                    ]),
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_receipt_');
        file_put_contents($tempFile, 'fake image binary data');

        $scanner = new GeminiComprobanteScanner(apiKey: 'dummy-key');
        $resultado = $scanner->scan($tempFile);

        @unlink($tempFile);

        $this->assertTrue($resultado->esComprobante);
        $this->assertEquals(100.00, $resultado->monto);
        $this->assertEquals('Banco Unión', $resultado->banco);
        $this->assertEquals('OP-998877', $resultado->referencia);
        $this->assertEquals('verificado', $resultado->determinarEstadoEscaneo(100.00));
    }

    public function test_gemini_scanner_aplica_contingencia_si_api_externa_falla(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response(['error' => 'Internal Server Error'], 500),
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'test_receipt_');
        file_put_contents($tempFile, 'fake image binary data');

        $scanner = new GeminiComprobanteScanner(apiKey: 'dummy-key');
        $resultado = $scanner->scan($tempFile);

        @unlink($tempFile);

        $this->assertFalse($resultado->esComprobante);
        $this->assertEquals(0.0, $resultado->confianza);
        $this->assertStringContainsString('Error en servicio de escaneo inteligente (500)', $resultado->mensajeError);
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

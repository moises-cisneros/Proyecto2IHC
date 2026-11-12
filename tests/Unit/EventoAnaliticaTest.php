<?php

namespace Tests\Unit;

use App\Models\Evento;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventoAnaliticaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: Test de cálculo de métricas de embudo con eventos sembrados en base de datos.
     */
    public function test_calculo_metricas_de_todos_los_embudos_con_eventos_sembrados(): void
    {
        // Embudo Creación
        Evento::registrar('plan_creacion_iniciada');
        Evento::registrar('plan_creacion_iniciada');
        Evento::registrar('plan_guardado');
        Evento::registrar('plan_confirmado');

        // Embudo Votación
        Evento::registrar('votacion_iniciada');
        Evento::registrar('votacion_iniciada');
        Evento::registrar('propuesta_creada');
        Evento::registrar('voto_emitido');

        // Embudo Invitaciones
        Evento::registrar('invitacion_iniciada');
        Evento::registrar('invitacion_enviada');
        Evento::registrar('invitacion_aceptada');
        Evento::registrar('invitacion_rechazada');

        // Embudo Gastos
        Evento::registrar('gasto_iniciado');
        Evento::registrar('gasto_registrado');
        Evento::registrar('pago_iniciado');
        Evento::registrar('pago_confirmado');
        Evento::registrar('pago_rechazado');

        // Embudo Retiro
        Evento::registrar('retiro_iniciado');
        Evento::registrar('retiro_confirmado');
        Evento::registrar('retiro_bloqueado_deuda');
        Evento::registrar('error_freno_tarea', ['motivo' => 'deuda_pendiente']);

        // Embudo Filtros
        Evento::registrar('busqueda_realizada');
        Evento::registrar('filtro_aplicado');
        Evento::registrar('busqueda_sin_resultados');

        $todos = Evento::metricasTodosLosEmbudos();

        $this->assertArrayHasKey('creacion', $todos);
        $this->assertEquals(2, $todos['creacion']['iniciados']);
        $this->assertEquals(1, $todos['creacion']['guardados']);
        $this->assertEquals(1, $todos['creacion']['confirmados']);
        $this->assertEquals(50.0, $todos['creacion']['tasa_conversion']);

        $this->assertArrayHasKey('votacion', $todos);
        $this->assertEquals(2, $todos['votacion']['iniciadas']);
        $this->assertEquals(1, $todos['votacion']['propuestas']);
        $this->assertEquals(1, $todos['votacion']['votos']);
        $this->assertEquals(50.0, $todos['votacion']['tasa_conversion']);

        $this->assertArrayHasKey('invitaciones', $todos);
        $this->assertEquals(1, $todos['invitaciones']['iniciadas']);
        $this->assertEquals(1, $todos['invitaciones']['enviadas']);
        $this->assertEquals(1, $todos['invitaciones']['aceptadas']);
        $this->assertEquals(1, $todos['invitaciones']['rechazadas']);
        $this->assertEquals(100.0, $todos['invitaciones']['tasa_conversion']);

        $this->assertArrayHasKey('gastos', $todos);
        $this->assertEquals(1, $todos['gastos']['gastos_iniciados']);
        $this->assertEquals(1, $todos['gastos']['gastos_registrados']);
        $this->assertEquals(1, $todos['gastos']['pagos_iniciados']);
        $this->assertEquals(1, $todos['gastos']['pagos_confirmados']);
        $this->assertEquals(100.0, $todos['gastos']['tasa_conversion']);

        $this->assertArrayHasKey('retiro', $todos);
        $this->assertEquals(1, $todos['retiro']['iniciados']);
        $this->assertEquals(1, $todos['retiro']['confirmados']);
        $this->assertEquals(2, $todos['retiro']['bloqueados_deuda']);
        $this->assertEquals(100.0, $todos['retiro']['tasa_conversion']);

        $this->assertArrayHasKey('filtros', $todos);
        $this->assertEquals(1, $todos['filtros']['busquedas']);
        $this->assertEquals(1, $todos['filtros']['sin_resultados']);
        $this->assertEquals(0.0, $todos['filtros']['tasa_efectividad']);

        $this->assertGreaterThan(0, $todos['total_eventos']);
        $this->assertArrayHasKey('deuda_pendiente', $todos['errores_frecuentes']);
    }

    /**
     * Caso 2: Test de cálculo de drop-off rate cuando hay abandonos intermedios.
     */
    public function test_calculo_de_drop_off_rate_con_abandonos_intermedios(): void
    {
        // 10 iniciados -> 4 guardados -> 1 confirmado
        for ($i = 0; $i < 10; $i++) {
            Evento::registrar('plan_creacion_iniciada');
        }
        for ($i = 0; $i < 4; $i++) {
            Evento::registrar('plan_guardado');
        }
        Evento::registrar('plan_confirmado');

        $metricas = Evento::metricasEmbudoCreacion();

        $this->assertEquals(10, $metricas['iniciados']);
        $this->assertEquals(4, $metricas['guardados']);
        $this->assertEquals(1, $metricas['confirmados']);
        $this->assertEquals(10.0, $metricas['tasa_conversion']);
        // Drop-off en guardado: (10 - 4) / 10 = 60.0%
        $this->assertEquals(60.0, $metricas['drop_off_guardado']);
        // Drop-off en confirmación: (4 - 1) / 4 = 75.0%
        $this->assertEquals(75.0, $metricas['drop_off_confirmacion']);
    }

    /**
     * Caso 3: Test con base de datos vacía devolviendo ceros sin arrojar excepciones de división.
     */
    public function test_base_de_datos_vacia_devuelve_ceros_sin_lanzar_division_por_cero(): void
    {
        $todos = Evento::metricasTodosLosEmbudos();

        $this->assertEquals(0, $todos['total_eventos']);
        $this->assertEquals(0.0, $todos['creacion']['tasa_conversion']);
        $this->assertEquals(0.0, $todos['creacion']['drop_off_guardado']);
        $this->assertEquals(0.0, $todos['votacion']['tasa_conversion']);
        $this->assertEquals(0.0, $todos['invitaciones']['tasa_conversion']);
        $this->assertEquals(0.0, $todos['gastos']['tasa_conversion']);
        $this->assertEquals(0.0, $todos['retiro']['tasa_conversion']);
        $this->assertEquals(0.0, $todos['filtros']['tasa_efectividad']);
        $this->assertIsArray($todos['errores_frecuentes']);
        $this->assertEmpty($todos['errores_frecuentes']);
    }

    /**
     * Caso 4 (Dominio): Test de formateo de fecha y hora en zona horaria boliviana.
     */
    public function test_formateo_de_fecha_y_hora_en_zona_horaria_boliviana(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 12, 18, 30, 0, 'UTC'));

        $evento = Evento::registrar('votacion_iniciada');

        // UTC 18:30:00 corresponde a UTC-4 (Bolivia) 14:30:00
        $this->assertEquals('2026-11-12 14:30:00', $evento->fecha_bolivia);
        $this->assertEquals('14:30:00', $evento->hora_bolivia);

        Carbon::setTestNow();
    }
}

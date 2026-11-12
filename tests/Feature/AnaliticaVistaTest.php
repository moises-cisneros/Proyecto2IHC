<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnaliticaVistaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Usuario anónimo debe ser redirigido al login.
     */
    public function test_usuario_no_autenticado_es_redirigido_al_intentar_ver_analitica(): void
    {
        $response = $this->get(route('analitica.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Caso 1 (H8 / AI): Verificar que el menú de navegación del usuario no contiene el link de analítica.
     */
    public function test_barra_navegacion_de_usuario_no_muestra_enlace_a_analitica(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('planes.listar'));

        $response->assertStatus(200);

        // La barra de menú principal (.nav-menu) no debe contener enlace directo a analítica
        $response->assertDontSee('<x-nav-link :href="route(\'analitica.index\')"', false);
    }

    /**
     * Caso 2: Verificar que la Dev Floating Toolbar se renderiza en esquina inferior en entorno local/testing.
     */
    public function test_dev_floating_toolbar_se_renderiza_en_entorno_local_o_testing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('planes.listar'));

        $response->assertStatus(200);
        $response->assertSee('dev-toolbar-flotante');
        $response->assertSee('Telemetría Dev · /analitica');
        $response->assertSee(route('analitica.index'));
    }

    /**
     * Caso 2b (Seguridad / Producción): La Dev Floating Toolbar no se renderiza en entorno de producción.
     */
    public function test_dev_floating_toolbar_no_se_renderiza_en_produccion(): void
    {
        $user = User::factory()->create();
        $this->app['env'] = 'production';

        $response = $this->actingAs($user)->get(route('planes.listar'));

        $response->assertStatus(200);
        $response->assertDontSee('dev-toolbar-flotante');
        $response->assertDontSee('Telemetría Dev · /analitica');
    }

    /**
     * Caso 3: Clic / navegación a /analitica despliega los 6 macroembudos con tarjetas y brand-bar.
     */
    public function test_panel_de_analitica_despliega_los_seis_embudos_y_eventos_recientes(): void
    {
        $user = User::factory()->create();

        // Sembrar eventos de los diferentes flujos
        Evento::registrar('plan_creacion_iniciada');
        Evento::registrar('plan_guardado');
        Evento::registrar('plan_confirmado');

        Evento::registrar('votacion_iniciada');
        Evento::registrar('propuesta_creada');
        Evento::registrar('voto_emitido');

        Evento::registrar('invitacion_enviada');
        Evento::registrar('invitacion_aceptada');

        Evento::registrar('gasto_registrado');
        Evento::registrar('pago_iniciado');
        Evento::registrar('pago_confirmado');

        Evento::registrar('retiro_iniciado');
        Evento::registrar('retiro_confirmado');

        Evento::registrar('busqueda_realizada');
        Evento::registrar('filtro_aplicado');

        $response = $this->actingAs($user)->get(route('analitica.index'));

        $response->assertStatus(200);
        $response->assertSee('Analítica de Interacción y Telemetría');
        $response->assertSee('Los 6 Embudos de Interacción');

        // Verificar la presencia de los 6 embudos
        $response->assertSee('Creación y Confirmación');
        $response->assertSee('Deliberación y Votación');
        $response->assertSee('Invitaciones y Convocatoria');
        $response->assertSee('Gastos y Liquidación');
        $response->assertSee('Retiro de Participantes');
        $response->assertSee('Búsquedas y Filtros');

        // Clases de diseño oficial
        $response->assertSee('tarjeta-embudo');
        $response->assertSee('brand-bar');

        // Verificación de eventos en hora boliviana
        $response->assertSee('Últimos 25 Eventos Registrados');
        $response->assertSee('plan_confirmado');
        $response->assertSee('voto_emitido');
    }
}

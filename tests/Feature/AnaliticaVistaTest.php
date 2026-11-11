<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnaliticaVistaTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_no_autenticado_es_redirigido_al_intentar_ver_analitica(): void
    {
        $response = $this->get(route('analitica.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_usuario_autenticado_puede_ver_panel_de_analitica_en_entorno_local(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('analitica.index'));

        $response->assertStatus(200);
        $response->assertSee('Analítica de Interacción');
        $response->assertSee('Embudo del Flujo Principal');
        $response->assertSee('¿Qué decisión de diseño cambiaría si supiera esto?');
        $response->assertSee('Solo Desarrollo');
    }

    public function test_panel_renderiza_contadores_y_eventos_recientes_con_hora_boliviana(): void
    {
        $user = User::factory()->create();

        Evento::registrar('plan_creacion_iniciada', ['desde' => 'modal']);
        Evento::registrar('plan_guardado', ['nombre' => 'Viaje']);
        Evento::registrar('plan_confirmado', ['motivo' => 'consenso']);

        $response = $this->actingAs($user)->get(route('analitica.index'));

        $response->assertStatus(200);
        $response->assertSee('plan_creacion_iniciada');
        $response->assertSee('plan_guardado');
        $response->assertSee('plan_confirmado');
        $response->assertSee('100%'); // 1 confirmado / 1 iniciado
    }

    public function test_barra_navegacion_incluye_enlace_a_analitica_en_entorno_desarrollo(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('planes.listar'));

        $response->assertStatus(200);
        $response->assertSee(route('analitica.index'));
        $response->assertSee('Analítica');
    }
}

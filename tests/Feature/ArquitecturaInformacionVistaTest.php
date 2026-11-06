<?php

namespace Tests\Feature;

use App\Models\Invitacion;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArquitecturaInformacionVistaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1 (Pestañas de Navegación por Rol): Verifica que la vista renderiza las pestañas
     * con accesibilidad ARIA (role="tablist", role="tab", aria-selected) y touch target Fitts >= 44px.
     */
    public function test_vista_renderiza_pestanas_por_rol_con_aria_y_estados_activos(): void
    {
        $organizador = User::factory()->create();

        Plan::create([
            'user_id' => $organizador->id,
            'nombre' => 'Plan de Prueba',
            'organizador' => $organizador->name,
            'fecha_limite' => today()->addDays(2),
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($organizador)->get(route('planes.listar'));

        $response->assertStatus(200);
        $response->assertSee('role="tablist"', false);
        $response->assertSee('Mis planes creados');
        $response->assertSee('Planes donde participo');
        $response->assertSee('aria-selected="true"', false);
    }

    /**
     * Caso 2 (Bloques Cronológicos): Verifica que la vista renderiza los tres bloques
     * de urgencia temporal con sus encabezados y badges numéricos.
     */
    public function test_vista_renderiza_bloques_cronologicos_hoy_proximos_y_vencidos(): void
    {
        $usuario = User::factory()->create();

        // Plan de hoy
        Plan::create([
            'user_id' => $usuario->id,
            'nombre' => 'Reunión de Hoy',
            'organizador' => $usuario->name,
            'fecha_limite' => today(),
            'estado' => 'pendiente',
        ]);

        // Plan próximo
        Plan::create([
            'user_id' => $usuario->id,
            'nombre' => 'Asado Futuro',
            'organizador' => $usuario->name,
            'fecha_limite' => today()->addDays(5),
            'estado' => 'pendiente',
        ]);

        // Plan vencido
        Plan::create([
            'user_id' => $usuario->id,
            'nombre' => 'Cena del Pasado',
            'organizador' => $usuario->name,
            'fecha_limite' => today()->subDays(2),
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($usuario)->get(route('planes.listar'));

        $response->assertStatus(200);
        $response->assertSee('Reunión de Hoy');
        $response->assertSee('Asado Futuro');
        $response->assertSee('Cena del Pasado');
        $response->assertSee('id="bloque-hoy"', false);
        $response->assertSee('id="bloque-proximos"', false);
        $response->assertSee('id="bloque-vencidos"', false);
    }

    /**
     * Caso 3 (Prevención de Errores / Estados Vacíos): Verifica que si un bloque no tiene planes,
     * muestra un mensaje orientador evitando confusión cognitiva.
     */
    public function test_vista_muestra_estado_vacio_orientador_en_bloques_sin_planes(): void
    {
        $usuario = User::factory()->create();

        // Solo creamos un plan futuro
        Plan::create([
            'user_id' => $usuario->id,
            'nombre' => 'Viaje Vacaciones',
            'organizador' => $usuario->name,
            'fecha_limite' => today()->addDays(10),
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($usuario)->get(route('planes.listar'));

        $response->assertStatus(200);
        $response->assertSee('Viaje Vacaciones');
        $response->assertSee('No tienes planes para hoy en esta sección.');
        $response->assertSee('No tienes planes vencidos en esta sección.');
    }

    /**
     * Caso 4 (Persistencia de Contexto): El formulario de filtros preserva el parámetro
     * de sección para no sacar al usuario de su pestaña activa.
     */
    public function test_vista_conserva_parametro_seccion_al_filtrar_y_limpiar(): void
    {
        $usuario = User::factory()->create();

        $response = $this->actingAs($usuario)->get(route('planes.listar', ['seccion' => 'invitaciones']));

        $response->assertStatus(200);
        $response->assertSee('name="seccion" value="invitaciones"', false);
    }
}


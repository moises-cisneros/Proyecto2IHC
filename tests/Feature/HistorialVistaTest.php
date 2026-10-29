<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\PlanActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistorialVistaTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->usuario = User::factory()->create(['name' => 'Carlos Mendoza']);
    }

    /**
     * Caso 1: La vista de historial renderiza correctamente con HTTP 200 y layout maestro.
     */
    public function test_vista_historial_renderiza_correctamente_para_usuario_autenticado(): void
    {
        $response = $this->actingAs($this->usuario)->get('/historial');

        $response->assertStatus(200);
        $response->assertViewIs('planes.historial');
        $response->assertSee('Historial de actividad');
        $response->assertSee('Volver a Mis Planes');
    }

    /**
     * Caso 2: La barra de navegación incluye el enlace accesible al Historial (Fitts >= 44px).
     */
    public function test_vista_muestra_enlace_a_historial_en_barra_de_navegacion(): void
    {
        $response = $this->actingAs($this->usuario)->get('/mis-planes');

        $response->assertStatus(200);
        $response->assertSee('Historial');
        $response->assertSee(route('historial.index'), false);
        $response->assertSee('aria-label="Ir al Historial de Actividad"', false);
    }

    /**
     * Caso 3: La tarjeta ActivityCard muestra explícitamente sus 4 dimensiones de interacción.
     */
    public function test_tarjeta_actividad_muestra_las_cuatro_dimensiones_explicitas(): void
    {
        $plan = Plan::factory()->create([
            'user_id' => $this->usuario->id,
            'nombre' => 'Viaje al Salto del Ángel',
        ]);

        PlanActivity::query()->where('user_id', $this->usuario->id)->delete();

        PlanActivity::create([
            'plan_id' => $plan->id,
            'user_id' => $this->usuario->id,
            'accion' => 'cambio_estado',
            'descripcion' => 'Se confirmó la logística del viaje.',
            'estado_resultante' => 'confirmado',
            'created_at' => now()->subMinutes(10),
        ]);

        $response = $this->actingAs($this->usuario)->get('/historial');

        $response->assertStatus(200);
        // Dimensión 1: Qué ocurrió
        $response->assertSee('Cambio de estado');
        $response->assertSee('Se confirmó la logística del viaje.');

        // Dimensión 2: Cuándo ocurrió
        $response->assertSee('hace 10 minutos');

        // Dimensión 3: Cuál fue el resultado
        $response->assertSee('Confirmado');

        // Dimensión 4: Qué elemento involucró
        $response->assertSee('Viaje al Salto del Ángel');
        $response->assertSee('Carlos Mendoza');
    }

    /**
     * Caso 4: El selector temático muestra exactamente los n=4 chips ergonómicos de Hick.
     */
    public function test_selector_de_chips_muestra_las_cuatro_categorias_de_hick(): void
    {
        $response = $this->actingAs($this->usuario)->get('/historial?categoria=estados');

        $response->assertStatus(200);
        $response->assertSee('Todos');
        $response->assertSee('Estados');
        $response->assertSee('Creaciones');
        $response->assertSee('Participación');

        // Verifica aria-pressed activo en el chip seleccionado
        $response->assertSee('aria-pressed="true"', false);
    }

    /**
     * Caso 5: Muestra un estado vacío ilustrado cuando no hay actividades en la categoría.
     */
    public function test_vista_muestra_estado_vacio_ilustrado_cuando_no_hay_actividades(): void
    {
        PlanActivity::query()->where('user_id', $this->usuario->id)->delete();

        $response = $this->actingAs($this->usuario)->get('/historial');

        $response->assertStatus(200);
        $response->assertSee('Sin actividades registradas');
        $response->assertSee('No se encontraron eventos en la categoría seleccionada.');
        $response->assertSee('Ir a mis planes');
    }
}

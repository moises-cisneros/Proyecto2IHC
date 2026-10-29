<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreacionPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    /**
     * Authenticated user creates a plan with organizer assigned automatically from session.
     */
    public function test_usuario_autenticado_crea_plan_con_organizador_automatico(): void
    {
        $user = User::factory()->create([
            'name' => 'Carlos Santana',
            'email' => 'carlos@example.com',
        ]);

        $response = $this->actingAs($user)->post('/planes', [
            'nombre' => 'Asado de Fin de Año',
            'fecha_limite' => '2026-12-31',
        ]);

        $response->assertRedirect('/mis-planes');
        $response->assertSessionHas('exito', '¡Plan creado!');

        $this->assertDatabaseHas('planes', [
            'nombre' => 'Asado de Fin de Año',
            'organizador' => 'Carlos Santana',
            'user_id' => $user->id,
            'estado' => 'pendiente',
        ]);
    }

    /**
     * Plan creation fails with validation error when name is missing.
     */
    public function test_creacion_plan_falla_sin_nombre(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/planes', [
            'fecha_limite' => '2026-12-31',
        ]);

        $response->assertSessionHasErrors(['nombre']);
        $this->assertDatabaseCount('planes', 0);
    }

    /**
     * Plan creation fails with validation error when deadline date is missing.
     */
    public function test_creacion_plan_falla_sin_fecha_limite(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/planes', [
            'nombre' => 'Cena de Equipo',
        ]);

        $response->assertSessionHasErrors(['fecha_limite']);
        $this->assertDatabaseCount('planes', 0);
    }

    /**
     * Unauthenticated guest is redirected to login when trying to create a plan.
     */
    public function test_usuario_no_autenticado_no_puede_crear_plan(): void
    {
        $response = $this->post('/planes', [
            'nombre' => 'Plan Fantasma',
            'fecha_limite' => '2026-12-31',
        ]);

        $response->assertRedirect('/ingresar');
        $this->assertDatabaseCount('planes', 0);
    }

    /**
     * Mis planes view displays read-only organizer badge and omits organizer text input.
     */
    public function test_vista_mis_planes_muestra_badge_organizador_y_omite_input(): void
    {
        $user = User::factory()->create([
            'name' => 'Mariana Ruiz',
        ]);

        $response = $this->actingAs($user)->get('/mis-planes');

        $response->assertStatus(200);
        $response->assertSee('Organizador: <strong>Mariana Ruiz</strong>', false);
        $response->assertDontSee('name="organizador"', false);
    }
}


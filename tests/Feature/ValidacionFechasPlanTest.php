<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidacionFechasPlanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    /**
     * Caso 1: StorePlanRequest falla si se envia una fecha anterior a la fecha actual.
     */
    public function test_creacion_falla_si_fecha_limite_es_pasada(): void
    {
        $user = User::factory()->create();
        $fechaPasada = Carbon::yesterday()->format('Y-m-d');

        $response = $this->actingAs($user)->post('/planes', [
            'nombre'       => 'Plan en el pasado',
            'fecha_limite' => $fechaPasada,
        ]);

        $response->assertSessionHasErrors(['fecha_limite']);
        $errores = session('errors')->get('fecha_limite');
        $this->assertStringContainsString('igual o posterior a la fecha actual', $errores[0]);
        $this->assertDatabaseCount('planes', 0);
    }

    /**
     * Caso 2: UpdatePlanRequest falla si se actualiza un plan con una fecha pasada.
     */
    public function test_actualizacion_falla_si_fecha_limite_es_pasada(): void
    {
        $user = User::factory()->create();
        $plan = Plan::create([
            'nombre'       => 'Plan Original',
            'organizador'  => $user->name,
            'fecha_limite' => Carbon::tomorrow()->format('Y-m-d'),
            'user_id'      => $user->id,
            'estado'       => 'pendiente',
        ]);

        $fechaPasada = Carbon::yesterday()->format('Y-m-d');

        $response = $this->actingAs($user)->put("/planes/{$plan->id}", [
            'nombre'       => 'Plan Modificado Pasado',
            'fecha_limite' => $fechaPasada,
        ]);

        $response->assertSessionHasErrors(['fecha_limite']);
        $plan->refresh();
        $this->assertEquals('Plan Original', $plan->nombre);
    }

    /**
     * Caso 3: Creacion y actualizacion son exitosas con fecha valida (hoy o futura).
     */
    public function test_creacion_y_actualizacion_exitosas_con_fecha_valida(): void
    {
        $user = User::factory()->create();
        $fechaFutura = Carbon::now()->addDays(5)->format('Y-m-d');

        // Creacion exitosa
        $responseCreate = $this->actingAs($user)->post('/planes', [
            'nombre'       => 'Viaje Futuro',
            'fecha_limite' => $fechaFutura,
        ]);

        $responseCreate->assertRedirect('/mis-planes');
        $responseCreate->assertSessionHas('exito', '¡Plan creado!');

        $plan = Plan::where('nombre', 'Viaje Futuro')->first();
        $this->assertNotNull($plan);

        // Actualizacion exitosa
        $fechaPosterior = Carbon::now()->addDays(10)->format('Y-m-d');
        $responseUpdate = $this->actingAs($user)->put("/planes/{$plan->id}", [
            'nombre'       => 'Viaje Futuro Reprogramado',
            'fecha_limite' => $fechaPosterior,
        ]);

        $responseUpdate->assertRedirect('/mis-planes');
        $plan->refresh();
        $this->assertEquals('Viaje Futuro Reprogramado', $plan->nombre);
    }

    /**
     * Caso 4 (Seguridad / Acceso): Usuario no autenticado es redirigido al login.
     */
    public function test_usuario_no_autenticado_es_redirigido_a_login(): void
    {
        $user = User::factory()->create();
        $plan = Plan::create([
            'nombre'       => 'Plan Privado',
            'organizador'  => $user->name,
            'fecha_limite' => Carbon::tomorrow()->format('Y-m-d'),
            'user_id'      => $user->id,
            'estado'       => 'pendiente',
        ]);

        // Intento anonimo de crear plan
        $responsePost = $this->post('/planes', [
            'nombre'       => 'Intento Anónimo',
            'fecha_limite' => Carbon::tomorrow()->format('Y-m-d'),
        ]);
        $responsePost->assertRedirect('/ingresar');

        // Intento anonimo de ver formulario de edicion
        $responseGetEdit = $this->get("/planes/{$plan->id}/editar");
        $responseGetEdit->assertRedirect('/ingresar');

        // Intento anonimo de actualizar plan
        $responsePut = $this->put("/planes/{$plan->id}", [
            'nombre'       => 'Intento Hack',
            'fecha_limite' => Carbon::tomorrow()->format('Y-m-d'),
        ]);
        $responsePut->assertRedirect('/ingresar');
    }
}

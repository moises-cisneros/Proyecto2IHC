<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\PlanActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistorialConsultaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: test_usuario_no_autenticado_no_puede_consultar_historial
     */
    public function test_usuario_no_autenticado_no_puede_consultar_historial(): void
    {
        $response = $this->get('/historial');

        $response->assertRedirect('/ingresar');
    }

    /**
     * Caso 2: test_usuario_solo_ve_sus_propias_actividades_en_el_historial
     */
    public function test_usuario_solo_ve_sus_propias_actividades_en_el_historial(): void
    {
        $usuarioA = User::factory()->create(['name' => 'Usuario A']);
        $usuarioB = User::factory()->create(['name' => 'Usuario B']);

        $planA = Plan::factory()->create(['user_id' => $usuarioA->id, 'nombre' => 'Plan de Usuario A']);
        $planB = Plan::factory()->create(['user_id' => $usuarioB->id, 'nombre' => 'Plan de Usuario B']);

        // Limpiamos actividades automáticas previas para aislar la prueba
        PlanActivity::query()->delete();

        $actividadA = PlanActivity::create([
            'plan_id' => $planA->id,
            'user_id' => $usuarioA->id,
            'accion' => 'actualizacion',
            'descripcion' => 'Actividad privada de Usuario A',
            'estado_resultante' => 'pendiente',
        ]);

        $actividadB = PlanActivity::create([
            'plan_id' => $planB->id,
            'user_id' => $usuarioB->id,
            'accion' => 'actualizacion',
            'descripcion' => 'Actividad privada de Usuario B',
            'estado_resultante' => 'pendiente',
        ]);

        $response = $this->actingAs($usuarioA)->get('/historial');

        $response->assertStatus(200);
        $response->assertSee('Actividad privada de Usuario A');
        $response->assertDontSee('Actividad privada de Usuario B');

        $actividades = $response->viewData('actividades');
        $this->assertTrue($actividades->contains($actividadA));
        $this->assertFalse($actividades->contains($actividadB));
    }

    /**
     * Caso 3: test_filtro_por_categoria_estados_retorna_solo_cambios_de_estado
     */
    public function test_filtro_por_categoria_estados_retorna_solo_cambios_de_estado(): void
    {
        $usuario = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $usuario->id]);

        PlanActivity::query()->where('user_id', $usuario->id)->delete();

        $actividadEstado = PlanActivity::create([
            'plan_id' => $plan->id,
            'user_id' => $usuario->id,
            'accion' => 'cambio_estado',
            'descripcion' => 'Estado cambiado a confirmado.',
            'estado_resultante' => 'confirmado',
        ]);

        $actividadCreacion = PlanActivity::create([
            'plan_id' => $plan->id,
            'user_id' => $usuario->id,
            'accion' => 'creacion',
            'descripcion' => 'Plan de prueba creado.',
            'estado_resultante' => 'pendiente',
        ]);

        $response = $this->actingAs($usuario)->get('/historial?categoria=estados');

        $response->assertStatus(200);
        $response->assertSee('Estado cambiado a confirmado.');
        $response->assertDontSee('Plan de prueba creado.');

        $actividades = $response->viewData('actividades');
        $this->assertCount(1, $actividades);
        $this->assertEquals($actividadEstado->id, $actividades->first()->id);
    }

    /**
     * Caso 4: test_actividades_se_retornan_en_orden_cronologico_descendente
     */
    public function test_actividades_se_retornan_en_orden_cronologico_descendente(): void
    {
        $usuario = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $usuario->id]);

        PlanActivity::query()->where('user_id', $usuario->id)->delete();

        $antigua = PlanActivity::create([
            'plan_id' => $plan->id,
            'user_id' => $usuario->id,
            'accion' => 'creacion',
            'descripcion' => 'Evento ocurrido hace 2 horas',
            'created_at' => now()->subHours(2),
        ]);

        $intermedia = PlanActivity::create([
            'plan_id' => $plan->id,
            'user_id' => $usuario->id,
            'accion' => 'actualizacion',
            'descripcion' => 'Evento ocurrido hace 1 hora',
            'created_at' => now()->subHour(),
        ]);

        $reciente = PlanActivity::create([
            'plan_id' => $plan->id,
            'user_id' => $usuario->id,
            'accion' => 'cambio_estado',
            'descripcion' => 'Evento ocurrido ahora mismo',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($usuario)->get('/historial');

        $response->assertStatus(200);
        $actividades = $response->viewData('actividades');

        $this->assertCount(3, $actividades);
        $this->assertEquals($reciente->id, $actividades[0]->id);
        $this->assertEquals($intermedia->id, $actividades[1]->id);
        $this->assertEquals($antigua->id, $actividades[2]->id);
    }

    /**
     * Caso 5 (complementario): test_filtro_por_categoria_creaciones_retorna_solo_creaciones
     */
    public function test_filtro_por_categoria_creaciones_retorna_solo_creaciones(): void
    {
        $usuario = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $usuario->id]);

        PlanActivity::query()->where('user_id', $usuario->id)->delete();

        $creacion = PlanActivity::create([
            'plan_id' => $plan->id,
            'user_id' => $usuario->id,
            'accion' => 'creacion',
            'descripcion' => 'Plan inicial registrado.',
            'estado_resultante' => 'pendiente',
        ]);

        $estado = PlanActivity::create([
            'plan_id' => $plan->id,
            'user_id' => $usuario->id,
            'accion' => 'cambio_estado',
            'descripcion' => 'Plan cancelado.',
            'estado_resultante' => 'cancelado',
        ]);

        $response = $this->actingAs($usuario)->get('/historial?categoria=creaciones');

        $response->assertStatus(200);
        $actividades = $response->viewData('actividades');

        $this->assertCount(1, $actividades);
        $this->assertEquals($creacion->id, $actividades->first()->id);
    }

    /**
     * Caso 6 (complementario): test_filtro_por_categoria_participacion_retorna_eventos_participativos
     */
    public function test_filtro_por_categoria_participacion_retorna_eventos_participativos(): void
    {
        $usuario = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $usuario->id]);

        PlanActivity::query()->where('user_id', $usuario->id)->delete();

        $participacion = PlanActivity::create([
            'plan_id' => $plan->id,
            'user_id' => $usuario->id,
            'accion' => 'voto',
            'descripcion' => 'Votó por una opción de fecha.',
        ]);

        $creacion = PlanActivity::create([
            'plan_id' => $plan->id,
            'user_id' => $usuario->id,
            'accion' => 'creacion',
            'descripcion' => 'Plan creado.',
        ]);

        $response = $this->actingAs($usuario)->get('/historial?categoria=participacion');

        $response->assertStatus(200);
        $actividades = $response->viewData('actividades');

        $this->assertCount(1, $actividades);
        $this->assertEquals($participacion->id, $actividades->first()->id);
    }
}

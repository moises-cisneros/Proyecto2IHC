<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\PlanActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanActivityTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;
    private Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->usuario = User::factory()->create();
        $this->plan = Plan::factory()->create(['user_id' => $this->usuario->id]);
    }

    /**
     * Caso 1: test_creacion_de_actividad_con_datos_validos_persiste_en_bd
     */
    public function test_creacion_de_actividad_con_datos_validos_persiste_en_bd(): void
    {
        $actividad = PlanActivity::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->usuario->id,
            'accion' => 'actualizacion',
            'descripcion' => 'Se actualizó la información general del plan.',
            'estado_resultante' => 'pendiente',
        ]);

        $this->assertDatabaseHas('plan_activities', [
            'id' => $actividad->id,
            'plan_id' => $this->plan->id,
            'user_id' => $this->usuario->id,
            'accion' => 'actualizacion',
            'descripcion' => 'Se actualizó la información general del plan.',
            'estado_resultante' => 'pendiente',
        ]);
    }

    /**
     * Caso 2: test_actividad_pertenece_a_plan_y_a_usuario
     */
    public function test_actividad_pertenece_a_plan_y_a_usuario(): void
    {
        $actividad = PlanActivity::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->usuario->id,
            'accion' => 'creacion',
            'descripcion' => 'Registro de auditoría inicial.',
            'estado_resultante' => 'pendiente',
        ]);

        $this->assertInstanceOf(Plan::class, $actividad->plan);
        $this->assertEquals($this->plan->id, $actividad->plan->id);

        $this->assertInstanceOf(User::class, $actividad->user);
        $this->assertEquals($this->usuario->id, $actividad->user->id);

        $this->assertTrue($this->plan->actividades->contains($actividad));
        $this->assertTrue($this->usuario->actividades->contains($actividad));
    }

    /**
     * Caso 3: test_cambio_de_estado_de_plan_genera_registro_en_historial
     */
    public function test_cambio_de_estado_de_plan_genera_registro_en_historial(): void
    {
        // El plan ya genero su actividad de creacion en setUp()
        $conteoInicial = $this->plan->actividades()->count();

        // Cambiar el estado del plan
        $this->plan->estado = 'confirmado';
        $this->plan->save();

        $this->assertEquals($conteoInicial + 1, $this->plan->actividades()->count());

        $ultimaActividad = $this->plan->actividades()->latest('id')->first();
        $this->assertNotNull($ultimaActividad);
        $this->assertEquals('cambio_estado', $ultimaActividad->accion);
        $this->assertEquals('confirmado', $ultimaActividad->estado_resultante);
        $this->assertStringContainsString('confirmado', $ultimaActividad->descripcion);
    }

    /**
     * Caso 4: test_scope_recientes_ordena_cronologicamente_descendente
     */
    public function test_scope_recientes_ordena_cronologicamente_descendente(): void
    {
        // Limpiamos actividades previas para probar orden explicito
        PlanActivity::query()->delete();

        $antigua = PlanActivity::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->usuario->id,
            'accion' => 'creacion',
            'descripcion' => 'Actividad más antigua',
            'estado_resultante' => 'pendiente',
            'created_at' => now()->subHours(2),
        ]);

        $intermedia = PlanActivity::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->usuario->id,
            'accion' => 'actualizacion',
            'descripcion' => 'Actividad intermedia',
            'estado_resultante' => 'pendiente',
            'created_at' => now()->subHour(),
        ]);

        $reciente = PlanActivity::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->usuario->id,
            'accion' => 'cambio_estado',
            'descripcion' => 'Actividad más reciente',
            'estado_resultante' => 'confirmado',
            'created_at' => now(),
        ]);

        $ordenadas = PlanActivity::recientes()->get();

        $this->assertCount(3, $ordenadas);
        $this->assertEquals($reciente->id, $ordenadas[0]->id);
        $this->assertEquals($intermedia->id, $ordenadas[1]->id);
        $this->assertEquals($antigua->id, $ordenadas[2]->id);
    }

    /**
     * Caso 5 (complementario): test_creacion_de_plan_registra_evento_en_historial_automaticamente
     */
    public function test_creacion_de_plan_registra_evento_en_historial_automaticamente(): void
    {
        $nuevoPlan = Plan::create([
            'nombre' => 'Cena de celebración',
            'organizador' => $this->usuario->name,
            'fecha_limite' => now()->addDays(5),
            'user_id' => $this->usuario->id,
            'estado' => 'pendiente',
        ]);

        $this->assertDatabaseHas('plan_activities', [
            'plan_id' => $nuevoPlan->id,
            'user_id' => $this->usuario->id,
            'accion' => 'creacion',
            'estado_resultante' => 'pendiente',
        ]);
    }
}

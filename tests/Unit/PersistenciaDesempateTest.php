<?php

namespace Tests\Unit;

use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use App\Models\Voto;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersistenciaDesempateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: Verificacion de ganador unico cuando una propuesta supera a las demas en votos.
     */
    public function test_deteccion_de_ganador_unico_por_mayoria_estricta(): void
    {
        $organizador = User::factory()->create();
        $participante1 = User::factory()->create();
        $participante2 = User::factory()->create();

        $plan = Plan::create([
            'nombre' => 'Cumpleaños en Grupo',
            'organizador' => $organizador->name,
            'fecha_limite' => Carbon::now()->addDays(7),
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        $fecha1 = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-15 20:00:00',
        ]);

        $fecha2 = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante1->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-16 20:00:00',
        ]);

        // fecha1 tiene 2 votos, fecha2 tiene 1 voto
        Voto::create(['opcion_id' => $fecha1->id, 'user_id' => $organizador->id]);
        Voto::create(['opcion_id' => $fecha1->id, 'user_id' => $participante1->id]);
        Voto::create(['opcion_id' => $fecha2->id, 'user_id' => $participante2->id]);

        $this->assertFalse($plan->tieneEmpateFechas());
        $ganador = $plan->obtenerGanadorIndiscutido('fecha');
        $this->assertNotNull($ganador);
        $this->assertEquals($fecha1->id, $ganador->id);
    }

    /**
     * Caso 2: Verificacion de deteccion de empate cuando dos o mas propuestas comparten el maximo de votos.
     */
    public function test_deteccion_de_empate_entre_propuestas_mas_votadas(): void
    {
        $organizador = User::factory()->create();
        $votante1 = User::factory()->create();
        $votante2 = User::factory()->create();

        $plan = Plan::create([
            'nombre' => 'Cena de Despedida',
            'organizador' => $organizador->name,
            'fecha_limite' => Carbon::now()->addDays(5),
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        $lugar1 = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'lugar',
            'valor' => 'Parrilla La Estancia',
        ]);

        $lugar2 = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $votante1->id,
            'tipo' => 'lugar',
            'valor' => 'Pizzeria Napolitana',
        ]);

        // Cada lugar tiene 1 voto -> empate
        Voto::create(['opcion_id' => $lugar1->id, 'user_id' => $votante1->id]);
        Voto::create(['opcion_id' => $lugar2->id, 'user_id' => $votante2->id]);

        $this->assertTrue($plan->tieneEmpateLugares());
        $this->assertNull($plan->obtenerGanadorIndiscutido('lugar'));

        $empatadas = $plan->obtenerPropuestasEmpatadas('lugar');
        $this->assertCount(2, $empatadas);
        $this->assertTrue($empatadas->pluck('id')->contains($lugar1->id));
        $this->assertTrue($empatadas->pluck('id')->contains($lugar2->id));
    }

    /**
     * Caso 3: Persistencia de la opcion ganadora y el texto de motivo_desempate.
     */
    public function test_persistencia_de_opcion_ganadora_y_motivo_de_desempate(): void
    {
        $organizador = User::factory()->create();

        $plan = Plan::create([
            'nombre' => 'Paseo al Aire Libre',
            'organizador' => $organizador->name,
            'fecha_limite' => Carbon::now()->addDays(3),
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        $fechaGanadora = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-11-20 10:00:00',
        ]);

        $motivo = 'Elegida porque la mayoria prefiere horario matutino.';
        $plan->asignarGanadora($fechaGanadora, $motivo);
        $plan->estado = 'confirmado';
        $plan->fecha_confirmacion = now();
        $plan->save();

        $plan->refresh();
        $this->assertEquals($fechaGanadora->id, $plan->propuesta_fecha_id);
        $this->assertEquals($motivo, $plan->motivo_desempate);
        $this->assertEquals('confirmado', $plan->estado);
        $this->assertNotNull($plan->fecha_confirmacion);
        $this->assertEquals($fechaGanadora->id, $plan->propuestaFechaGanadora->id);
    }

    /**
     * Caso 4 (Seguridad / Integridad): Una propuesta ajena al plan no puede ser asignada como ganadora.
     */
    public function test_seguridad_propuesta_ajena_no_puede_ser_asignada_como_ganadora(): void
    {
        $organizador = User::factory()->create();
        $otroUsuario = User::factory()->create();

        $planA = Plan::create([
            'nombre' => 'Plan A',
            'organizador' => $organizador->name,
            'fecha_limite' => Carbon::now()->addDays(4),
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        $planB = Plan::create([
            'nombre' => 'Plan B Ajeno',
            'organizador' => $otroUsuario->name,
            'fecha_limite' => Carbon::now()->addDays(4),
            'user_id' => $otroUsuario->id,
            'estado' => 'pendiente',
        ]);

        $propuestaAjena = Opcion::create([
            'plan_id' => $planB->id,
            'user_id' => $otroUsuario->id,
            'tipo' => 'fecha',
            'valor' => '2026-11-25 18:00:00',
        ]);

        $this->assertFalse($planA->validarPropuestaPertenece($propuestaAjena, 'fecha'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('no pertenece al plan');

        $planA->asignarGanadora($propuestaAjena, 'Intento de asignacion indebida');
    }
}

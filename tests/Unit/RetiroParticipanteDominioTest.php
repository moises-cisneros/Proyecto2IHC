<?php

namespace Tests\Unit;

use App\Models\Gasto;
use App\Models\GastoDivision;
use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use App\Models\Voto;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetiroParticipanteDominioTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: Participante sin deuda se retira; sus 2 votos son eliminados y el total de votos desciende en 2.
     */
    public function test_participante_sin_deuda_se_retira_anulando_sus_dos_votos(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
        ]);

        $plan->invitaciones()->create([
            'email' => $participante->email,
            'user_id' => $participante->id,
            'estado' => 'aceptada',
            'token' => 'tok_participante_1',
        ]);

        $opcionFecha = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-15T18:00',
        ]);

        $opcionLugar = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'lugar',
            'valor' => 'Plaza Principal',
        ]);

        // Participante emite 2 votos
        Voto::create(['opcion_id' => $opcionFecha->id, 'user_id' => $participante->id]);
        Voto::create(['opcion_id' => $opcionLugar->id, 'user_id' => $participante->id]);

        $this->assertEquals(1, $opcionFecha->votos()->count());
        $this->assertEquals(1, $opcionLugar->votos()->count());
        $this->assertTrue($plan->participantes()->where('users.id', $participante->id)->exists());

        // Acción de dominio: retirar participante
        $plan->retirarParticipante($participante);

        // Aserciones: conteo de votos de las opciones desciende exactamente a 0
        $this->assertEquals(0, $opcionFecha->votos()->count());
        $this->assertEquals(0, $opcionLugar->votos()->count());
        $this->assertDatabaseMissing('votos', [
            'user_id' => $participante->id,
        ]);
        $this->assertFalse($plan->fresh()->participantes()->where('users.id', $participante->id)->exists());
    }

    /**
     * Caso 2: Participante con saldo deudor pendiente intenta retirarse; el sistema arroja excepción y preserva membresía y votos.
     */
    public function test_participante_con_saldo_deudor_pendiente_no_puede_retirarse(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
        ]);

        $plan->invitaciones()->create([
            'email' => $participante->email,
            'user_id' => $participante->id,
            'estado' => 'aceptada',
            'token' => 'tok_deudor_1',
        ]);

        $opcion = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'lugar',
            'valor' => 'Restaurante El Jardín',
        ]);

        Voto::create(['opcion_id' => $opcion->id, 'user_id' => $participante->id]);

        // Crear gasto pagado por el organizador donde el participante adeuda 50 Bs
        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Alquiler del salón',
            'monto_total' => 100.00,
            'tipo_division' => 'personalizada',
        ]);

        GastoDivision::create([
            'gasto_id' => $gasto->id,
            'user_id' => $organizador->id,
            'monto_asignado' => 50.00,
        ]);

        GastoDivision::create([
            'gasto_id' => $gasto->id,
            'user_id' => $participante->id,
            'monto_asignado' => 50.00,
        ]);

        $this->assertTrue($plan->usuarioTieneDeuda($participante->id));

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('No puedes retirarte mientras tengas deudas pendientes.');

        try {
            $plan->retirarParticipante($participante);
        } finally {
            // Membresía y votos permanecen intactos
            $this->assertTrue($plan->fresh()->participantes()->where('users.id', $participante->id)->exists());
            $this->assertEquals(1, $opcion->votos()->count());
        }
    }

    /**
     * Caso 3: Organizador intenta retirarse; el sistema bloquea la operación arrojando excepción.
     */
    public function test_organizador_no_puede_retirarse_de_su_propio_plan(): void
    {
        $organizador = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('El organizador no puede retirarse del plan.');

        $plan->retirarParticipante($organizador);
    }

    /**
     * Caso 4 (Trazabilidad): El retiro exitoso genera una actividad formal en la bitácora del plan.
     */
    public function test_retiro_exitoso_genera_registro_en_historial_de_actividades(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create(['name' => 'Carla Méndez']);

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
        ]);

        $plan->invitaciones()->create([
            'email' => $participante->email,
            'user_id' => $participante->id,
            'estado' => 'aceptada',
            'token' => 'tok_carla_1',
        ]);

        $plan->retirarParticipante($participante);

        $this->assertDatabaseHas('plan_activities', [
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'accion' => 'participante_retirado',
            'descripcion' => 'Carla Méndez se retiró del plan.',
            'estado_resultante' => 'pendiente',
        ]);
    }
}

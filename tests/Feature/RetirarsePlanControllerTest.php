<?php

namespace Tests\Feature;

use App\Models\Gasto;
use App\Models\GastoDivision;
use App\Models\Invitacion;
use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use App\Models\Voto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetirarsePlanControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: Test positivo de retiro exitoso con redirección 302 a planes.listar.
     */
    public function test_participante_puede_retirarse_exitosamente_de_un_plan_pendiente(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
        ]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'estado' => 'aceptada',
            'token' => 'tok_participante_1',
        ]);

        $opcion = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-15 18:00',
        ]);

        Voto::create([
            'opcion_id' => $opcion->id,
            'user_id' => $participante->id,
        ]);

        $response = $this->actingAs($participante)->post(route('planes.retirarse', $plan));

        $response->assertStatus(302);
        $response->assertRedirect(route('planes.listar'));
        $response->assertSessionHas('exito');

        // Membresía y votos desvinculados
        $this->assertDatabaseMissing('invitaciones', [
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
        ]);
        $this->assertDatabaseMissing('votos', [
            'opcion_id' => $opcion->id,
            'user_id' => $participante->id,
        ]);

        // Eventos analíticos registrados
        $this->assertDatabaseHas('eventos', [
            'evento' => 'participante_retirado',
        ]);
        $this->assertDatabaseHas('eventos', [
            'evento' => 'retiro_confirmado',
        ]);
    }

    /**
     * Caso 2: Test negativo de usuario anónimo redirigiendo 302 a login.
     */
    public function test_usuario_anonimo_redirige_a_login(): void
    {
        $organizador = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
        ]);

        $response = $this->post(route('planes.retirarse', $plan));

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * Caso 3: Test negativo de organizador recibiendo 403 Forbidden al intentar retirarse.
     */
    public function test_organizador_recibe_403_forbidden_al_intentar_retirarse(): void
    {
        $organizador = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($organizador)->post(route('planes.retirarse', $plan));

        $response->assertStatus(403);
    }

    /**
     * Caso 4 (Seguridad / Deuda): Test negativo de participante con deuda recibiendo mensaje flash de error.
     */
    public function test_participante_con_deuda_recibe_error_flash_y_bloquea_retiro(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
        ]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'estado' => 'aceptada',
            'token' => 'tok_participante_deuda',
        ]);

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

        $response = $this->actingAs($participante)->from(route('planes.detalle', $plan))
            ->post(route('planes.retirarse', $plan));

        $response->assertStatus(302);
        $response->assertRedirect(route('planes.detalle', $plan));
        $response->assertSessionHas('error');

        // La membresía sigue intacta
        $this->assertDatabaseHas('invitaciones', [
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'estado' => 'aceptada',
        ]);

        // Se registra evento de freno por deuda
        $this->assertDatabaseHas('eventos', [
            'evento' => 'error_freno_tarea',
        ]);
        $this->assertDatabaseHas('eventos', [
            'evento' => 'retiro_bloqueado_deuda',
        ]);
    }

    /**
     * Caso 5: Test negativo de usuario ajeno/no participante recibiendo 403 Forbidden.
     */
    public function test_usuario_ajeno_recibe_403_forbidden(): void
    {
        $organizador = User::factory()->create();
        $usuarioAjeno = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($usuarioAjeno)->post(route('planes.retirarse', $plan));

        $response->assertStatus(403);
    }

    /**
     * Caso 6: Test de validación 422 si el plan no se encuentra en estado pendiente al solicitar JSON.
     */
    public function test_retiro_en_plan_confirmado_retorna_422_en_solicitud_json(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'confirmado',
        ]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'estado' => 'aceptada',
            'token' => 'tok_participante_confirmado',
        ]);

        $response = $this->actingAs($participante)->postJson(route('planes.retirarse', $plan));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['plan']);
    }
}

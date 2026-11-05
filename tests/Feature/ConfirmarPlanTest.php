<?php

namespace Tests\Feature;

use App\Models\Invitacion;
use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use App\Models\Voto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmarPlanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: El organizador confirma un plan con ganador indiscutido exitosamente.
     */
    public function test_organizador_confirma_plan_con_ganador_indiscutido_exitosamente(): void
    {
        $organizador = User::factory()->create();

        $plan = Plan::create([
            'user_id' => $organizador->id,
            'nombre' => 'Viaje al Lago',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(5),
            'estado' => 'pendiente',
        ]);

        $opcionFecha1 = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-15T10:00',
        ]);

        $opcionFecha2 = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-20T10:00',
        ]);

        // Voto para hacer a opcionFecha1 ganadora indiscutida
        Voto::create([
            'opcion_id' => $opcionFecha1->id,
            'user_id' => $organizador->id,
        ]);

        $response = $this->actingAs($organizador)->post(route('planes.confirmar', $plan), [
            'propuesta_fecha_id' => $opcionFecha1->id,
            'propuesta_lugar_id' => null,
            'motivo_desempate' => null,
        ]);

        $response->assertRedirect(route('planes.detalle', $plan));
        $response->assertSessionHas('exito');

        $plan->refresh();
        $this->assertSame('confirmado', $plan->estado);
        $this->assertEquals($opcionFecha1->id, $plan->propuesta_fecha_id);
        $this->assertNull($plan->propuesta_lugar_id);
        $this->assertNotNull($plan->fecha_confirmacion);
    }

    /**
     * Caso 2: El organizador resuelve un empate seleccionando una opción empatada y aportando un motivo válido.
     */
    public function test_organizador_resuelve_empate_enviando_opcion_y_motivo_valido(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::create([
            'user_id' => $organizador->id,
            'nombre' => 'Cena de Fin de Año',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(5),
            'estado' => 'pendiente',
        ]);

        $opcion1 = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-30T20:00',
        ]);

        $opcion2 = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-31T20:00',
        ]);

        // Simular empate: 1 voto cada una
        Voto::create(['opcion_id' => $opcion1->id, 'user_id' => $organizador->id]);
        Voto::create(['opcion_id' => $opcion2->id, 'user_id' => $participante->id]);

        $this->assertTrue($plan->tieneEmpateFechas());

        $motivo = 'Elegí el 31 porque la mayoría de participantes confirmó mejor disponibilidad horaria.';

        $response = $this->actingAs($organizador)->post(route('planes.confirmar', $plan), [
            'propuesta_fecha_id' => $opcion2->id,
            'propuesta_lugar_id' => null,
            'motivo_desempate' => $motivo,
        ]);

        $response->assertRedirect(route('planes.detalle', $plan));
        $response->assertSessionHas('exito');

        $plan->refresh();
        $this->assertSame('confirmado', $plan->estado);
        $this->assertEquals($opcion2->id, $plan->propuesta_fecha_id);
        $this->assertSame($motivo, $plan->motivo_desempate);
    }

    /**
     * Caso 3: Fallo de validación si existe empate y no se envía motivo_desempate o este es muy corto (< 5 caracteres).
     */
    public function test_confirmar_plan_con_empate_sin_motivo_retorna_error_de_validacion(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::create([
            'user_id' => $organizador->id,
            'nombre' => 'Partido de Fútbol',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(5),
            'estado' => 'pendiente',
        ]);

        $opcion1 = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-10T18:00',
        ]);

        $opcion2 = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-11T18:00',
        ]);

        Voto::create(['opcion_id' => $opcion1->id, 'user_id' => $organizador->id]);
        Voto::create(['opcion_id' => $opcion2->id, 'user_id' => $participante->id]);

        $this->assertTrue($plan->tieneEmpateFechas());

        // Petición sin motivo
        $response = $this->actingAs($organizador)->post(route('planes.confirmar', $plan), [
            'propuesta_fecha_id' => $opcion1->id,
            'propuesta_lugar_id' => null,
            'motivo_desempate' => '',
        ]);

        $response->assertSessionHasErrors(['motivo_desempate']);

        // Petición con motivo menor a 5 caracteres
        $responseCorta = $this->actingAs($organizador)->post(route('planes.confirmar', $plan), [
            'propuesta_fecha_id' => $opcion1->id,
            'propuesta_lugar_id' => null,
            'motivo_desempate' => 'Hola',
        ]);

        $responseCorta->assertSessionHasErrors(['motivo_desempate']);

        $plan->refresh();
        $this->assertSame('pendiente', $plan->estado);
    }

    /**
     * Caso 4 (Seguridad / Rechazo 403): Un participante autenticado que no es el creador recibe 403 Forbidden.
     */
    public function test_participante_no_organizador_recibe_403_al_intentar_confirmar_plan(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::create([
            'user_id' => $organizador->id,
            'nombre' => 'Asado Grupal',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(5),
            'estado' => 'pendiente',
        ]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'token' => 'token-participante-403',
            'estado' => 'aceptada',
        ]);

        $opcion = Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-15T12:00',
        ]);

        $response = $this->actingAs($participante)->post(route('planes.confirmar', $plan), [
            'propuesta_fecha_id' => $opcion->id,
            'motivo_desempate' => 'Quiero confirmarlo yo',
        ]);

        $response->assertStatus(403);

        $plan->refresh();
        $this->assertSame('pendiente', $plan->estado);
    }

    /**
     * Caso 5 (Seguridad / Identidad): Usuario anónimo es redirigido a login (HTTP 302).
     */
    public function test_usuario_no_autenticado_es_redirigido_al_intentar_confirmar_plan(): void
    {
        $organizador = User::factory()->create();

        $plan = Plan::create([
            'user_id' => $organizador->id,
            'nombre' => 'Caminata Silvestre',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(5),
            'estado' => 'pendiente',
        ]);

        $response = $this->post(route('planes.confirmar', $plan), [
            'propuesta_fecha_id' => 1,
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * Caso 6 (Seguridad / Bloqueo por Estado): Plan cancelado o ya confirmado deniega confirmación con 403.
     */
    public function test_plan_cancelado_o_ya_confirmado_deniega_confirmacion_con_403(): void
    {
        $organizador = User::factory()->create();

        $planCancelado = Plan::create([
            'user_id' => $organizador->id,
            'nombre' => 'Viaje Cancelado',
            'organizador' => $organizador->name,
            'fecha_limite' => now()->addDays(5),
            'estado' => 'cancelado',
        ]);

        $opcion = Opcion::create([
            'plan_id' => $planCancelado->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-15T12:00',
        ]);

        $response = $this->actingAs($organizador)->post(route('planes.confirmar', $planCancelado), [
            'propuesta_fecha_id' => $opcion->id,
        ]);

        $response->assertStatus(403);
    }
}

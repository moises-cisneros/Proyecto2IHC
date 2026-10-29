<?php

namespace Tests\Feature;

use App\Models\Invitacion;
use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use App\Models\Voto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbandonoPlanTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: Un participante con invitación aceptada puede desvincularse del plan grupal.
     */
    public function test_participante_aceptado_puede_abandonar_plan_compartido(): void
    {
        $creador = User::factory()->create();
        $participante = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $creador->id]);

        Invitacion::factory()->aceptada($participante)->create([
            'plan_id' => $plan->id,
            'email'   => $participante->email,
        ]);

        $response = $this->actingAs($participante)
            ->post(route('planes.abandonar', $plan));

        $response->assertRedirect(route('planes.listar'));
        $response->assertSessionHas('exito');

        // La membresía en invitaciones para ese plan ya no debe existir
        $this->assertDatabaseMissing('invitaciones', [
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
        ]);

        $plan->refresh();
        $this->assertFalse($plan->participantes->contains('id', $participante->id));
    }

    /**
     * Caso 2: El creador u organizador no puede abandonar su propio plan (debe cancelarlo).
     */
    public function test_creador_no_puede_abandonar_su_propio_plan_debe_cancelarlo(): void
    {
        $creador = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $creador->id]);

        $response = $this->actingAs($creador)
            ->post(route('planes.abandonar', $plan));

        // Debe ser rechazado con 403 Forbidden
        $response->assertStatus(403);

        $plan->refresh();
        $this->assertEquals($creador->id, $plan->user_id);
    }

    /**
     * Caso 3: Al abandonar el plan, se revoca la membresía del participante sin borrar el plan ni afectar al resto.
     */
    public function test_abandonar_plan_elimina_membresia_sin_borrar_el_plan_para_el_resto(): void
    {
        $creador = User::factory()->create();
        $participante1 = User::factory()->create();
        $participante2 = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $creador->id,
            'nombre'  => 'Festival de Música',
        ]);

        Invitacion::factory()->aceptada($participante1)->create([
            'plan_id' => $plan->id,
            'email'   => $participante1->email,
        ]);

        Invitacion::factory()->aceptada($participante2)->create([
            'plan_id' => $plan->id,
            'email'   => $participante2->email,
        ]);

        // Participante 1 abandona el plan
        $response = $this->actingAs($participante1)
            ->post(route('planes.abandonar', $plan));

        $response->assertRedirect(route('planes.listar'));

        // El plan principal sigue existiendo
        $this->assertDatabaseHas('planes', [
            'id'     => $plan->id,
            'nombre' => 'Festival de Música',
        ]);

        // La membresía de Participante 1 fue eliminada
        $this->assertDatabaseMissing('invitaciones', [
            'plan_id' => $plan->id,
            'user_id' => $participante1->id,
        ]);

        // La membresía de Participante 2 permanece intacta
        $this->assertDatabaseHas('invitaciones', [
            'plan_id' => $plan->id,
            'user_id' => $participante2->id,
            'estado'  => 'aceptada',
        ]);

        $plan->refresh();
        $this->assertTrue($plan->participantes->contains('id', $participante2->id));
        $this->assertFalse($plan->participantes->contains('id', $participante1->id));
    }

    /**
     * Caso 4: Al abandonar el plan, se remueven de manera atómica todos los votos previos del participante.
     */
    public function test_abandonar_plan_remueve_votos_previos_del_participante(): void
    {
        $creador = User::factory()->create();
        $participante1 = User::factory()->create();
        $participante2 = User::factory()->create();

        $plan = Plan::factory()->create(['user_id' => $creador->id]);

        $opcionFecha = Opcion::factory()->create([
            'plan_id' => $plan->id,
            'user_id' => $creador->id,
            'tipo'    => 'fecha',
            'valor'   => '2026-11-20',
        ]);

        $opcionLugar = Opcion::factory()->create([
            'plan_id' => $plan->id,
            'user_id' => $creador->id,
            'tipo'    => 'lugar',
            'valor'   => 'Club Campestre',
        ]);

        Invitacion::factory()->aceptada($participante1)->create([
            'plan_id' => $plan->id,
            'email'   => $participante1->email,
        ]);

        Invitacion::factory()->aceptada($participante2)->create([
            'plan_id' => $plan->id,
            'email'   => $participante2->email,
        ]);

        // Participante 1 vota en ambas opciones
        Voto::factory()->create([
            'opcion_id' => $opcionFecha->id,
            'user_id'   => $participante1->id,
        ]);
        Voto::factory()->create([
            'opcion_id' => $opcionLugar->id,
            'user_id'   => $participante1->id,
        ]);

        // Participante 2 vota en la primera opción
        Voto::factory()->create([
            'opcion_id' => $opcionFecha->id,
            'user_id'   => $participante2->id,
        ]);

        // Participante 1 abandona el plan
        $this->actingAs($participante1)
            ->post(route('planes.abandonar', $plan));

        // Todos los votos del Participante 1 en el plan fueron removidos
        $this->assertDatabaseMissing('votos', [
            'opcion_id' => $opcionFecha->id,
            'user_id'   => $participante1->id,
        ]);
        $this->assertDatabaseMissing('votos', [
            'opcion_id' => $opcionLugar->id,
            'user_id'   => $participante1->id,
        ]);

        // El voto del Participante 2 sigue vigente
        $this->assertDatabaseHas('votos', [
            'opcion_id' => $opcionFecha->id,
            'user_id'   => $participante2->id,
        ]);
    }

    /**
     * Caso complementario: Usuario ajeno o no participante recibe 403 al intentar abandonar.
     */
    public function test_usuario_no_participante_recibe_403_al_intentar_abandonar(): void
    {
        $creador = User::factory()->create();
        $ajeno = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $creador->id]);

        $response = $this->actingAs($ajeno)
            ->post(route('planes.abandonar', $plan));

        $response->assertStatus(403);
    }
}

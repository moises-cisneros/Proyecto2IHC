<?php

namespace Tests\Feature;

use App\Models\Invitacion;
use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use App\Models\Voto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ColaboracionEndpointsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: El creador del plan puede invitar a un colaborador generando un token único.
     */
    public function test_creador_puede_enviar_invitacion_con_token_unico(): void
    {
        $creador = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $creador->id]);

        $response = $this->actingAs($creador)
            ->post(route('planes.invitar', $plan), [
                'email' => 'colaborador1@ejemplo.com',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('invitaciones', [
            'plan_id' => $plan->id,
            'email'   => 'colaborador1@ejemplo.com',
            'estado'  => 'pendiente',
        ]);

        $invitacion1 = Invitacion::where('email', 'colaborador1@ejemplo.com')->first();
        $this->assertNotNull($invitacion1->token);
        $this->assertGreaterThan(20, strlen($invitacion1->token));

        // Enviar una segunda invitación a otro email y validar unicidad del token
        $this->actingAs($creador)
            ->post(route('planes.invitar', $plan), [
                'email' => 'colaborador2@ejemplo.com',
            ]);

        $invitacion2 = Invitacion::where('email', 'colaborador2@ejemplo.com')->first();
        $this->assertNotEquals($invitacion1->token, $invitacion2->token);
    }

    /**
     * Caso 2: Un participante con invitación aceptada puede proponer opciones de fecha o lugar.
     */
    public function test_participante_con_invitacion_aceptada_puede_proponer_fecha_o_lugar(): void
    {
        $creador = User::factory()->create();
        $participante = User::factory()->create(['email' => 'participante@ejemplo.com']);
        $plan = Plan::factory()->create(['user_id' => $creador->id]);

        Invitacion::factory()->aceptada()->create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email'   => $participante->email,
        ]);

        // Proponer opción de fecha
        $responseFecha = $this->actingAs($participante)
            ->post(route('planes.opciones.guardar', $plan), [
                'tipo'        => 'fecha',
                'valor'       => '2026-11-20 19:30:00',
                'descripcion' => 'Viernes por la noche',
            ]);

        $responseFecha->assertSessionHasNoErrors();
        $this->assertDatabaseHas('opciones', [
            'plan_id'     => $plan->id,
            'user_id'     => $participante->id,
            'tipo'        => 'fecha',
            'valor'       => '2026-11-20 19:30:00',
            'descripcion' => 'Viernes por la noche',
        ]);

        // Proponer opción de lugar
        $responseLugar = $this->actingAs($participante)
            ->post(route('planes.opciones.guardar', $plan), [
                'tipo'        => 'lugar',
                'valor'       => 'Pizzería Napolitana',
                'descripcion' => 'Mesa al aire libre',
            ]);

        $responseLugar->assertSessionHasNoErrors();
        $this->assertDatabaseHas('opciones', [
            'plan_id'     => $plan->id,
            'user_id'     => $participante->id,
            'tipo'        => 'lugar',
            'valor'       => 'Pizzería Napolitana',
            'descripcion' => 'Mesa al aire libre',
        ]);
    }

    /**
     * Caso 3: Un usuario ajeno (no invitado) o con invitación pendiente recibe 403 al proponer o votar.
     */
    public function test_usuario_no_invitado_recibe_403_al_intentar_proponer_o_votar(): void
    {
        $creador = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $creador->id]);
        $opcion = Opcion::factory()->create([
            'plan_id' => $plan->id,
            'user_id' => $creador->id,
        ]);

        $ajeno = User::factory()->create();

        // Intento de proponer opción sin invitación
        $responseProponerAjeno = $this->actingAs($ajeno)
            ->post(route('planes.opciones.guardar', $plan), [
                'tipo'  => 'lugar',
                'valor' => 'Lugar no autorizado',
            ]);
        $responseProponerAjeno->assertStatus(403);

        // Intento de votar sin invitación
        $responseVotarAjeno = $this->actingAs($ajeno)
            ->post(route('opciones.votar', $opcion));
        $responseVotarAjeno->assertStatus(403);

        // Usuario con invitación pendiente (aún no aceptada) tampoco puede proponer ni votar
        $usuarioPendiente = User::factory()->create();
        Invitacion::factory()->pendiente()->create([
            'plan_id' => $plan->id,
            'user_id' => $usuarioPendiente->id,
            'email'   => $usuarioPendiente->email,
        ]);

        $responseProponerPendiente = $this->actingAs($usuarioPendiente)
            ->post(route('planes.opciones.guardar', $plan), [
                'tipo'  => 'fecha',
                'valor' => '2026-12-01',
            ]);
        $responseProponerPendiente->assertStatus(403);

        $responseVotarPendiente = $this->actingAs($usuarioPendiente)
            ->post(route('opciones.votar', $opcion));
        $responseVotarPendiente->assertStatus(403);
    }

    /**
     * Caso 4: Un participante con invitación aceptada no puede eliminar ni cancelar el plan del creador.
     */
    public function test_participante_invitado_no_puede_eliminar_ni_cancelar_plan_del_creador(): void
    {
        $creador = User::factory()->create();
        $participante = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $creador->id,
            'estado'  => 'pendiente',
        ]);

        Invitacion::factory()->aceptada()->create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email'   => $participante->email,
        ]);

        // Intento de eliminar el plan por parte del participante
        $responseEliminar = $this->actingAs($participante)
            ->delete(route('planes.eliminar', $plan));
        $responseEliminar->assertStatus(403);
        $this->assertDatabaseHas('planes', ['id' => $plan->id]);

        // Intento de cancelar el plan por parte del participante
        $responseCancelar = $this->actingAs($participante)
            ->put(route('planes.estado', $plan), [
                'estado' => 'cancelado',
            ]);
        $responseCancelar->assertStatus(403);

        $plan->refresh();
        $this->assertEquals('pendiente', $plan->estado);
    }

    /**
     * Caso complementario: Un participante con invitación aceptada puede votar en las opciones.
     */
    public function test_participante_con_invitacion_aceptada_puede_votar_en_opciones(): void
    {
        $creador = User::factory()->create();
        $participante = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $creador->id]);
        $opcion = Opcion::factory()->create([
            'plan_id' => $plan->id,
            'user_id' => $creador->id,
        ]);

        Invitacion::factory()->aceptada()->create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email'   => $participante->email,
        ]);

        $responseVotar = $this->actingAs($participante)
            ->post(route('opciones.votar', $opcion));

        $responseVotar->assertSessionHasNoErrors();
        $this->assertDatabaseHas('votos', [
            'opcion_id' => $opcion->id,
            'user_id'   => $participante->id,
        ]);

        // Conmutar el voto (retirarlo)
        $responseRetirar = $this->actingAs($participante)
            ->post(route('opciones.votar', $opcion));

        $responseRetirar->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('votos', [
            'opcion_id' => $opcion->id,
            'user_id'   => $participante->id,
        ]);
    }

    /**
     * Caso complementario: Usuario no creador recibe 403 al intentar enviar invitaciones.
     */
    public function test_usuario_no_creador_recibe_403_al_intentar_invitar(): void
    {
        $creador = User::factory()->create();
        $participante = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $creador->id]);

        Invitacion::factory()->aceptada()->create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email'   => $participante->email,
        ]);

        $response = $this->actingAs($participante)
            ->post(route('planes.invitar', $plan), [
                'email' => 'otro@ejemplo.com',
            ]);

        $response->assertStatus(403);
    }
}

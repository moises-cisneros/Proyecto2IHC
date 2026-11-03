<?php

namespace Tests\Feature;

use App\Models\Invitacion;
use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use App\Models\Voto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReglasColaboracionCierreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: Un participante puede emitir votos independientes sobre múltiples opciones en un plan abierto.
     */
    public function test_usuario_puede_votar_por_multiples_opciones_en_plan_abierto(): void
    {
        $creador = User::factory()->create();
        $participante = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $creador->id,
            'estado' => 'pendiente',
            'fecha_limite' => now()->addDays(5),
        ]);

        Invitacion::factory()->aceptada($participante)->create([
            'plan_id' => $plan->id,
            'email' => $participante->email,
        ]);

        $opcionFecha1 = Opcion::factory()->create([
            'plan_id' => $plan->id,
            'user_id' => $creador->id,
            'tipo' => 'fecha',
            'valor' => 'Viernes 20:00',
        ]);

        $opcionFecha2 = Opcion::factory()->create([
            'plan_id' => $plan->id,
            'user_id' => $creador->id,
            'tipo' => 'fecha',
            'valor' => 'Sábado 21:00',
        ]);

        $opcionLugar = Opcion::factory()->create([
            'plan_id' => $plan->id,
            'user_id' => $creador->id,
            'tipo' => 'lugar',
            'valor' => 'Pizzería Napolitana',
        ]);

        // Votar por la primera fecha
        $response1 = $this->actingAs($participante)->post(route('opciones.votar', $opcionFecha1));
        $response1->assertSessionHasNoErrors();
        $this->assertDatabaseHas('votos', [
            'opcion_id' => $opcionFecha1->id,
            'user_id' => $participante->id,
        ]);

        // Votar por la segunda fecha (aprobación múltiple simultánea)
        $response2 = $this->actingAs($participante)->post(route('opciones.votar', $opcionFecha2));
        $response2->assertSessionHasNoErrors();
        $this->assertDatabaseHas('votos', [
            'opcion_id' => $opcionFecha2->id,
            'user_id' => $participante->id,
        ]);

        // Votar por el lugar
        $response3 = $this->actingAs($participante)->post(route('opciones.votar', $opcionLugar));
        $response3->assertSessionHasNoErrors();
        $this->assertDatabaseHas('votos', [
            'opcion_id' => $opcionLugar->id,
            'user_id' => $participante->id,
        ]);

        // Verificar que los tres votos coexisten y los contadores aumentan
        $this->assertEquals(1, $opcionFecha1->votos()->count());
        $this->assertEquals(1, $opcionFecha2->votos()->count());
        $this->assertEquals(1, $opcionLugar->votos()->count());
    }

    /**
     * Caso 2: El usuario puede retirar su voto previamente emitido mediante toggle.
     */
    public function test_usuario_puede_retirar_su_voto_previamente_emitido(): void
    {
        $creador = User::factory()->create();
        $participante = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $creador->id,
            'estado' => 'pendiente',
            'fecha_limite' => now()->addDays(3),
        ]);

        Invitacion::factory()->aceptada($participante)->create([
            'plan_id' => $plan->id,
            'email' => $participante->email,
        ]);

        $opcion = Opcion::factory()->create([
            'plan_id' => $plan->id,
            'user_id' => $creador->id,
            'valor' => 'Playa Blanca',
        ]);

        // Emitir primer voto
        $this->actingAs($participante)->post(route('opciones.votar', $opcion));
        $this->assertDatabaseHas('votos', [
            'opcion_id' => $opcion->id,
            'user_id' => $participante->id,
        ]);
        $this->assertEquals(1, $opcion->votos()->count());

        // Pulsar de nuevo el botón de votar para retirar
        $responseToggle = $this->actingAs($participante)->post(route('opciones.votar', $opcion));
        $responseToggle->assertSessionHas('exito', 'Voto retirado.');

        // Comprobar que el voto fue eliminado y el conteo decrementa
        $this->assertDatabaseMissing('votos', [
            'opcion_id' => $opcion->id,
            'user_id' => $participante->id,
        ]);
        $this->assertEquals(0, $opcion->votos()->count());
    }

    /**
     * Caso 3: En un plan confirmado (sala cerrada), cualquier intento de votar o proponer retorna 403 Forbidden.
     */
    public function test_intento_de_votar_o_proponer_en_plan_confirmado_retorna_403(): void
    {
        $creador = User::factory()->create();
        $participante = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $creador->id,
            'estado' => 'confirmado',
            'fecha_limite' => now()->addDays(5),
        ]);

        Invitacion::factory()->aceptada($participante)->create([
            'plan_id' => $plan->id,
            'email' => $participante->email,
        ]);

        $opcion = Opcion::factory()->create([
            'plan_id' => $plan->id,
            'user_id' => $creador->id,
            'valor' => 'Restaurante Central',
        ]);

        // Intento de votar en plan confirmado -> 403
        $responseVotar = $this->actingAs($participante)->post(route('opciones.votar', $opcion));
        $responseVotar->assertStatus(403);
        $this->assertDatabaseMissing('votos', [
            'opcion_id' => $opcion->id,
            'user_id' => $participante->id,
        ]);

        // Intento del creador de votar en plan confirmado -> 403
        $responseVotarCreador = $this->actingAs($creador)->post(route('opciones.votar', $opcion));
        $responseVotarCreador->assertStatus(403);

        // Intento de proponer nueva opción en plan confirmado -> 403
        $responseProponer = $this->actingAs($participante)->post(route('planes.opciones.guardar', $plan), [
            'tipo' => 'lugar',
            'valor' => 'Nuevo Bar',
        ]);
        $responseProponer->assertStatus(403);
    }

    /**
     * Caso 4 (Seguridad / Caso Negativo): Invitar colaboradores sigue permitido en plan confirmado pero queda bloqueado en cancelado.
     */
    public function test_invitacion_permitida_en_plan_confirmado_pero_bloqueada_en_cancelado(): void
    {
        $creador = User::factory()->create();

        // 1. Plan confirmado: el creador puede seguir invitando para sumar personas al evento ya acordado
        $planConfirmado = Plan::factory()->create([
            'user_id' => $creador->id,
            'estado' => 'confirmado',
            'fecha_limite' => now()->addDays(5),
        ]);

        $responseInvitarConfirmado = $this->actingAs($creador)->post(route('planes.invitar', $planConfirmado), [
            'email' => 'nuevo_amigo@example.com',
        ]);
        $responseInvitarConfirmado->assertSessionHasNoErrors();
        $this->assertDatabaseHas('invitaciones', [
            'plan_id' => $planConfirmado->id,
            'email' => 'nuevo_amigo@example.com',
            'estado' => 'pendiente',
        ]);

        // 2. Plan cancelado: invitar colaboradores debe ser rechazado con 403 Forbidden
        $planCancelado = Plan::factory()->create([
            'user_id' => $creador->id,
            'estado' => 'cancelado',
            'fecha_limite' => now()->addDays(5),
        ]);

        $responseInvitarCancelado = $this->actingAs($creador)->post(route('planes.invitar', $planCancelado), [
            'email' => 'otro_amigo@example.com',
        ]);
        $responseInvitarCancelado->assertStatus(403);
        $this->assertDatabaseMissing('invitaciones', [
            'plan_id' => $planCancelado->id,
            'email' => 'otro_amigo@example.com',
        ]);
    }

    /**
     * Caso 5 (Complementario): Intento de votar o proponer en un plan con fecha límite vencida retorna 403.
     */
    public function test_intento_de_votar_o_proponer_en_plan_vencido_retorna_403(): void
    {
        $creador = User::factory()->create();
        $participante = User::factory()->create();
        $planVencido = Plan::factory()->create([
            'user_id' => $creador->id,
            'estado' => 'pendiente',
            'fecha_limite' => now()->subDay(), // Vencido ayer
        ]);

        Invitacion::factory()->aceptada($participante)->create([
            'plan_id' => $planVencido->id,
            'email' => $participante->email,
        ]);

        $opcion = Opcion::factory()->create([
            'plan_id' => $planVencido->id,
            'user_id' => $creador->id,
            'valor' => 'Cine',
        ]);

        $responseVotar = $this->actingAs($participante)->post(route('opciones.votar', $opcion));
        $responseVotar->assertStatus(403);

        $responseProponer = $this->actingAs($participante)->post(route('planes.opciones.guardar', $planVencido), [
            'tipo' => 'fecha',
            'valor' => '2026-11-20 20:00:00',
        ]);
        $responseProponer->assertStatus(403);
    }
}

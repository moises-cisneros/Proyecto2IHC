<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Prueba de simetría (skill dark-patterns): entrar y salir cuestan lo mismo en toda pantalla donde se ofrece.
 */
class SimetriaInvitacionListadoTest extends TestCase
{
    use RefreshDatabase;

    private function invitacionPendiente(): array
    {
        $organizador = User::factory()->create();
        $invitado = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
        ]);
        $plan->invitaciones()->create([
            'email' => $invitado->email,
            'user_id' => $invitado->id,
            'estado' => 'pendiente',
            'token' => 'tok-simetria-'.$plan->id,
        ]);

        return [$plan, $invitado];
    }

    public function test_listado_ofrece_rechazar_junto_a_aceptar_la_invitacion(): void
    {
        [$plan, $invitado] = $this->invitacionPendiente();

        $this->actingAs($invitado)->get(route('planes.listar', ['seccion' => 'invitaciones']))
            ->assertOk()
            ->assertSee(route('planes.aceptar', $plan), false)
            ->assertSee(route('planes.rechazar-invitacion', $plan), false)
            ->assertSee('Aceptar invitación')
            ->assertSee('Rechazar invitación');
    }

    public function test_rechazar_desde_el_listado_deja_de_mostrar_la_invitacion_pendiente(): void
    {
        [$plan, $invitado] = $this->invitacionPendiente();

        $this->actingAs($invitado)->post(route('planes.rechazar-invitacion', $plan))->assertRedirect();

        $this->assertDatabaseHas('invitaciones', [
            'plan_id' => $plan->id,
            'user_id' => $invitado->id,
            'estado' => 'rechazada',
        ]);
    }

    public function test_ajeno_sin_invitacion_recibe_403_al_rechazar_y_anonimo_redirige_302(): void
    {
        [$plan] = $this->invitacionPendiente();

        $this->actingAs(User::factory()->create())
            ->post(route('planes.rechazar-invitacion', $plan))
            ->assertForbidden();

        auth()->logout();
        $this->post(route('planes.rechazar-invitacion', $plan))->assertRedirect(route('login'));
    }

    public function test_plan_cancelado_no_muestra_boton_editar_deshabilitado_sin_explicacion(): void
    {
        $organizador = User::factory()->create();
        Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'cancelado',
        ]);

        $this->actingAs($organizador)->get(route('planes.listar'))
            ->assertOk()
            ->assertDontSee('Editar no disponible en plan cancelado')
            ->assertDontSee('Un plan cancelado no puede editarse');
    }
}

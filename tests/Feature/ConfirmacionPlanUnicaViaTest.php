<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmacionPlanUnicaViaTest extends TestCase
{
    use RefreshDatabase;

    private function planPendienteDe(User $organizador): Plan
    {
        return Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
        ]);
    }

    public function test_cambiar_estado_rechaza_confirmar_y_no_registra_evento_de_confirmacion(): void
    {
        $organizador = User::factory()->create();
        $plan = $this->planPendienteDe($organizador);

        $this->actingAs($organizador)
            ->put(route('planes.estado', $plan), ['estado' => 'confirmado'])
            ->assertSessionHasErrors('estado');

        $this->assertSame('pendiente', $plan->fresh()->estado);
        $this->assertDatabaseMissing('eventos', ['evento' => 'plan_confirmado']);
    }

    public function test_cambiar_estado_sigue_permitiendo_cancelar_un_plan_confirmado(): void
    {
        $organizador = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'confirmado',
        ]);

        $this->actingAs($organizador)
            ->put(route('planes.estado', $plan), ['estado' => 'cancelado'])
            ->assertRedirect(route('planes.listar'));

        $this->assertSame('cancelado', $plan->fresh()->estado);
    }

    public function test_listado_no_ofrece_confirmar_por_formulario_sino_enlace_a_la_sala(): void
    {
        $organizador = User::factory()->create();
        $plan = $this->planPendienteDe($organizador);

        $html = $this->actingAs($organizador)->get(route('planes.listar'))->assertOk()->getContent();

        $this->assertStringNotContainsString('<input type="hidden" name="estado" value="confirmado">', $html);
        $this->assertStringContainsString('para confirmar el plan', $html);
        $this->assertStringContainsString(route('planes.detalle', $plan), $html);
    }

    public function test_cancelar_plan_desde_el_listado_usa_la_variante_destructiva_del_dialogo(): void
    {
        $organizador = User::factory()->create();
        Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'confirmado',
        ]);

        $this->actingAs($organizador)->get(route('planes.listar'))
            ->assertOk()
            ->assertSee('data-type="danger"', false)
            ->assertSee('data-btn-cancel="Mantener plan"', false);
    }

    public function test_plan_cancelado_no_muestra_controles_deshabilitados_sin_explicacion(): void
    {
        $organizador = User::factory()->create();
        Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'cancelado',
        ]);

        $this->actingAs($organizador)->get(route('planes.listar'))
            ->assertOk()
            ->assertDontSee('Un plan cancelado no puede volver a confirmarse');
    }

    public function test_participante_no_puede_cambiar_el_estado_403_y_anonimo_redirige_302(): void
    {
        $organizador = User::factory()->create();
        $plan = $this->planPendienteDe($organizador);

        $this->actingAs(User::factory()->create())
            ->put(route('planes.estado', $plan), ['estado' => 'cancelado'])
            ->assertForbidden();

        auth()->logout();
        $this->put(route('planes.estado', $plan), ['estado' => 'cancelado'])->assertRedirect(route('login'));
    }
}

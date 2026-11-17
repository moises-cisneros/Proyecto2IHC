<?php

namespace Tests\Unit;

use App\Models\Invitacion;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitacionScopeDeUsuarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_encuentra_invitacion_vinculada_por_user_id(): void
    {
        $plan = Plan::factory()->create();
        $usuario = User::factory()->create();
        Invitacion::factory()->aceptada($usuario)->create(['plan_id' => $plan->id, 'email' => 'otro@ejemplo.com']);

        $this->assertTrue($plan->invitaciones()->deUsuario($usuario)->exists());
    }

    public function test_scope_encuentra_invitacion_pendiente_solo_por_correo(): void
    {
        $plan = Plan::factory()->create();
        $usuario = User::factory()->create();
        Invitacion::create([
            'plan_id' => $plan->id,
            'email' => $usuario->email,
            'estado' => 'pendiente',
            'token' => 'tok_scope_correo',
        ]);

        $this->assertTrue($plan->invitaciones()->where('estado', 'pendiente')->deUsuario($usuario)->exists());
    }

    public function test_scope_no_mezcla_invitaciones_de_otros_usuarios_ni_de_otros_planes(): void
    {
        $plan = Plan::factory()->create();
        $otroPlan = Plan::factory()->create();
        $usuario = User::factory()->create();
        $ajeno = User::factory()->create();

        Invitacion::factory()->aceptada($ajeno)->create(['plan_id' => $plan->id, 'email' => $ajeno->email]);
        Invitacion::factory()->aceptada($usuario)->create(['plan_id' => $otroPlan->id, 'email' => $usuario->email]);

        $this->assertFalse($plan->invitaciones()->deUsuario($usuario)->exists());
    }

    public function test_plan_reconoce_participante_aceptado_y_rechaza_pendiente_o_ajeno(): void
    {
        $plan = Plan::factory()->create();
        $aceptado = User::factory()->create();
        $pendiente = User::factory()->create();
        $ajeno = User::factory()->create();

        Invitacion::factory()->aceptada($aceptado)->create(['plan_id' => $plan->id, 'email' => $aceptado->email]);
        Invitacion::create([
            'plan_id' => $plan->id,
            'email' => $pendiente->email,
            'estado' => 'pendiente',
            'token' => 'tok_scope_pendiente',
        ]);

        $this->assertTrue($plan->esParticipanteAceptado($aceptado));
        $this->assertFalse($plan->esParticipanteAceptado($pendiente));
        $this->assertFalse($plan->esParticipanteAceptado($ajeno));
    }
}

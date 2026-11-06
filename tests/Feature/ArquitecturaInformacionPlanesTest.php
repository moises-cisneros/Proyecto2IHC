<?php

namespace Tests\Feature;

use App\Models\Invitacion;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArquitecturaInformacionPlanesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1 (Seguridad / Autenticación): Un usuario anónimo es redirigido a login.
     */
    public function test_usuario_no_autenticado_es_redirigido_a_login(): void
    {
        $response = $this->get(route('planes.listar'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Caso 2 (Segmentación de Rol): La sección 'creados' retorna únicamente planes donde el usuario es organizador.
     */
    public function test_seccion_creados_retorna_unicamente_planes_del_organizador(): void
    {
        $organizador = User::factory()->create();
        $otroUsuario = User::factory()->create();

        // Plan creado por el organizador
        $planPropio = Plan::create([
            'nombre' => 'Cena de Navidad',
            'organizador' => $organizador->name,
            'fecha_limite' => today()->addDays(5)->toDateString(),
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        // Plan creado por otro usuario donde el organizador es invitado
        $planInvitado = Plan::create([
            'nombre' => 'Paseo al Volcán',
            'organizador' => $otroUsuario->name,
            'fecha_limite' => today()->addDays(7)->toDateString(),
            'user_id' => $otroUsuario->id,
            'estado' => 'pendiente',
        ]);

        Invitacion::create([
            'plan_id' => $planInvitado->id,
            'user_id' => $organizador->id,
            'email' => $organizador->email,
            'token' => 'token-test-creados-1',
            'estado' => 'aceptada',
        ]);

        // Consulta sin parámetro (por defecto debe ser 'creados')
        $responseDefault = $this->actingAs($organizador)->get(route('planes.listar'));
        $responseDefault->assertOk();
        $responseDefault->assertViewHas('seccion', 'creados');
        $planesDefault = $responseDefault->viewData('planes');
        $this->assertTrue($planesDefault->contains($planPropio));
        $this->assertFalse($planesDefault->contains($planInvitado));

        // Consulta explícita con ?seccion=creados
        $responseExplicita = $this->actingAs($organizador)->get(route('planes.listar', ['seccion' => 'creados']));
        $responseExplicita->assertOk();
        $responseExplicita->assertViewHas('seccion', 'creados');
        $planesExplicita = $responseExplicita->viewData('planes');
        $this->assertTrue($planesExplicita->contains($planPropio));
        $this->assertFalse($planesExplicita->contains($planInvitado));
    }

    /**
     * Caso 3 (Segmentación de Rol): La sección 'invitaciones' retorna únicamente planes donde el usuario es invitado.
     */
    public function test_seccion_invitaciones_retorna_unicamente_planes_donde_es_invitado(): void
    {
        $organizador = User::factory()->create();
        $otroUsuario = User::factory()->create();

        // Plan creado por el usuario logueado
        $planPropio = Plan::create([
            'nombre' => 'Cumpleaños Familiar',
            'organizador' => $organizador->name,
            'fecha_limite' => today()->addDays(4)->toDateString(),
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        // Plan creado por otro usuario donde el usuario logueado tiene invitación
        $planInvitado = Plan::create([
            'nombre' => 'Hackathon Universitario',
            'organizador' => $otroUsuario->name,
            'fecha_limite' => today()->addDays(10)->toDateString(),
            'user_id' => $otroUsuario->id,
            'estado' => 'pendiente',
        ]);

        Invitacion::create([
            'plan_id' => $planInvitado->id,
            'user_id' => $organizador->id,
            'email' => $organizador->email,
            'token' => 'token-test-invitaciones-1',
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($organizador)->get(route('planes.listar', ['seccion' => 'invitaciones']));

        $response->assertOk();
        $response->assertViewHas('seccion', 'invitaciones');

        $planesInvitaciones = $response->viewData('planes');
        $this->assertTrue($planesInvitaciones->contains($planInvitado));
        $this->assertFalse($planesInvitaciones->contains($planPropio));
    }

    /**
     * Caso 4 (Agrupación Temporal por Urgencia): Partición correcta en vencidos, hoy y próximos con contadores.
     */
    public function test_planes_se_agrupan_correctamente_en_vencidos_hoy_y_proximos(): void
    {
        $usuario = User::factory()->create();

        $planVencido = Plan::create([
            'nombre' => 'Plan Pasado',
            'organizador' => $usuario->name,
            'fecha_limite' => today()->subDays(2)->toDateString(),
            'user_id' => $usuario->id,
            'estado' => 'pendiente',
        ]);

        $planHoy = Plan::create([
            'nombre' => 'Plan Para Hoy',
            'organizador' => $usuario->name,
            'fecha_limite' => today()->toDateString(),
            'user_id' => $usuario->id,
            'estado' => 'pendiente',
        ]);

        $planProximo = Plan::create([
            'nombre' => 'Plan Futuro',
            'organizador' => $usuario->name,
            'fecha_limite' => today()->addDays(5)->toDateString(),
            'user_id' => $usuario->id,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($usuario)->get(route('planes.listar'));

        $response->assertOk();

        $vencidos = $response->viewData('vencidos');
        $hoy = $response->viewData('hoy');
        $proximos = $response->viewData('proximos');
        $contadores = $response->viewData('contadores');

        $this->assertCount(1, $vencidos);
        $this->assertTrue($vencidos->contains($planVencido));

        $this->assertCount(1, $hoy);
        $this->assertTrue($hoy->contains($planHoy));

        $this->assertCount(1, $proximos);
        $this->assertTrue($proximos->contains($planProximo));

        $this->assertSame([
            'vencidos' => 1,
            'hoy' => 1,
            'proximos' => 1,
        ], $contadores);
    }
}

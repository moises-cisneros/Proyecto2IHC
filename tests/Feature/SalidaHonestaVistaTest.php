<?php

namespace Tests\Feature;

use App\Models\Gasto;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalidaHonestaVistaTest extends TestCase
{
    use RefreshDatabase;

    public function test_vista_cuenta_renderiza_secciones_y_botones_fitts(): void
    {
        $user = User::factory()->create([
            'name' => 'Carlos Usuario',
            'email' => 'carlos@planazo.com',
        ]);

        $response = $this->actingAs($user)->get('/perfil/datos');

        $response->assertStatus(200);
        $response->assertSee('Mi Cuenta y Privacidad');
        $response->assertSee('Carlos Usuario');
        $response->assertSee('carlos@planazo.com');

        // Sección de portabilidad
        $response->assertSee('tarjeta-acciones', false);
        $response->assertSee('Descargar mis datos', false);
        $response->assertSee(route('perfil.datos.exportar', ['formato' => 'json']), false);
        $response->assertSee(route('perfil.datos.exportar', ['formato' => 'csv']), false);

        // Modal nativo de salida simétrica
        $response->assertSee('id="modal-eliminar-cuenta"', false);
        $response->assertSee('Conservar cuenta');
        $response->assertSee('Sí, eliminar mi cuenta');
    }

    public function test_vista_cuenta_con_bloqueos_renderiza_caja_salida_honesta_y_enlaces_resolucion(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $user->id,
            'nombre' => 'Plan Bloqueante 1',
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($user)->get('/perfil/datos');

        $response->assertStatus(200);
        $response->assertSee('id="modal-bloqueos-cuenta"', false);
        $response->assertSee('No puedes dar de baja tu cuenta todavía');
        $response->assertSee('Tienes acciones pendientes que requieren tu atención');
        $response->assertDontSee('¿Por qué no puedes dar de baja tu cuenta');
        $response->assertDontSee('¿Qué debes hacer para resolverlo?');
        $response->assertDontSee('id="modal-eliminar-cuenta"', false);
        $response->assertSee('Plan Bloqueante 1');
        $response->assertSee(route('planes.detalle', $plan->id), false);
    }

    public function test_tabla_planes_renderiza_dialogo_honesto_al_intentar_eliminar_plan(): void
    {
        $organizador = User::factory()->create();
        $planConfirmado = Plan::factory()->create([
            'user_id' => $organizador->id,
            'nombre' => 'Plan Ya Confirmado',
            'estado' => 'confirmado',
        ]);

        $response = $this->actingAs($organizador)->get('/mis-planes');

        $response->assertStatus(200);
        $response->assertSee("id=\"modal-eliminar-plan-{$planConfirmado->id}\"", false);
        $response->assertSee('caja-salida-honesta', false);
        $response->assertSee('No es posible eliminar este plan');
        $response->assertSee('Plan confirmado');
        $response->assertSee('Entendido, volver');
    }

    public function test_participantes_y_cabecera_renderizan_boton_simetrico_rechazar_invitacion(): void
    {
        $organizador = User::factory()->create();
        $invitado = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'nombre' => 'Plan Con Invitación',
            'estado' => 'pendiente',
        ]);

        $plan->invitaciones()->create([
            'email' => $invitado->email,
            'user_id' => $invitado->id,
            'estado' => 'pendiente',
            'token' => 'token-test-rechazo',
        ]);

        $response = $this->actingAs($invitado)->get("/planes/{$plan->id}");

        $response->assertStatus(200);
        // Botón Aceptar y Rechazar en cabecera y lista de participantes
        $response->assertSee(route('planes.aceptar', $plan), false);
        $response->assertSee(route('planes.rechazar-invitacion', $plan), false);
        $response->assertSee('Aceptar invitación');
        $response->assertSee('Rechazar invitación');
    }

    public function test_gastos_modalidad_pago_no_tiene_preseleccion_obligada(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'estado' => 'confirmado',
        ]);

        $plan->invitaciones()->create([
            'email' => $participante->email,
            'user_id' => $participante->id,
            'estado' => 'aceptada',
            'token' => 'token-gasto-test',
        ]);

        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Gasto para liquidar',
            'monto_total' => 100.00,
            'tipo_division' => 'equitativo',
        ]);

        $gasto->divisiones()->createMany([
            ['user_id' => $organizador->id, 'monto_asignado' => 50.00],
            ['user_id' => $participante->id, 'monto_asignado' => 50.00],
        ]);

        $response = $this->actingAs($participante)->get("/planes/{$plan->id}?tab=gastos");

        $response->assertStatus(200);
        // Verifica que radio-pago-total no esté checked por defecto
        $response->assertDontSee('id="radio-pago-total" value="total" checked', false);

        // La preselección tampoco puede ocurrir por JavaScript ni verse como «activa» antes de elegir
        $html = $response->getContent();
        $this->assertStringNotContainsString('radioTotal.checked = true', $html);
        $this->assertStringNotContainsString('gastos-modalidad gastos-modalidad--activa', $html);
        $this->assertStringContainsString('establecerModalidadNeutral();', $html);
        // Elegir modalidad es obligatorio para enviar el pago
        $this->assertStringContainsString('id="radio-pago-total" value="total" required', $html);
        $this->assertStringContainsString('id="radio-pago-parcial" value="parcial" required', $html);
    }

    public function test_navbar_renderiza_enlace_mi_cuenta(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/mis-planes');

        $response->assertStatus(200);
        $response->assertSee(route('perfil.datos'), false);
        $response->assertSee('Mi Cuenta');
    }
}

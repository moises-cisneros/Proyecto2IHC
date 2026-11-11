<?php

namespace Tests\Feature;

use App\Models\Invitacion;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GastosComprobanteVistaTest extends TestCase
{
    use RefreshDatabase;

    public function test_modal_registro_pago_muestra_datos_de_cobro_del_acreedor(): void
    {
        $organizador = User::factory()->create([
            'alias_pago' => 'organizador.alias.qr',
            'cbu_pago' => '1122334455667788',
        ]);
        $participante = User::factory()->create();

        $plan = Plan::factory()->create(['user_id' => $organizador->id]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'token' => 'token-vista-1',
            'estado' => 'aceptada',
        ]);

        $response = $this->actingAs($participante)->get(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));

        $response->assertStatus(200);
        $response->assertSee('contenedor-datos-receptor', false);
        $response->assertSee('input-pago-comprobante', false);
        $response->assertSee('organizador.alias.qr', false);
        $response->assertSee('modal-configurar-cobro', false);
        $response->assertSee('aviso-procesando-pago', false);
        $response->assertSee('btn-submit-pago', false);
        $response->assertSee('spinner-submit-pago', false);
        $response->assertSee('Procesando tu pago', false);
    }

    public function test_lista_pagos_renderiza_badge_de_verificacion_y_enlace_a_comprobante(): void
    {
        $creador = User::factory()->create();
        $deudor = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $creador->id]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $deudor->id,
            'email' => $deudor->email,
            'token' => 'token-vista-2',
            'estado' => 'aceptada',
        ]);

        $pago = Pago::create([
            'plan_id' => $plan->id,
            'deudor_id' => $deudor->id,
            'receptor_id' => $creador->id,
            'monto' => 120.00,
            'comprobante_path' => 'comprobantes/test_comprobante.jpg',
            'monto_detectado' => 120.00,
            'banco_detectado' => 'Banco Nacional de Bolivia',
            'estado_escaneo' => 'verificado',
            'confianza_escaneo' => 0.97,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($creador)->get(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));

        $response->assertStatus(200);
        $response->assertSee('Ver Comprobante');
        $response->assertSee('Monto Verificado: Bs 120.00');
        $response->assertSee('Banco Nacional de Bolivia');
        $response->assertSee('modal-comprobante-'.$pago->id);
    }

    public function test_modal_ampliacion_comprobante_es_accesible_con_dialog_nativo(): void
    {
        $creador = User::factory()->create();
        $deudor = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $creador->id]);

        $pago = Pago::create([
            'plan_id' => $plan->id,
            'deudor_id' => $deudor->id,
            'receptor_id' => $creador->id,
            'monto' => 80.00,
            'comprobante_path' => 'comprobantes/recibo_80.png',
            'estado_escaneo' => 'discrepante',
            'monto_detectado' => 40.00,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($creador)->get(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));

        $response->assertStatus(200);
        $response->assertSee('<dialog id="modal-comprobante-'.$pago->id.'"', false);
        $response->assertSee('recibo_80.png');
        $response->assertSee('Alerta: Comprobante indica Bs 40.00');
    }

    public function test_usuario_no_acreedor_no_visualiza_botones_de_confirmacion_de_pago(): void
    {
        $creador = User::factory()->create();
        $deudor = User::factory()->create();
        $tercero = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $creador->id]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $deudor->id,
            'email' => $deudor->email,
            'token' => 'token-vista-3',
            'estado' => 'aceptada',
        ]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $tercero->id,
            'email' => $tercero->email,
            'token' => 'token-vista-4',
            'estado' => 'aceptada',
        ]);

        $pago = Pago::create([
            'plan_id' => $plan->id,
            'deudor_id' => $deudor->id,
            'receptor_id' => $creador->id,
            'monto' => 60.00,
            'estado' => 'pendiente',
        ]);

        // El deudor visualiza la lista, pero NO ve el formulario ni botón de confirmar
        $responseDeudor = $this->actingAs($deudor)->get(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));
        $responseDeudor->assertStatus(200);
        $responseDeudor->assertDontSee('Confirmar pago');
        $responseDeudor->assertSee('Esperando confirmación del receptor');

        // El tercero tampoco ve el botón de confirmar
        $responseTercero = $this->actingAs($tercero)->get(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));
        $responseTercero->assertStatus(200);
        $responseTercero->assertDontSee('Confirmar pago');
    }
}

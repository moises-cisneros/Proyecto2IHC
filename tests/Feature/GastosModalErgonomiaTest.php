<?php

namespace Tests\Feature;

use App\Models\Gasto;
use App\Models\GastoDivision;
use App\Models\Invitacion;
use App\Models\Pago;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GastosModalErgonomiaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1: Apertura de modal contiene selector de intención y monto con readonly por defecto.
     */
    public function test_vista_gastos_renderiza_selector_de_modalidad_de_pago_y_bloqueo_readonly(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $organizador->id]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $participante->id,
            'email' => $participante->email,
            'token' => 'token-ergonomia-1',
            'estado' => 'aceptada',
        ]);

        $response = $this->actingAs($participante)->get(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));

        $response->assertStatus(200);
        $response->assertSee('id="selector-modalidad-pago"', false);
        $response->assertSee('id="radio-pago-total"', false);
        $response->assertSee('id="radio-pago-parcial"', false);
        $response->assertSee('Pagar totalidad', false);
        $response->assertSee('Pago parcial', false);
        $response->assertSee('readonly', false);
    }

    /**
     * Caso 2: Modal de revisión muestra badge verde asistido por IA cuando monto_detectado == monto.
     */
    public function test_modal_revision_comprobante_renderiza_badge_asistido_por_ia_cuando_montos_coinciden(): void
    {
        $acreedor = User::factory()->create(['name' => 'Acreedor IA']);
        $deudor = User::factory()->create(['name' => 'Deudor IA']);
        $plan = Plan::factory()->create(['user_id' => $acreedor->id]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $deudor->id,
            'email' => $deudor->email,
            'token' => 'token-ergonomia-2',
            'estado' => 'aceptada',
        ]);

        $pago = Pago::create([
            'plan_id' => $plan->id,
            'deudor_id' => $deudor->id,
            'receptor_id' => $acreedor->id,
            'monto' => 75.00,
            'estado' => 'pendiente',
            'comprobante_path' => 'comprobantes/fake_ia_receipt.png',
            'monto_detectado' => 75.00,
            'banco_detectado' => 'Banco Unión',
            'estado_escaneo' => 'verificado',
        ]);

        $response = $this->actingAs($acreedor)->get(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));

        $response->assertStatus(200);
        $response->assertSee('Comprobante verificado por IA: Monto coincide (Bs 75.00)');
        $response->assertSee('Confirmar pago en 1 clic');
        $response->assertSee('badge-ia-verificado');
    }

    /**
     * Caso 3: Auditoría de diseño del componente sin emojis crudos y cumplimiento de Fitts.
     */
    public function test_auditoria_sistema_diseno_cero_emojis_y_areas_tactiles_fitts_en_modal_pago(): void
    {
        $vistaPath = resource_path('views/planes/_gastos_liquidacion.blade.php');
        $contenido = file_get_contents($vistaPath);

        // Sin emojis crudos
        $this->assertDoesNotMatchRegularExpression('#[\x{1F300}-\x{1F9FF}]#u', $contenido);

        // Áreas táctiles de Fitts (>= 44px) en botones del modal
        $this->assertStringContainsString('min-height: 44px', $contenido);
        $this->assertStringContainsString('min-width: 44px', $contenido);

        // Separación antagónica de botones (gap >= 12px)
        $this->assertStringContainsString('gap: 12px', $contenido);
    }

    /**
     * Caso 4: Matriz de deudas se inyecta en JavaScript para autocompletado en cliente.
     */
    public function test_deudas_por_acreedor_se_inyectan_en_script_como_objeto_reactivo(): void
    {
        $organizador = User::factory()->create();
        $deudor = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $organizador->id]);

        Invitacion::create([
            'plan_id' => $plan->id,
            'user_id' => $deudor->id,
            'email' => $deudor->email,
            'token' => 'token-ergonomia-3',
            'estado' => 'aceptada',
        ]);

        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Alquiler',
            'monto_total' => 120.00,
            'tipo_division' => 'equitativa',
        ]);

        GastoDivision::create([
            'gasto_id' => $gasto->id,
            'user_id' => $organizador->id,
            'monto_asignado' => 60.00,
        ]);

        GastoDivision::create([
            'gasto_id' => $gasto->id,
            'user_id' => $deudor->id,
            'monto_asignado' => 60.00,
        ]);

        $response = $this->actingAs($deudor)->get(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));

        $response->assertStatus(200);
        $response->assertSee('var deudasPorAcreedor = {', false);
        $response->assertSee('"'.$organizador->id.'":60', false);
    }
}

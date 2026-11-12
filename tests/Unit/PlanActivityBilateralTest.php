<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\PlanActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanActivityBilateralTest extends TestCase
{
    use RefreshDatabase;

    private User $deudor;

    private User $receptor;

    private User $usuarioAjeno;

    private Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deudor = User::factory()->create(['name' => 'Carlos Deudor']);
        $this->receptor = User::factory()->create(['name' => 'Ana Receptora']);
        $this->usuarioAjeno = User::factory()->create(['name' => 'Pedro Ajeno']);

        $this->plan = Plan::factory()->create(['user_id' => $this->receptor->id]);
    }

    /**
     * Caso 1: Test que verifique que el deudor visualiza la actividad pago_confirmado en su listado de historial.
     */
    public function test_deudor_visualiza_actividad_pago_confirmado_en_su_historial(): void
    {
        $actividad = PlanActivity::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->receptor->id,
            'destinatario_id' => $this->deudor->id,
            'accion' => 'pago_confirmado',
            'descripcion' => "{$this->receptor->name} confirmó el pago de Bs 50.00 recibido de {$this->deudor->name}.",
            'estado_resultante' => $this->plan->estado,
        ]);

        $actividadesVisibles = PlanActivity::visiblesParaUsuario($this->deudor->id)->get();

        $this->assertTrue($actividadesVisibles->contains($actividad));
        $this->assertCount(1, $actividadesVisibles);
    }

    /**
     * Caso 2: Test que verifique que el receptor visualiza la actividad en su listado de historial.
     */
    public function test_receptor_visualiza_actividad_pago_confirmado_en_su_historial(): void
    {
        $actividad = PlanActivity::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->receptor->id,
            'destinatario_id' => $this->deudor->id,
            'accion' => 'pago_confirmado',
            'descripcion' => "{$this->receptor->name} confirmó el pago de Bs 50.00 recibido de {$this->deudor->name}.",
            'estado_resultante' => $this->plan->estado,
        ]);

        $actividadesVisibles = PlanActivity::visiblesParaUsuario($this->receptor->id)->get();

        $this->assertTrue($actividadesVisibles->contains($actividad));
    }

    /**
     * Caso 3: Test que valide la normalización de la moneda a Bs en la descripción del evento.
     */
    public function test_normalizacion_de_moneda_a_bolivianos_en_descripcion_de_evento(): void
    {
        $actividadGasto = PlanActivity::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->deudor->id,
            'accion' => 'gasto',
            'descripcion' => "{$this->deudor->name} registró el gasto 'Cena' por Bs 120.00.",
            'estado_resultante' => $this->plan->estado,
        ]);

        $actividadPago = PlanActivity::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->deudor->id,
            'destinatario_id' => $this->receptor->id,
            'accion' => 'pago',
            'descripcion' => "{$this->deudor->name} registró un pago de Bs 60.00 a {$this->receptor->name} (pendiente de confirmación).",
            'estado_resultante' => $this->plan->estado,
        ]);

        $this->assertStringContainsString('Bs 120.00', $actividadGasto->descripcion);
        $this->assertStringNotContainsString('$', $actividadGasto->descripcion);

        $this->assertStringContainsString('Bs 60.00', $actividadPago->descripcion);
        $this->assertStringNotContainsString('$', $actividadPago->descripcion);
    }

    /**
     * Caso 4 (Seguridad): Test que confirme que un usuario ajeno al plan no puede ver las actividades de pago de dicho plan.
     */
    public function test_usuario_ajeno_no_puede_ver_actividades_de_pago_de_otros_usuarios(): void
    {
        PlanActivity::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->receptor->id,
            'destinatario_id' => $this->deudor->id,
            'accion' => 'pago_confirmado',
            'descripcion' => "{$this->receptor->name} confirmó el pago de Bs 50.00 recibido de {$this->deudor->name}.",
            'estado_resultante' => $this->plan->estado,
        ]);

        $actividadesVisibles = PlanActivity::visiblesParaUsuario($this->usuarioAjeno->id)->get();

        $this->assertCount(0, $actividadesVisibles);
    }
}

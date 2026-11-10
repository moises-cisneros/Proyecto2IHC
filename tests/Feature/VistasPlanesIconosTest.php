<?php

namespace Tests\Feature;

use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VistasPlanesIconosTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifies that plan views render SVG icons properly and do not contain raw emojis.
     */
    public function test_vistas_planes_renderizan_iconos_svg_correctamente(): void
    {
        $organizador = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'estado' => 'cancelado',
        ]);

        $response = $this->actingAs($organizador)->get(route('planes.detalle', $plan));

        $response->assertOk();
        // SVG icons must be rendered
        $response->assertSee('icono-svg', false);
        $response->assertSee('viewBox="0 0 24 24"', false);
        $response->assertSee('aria-hidden="true"', false);

        // No raw emojis should be present in the rendered HTML
        $content = $response->getContent();
        $this->assertStringNotContainsString('🚫', $content);
        $this->assertStringNotContainsString('📅', $content);
        $this->assertStringNotContainsString('📍', $content);
    }

    /**
     * Verifies that table action buttons maintain accessible aria-labels and use utility classes.
     */
    public function test_botones_accion_mantienen_atributos_accesibles_y_aria_labels(): void
    {
        $organizador = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($organizador)->get(route('planes.listar'));

        $response->assertOk();
        $response->assertSee('boton-accion-md', false);
        $response->assertSee('boton-accion-sm', false);
        $response->assertSee('sr-only', false);
        $response->assertSee('aria-label="Editar plan '.$plan->nombre.'"', false);
        $response->assertSee('aria-label="Eliminar plan '.$plan->nombre.'"', false);
    }

    /**
     * Verifies that the collaborative room does not leak raw emojis in tabs, buttons or alerts.
     */
    public function test_sala_votacion_no_contiene_emojis_crudos_en_pestanas_ni_alertas(): void
    {
        $organizador = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'estado' => 'confirmado',
        ]);

        Opcion::create([
            'plan_id' => $plan->id,
            'user_id' => $organizador->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-25 20:00:00',
        ]);

        $response = $this->actingAs($organizador)->get(route('planes.detalle', $plan));

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringNotContainsString('📅', $content);
        $this->assertStringNotContainsString('📍', $content);
        $this->assertStringNotContainsString('⚖', $content);
        $this->assertStringNotContainsString('💰', $content);
    }

    /**
     * Verifies that the expenses & liquidation tab does not contain raw emojis and renders semantic icons.
     */
    public function test_sala_gastos_no_contiene_emojis_crudos_y_renderiza_iconos_dinero_y_pago(): void
    {
        $organizador = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'estado' => 'confirmado',
        ]);

        $response = $this->actingAs($organizador)->get(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));

        $response->assertOk();
        $response->assertSee('icono-svg', false);
        $content = $response->getContent();

        $this->assertStringNotContainsString('💰', $content);
        $this->assertStringNotContainsString('💸', $content);
        $this->assertStringNotContainsString('📤', $content);
        $this->assertStringNotContainsString('📥', $content);
    }
}

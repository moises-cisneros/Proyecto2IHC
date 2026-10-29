<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToastAlertaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Renders success toast notification when 'exito' flash exists in session.
     */
    public function test_renderiza_toast_exito_cuando_hay_mensaje_flash_en_sesion(): void
    {
        $response = $this->withSession([
            'exito' => '¡Plan creado exitosamente!',
        ])->get('/ingresar');

        $response->assertOk();
        $response->assertSee('toast-alerta', false);
        $response->assertSee('toast-exito', false);
        $response->assertSee('role="status"', false);
        $response->assertSee('¡Plan creado exitosamente!');
    }

    /**
     * Renders error toast notification with role alert when 'error' flash exists in session.
     */
    public function test_renderiza_toast_error_cuando_hay_mensaje_flash_en_sesion(): void
    {
        $response = $this->withSession([
            'error' => 'No tienes permisos para realizar esta acción.',
        ])->get('/ingresar');

        $response->assertOk();
        $response->assertSee('toast-alerta', false);
        $response->assertSee('toast-error', false);
        $response->assertSee('role="alert"', false);
        $response->assertSee('No tienes permisos para realizar esta acción.');
    }

    /**
     * Does not render toast element when there are no flash messages in session.
     */
    public function test_no_renderiza_toast_cuando_la_sesion_esta_vacia(): void
    {
        $response = $this->get('/ingresar');

        $response->assertOk();
        $response->assertDontSee('toast-alerta');
        $response->assertDontSee('toast-contenedor');
    }

    /**
     * Verifies that the toast contains an accessible close button and floating fixed position.
     */
    public function test_toast_contiene_boton_de_cierre_accesible_y_posicion_flotante(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->withSession([
            'exito' => 'Actualización completada',
        ])->get('/mis-planes');

        $response->assertOk();
        $response->assertSee('id="toast-btn-cerrar"', false);
        $response->assertSee('aria-label="Cerrar notificación"', false);
        $response->assertSee('fixed top-5 right-5 z-50', false);
        $response->assertSee('Actualización completada');
    }
}

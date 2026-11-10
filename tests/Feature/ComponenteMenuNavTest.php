<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ComponenteMenuNavTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Caso 1 (Componente NavLink Activo): Renderiza link accesible con clase activa,
     * atributo aria-current="page" e icono SVG integrado.
     */
    public function test_componente_nav_link_renderiza_enlace_con_icono_y_estado_activo(): void
    {
        $rendered = (string) Blade::render(
            '<x-nav-link href="/mis-planes" :activo="true" icono="calendario" aria-label="Ir a Mis Planes">Mis Planes</x-nav-link>'
        );

        $this->assertStringContainsString('class="nav-item nav-item-activo"', $rendered);
        $this->assertStringContainsString('aria-current="page"', $rendered);
        $this->assertStringContainsString('aria-label="Ir a Mis Planes"', $rendered);
        $this->assertStringContainsString('<svg', $rendered);
        $this->assertStringContainsString('Mis Planes', $rendered);
    }

    /**
     * Caso 2 (Componente NavLink Inactivo): Renderiza link con clase inactiva y sin aria-current.
     */
    public function test_componente_nav_link_renderiza_estado_inactivo(): void
    {
        $rendered = (string) Blade::render(
            '<x-nav-link href="/historial" :activo="false">Historial</x-nav-link>'
        );

        $this->assertStringContainsString('class="nav-item nav-item-inactivo"', $rendered);
        $this->assertStringNotContainsString('aria-current="page"', $rendered);
        $this->assertStringContainsString('Historial', $rendered);
    }

    /**
     * Caso 3 (Iconos Historial y Salir): El componente icono soporta los glifos de navegacion.
     */
    public function test_icono_soporta_glifos_de_historial_y_salir(): void
    {
        $renderedHistorial = (string) Blade::render('<x-icono nombre="historial" />');
        $this->assertStringContainsString('<svg', $renderedHistorial);
        $this->assertStringContainsString('circle', $renderedHistorial);

        $renderedSalir = (string) Blade::render('<x-icono nombre="salir" />');
        $this->assertStringContainsString('<svg', $renderedSalir);
        $this->assertStringContainsString('path', $renderedSalir);
    }

    /**
     * Caso 4 (Header Completo en Vista): La barra superior renderiza el menú estilizado,
     * el chip de usuario con avatar y el botón salir accesible.
     */
    public function test_barra_navegacion_renderiza_menu_y_perfil_con_avatar(): void
    {
        $user = User::factory()->create(['name' => 'Carlos Mendoza']);

        $response = $this->actingAs($user)->get('/mis-planes');

        $response->assertStatus(200);
        $response->assertSee('class="nav-menu"', false);
        $response->assertSee('class="nav-usuario"', false);
        $response->assertSee('class="nav-usuario-avatar"', false);
        $response->assertSee('C', false); // Inicial del nombre
        $response->assertSee('Carlos Mendoza');
        $response->assertSee('class="boton-salir"', false);
    }
}

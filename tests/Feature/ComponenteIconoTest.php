<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ComponenteIconoTest extends TestCase
{
    /**
     * Verifies that the icon component correctly renders valid icons with accessible SVG attributes.
     */
    public function test_renderiza_iconos_validos_con_atributos_svg_accesibles(): void
    {
        $iconos = [
            'calendario',
            'ubicacion',
            'bloqueado',
            'check',
            'editar',
            'eliminar',
            'dinero',
            'pago',
        ];

        foreach ($iconos as $nombre) {
            $rendered = (string) Blade::render("<x-icono nombre=\"{$nombre}\" />");

            $this->assertStringContainsString('<svg', $rendered, "Fallo al renderizar el icono {$nombre}");
            $this->assertStringContainsString('viewBox="0 0 24 24"', $rendered);
            $this->assertStringContainsString('aria-hidden="true"', $rendered);
            $this->assertStringContainsString('stroke="currentColor"', $rendered);
            $this->assertStringContainsString('fill="none"', $rendered);
        }
    }

    /**
     * Verifies that the icon component supports custom CSS classes and attribute merging.
     */
    public function test_soporta_clases_personalizadas_y_atributos_adicionales(): void
    {
        $rendered = (string) Blade::render('<x-icono nombre="calendario" class="w-6 h-6 custom-clase" />');

        $this->assertStringContainsString('class="', $rendered);
        $this->assertStringContainsString('w-6', $rendered);
        $this->assertStringContainsString('h-6', $rendered);
        $this->assertStringContainsString('custom-clase', $rendered);
    }

    /**
     * Verifies fallback behavior when an unknown icon is requested.
     */
    public function test_comportamiento_predecible_ante_icono_desconocido(): void
    {
        $rendered = (string) Blade::render('<x-icono nombre="no_existe_este_icono" />');

        // Should return empty string without throwing an exception
        $this->assertSame('', trim($rendered));
    }

    /**
     * Verifies that additional icons for system workflows (balance, card) are supported.
     */
    public function test_soporta_iconos_adicionales_del_sistema(): void
    {
        $adicionales = ['balanza', 'tarjeta'];

        foreach ($adicionales as $nombre) {
            $rendered = (string) Blade::render("<x-icono nombre=\"{$nombre}\" />");

            $this->assertStringContainsString('<svg', $rendered, "Fallo al renderizar el icono adicional {$nombre}");
            $this->assertStringContainsString('viewBox="0 0 24 24"', $rendered);
            $this->assertStringContainsString('aria-hidden="true"', $rendered);
        }
    }
}

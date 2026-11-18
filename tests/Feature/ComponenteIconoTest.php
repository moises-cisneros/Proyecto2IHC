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
            'planazo',
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
            $this->assertStringContainsString('width="20"', $rendered);
            $this->assertStringContainsString('height="20"', $rendered);
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
     * Verifies that the icon component supports compact (16px), standard (20px), and prominent (24px) size variants.
     */
    public function test_soporta_variantes_de_tamano_compacto_estandar_y_destacado(): void
    {
        $renderedSm = (string) Blade::render('<x-icono nombre="calendario" tamano="sm" />');
        $this->assertStringContainsString('width="16"', $renderedSm);
        $this->assertStringContainsString('height="16"', $renderedSm);
        $this->assertStringContainsString('icono-sm', $renderedSm);

        $renderedMd = (string) Blade::render('<x-icono nombre="calendario" tamano="md" />');
        $this->assertStringContainsString('width="20"', $renderedMd);
        $this->assertStringContainsString('height="20"', $renderedMd);
        $this->assertStringContainsString('icono-md', $renderedMd);

        $renderedLg = (string) Blade::render('<x-icono nombre="calendario" tamano="lg" />');
        $this->assertStringContainsString('width="24"', $renderedLg);
        $this->assertStringContainsString('height="24"', $renderedLg);
        $this->assertStringContainsString('icono-lg', $renderedLg);
    }
}

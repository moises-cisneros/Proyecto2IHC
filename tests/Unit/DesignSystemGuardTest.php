<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Guards the contract in resources/DESIGN.md: views must not carry static
 * inline styles, and the stylesheet must not use transitions or raw colors.
 */
class DesignSystemGuardTest extends TestCase
{
    private function viewFiles(): array
    {
        $root = dirname(__DIR__, 2).'/resources/views';
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
        foreach ($iterator as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    public function test_vistas_no_contienen_estilos_inline_estaticos(): void
    {
        $offenders = [];
        foreach ($this->viewFiles() as $path) {
            preg_match_all('/style="([^"]*)"/', file_get_contents($path), $matches);
            foreach ($matches[1] as $value) {
                // Dynamic values computed by Blade are the only allowed inline styles.
                if (! str_contains($value, '{{')) {
                    $offenders[] = basename($path).': '.trim($value);
                }
            }
        }

        $this->assertSame([], $offenders, "Static inline styles found:\n".implode("\n", $offenders));
    }

    public function test_hoja_de_estilos_no_usa_transiciones_ni_colores_crudos(): void
    {
        $css = file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');

        $this->assertDoesNotMatchRegularExpression('/\b(transition|animation)\s*:/', $css, 'DESIGN.md §1: no transitions/animations.');
        $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b/', $css, 'DESIGN.md §1: colors must come from tokens.');
        $this->assertDoesNotMatchRegularExpression('/rgba?\(/', $css, 'DESIGN.md §1: colors must come from tokens.');
    }

    public function test_hoja_de_estilos_usa_tokens_para_tipografia_y_dimensiones_reutilizables(): void
    {
        $css = file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');

        $this->assertStringNotContainsString('NEEDS TOKEN', $css, 'Every dimension must be tokenized or documented as local.');
        $this->assertDoesNotMatchRegularExpression('/line-height:\s*[0-9.]+;/', $css, 'Use --line-height* tokens.');
        $this->assertDoesNotMatchRegularExpression('/font-family:\s*monospace/', $css, 'Use --font-mono.');
        $this->assertDoesNotMatchRegularExpression('/letter-spacing:\s*[0-9.]+(px|em)/', $css, 'Use --letter-spacing-wide.');

        // Raw px outside tokens: only borders/outlines (1-3px) and the documented local dimensions (DESIGN.md §9).
        $allowed = [1, 2, 3, 68, 70, 80, 104, 110, 150, 160, 170, 180, 210, 220, 260, 320, 340, 880];
        $offenders = [];
        foreach (explode("\n", $css) as $number => $line) {
            $code = preg_replace('#/\*.*?\*/#', '', $line);
            $trimmed = ltrim($line);
            if (str_starts_with($trimmed, '/*') || str_starts_with($trimmed, '*') || str_contains($code, '@media') || str_contains($code, '--')) {
                continue;
            }
            if (preg_match_all('/(?<![\w.-])(\d+)px/', $code, $matches)) {
                foreach ($matches[1] as $px) {
                    if (! in_array((int) $px, $allowed, true)) {
                        $offenders[] = 'L'.($number + 1).': '.trim($line);
                    }
                }
            }
        }

        $this->assertSame([], $offenders, "Raw px values without token:\n".implode("\n", $offenders));
    }

    public function test_clases_de_vistas_sin_css_estan_declaradas_como_hooks(): void
    {
        $root = dirname(__DIR__, 2);
        $css = file_get_contents($root.'/resources/css/app.css').file_get_contents($root.'/resources/css/tokens.css');
        preg_match_all('/\.([a-zA-Z][\w-]*)/', $css, $defined);
        $defined = array_flip($defined[1]);

        // Hooks consumed by JS (resources/views scripts, public/js/ui.js), Playwright specs or PHPUnit assertions.
        $hooks = array_flip([
            'badge', 'badge-escaneo', 'badge-estado-pago', 'badge-ia-verificado', 'badge-votos', 'boton-retirarse',
            'check-participante', 'contenedor-monto-personalizado', 'discrepante', 'input-monto-indiv',
            'seccion-participantes', 'toast-mensaje', 'verificado',
        ]);

        $unknown = [];
        foreach ($this->viewFiles() as $path) {
            preg_match_all('/class="([^"]*)"/', file_get_contents($path), $matches);
            foreach ($matches[1] as $value) {
                foreach (preg_split('/\s+/', trim($value)) as $class) {
                    if (preg_match('/^[a-zA-Z][\w-]*$/', $class) && ! isset($defined[$class]) && ! isset($hooks[$class])) {
                        $unknown[$class] = basename($path);
                    }
                }
            }
        }

        $this->assertSame([], $unknown, 'Classes without CSS and not declared as hooks: '.json_encode($unknown));
    }

    public function test_clases_de_componentes_reemplazados_no_reaparecen(): void
    {
        $legacy = ['cuenta-tarjeta', 'cuenta-avatar', 'cuenta-badge', 'cuenta-metrica', 'cuenta-input', 'cuenta-form', 'nav-usuario-avatar', 'avatar-circulo', 'tarjeta-exportacion-datos', 'acceso-form'];
        $haystack = file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');
        foreach ($this->viewFiles() as $path) {
            $haystack .= file_get_contents($path);
        }

        foreach ($legacy as $class) {
            $this->assertStringNotContainsString($class, $haystack, "Legacy class {$class} must stay replaced by the global component.");
        }
    }
}

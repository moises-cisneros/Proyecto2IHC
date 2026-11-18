<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Mantiene sincronizado el contrato `resources/FLOWS.md` con el código:
 * si cambian rutas, vistas, flujos o pruebas citadas, el contrato debe actualizarse (y viceversa).
 */
class FlowsContractTest extends TestCase
{
    private string $contrato;

    protected function setUp(): void
    {
        parent::setUp();
        $this->contrato = (string) file_get_contents(base_path('resources/FLOWS.md'));
    }

    /** Texto de una sección `## N.` hasta la siguiente. */
    private function seccion(string $numero): string
    {
        $this->assertSame(1, preg_match('/^## '.preg_quote($numero, '/').'\. .*?(?=^## \d+\. |\z)/ms', $this->contrato, $m), "Falta la sección {$numero} en FLOWS.md");

        return $m[0];
    }

    public function test_el_contrato_existe_y_declara_sus_secciones_principales(): void
    {
        $this->assertNotSame('', $this->contrato);
        foreach (['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'] as $n) {
            $this->seccion($n);
        }
    }

    public function test_todas_las_rutas_nombradas_de_la_app_figuran_en_el_inventario(): void
    {
        $enCodigo = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($r) => $r->getName())
            ->filter(fn ($n) => $n && ! str_starts_with($n, 'storage.'))
            ->values()
            ->all();

        preg_match_all('/^\|\s*`([a-z0-9._-]+)`\s*\|/m', $this->seccion('9'), $m);
        $enContrato = $m[1];

        $this->assertEqualsCanonicalizing(
            $enCodigo,
            $enContrato,
            'El inventario de rutas de FLOWS.md (§9) no coincide con routes/web.php. Faltan o sobran: '
            .implode(', ', array_merge(array_diff($enCodigo, $enContrato), array_diff($enContrato, $enCodigo)))
        );
    }

    public function test_las_vistas_citadas_en_la_tabla_de_pantallas_existen(): void
    {
        $tabla = $this->seccion('2');
        preg_match_all('/`([a-z_]+\/[a-z_]+)`/', $tabla, $m);
        $vistas = array_unique(array_filter($m[1], fn ($v) => ! str_starts_with($v, 'e2e')));

        $this->assertNotEmpty($vistas);
        foreach ($vistas as $vista) {
            if (str_contains($vista, 'GET ')) {
                continue;
            }
            $this->assertFileExists(resource_path("views/{$vista}.blade.php"), "La vista {$vista} citada en FLOWS.md no existe");
        }
    }

    public function test_cada_pantalla_del_mapa_tiene_su_composicion_y_hay_once_task_flows(): void
    {
        foreach (['ingresar', 'registro', 'recuperar', 'mis-planes', 'sala', 'editar-plan', 'historial', 'mi-cuenta', 'analitica'] as $id) {
            $this->assertMatchesRegularExpression('/^### 4\.\d+ .*`'.preg_quote($id, '/').'`/m', $this->contrato, "Falta la composición de la pantalla {$id}");
        }
        for ($i = 1; $i <= 11; $i++) {
            $this->assertMatchesRegularExpression("/^### TF{$i} — /m", $this->contrato, "Falta el Task Flow TF{$i}");
        }
    }

    public function test_los_specs_e2e_y_tests_citados_existen(): void
    {
        preg_match_all('/`(e2e\/[a-z0-9.\-]+\.spec\.ts)`/', $this->contrato, $e2e);
        foreach (array_unique($e2e[1]) as $spec) {
            $this->assertFileExists(base_path($spec), "El spec {$spec} citado en FLOWS.md no existe");
        }

        preg_match_all('/`([A-Za-z]+Test)(?:\.php)?`/', $this->contrato, $tests);
        foreach (array_unique($tests[1]) as $clase) {
            $this->assertTrue(
                file_exists(base_path("tests/Feature/{$clase}.php")) || file_exists(base_path("tests/Unit/{$clase}.php")),
                "El test {$clase} citado en FLOWS.md no existe"
            );
        }
    }

    public function test_el_contrato_no_menciona_rutas_ni_parametros_eliminados(): void
    {
        $this->assertStringNotContainsString('planes.abandonar', $this->contrato);
        $this->assertStringNotContainsString('pestana=', $this->contrato);
    }
}

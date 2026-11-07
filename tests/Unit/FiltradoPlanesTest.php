<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiltradoPlanesTest extends TestCase
{
    use RefreshDatabase;

    // Usuario común para todos los tests
    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->usuario = User::factory()->create();
    }

    // ---------- Casos con resultados ----------

    /** Filtra por estado y devuelve solo los confirmados */
    public function test_filtra_por_estado_confirmado(): void
    {
        Plan::factory()->confirmado()->create(['user_id' => $this->usuario->id, 'nombre' => 'Viaje a la playa']);
        Plan::factory()->create(['user_id' => $this->usuario->id, 'nombre' => 'Cena familiar']); // pendiente

        $resultado = Plan::where('user_id', $this->usuario->id)
            ->filtrar(null, 'confirmado', null)
            ->get();

        $this->assertCount(1, $resultado);
        $this->assertEquals('confirmado', $resultado->first()->estado);
    }

    /** Busca por nombre y devuelve coincidencias parciales */
    public function test_filtra_por_nombre(): void
    {
        Plan::factory()->create(['user_id' => $this->usuario->id, 'nombre' => 'Viaje a la playa']);
        Plan::factory()->create(['user_id' => $this->usuario->id, 'nombre' => 'Cena familiar']);

        $resultado = Plan::where('user_id', $this->usuario->id)
            ->filtrar('Viaje', null, null)
            ->get();

        $this->assertCount(1, $resultado);
        $this->assertStringContainsString('Viaje', $resultado->first()->nombre);
    }

    /** Combina estado confirmado y próximos 7 días */
    public function test_combina_estado_y_fecha(): void
    {
        // Este cumple ambos filtros
        Plan::factory()->confirmado()->create([
            'user_id' => $this->usuario->id,
            'fecha_limite' => now()->addDays(3),
        ]);
        // Este está confirmado pero fuera del rango de 7 días
        Plan::factory()->confirmado()->create([
            'user_id' => $this->usuario->id,
            'fecha_limite' => now()->addDays(20),
        ]);

        $resultado = Plan::where('user_id', $this->usuario->id)
            ->filtrar(null, 'confirmado', '7dias')
            ->get();

        $this->assertCount(1, $resultado);
    }

    // ---------- Casos sin resultados ----------

    /** Cuando no hay coincidencias devuelve colección vacía */
    public function test_filtro_sin_resultados(): void
    {
        Plan::factory()->create(['user_id' => $this->usuario->id, 'nombre' => 'Cena familiar']);

        $resultado = Plan::where('user_id', $this->usuario->id)
            ->filtrar('Viaje', null, null)
            ->get();

        $this->assertCount(0, $resultado);
    }

    /** El filtro "vencidos" no incluye planes con fecha futura */
    public function test_vencidos_no_incluye_futuros(): void
    {
        Plan::factory()->create([
            'user_id' => $this->usuario->id,
            'fecha_limite' => now()->addDays(5), // futuro
        ]);

        $resultado = Plan::where('user_id', $this->usuario->id)
            ->filtrar(null, null, 'vencidos')
            ->get();

        $this->assertCount(0, $resultado);
    }
}

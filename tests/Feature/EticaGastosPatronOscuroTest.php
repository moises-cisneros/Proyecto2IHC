<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EticaGastosPatronOscuroTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test ético / HCI: Verifica que el modal de nuevo gasto no presente casillas
     * premarcadas por defecto (prevención de Pre-ticked Box / Opt-in involuntario).
     */
    public function test_modal_crear_gasto_no_incluye_casillas_de_participantes_premarcadas_por_defecto(): void
    {
        $organizador = User::factory()->create(['name' => 'Lucas']);
        $colaborador = User::factory()->create(['name' => 'Ana']);

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
            'estado' => 'pendiente',
        ]);

        $plan->invitaciones()->create([
            'email' => $colaborador->email,
            'user_id' => $colaborador->id,
            'estado' => 'aceptada',
            'token' => 'token-prueba-etica-1',
        ]);

        $response = $this->actingAs($organizador)->get(route('planes.detalle', [
            'plan' => $plan,
            'tab' => 'gastos',
        ]));

        $response->assertOk();

        // 1. Verificar que se renderizan los checkboxes de participantes
        $response->assertSee('class="check-participante"', false);

        // 2. Verificar que NO contienen el atributo 'checked' que forzaba la preselección
        $contenido = $response->getContent();

        // Extraer las etiquetas <input ... class="check-participante" ...>
        preg_match_all('/<input[^>]*class="check-participante"[^>]*>/i', $contenido, $matches);
        $this->assertNotEmpty($matches[0], 'Debe existir al menos una casilla de participante');

        foreach ($matches[0] as $inputTag) {
            $this->assertStringNotContainsString(
                'checked',
                $inputTag,
                "La casilla de participante no debe contener 'checked' por defecto (violación de opt-in explícito)"
            );
        }

        // 3. Verificar texto orientador neutral de opt-in consciente
        $response->assertSee('Selecciona explícitamente a las personas que asumirán este gasto (opt-in consciente).');
    }

    /**
     * Test de integridad: Intentar registrar un gasto sin seleccionar participantes
     * debe ser rechazado por validación para evitar imputaciones automáticas.
     */
    public function test_registro_de_gasto_requiere_seleccion_consciente_de_al_menos_un_participante(): void
    {
        $organizador = User::factory()->create();
        $plan = Plan::factory()->create(['user_id' => $organizador->id]);

        $response = $this->actingAs($organizador)->post(route('planes.gastos.guardar', $plan), [
            'concepto' => 'Gasto sin consentir',
            'monto_total' => 50.00,
            'tipo_division' => 'equitativa',
            'participantes' => [], // Ninguno seleccionado
        ]);

        $response->assertSessionHasErrors('participantes');
    }

    /**
     * Test de consecuencia ética: La división equitativa se aplica únicamente a los
     * participantes seleccionados deliberadamente, protegiendo a los ausentes de contraer deudas.
     */
    public function test_gasto_se_divide_exclusivamente_entre_los_participantes_seleccionados_voluntariamente(): void
    {
        $organizador = User::factory()->create(['name' => 'Lucas']);
        $ana = User::factory()->create(['name' => 'Ana']);
        $carlos = User::factory()->create(['name' => 'Carlos']); // Carlos no consumió

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'organizador' => $organizador->name,
        ]);

        $plan->invitaciones()->create([
            'email' => $ana->email,
            'user_id' => $ana->id,
            'estado' => 'aceptada',
            'token' => 'token-ana',
        ]);

        $plan->invitaciones()->create([
            'email' => $carlos->email,
            'user_id' => $carlos->id,
            'estado' => 'aceptada',
            'token' => 'token-carlos',
        ]);

        // Lucas anota un gasto de 60 Bs pero selecciona ÚNICAMENTE a él mismo y a Ana
        $response = $this->actingAs($organizador)->post(route('planes.gastos.guardar', $plan), [
            'concepto' => 'Almuerzo para dos',
            'monto_total' => 60.00,
            'tipo_division' => 'equitativa',
            'participantes' => [$organizador->id, $ana->id],
        ]);

        $response->assertRedirect(route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']));
        $response->assertSessionHas('exito');

        $gasto = $plan->gastos()->where('concepto', 'Almuerzo para dos')->first();
        $this->assertNotNull($gasto);

        // Divisiones creadas
        $divisiones = $gasto->divisiones;
        $this->assertCount(2, $divisiones, 'Solo deben generarse 2 cuotas de deuda');

        // Carlos no debe tener ninguna cuota imputada
        $this->assertFalse(
            $divisiones->pluck('user_id')->contains($carlos->id),
            'Carlos no debe tener deuda asignada porque no fue seleccionado'
        );

        // Ana debe tener su cuota de 30 Bs
        $divisionAna = $divisiones->firstWhere('user_id', $ana->id);
        $this->assertEquals(30.00, (float) $divisionAna->monto_asignado);
    }
}

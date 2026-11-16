<?php

namespace Tests\Feature;

use App\Models\Gasto;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SalidaHonestaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * CA-01: Exportación de datos de usuario en formato JSON y CSV sin credenciales ni tokens sensibles.
     */
    public function test_exportacion_de_datos_en_formatos_json_y_csv(): void
    {
        $user = User::factory()->create([
            'name' => 'Usuario Portabilidad',
            'email' => 'portabilidad@planazo.com',
            'alias_pago' => 'alias.portabilidad',
        ]);

        // 1. Descarga JSON
        $responseJson = $this->actingAs($user)->get('/perfil/datos/exportar?formato=json');
        $responseJson->assertStatus(200);
        $responseJson->assertHeader('Content-Type', 'application/json');
        $this->assertStringContainsString('mis-datos-planazo.json', (string) $responseJson->headers->get('Content-Disposition'));
        $this->assertStringContainsString('Usuario Portabilidad', $responseJson->getContent());
        $this->assertStringNotContainsString('password', strtolower($responseJson->getContent()));

        // 2. Descarga CSV
        $responseCsv = $this->actingAs($user)->get('/perfil/datos/exportar?formato=csv');
        $responseCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $responseCsv->headers->get('Content-Type'));
        $this->assertStringContainsString('mis-datos-planazo.csv', (string) $responseCsv->headers->get('Content-Disposition'));
        $this->assertStringContainsString('Usuario Portabilidad', $responseCsv->getContent());
    }

    /**
     * CA-02: Intento de eliminación de cuenta bloqueado por organizar planes abiertos (status 302 y error descriptivo).
     */
    public function test_intento_de_eliminacion_de_cuenta_bloqueado_por_planes_abiertos(): void
    {
        $organizador = User::factory()->create(['password' => bcrypt('password123')]);
        Plan::factory()->create([
            'user_id' => $organizador->id,
            'nombre' => 'Plan de Fin de Semana',
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($organizador)->post('/perfil/eliminar-cuenta', [
            'current_password' => 'password123',
        ]);

        $response->assertRedirect('/perfil/datos');
        $response->assertSessionHas('error');
        $response->assertSessionHas('bloqueos_cuenta');

        $this->assertDatabaseHas('eventos', [
            'evento' => 'cuenta_eliminacion_bloqueada',
        ]);
        $this->assertNotNull($organizador->fresh());
    }

    /**
     * CA-03: Intento de eliminación de cuenta bloqueado por deudas pendientes en gastos.
     */
    public function test_intento_de_eliminacion_de_cuenta_bloqueado_por_deudas_en_gastos(): void
    {
        $organizador = User::factory()->create();
        $deudor = User::factory()->create(['password' => bcrypt('password123')]);

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'estado' => 'confirmado',
        ]);

        $plan->invitaciones()->create([
            'email' => $deudor->email,
            'user_id' => $deudor->id,
            'estado' => 'aceptada',
            'token' => 'token-deuda-salida',
        ]);

        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Alquiler de local',
            'monto_total' => 200.00,
            'tipo_division' => 'equitativo',
        ]);

        $gasto->divisiones()->createMany([
            ['user_id' => $organizador->id, 'monto_asignado' => 100.00],
            ['user_id' => $deudor->id, 'monto_asignado' => 100.00],
        ]);

        $response = $this->actingAs($deudor)->post('/perfil/eliminar-cuenta', [
            'current_password' => 'password123',
        ]);

        $response->assertRedirect('/perfil/datos');
        $response->assertSessionHas('error');
        $response->assertSessionHas('bloqueos_cuenta');
        $this->assertNotNull($deudor->fresh());
    }

    /**
     * CA-04: Eliminación exitosa de cuenta y verificación de imposibilidad de inicio de sesión posterior.
     */
    public function test_eliminacion_exitosa_de_cuenta_y_bloqueo_de_inicio_de_sesion_futuro(): void
    {
        $user = User::factory()->create(['password' => bcrypt('miClaveSegura123')]);

        $response = $this->actingAs($user)->post('/perfil/eliminar-cuenta', [
            'current_password' => 'miClaveSegura123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('exito');
        $this->assertGuest();

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertFalse((bool) $user->fresh()->activo);

        // Verificación de que las credenciales no permiten login futuro
        $this->assertFalse(Auth::attempt([
            'email' => $user->email,
            'password' => 'miClaveSegura123',
            'activo' => true,
        ]));

        $this->assertDatabaseHas('eventos', [
            'evento' => 'cuenta_eliminada',
        ]);
    }

    /**
     * CA-05: Bloqueo de eliminación de plan con deudas pendientes entre participantes.
     */
    public function test_bloqueo_honesto_de_eliminacion_de_plan_con_deudas_pendientes(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'nombre' => 'Viaje al Lago',
            'estado' => 'pendiente',
        ]);

        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Combustible',
            'monto_total' => 150.00,
            'tipo_division' => 'equitativo',
        ]);

        $gasto->divisiones()->createMany([
            ['user_id' => $organizador->id, 'monto_asignado' => 75.00],
            ['user_id' => $participante->id, 'monto_asignado' => 75.00],
        ]);

        $response = $this->actingAs($organizador)->delete("/planes/{$plan->id}");

        $response->assertRedirect();
        $response->assertSessionHas('bloqueo_plan_eliminacion');
        $this->assertNotNull($plan->fresh());

        $this->assertDatabaseHas('eventos', [
            'evento' => 'plan_eliminacion_bloqueada',
        ]);
    }

    /**
     * CA-06: Desvinculación y rechazo simétrico de invitación pendiente.
     */
    public function test_flujo_de_rechazo_simetrico_de_invitacion_pendiente(): void
    {
        $organizador = User::factory()->create();
        $invitado = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        $invitacion = $plan->invitaciones()->create([
            'email' => $invitado->email,
            'user_id' => $invitado->id,
            'estado' => 'pendiente',
            'token' => 'token-rechazo-formal',
        ]);

        $response = $this->actingAs($invitado)->post("/planes/{$plan->id}/rechazar-invitacion");

        $response->assertRedirect('/mis-planes');
        $response->assertSessionHas('exito');
        $this->assertEquals('rechazada', $invitacion->fresh()->estado);

        $this->assertDatabaseHas('eventos', [
            'evento' => 'invitacion_rechazada',
        ]);
    }
}

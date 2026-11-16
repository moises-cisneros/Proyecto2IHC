<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Gasto;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalidaHonestaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_anonimo_es_redirigido_al_intentar_ver_datos_o_exportar(): void
    {
        $this->get('/perfil/datos')->assertRedirect('/ingresar');
        $this->get('/perfil/datos/exportar?formato=json')->assertRedirect('/ingresar');
        $this->get('/perfil/datos/exportar?formato=csv')->assertRedirect('/ingresar');
    }

    public function test_usuario_autenticado_puede_ver_resumen_de_sus_datos(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/perfil/datos');

        $response->assertStatus(200);
        $response->assertViewIs('perfil.cuenta');
        $response->assertViewHas('usuario', $user);
        $response->assertViewHas('bloqueosCuenta');
    }

    public function test_exportar_datos_en_formato_json_descarga_archivo_con_headers_adecuados(): void
    {
        $user = User::factory()->create(['name' => 'Usuario Export']);

        $response = $this->actingAs($user)->get('/perfil/datos/exportar?formato=json');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $this->assertStringContainsString('mis-datos-planazo.json', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('Usuario Export', $response->getContent());
    }

    public function test_exportar_datos_en_formato_csv_descarga_archivo_con_headers_adecuados(): void
    {
        $user = User::factory()->create(['name' => 'Usuario CSV']);

        $response = $this->actingAs($user)->get('/perfil/datos/exportar?formato=csv');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('mis-datos-planazo.csv', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('Usuario CSV', $response->getContent());
    }

    public function test_eliminar_cuenta_falla_si_contrasena_actual_es_incorrecta(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correcta123')]);

        $response = $this->actingAs($user)->post('/perfil/eliminar-cuenta', [
            'current_password' => 'incorrecta',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertNotNull($user->fresh());
    }

    public function test_eliminar_cuenta_bloqueada_por_planes_abiertos_emite_evento_sin_pii(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        Plan::factory()->create([
            'user_id' => $user->id,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($user)->post('/perfil/eliminar-cuenta', [
            'current_password' => 'password123',
        ]);

        $response->assertRedirect('/perfil/datos');
        $response->assertSessionHas('error');
        $response->assertSessionHas('bloqueos_cuenta');

        $this->assertDatabaseHas('eventos', [
            'evento' => 'cuenta_eliminacion_bloqueada',
        ]);

        $evento = Evento::where('evento', 'cuenta_eliminacion_bloqueada')->latest('id')->first();
        $this->assertStringNotContainsString($user->email, json_encode($evento->props));
        $this->assertStringNotContainsString($user->name, json_encode($evento->props));
    }

    public function test_eliminar_cuenta_exitosa_cierra_sesion_invalida_usuario_y_emite_evento(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->actingAs($user)->post('/perfil/eliminar-cuenta', [
            'current_password' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('exito');
        $this->assertGuest();

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertFalse((bool) $user->fresh()->activo);

        $this->assertDatabaseHas('eventos', [
            'evento' => 'cuenta_eliminada',
        ]);
    }

    public function test_eliminar_plan_con_deudas_pendientes_bloquea_y_emite_evento(): void
    {
        $organizador = User::factory()->create();
        $participante = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Gasto con saldo',
            'monto_total' => 80.00,
            'tipo_division' => 'equitativo',
        ]);

        $gasto->divisiones()->createMany([
            ['user_id' => $organizador->id, 'monto_asignado' => 40.00],
            ['user_id' => $participante->id, 'monto_asignado' => 40.00],
        ]);

        $response = $this->actingAs($organizador)->delete("/planes/{$plan->id}");

        $response->assertRedirect();
        $response->assertSessionHas('bloqueo_plan_eliminacion');
        $this->assertNotNull($plan->fresh());

        $this->assertDatabaseHas('eventos', [
            'evento' => 'plan_eliminacion_bloqueada',
        ]);
    }

    public function test_eliminar_plan_sin_impedimentos_elimina_correctamente_y_emite_evento(): void
    {
        $organizador = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($organizador)->delete("/planes/{$plan->id}");

        $response->assertRedirect('/mis-planes');
        $response->assertSessionHas('exito');
        $this->assertDatabaseMissing('planes', ['id' => $plan->id]);

        $this->assertDatabaseHas('eventos', [
            'evento' => 'plan_eliminado',
        ]);
    }

    public function test_rechazar_invitacion_pendiente_desvincula_y_emite_evento(): void
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
            'token' => 'token-rechazar-123',
        ]);

        $response = $this->actingAs($invitado)->post("/planes/{$plan->id}/rechazar-invitacion");

        $response->assertRedirect('/mis-planes');
        $response->assertSessionHas('exito');
        $this->assertEquals('rechazada', $invitacion->fresh()->estado);

        $this->assertDatabaseHas('eventos', [
            'evento' => 'invitacion_rechazada',
        ]);
    }

    public function test_rechazar_invitacion_sin_ser_invitado_retorna_403(): void
    {
        $organizador = User::factory()->create();
        $usuarioAjeno = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($usuarioAjeno)->post("/planes/{$plan->id}/rechazar-invitacion");

        $response->assertStatus(403);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PerfilGestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_anonimo_es_redirigido_al_intentar_actualizar_perfil_o_contrasena(): void
    {
        $this->put('/perfil/datos', [
            'name' => 'Nuevo Nombre',
            'email' => 'nuevo@planazo.com',
        ])->assertRedirect('/ingresar');

        $this->put('/perfil/contrasena', [
            'current_password' => 'password123',
            'password' => 'nuevaPassword123',
            'password_confirmation' => 'nuevaPassword123',
        ])->assertRedirect('/ingresar');
    }

    public function test_usuario_puede_actualizar_su_nombre_y_correo(): void
    {
        $usuario = User::factory()->create([
            'name' => 'Nombre Antiguo',
            'email' => 'antiguo@planazo.com',
        ]);

        $response = $this->actingAs($usuario)->put(route('perfil.actualizar'), [
            'name' => 'Nombre Actualizado',
            'email' => 'actualizado@planazo.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('exito');

        $usuario->refresh();
        $this->assertSame('Nombre Actualizado', $usuario->name);
        $this->assertSame('actualizado@planazo.com', $usuario->email);
    }

    public function test_actualizar_perfil_falla_si_correo_ya_esta_en_uso(): void
    {
        User::factory()->create(['email' => 'otro@planazo.com']);
        $usuario = User::factory()->create(['email' => 'mio@planazo.com']);

        $response = $this->actingAs($usuario)->put(route('perfil.actualizar'), [
            'name' => 'Mi Nombre',
            'email' => 'otro@planazo.com',
        ]);

        $response->assertSessionHasErrors(['email']);
        $usuario->refresh();
        $this->assertSame('mio@planazo.com', $usuario->email);
    }

    public function test_usuario_puede_actualizar_su_contrasena_con_credencial_valida(): void
    {
        $usuario = User::factory()->create([
            'password' => Hash::make('claveActual123'),
        ]);

        $response = $this->actingAs($usuario)->put(route('perfil.contrasena.actualizar'), [
            'current_password' => 'claveActual123',
            'password' => 'nuevaClaveSegura456',
            'password_confirmation' => 'nuevaClaveSegura456',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('exito');

        $usuario->refresh();
        $this->assertTrue(Hash::check('nuevaClaveSegura456', $usuario->password));
    }

    public function test_actualizar_contrasena_falla_si_contrasena_actual_es_incorrecta(): void
    {
        $usuario = User::factory()->create([
            'password' => Hash::make('claveCorrecta123'),
        ]);

        $response = $this->actingAs($usuario)->put(route('perfil.contrasena.actualizar'), [
            'current_password' => 'claveErronea999',
            'password' => 'nuevaClaveSegura456',
            'password_confirmation' => 'nuevaClaveSegura456',
        ]);

        $response->assertSessionHasErrors(['current_password'], null, 'cambioContrasena');
        $usuario->refresh();
        $this->assertTrue(Hash::check('claveCorrecta123', $usuario->password));
    }

    public function test_vista_cuenta_expone_formularios_de_edicion_y_metricas_con_enlaces(): void
    {
        $usuario = User::factory()->create();
        Plan::factory()->create(['user_id' => $usuario->id, 'estado' => 'confirmado']);

        $response = $this->actingAs($usuario)->get(route('perfil.datos'));

        $response->assertOk();
        $response->assertSee('action="'.route('perfil.actualizar').'"', false);
        $response->assertSee('action="'.route('perfil.cobro.actualizar').'"', false);
        $response->assertSee('action="'.route('perfil.contrasena.actualizar').'"', false);
        $response->assertSee('Planes activos');
        $response->assertSee('Completados');
        $response->assertSee('Participaciones');
        $response->assertSee('metrica-enlace', false);
        $this->assertStringNotContainsString('style="', file_get_contents(resource_path('views/perfil/cuenta.blade.php')));
    }

    public function test_error_de_cambio_de_contrasena_no_reabre_el_modal_de_eliminacion(): void
    {
        $usuario = User::factory()->create(['password' => Hash::make('claveCorrecta123')]);

        $this->actingAs($usuario)->put(route('perfil.contrasena.actualizar'), [
            'current_password' => 'incorrecta',
            'password' => 'nuevaClaveSegura456',
            'password_confirmation' => 'nuevaClaveSegura456',
        ]);

        $response = $this->actingAs($usuario)->get(route('perfil.datos'));

        $response->assertDontSee("getElementById('modal-eliminar-cuenta')?.showModal()", false);
    }

    public function test_vista_cuenta_muestra_tarjeta_de_perfil_y_filas_de_bloqueo_estructuradas(): void
    {
        $usuario = User::factory()->create(['name' => 'Ana Perfil', 'email' => 'ana@planazo.com']);
        Plan::factory()->create([
            'user_id' => $usuario->id,
            'nombre' => 'Plan Pendiente Largo',
            'estado' => 'pendiente',
        ]);

        $response = $this->actingAs($usuario)->get(route('perfil.datos'));

        $response->assertOk();
        $response->assertSee('cuenta-perfil', false);
        $response->assertSee('avatar avatar-lg', false);
        $response->assertSee('Miembro desde');
        $response->assertSee('caja-salida-honesta-nombre', false);
        $response->assertSee('Plan Pendiente Largo');
        $response->assertSee('Plan abierto · Pendiente', false);
    }

    public function test_usuario_puede_eliminar_su_qr_de_cobro(): void
    {
        Storage::fake('public');
        $usuario = User::factory()->create();

        // Subir QR inicial
        $archivo = UploadedFile::fake()->image('qr_test.png', 200, 200);
        $this->actingAs($usuario)->put(route('perfil.cobro.actualizar'), [
            'alias_pago' => 'usuario.mp',
            'qr_pago' => $archivo,
        ]);

        $usuario->refresh();
        $this->assertNotNull($usuario->qr_pago_path);
        Storage::disk('public')->assertExists($usuario->qr_pago_path);

        // Ahora solicitar eliminación de QR
        $response = $this->actingAs($usuario)->put(route('perfil.cobro.actualizar'), [
            'alias_pago' => 'usuario.mp',
            'eliminar_qr' => '1',
        ]);

        $response->assertRedirect();
        $usuario->refresh();
        $this->assertNull($usuario->qr_pago_path);
    }
}

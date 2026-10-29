<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AutentificacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    /**
     * Usuario no autenticado es redirigido a /ingresar al consultar /mis-planes.
     */
    public function test_usuario_no_autenticado_es_redirigido_al_intentar_acceder_a_mis_planes(): void
    {
        $response = $this->get('/mis-planes');

        $response->assertRedirect('/ingresar');
    }

    /**
     * Inicio de sesión exitoso con credenciales correctas.
     */
    public function test_usuario_puede_iniciar_sesion_con_credenciales_correctas(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/ingresar', [
            'correo' => 'test@example.com',
            'contrasena' => 'password',
        ]);

        $response->assertRedirect('/mis-planes');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Intento de login fallido con credenciales inválidas.
     */
    public function test_login_falla_con_credenciales_invalidas_sin_revelar_campo_especifico(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/ingresar', [
            'correo' => 'test@example.com',
            'contrasena' => 'incorrecta',
        ]);

        $response->assertSessionHas('error', 'El correo o la contraseña no son correctos.');
        $this->assertGuest();
    }

    /**
     * Validación de correo único y longitud mínima de contraseña en registro.
     */
    public function test_registro_valida_correo_unico_y_contrasena_minima_8_caracteres(): void
    {
        User::factory()->create([
            'email' => 'duplicado@example.com',
        ]);

        $response = $this->post('/registro', [
            'nombre' => 'Nuevo Usuario',
            'correo' => 'duplicado@example.com',
            'contrasena' => '123',
            'contrasena_confirmation' => '123',
        ]);

        $response->assertSessionHasErrors(['correo', 'contrasena']);
        $this->assertGuest();
    }

    /**
     * Registro exitoso de usuario con inicio de sesión automático.
     */
    public function test_usuario_puede_registrarse_correctamente_e_iniciar_sesion_automaticamente(): void
    {
        $response = $this->post('/registro', [
            'nombre' => 'Nuevo Usuario',
            'correo' => 'nuevo@example.com',
            'contrasena' => 'password123',
            'contrasena_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/mis-planes');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@example.com',
            'name' => 'Nuevo Usuario',
        ]);
    }

    /**
     * Usuario autenticado puede cerrar sesión correctamente.
     */
    public function test_usuario_autenticado_puede_cerrar_sesion(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/salir');

        $response->assertRedirect('/ingresar');
        $this->assertGuest();
    }
}

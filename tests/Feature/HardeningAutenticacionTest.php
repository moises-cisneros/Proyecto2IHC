<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HardeningAutenticacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cierre_de_sesion_invalida_sesion_y_regenera_token(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario);
        $sesionIdAnterior = session()->getId();
        $tokenAnterior = csrf_token();

        $respuesta = $this->post(route('salir'));

        $respuesta->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertNotEquals($sesionIdAnterior, session()->getId());
        $this->assertNotEquals($tokenAnterior, csrf_token());
    }

    public function test_usuario_autenticado_es_redirigido_al_visitar_login_o_registro(): void
    {
        $usuario = User::factory()->create();

        $resLogin = $this->actingAs($usuario)->get(route('login'));
        $resLogin->assertRedirect(route('planes.listar'));

        $resRegistro = $this->actingAs($usuario)->get(route('registro'));
        $resRegistro->assertRedirect(route('planes.listar'));

        $resRecuperar = $this->actingAs($usuario)->get(route('recuperar'));
        $resRecuperar->assertRedirect(route('planes.listar'));
    }

    public function test_recuperacion_contrasena_retorna_mensaje_generico_sin_revelar_existencia(): void
    {
        $usuarioExistente = User::factory()->create(['email' => 'existe@planazo.test']);

        // 1. Correo que existe
        $resExiste = $this->post(route('enviar.correo'), [
            'correo' => 'existe@planazo.test',
        ]);
        $resExiste->assertSessionHas('exito', 'Si el correo ingresado está registrado, te enviamos un enlace de recuperación.');

        // 2. Correo que NO existe
        $resNoExiste = $this->post(route('enviar.correo'), [
            'correo' => 'inexistente@planazo.test',
        ]);
        $resNoExiste->assertSessionHas('exito', 'Si el correo ingresado está registrado, te enviamos un enlace de recuperación.');
        $resNoExiste->assertSessionMissing('error');
    }

    public function test_exceso_de_intentos_de_login_es_bloqueado_por_rate_limiting(): void
    {
        // 5 intentos permitidos en 1 minuto según throttle:5,1
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('ingresar'), [
                'correo' => 'prueba@planazo.test',
                'contrasena' => 'password123',
            ]);
        }

        // El 6to intento debe recibir 429 Too Many Requests
        $respuestaBloqueada = $this->post(route('ingresar'), [
            'correo' => 'prueba@planazo.test',
            'contrasena' => 'password123',
        ]);

        $respuestaBloqueada->assertStatus(429);
    }
}

<?php

namespace Tests\Unit;

use App\Models\Gasto;
use App\Models\Invitacion;
use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use App\Models\Voto;
use App\Services\UserDataExportService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SalidaHonestaDominioTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_sin_impedimentos_puede_eliminar_su_cuenta(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->puedeEliminarCuenta());
        $this->assertEmpty($user->validarBloqueosEliminacionCuenta());
    }

    public function test_usuario_organizador_de_planes_abiertos_no_puede_eliminar_su_cuenta(): void
    {
        $user = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $user->id,
            'estado' => 'pendiente',
        ]);

        $this->assertFalse($user->puedeEliminarCuenta());
        $bloqueos = $user->validarBloqueosEliminacionCuenta();
        $this->assertArrayHasKey('planes_abiertos_organizados', $bloqueos);
        $this->assertCount(1, $bloqueos['planes_abiertos_organizados']['planes']);
        $this->assertEquals($plan->id, $bloqueos['planes_abiertos_organizados']['planes'][0]['id']);
    }

    public function test_usuario_con_deudas_en_gastos_no_puede_eliminar_su_cuenta(): void
    {
        $organizador = User::factory()->create();
        $deudor = User::factory()->create();

        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'estado' => 'confirmado',
        ]);

        $plan->invitaciones()->create([
            'email' => $deudor->email,
            'user_id' => $deudor->id,
            'estado' => 'aceptada',
            'token' => 'token-deudor-test',
        ]);

        $gasto = Gasto::create([
            'plan_id' => $plan->id,
            'pagador_id' => $organizador->id,
            'concepto' => 'Cena grupal',
            'monto_total' => 100.00,
            'tipo_division' => 'equitativo',
        ]);

        $gasto->divisiones()->createMany([
            ['user_id' => $organizador->id, 'monto_asignado' => 50.00],
            ['user_id' => $deudor->id, 'monto_asignado' => 50.00],
        ]);

        $this->assertFalse($deudor->puedeEliminarCuenta());
        $bloqueos = $deudor->validarBloqueosEliminacionCuenta();
        $this->assertArrayHasKey('deudas_pendientes_gastos', $bloqueos);
        $this->assertEquals(50.00, $bloqueos['deudas_pendientes_gastos']['planes'][0]['monto']);
    }

    public function test_eliminar_cuenta_invalida_credenciales_y_deshabilita_acceso(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $user->eliminarCuenta();

        $this->assertFalse($user->activo);
        $this->assertSoftDeleted('users', ['id' => $user->id]);

        $this->assertFalse(Auth::attempt([
            'email' => $user->email,
            'password' => 'password123',
            'activo' => true,
        ]));
    }

    public function test_intento_de_eliminar_cuenta_con_bloqueos_lanza_excepcion_de_dominio(): void
    {
        $user = User::factory()->create();
        Plan::factory()->create([
            'user_id' => $user->id,
            'estado' => 'pendiente',
        ]);

        $this->expectException(DomainException::class);
        $user->eliminarCuenta();
    }

    public function test_plan_confirmado_bloquea_eliminacion(): void
    {
        $organizador = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'estado' => 'confirmado',
        ]);

        $this->assertFalse($plan->puedeEliminarse());
        $bloqueos = $plan->validarBloqueosEliminacion();
        $this->assertArrayHasKey('plan_confirmado', $bloqueos);
    }

    public function test_plan_con_deudas_pendientes_bloquea_eliminacion(): void
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
            'concepto' => 'Transporte',
            'monto_total' => 60.00,
            'tipo_division' => 'equitativo',
        ]);

        $gasto->divisiones()->createMany([
            ['user_id' => $organizador->id, 'monto_asignado' => 30.00],
            ['user_id' => $participante->id, 'monto_asignado' => 30.00],
        ]);

        $this->assertFalse($plan->puedeEliminarse());
        $bloqueos = $plan->validarBloqueosEliminacion();
        $this->assertArrayHasKey('deudas_pendientes_gastos', $bloqueos);
    }

    public function test_plan_sin_pendientes_ni_confirmado_puede_eliminarse(): void
    {
        $organizador = User::factory()->create();
        $plan = Plan::factory()->create([
            'user_id' => $organizador->id,
            'estado' => 'pendiente',
        ]);

        $this->assertTrue($plan->puedeEliminarse());
        $this->assertEmpty($plan->validarBloqueosEliminacion());
    }

    public function test_invitacion_puede_ser_rechazada_formalmente(): void
    {
        $invitacion = Invitacion::factory()->create([
            'estado' => 'pendiente',
        ]);

        $invitacion->rechazar();

        $this->assertEquals('rechazada', $invitacion->fresh()->estado);
    }

    public function test_servicio_exportacion_datos_genera_json_y_csv_sin_datos_sensibles(): void
    {
        $user = User::factory()->create([
            'name' => 'Usuario Test',
            'email' => 'test@planazo.com',
            'alias_pago' => 'test.planazo',
            'cbu_pago' => '1234567890123456789012',
        ]);

        $planOrganizado = Plan::factory()->create([
            'user_id' => $user->id,
            'nombre' => 'Mi Plan Organizado',
            'estado' => 'pendiente',
        ]);

        $opcion = Opcion::create([
            'plan_id' => $planOrganizado->id,
            'user_id' => $user->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-01 19:00:00',
        ]);

        Voto::create([
            'user_id' => $user->id,
            'opcion_id' => $opcion->id,
        ]);

        $service = new UserDataExportService;
        $json = $service->exportarJson($user);

        $this->assertEquals('Usuario Test', $json['perfil']['nombre']);
        $this->assertEquals('test@planazo.com', $json['perfil']['email']);
        $this->assertEquals('test.planazo', $json['perfil']['alias_pago']);
        $this->assertArrayNotHasKey('password', $json['perfil']);
        $this->assertArrayNotHasKey('remember_token', $json['perfil']);
        $this->assertCount(1, $json['planes_organizados']);
        $this->assertCount(1, $json['votos_emitidos']);

        $csv = $service->exportarCsv($user);
        $this->assertStringContainsString('Usuario Test', $csv);
        $this->assertStringContainsString('test@planazo.com', $csv);
        $this->assertStringContainsString('Mi Plan Organizado', $csv);
        $this->assertStringNotContainsString('password', strtolower($csv));
    }
}

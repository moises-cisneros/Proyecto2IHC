<?php

namespace Tests\Unit;

use App\Models\Invitacion;
use App\Models\Opcion;
use App\Models\Plan;
use App\Models\User;
use App\Models\Voto;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ColaboracionModelosTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;
    private Plan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->usuario = User::factory()->create();
        $this->plan = Plan::factory()->create(['user_id' => $this->usuario->id]);
    }

    /**
     * Caso 1: test_plan_tiene_muchas_opciones_de_fecha_o_lugar
     */
    public function test_plan_tiene_muchas_opciones_de_fecha_o_lugar(): void
    {
        $opcionFecha = Opcion::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->usuario->id,
            'tipo' => 'fecha',
            'valor' => '2026-11-15',
            'descripcion' => 'Propuesta fin de semana',
        ]);

        $opcionLugar = Opcion::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->usuario->id,
            'tipo' => 'lugar',
            'valor' => 'Restaurante El Lago',
            'descripcion' => 'Terraza exterior',
        ]);

        $this->assertEquals(2, $this->plan->opciones()->count());
        $this->assertTrue($this->plan->opciones->contains($opcionFecha));
        $this->assertTrue($this->plan->opciones->contains($opcionLugar));

        // Validación de scopes de Opcion
        $this->assertEquals(1, $this->plan->opciones()->fechas()->count());
        $this->assertEquals(1, $this->plan->opciones()->lugares()->count());
    }

    /**
     * Caso 2: test_plan_tiene_participantes_a_traves_de_invitaciones_aceptadas
     */
    public function test_plan_tiene_participantes_a_traves_de_invitaciones_aceptadas(): void
    {
        $participanteAceptado = User::factory()->create(['name' => 'Participante A']);
        $participantePendiente = User::factory()->create(['name' => 'Participante B']);

        // Invitación aceptada vinculada a usuario
        Invitacion::create([
            'plan_id' => $this->plan->id,
            'email' => $participanteAceptado->email,
            'token' => Str::random(40),
            'estado' => 'aceptada',
            'user_id' => $participanteAceptado->id,
        ]);

        // Invitación pendiente sin aceptar
        Invitacion::create([
            'plan_id' => $this->plan->id,
            'email' => $participantePendiente->email,
            'token' => Str::random(40),
            'estado' => 'pendiente',
            'user_id' => $participantePendiente->id,
        ]);

        $participantes = $this->plan->participantes()->get();

        $this->assertCount(1, $participantes);
        $this->assertTrue($participantes->contains($participanteAceptado));
        $this->assertFalse($participantes->contains($participantePendiente));
    }

    /**
     * Caso 3: test_voto_pertenece_a_usuario_y_a_opcion
     */
    public function test_voto_pertenece_a_usuario_y_a_opcion(): void
    {
        $opcion = Opcion::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->usuario->id,
            'tipo' => 'lugar',
            'valor' => 'Parque Central',
        ]);

        $votante = User::factory()->create();

        $voto = Voto::create([
            'opcion_id' => $opcion->id,
            'user_id' => $votante->id,
        ]);

        $this->assertInstanceOf(Opcion::class, $voto->opcion);
        $this->assertEquals($opcion->id, $voto->opcion->id);

        $this->assertInstanceOf(User::class, $voto->user);
        $this->assertEquals($votante->id, $voto->user->id);

        $this->assertTrue($opcion->votos->contains($voto));
        $this->assertTrue($votante->votos->contains($voto));
    }

    /**
     * Caso 4: test_base_de_datos_impide_voto_duplicado_del_mismo_usuario_por_opcion
     */
    public function test_base_de_datos_impide_voto_duplicado_del_mismo_usuario_por_opcion(): void
    {
        $opcion = Opcion::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->usuario->id,
            'tipo' => 'fecha',
            'valor' => '2026-12-01',
        ]);

        $votante = User::factory()->create();

        // Primer voto válido
        Voto::create([
            'opcion_id' => $opcion->id,
            'user_id' => $votante->id,
        ]);

        // El segundo voto idéntico debe ser rechazado por la restricción UNIQUE de la BD
        $this->expectException(QueryException::class);

        Voto::create([
            'opcion_id' => $opcion->id,
            'user_id' => $votante->id,
        ]);
    }

    /**
     * Caso 5 (complementario): test_invitacion_pertenece_a_plan_y_a_usuario
     */
    public function test_invitacion_pertenece_a_plan_y_a_usuario(): void
    {
        $invitado = User::factory()->create();

        $invitacion = Invitacion::create([
            'plan_id' => $this->plan->id,
            'email' => $invitado->email,
            'token' => 'token-unico-12345',
            'estado' => 'aceptada',
            'user_id' => $invitado->id,
        ]);

        $this->assertInstanceOf(Plan::class, $invitacion->plan);
        $this->assertEquals($this->plan->id, $invitacion->plan->id);

        $this->assertInstanceOf(User::class, $invitacion->user);
        $this->assertEquals($invitado->id, $invitacion->user->id);
    }

    /**
     * Caso 6 (complementario): test_opcion_tiene_votantes_a_traves_de_votos
     */
    public function test_opcion_tiene_votantes_a_traves_de_votos(): void
    {
        $opcion = Opcion::create([
            'plan_id' => $this->plan->id,
            'user_id' => $this->usuario->id,
            'tipo' => 'lugar',
            'valor' => 'Centro Comercial',
        ]);

        $votanteA = User::factory()->create();
        $votanteB = User::factory()->create();

        Voto::create(['opcion_id' => $opcion->id, 'user_id' => $votanteA->id]);
        Voto::create(['opcion_id' => $opcion->id, 'user_id' => $votanteB->id]);

        $this->assertCount(2, $opcion->votantes);
        $this->assertTrue($opcion->votantes->contains($votanteA));
        $this->assertTrue($opcion->votantes->contains($votanteB));
    }
}

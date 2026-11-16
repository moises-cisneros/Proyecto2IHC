<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'activo',
        'alias_pago',
        'cbu_pago',
        'qr_pago_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /**
     * Actividades registradas por el usuario.
     */
    public function actividades(): HasMany
    {
        return $this->hasMany(PlanActivity::class);
    }

    /**
     * Actividades donde el usuario es destinatario o contraparte bilateral.
     */
    public function actividadesRecibidas(): HasMany
    {
        return $this->hasMany(PlanActivity::class, 'destinatario_id');
    }

    /**
     * Planes creados por el usuario.
     */
    public function planes(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    /**
     * Invitaciones asociadas al usuario.
     */
    public function invitaciones(): HasMany
    {
        return $this->hasMany(Invitacion::class);
    }

    /**
     * Planes en los que el usuario participa mediante invitaciones aceptadas.
     */
    public function planesParticipados(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'invitaciones', 'user_id', 'plan_id')
            ->wherePivot('estado', 'aceptada');
    }

    /**
     * Opciones de fecha o lugar propuestas por el usuario.
     */
    public function opciones(): HasMany
    {
        return $this->hasMany(Opcion::class);
    }

    /**
     * Votos emitidos por el usuario.
     */
    public function votos(): HasMany
    {
        return $this->hasMany(Voto::class);
    }

    /**
     * Gastos pagados por el usuario.
     */
    public function gastosPagados(): HasMany
    {
        return $this->hasMany(Gasto::class, 'pagador_id');
    }

    /**
     * Divisiones de gastos asignadas al usuario.
     */
    public function gastoDivisiones(): HasMany
    {
        return $this->hasMany(GastoDivision::class, 'user_id');
    }

    /**
     * Pagos realizados por el usuario a otros participantes.
     */
    public function pagosRealizados(): HasMany
    {
        return $this->hasMany(Pago::class, 'deudor_id');
    }

    /**
     * Pagos recibidos por el usuario de otros participantes.
     */
    public function pagosRecibidos(): HasMany
    {
        return $this->hasMany(Pago::class, 'receptor_id');
    }

    /**
     * Evalúa si el usuario tiene al menos un método de cobro configurado (Alias, CBU o QR).
     */
    public function tieneDatosCobro(): bool
    {
        return ! empty($this->alias_pago) || ! empty($this->cbu_pago) || ! empty($this->qr_pago_path);
    }

    /**
     * Evalúa si el usuario puede eliminar su cuenta sin restricciones de planes abiertos o deudas.
     */
    public function puedeEliminarCuenta(): bool
    {
        return empty($this->validarBloqueosEliminacionCuenta());
    }

    /**
     * Valida y retorna los motivos de bloqueo que impiden la eliminación honesta de la cuenta.
     *
     * @return array<string, array{codigo: string, motivo: string, planes: array}>
     */
    public function validarBloqueosEliminacionCuenta(): array
    {
        $bloqueos = [];

        // 1. Planes abiertos organizados (debe transferirlos o cancelarlos)
        $planesAbiertos = $this->planes()
            ->whereIn('estado', ['pendiente', 'propuesta', 'votando'])
            ->get();

        if ($planesAbiertos->isNotEmpty()) {
            $bloqueos['planes_abiertos_organizados'] = [
                'codigo' => 'planes_abiertos_organizados',
                'motivo' => 'Organizas planes abiertos que aún no han sido transferidos o cancelados.',
                'planes' => $planesAbiertos->map(fn ($p) => [
                    'id' => $p->id,
                    'nombre' => $p->nombre,
                    'estado' => $p->estado,
                ])->all(),
            ];
        }

        // 2. Deudas pendientes en gastos de planes activos
        $planesConDeuda = [];
        $todosPlanes = $this->planes->merge($this->planesParticipados)->unique('id');

        foreach ($todosPlanes as $plan) {
            if ($plan->estado !== 'cancelado' && $plan->usuarioTieneDeuda($this->id)) {
                $planesConDeuda[] = [
                    'id' => $plan->id,
                    'nombre' => $plan->nombre,
                    'monto' => $plan->obtenerMontoDeudaUsuario($this->id),
                ];
            }
        }

        if (! empty($planesConDeuda)) {
            $bloqueos['deudas_pendientes_gastos'] = [
                'codigo' => 'deudas_pendientes_gastos',
                'motivo' => 'Mantienes saldos deudores pendientes de liquidación en gastos compartidos.',
                'planes' => $planesConDeuda,
            ];
        }

        return $bloqueos;
    }

    /**
     * Ejecuta la baja definitiva de la cuenta invalidando credenciales y aplicando borrado lógico.
     *
     * @throws DomainException
     */
    public function eliminarCuenta(): void
    {
        if (! $this->puedeEliminarCuenta()) {
            throw new DomainException('No puedes eliminar tu cuenta mientras tengas planes abiertos organizados o deudas pendientes.');
        }

        $this->activo = false;
        $this->password = Hash::make(Str::random(40));
        $this->save();

        $this->delete();
    }
}

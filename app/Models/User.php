<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
}

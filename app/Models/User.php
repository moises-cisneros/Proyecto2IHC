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
}

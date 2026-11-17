<?php

namespace App\Models;

use Database\Factories\InvitacionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitacion extends Model
{
    /** @use HasFactory<InvitacionFactory> */
    use HasFactory;

    protected $table = 'invitaciones';

    protected $fillable = [
        'plan_id',
        'email',
        'token',
        'estado',
        'user_id',
    ];

    /**
     * Plan al que pertenece la invitación.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Usuario que aceptó o se vinculó a la invitación (opcional).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para invitaciones aceptadas.
     */
    public function scopeAceptadas($query)
    {
        return $query->where('estado', 'aceptada');
    }

    /**
     * Scope: invitaciones que pertenecen al usuario, vinculadas por su id o por su correo.
     */
    public function scopeDeUsuario($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere('email', $user->email);
        });
    }

    /**
     * Rechaza formalmente la invitación pendiente.
     */
    public function rechazar(): void
    {
        $this->estado = 'rechazada';
        $this->save();
    }
}

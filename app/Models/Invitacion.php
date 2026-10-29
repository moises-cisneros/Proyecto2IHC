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
}

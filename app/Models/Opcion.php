<?php

namespace App\Models;

use Database\Factories\OpcionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opcion extends Model
{
    /** @use HasFactory<OpcionFactory> */
    use HasFactory;

    protected $table = 'opciones';

    protected $fillable = [
        'plan_id',
        'user_id',
        'tipo',
        'valor',
        'descripcion',
    ];

    /**
     * Plan al que pertenece esta opción propuesta.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Usuario que propuso la opción.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Votos registrados para esta opción.
     */
    public function votos(): HasMany
    {
        return $this->hasMany(Voto::class);
    }

    /**
     * Usuarios que han votado por esta opción.
     */
    public function votantes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'votos', 'opcion_id', 'user_id');
    }

    /**
     * Scope para opciones de tipo fecha.
     */
    public function scopeFechas($query)
    {
        return $query->where('tipo', 'fecha');
    }

    /**
     * Scope para opciones de tipo lugar.
     */
    public function scopeLugares($query)
    {
        return $query->where('tipo', 'lugar');
    }
}

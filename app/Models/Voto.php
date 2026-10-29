<?php

namespace App\Models;

use Database\Factories\VotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voto extends Model
{
    /** @use HasFactory<VotoFactory> */
    use HasFactory;

    protected $table = 'votos';

    protected $fillable = [
        'opcion_id',
        'user_id',
    ];

    /**
     * Opción por la que se votó.
     */
    public function opcion(): BelongsTo
    {
        return $this->belongsTo(Opcion::class);
    }

    /**
     * Usuario que emitió el voto.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GastoDivision extends Model
{
    use HasFactory;

    protected $table = 'gasto_divisiones';

    protected $fillable = [
        'gasto_id',
        'user_id',
        'monto_asignado',
    ];

    protected $casts = [
        'monto_asignado' => 'decimal:2',
    ];

    /**
     * Gasto al que corresponde esta división.
     */
    public function gasto(): BelongsTo
    {
        return $this->belongsTo(Gasto::class, 'gasto_id');
    }

    /**
     * Usuario asignado a esta porción del gasto.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

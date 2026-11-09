<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gasto extends Model
{
    use HasFactory;

    protected $table = 'gastos';

    protected $fillable = [
        'plan_id',
        'pagador_id',
        'concepto',
        'monto_total',
        'tipo_division',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
    ];

    /**
     * Plan al que pertenece el gasto.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Usuario que pagó el gasto.
     */
    public function pagador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pagador_id');
    }

    /**
     * Divisiones individuales asignadas a los participantes.
     */
    public function divisiones(): HasMany
    {
        return $this->hasMany(GastoDivision::class, 'gasto_id');
    }

    /**
     * Participantes entre quienes se dividió el gasto.
     */
    public function participantes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'gasto_divisiones', 'gasto_id', 'user_id')
            ->withPivot('monto_asignado')
            ->withTimestamps();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'plan_id',
        'deudor_id',
        'receptor_id',
        'monto',
        'estado',
        'motivo_rechazo',
        'fecha_resolucion',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_resolucion' => 'datetime',
    ];

    /**
     * Plan al que pertenece el pago.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Usuario deudor que emite el pago.
     */
    public function deudor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deudor_id');
    }

    /**
     * Usuario acreedor/receptor que recibe y confirma el pago.
     */
    public function receptor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receptor_id');
    }

    /**
     * Evalúa si el pago está en estado pendiente de confirmación.
     */
    public function esPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    /**
     * Evalúa si el pago fue confirmado.
     */
    public function esConfirmado(): bool
    {
        return $this->estado === 'confirmado';
    }

    /**
     * Evalúa si el pago fue rechazado.
     */
    public function esRechazado(): bool
    {
        return $this->estado === 'rechazado';
    }
}

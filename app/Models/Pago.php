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
        'comprobante_path',
        'monto_detectado',
        'banco_detectado',
        'estado_escaneo',
        'confianza_escaneo',
        'detalles_escaneo',
        'estado',
        'motivo_rechazo',
        'fecha_resolucion',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'monto_detectado' => 'decimal:2',
        'confianza_escaneo' => 'decimal:2',
        'detalles_escaneo' => 'array',
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

    /**
     * Evalúa si el pago tiene un archivo de comprobante adjunto.
     */
    public function tieneComprobante(): bool
    {
        return ! empty($this->comprobante_path);
    }

    /**
     * Evalúa si el análisis del comprobante verificó coincidencia con el monto adeudado.
     */
    public function esEscaneoVerificado(): bool
    {
        return $this->estado_escaneo === 'verificado';
    }

    /**
     * Evalúa si el escaneo detectó una discrepancia de monto respecto a la deuda.
     */
    public function esEscaneoDiscrepante(): bool
    {
        return $this->estado_escaneo === 'discrepante';
    }

    /**
     * Evalúa si el archivo subido fue catalogado como documento inválido o ilegible.
     */
    public function esEscaneoInvalido(): bool
    {
        return $this->estado_escaneo === 'invalido';
    }

    /**
     * Evalúa si el escaneo aún está pendiente de procesamiento.
     */
    public function esEscaneoPendiente(): bool
    {
        return $this->estado_escaneo === 'pendiente';
    }
}

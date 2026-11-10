<?php

namespace App\DTOs;

class ComprobanteScanResult
{
    public function __construct(
        public readonly bool $esComprobante,
        public readonly ?float $monto = null,
        public readonly ?string $banco = null,
        public readonly ?string $referencia = null,
        public readonly float $confianza = 0.0,
        public readonly array $rawPayload = [],
        public readonly ?string $mensajeError = null,
    ) {}

    /**
     * Evalúa si el monto extraído coincide con el monto esperado dentro de un margen de tolerancia.
     */
    public function coincideConMonto(float $montoEsperado, float $tolerancia = 0.05): bool
    {
        if (! $this->esComprobante || $this->monto === null) {
            return false;
        }

        return abs($this->monto - $montoEsperado) <= $tolerancia;
    }

    /**
     * Determina el estado de escaneo ('verificado', 'discrepante', 'invalido') según el monto esperado.
     */
    public function determinarEstadoEscaneo(float $montoEsperado): string
    {
        if (! $this->esComprobante) {
            return 'invalido';
        }

        if ($this->monto === null) {
            return 'invalido';
        }

        return $this->coincideConMonto($montoEsperado) ? 'verificado' : 'discrepante';
    }

    /**
     * Convierte el DTO a un array serializable.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'es_comprobante' => $this->esComprobante,
            'monto' => $this->monto,
            'banco' => $this->banco,
            'referencia' => $this->referencia,
            'confianza' => $this->confianza,
            'mensaje_error' => $this->mensajeError,
            'raw_payload' => $this->rawPayload,
        ];
    }
}

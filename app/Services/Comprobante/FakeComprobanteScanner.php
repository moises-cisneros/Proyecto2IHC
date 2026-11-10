<?php

namespace App\Services\Comprobante;

use App\Contracts\ComprobanteScannerInterface;
use App\DTOs\ComprobanteScanResult;

class FakeComprobanteScanner implements ComprobanteScannerInterface
{
    protected ?ComprobanteScanResult $mockResult = null;

    /**
     * Permite fijar un resultado específico para pruebas unitarias.
     */
    public function setMockResult(?ComprobanteScanResult $result): self
    {
        $this->mockResult = $result;

        return $this;
    }

    /**
     * Procesa la imagen simulando la extracción de comprobante según el nombre del archivo o fixture.
     */
    public function scan(string $absoluteOrStoragePath): ComprobanteScanResult
    {
        if ($this->mockResult !== null) {
            return $this->mockResult;
        }

        $normalized = strtolower(basename($absoluteOrStoragePath));

        if (str_contains($normalized, 'invalido') || str_contains($normalized, 'error') || str_contains($normalized, 'meme')) {
            return new ComprobanteScanResult(
                esComprobante: false,
                monto: null,
                banco: null,
                referencia: null,
                confianza: 0.10,
                rawPayload: ['motivo' => 'Documento no reconocido como comprobante bancario'],
                mensajeError: 'El archivo no contiene un comprobante legible o válido.'
            );
        }

        if (str_contains($normalized, 'discrepante')) {
            return new ComprobanteScanResult(
                esComprobante: true,
                monto: 35.50,
                banco: 'Banco Mercantil Santa Cruz',
                referencia: 'TRX-DISCREP-7721',
                confianza: 0.92,
                rawPayload: ['tipo' => 'transferencia_qr', 'monto_detectado' => 35.50]
            );
        }

        // Caso por defecto (coincidente / válido)
        return new ComprobanteScanResult(
            esComprobante: true,
            monto: 100.00,
            banco: 'Banco Unión',
            referencia: 'TRX-VALID-9901',
            confianza: 0.98,
            rawPayload: ['tipo' => 'transferencia_interbancaria', 'monto_detectado' => 100.00]
        );
    }
}

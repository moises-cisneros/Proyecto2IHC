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

        if (str_contains($normalized, 'invalido') || str_contains($normalized, 'error') || str_contains($normalized, 'meme') || str_contains($normalized, 'ticket')) {
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

        if (str_contains($normalized, 'bnb') || str_contains($normalized, '20bs')) {
            return new ComprobanteScanResult(
                esComprobante: true,
                monto: 20.00,
                banco: 'Banco Nacional de Bolivia',
                referencia: 'BNB-98432170',
                confianza: 0.98,
                rawPayload: ['monto_detectado' => 20.00, 'banco' => 'Banco Nacional de Bolivia', 'referencia' => 'BNB-98432170']
            );
        }

        if (str_contains($normalized, 'union') || str_contains($normalized, '100bs')) {
            return new ComprobanteScanResult(
                esComprobante: true,
                monto: 100.00,
                banco: 'Banco Unión',
                referencia: 'BU-202611-77889',
                confianza: 0.98,
                rawPayload: ['monto_detectado' => 100.00, 'banco' => 'Banco Unión', 'referencia' => 'BU-202611-77889']
            );
        }

        if (str_contains($normalized, 'yape') || str_contains($normalized, '25bs')) {
            return new ComprobanteScanResult(
                esComprobante: true,
                monto: 25.00,
                banco: 'Yape Bolivia',
                referencia: 'YAPE-482910',
                confianza: 0.98,
                rawPayload: ['monto_detectado' => 25.00, 'banco' => 'Yape Bolivia', 'referencia' => 'YAPE-482910']
            );
        }

        if (str_contains($normalized, 'yasta') || str_contains($normalized, '35bs')) {
            return new ComprobanteScanResult(
                esComprobante: true,
                monto: 35.50,
                banco: 'Yastá QR',
                referencia: 'YST-55992',
                confianza: 0.95,
                rawPayload: ['monto_detectado' => 35.50, 'banco' => 'Yastá QR', 'referencia' => 'YST-55992']
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

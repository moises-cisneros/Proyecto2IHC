<?php

namespace App\Services\Comprobante;

use App\Contracts\ComprobanteScannerInterface;
use App\DTOs\ComprobanteScanResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GeminiComprobanteScanner implements ComprobanteScannerInterface
{
    public function __construct(
        protected ?string $apiKey = null,
        protected string $model = 'gemini-2.5-flash',
    ) {
        $this->apiKey = $apiKey ?: config('services.gemini.key');
        $this->model = config('services.gemini.model', 'gemini-2.5-flash');
    }

    /**
     * Escanea el comprobante utilizando la API Multimodal de Google Gemini.
     */
    public function scan(string $absoluteOrStoragePath): ComprobanteScanResult
    {
        if (empty($this->apiKey)) {
            Log::warning('[GeminiComprobanteScanner] No se configuró GEMINI_API_KEY. Fallback a Fake scanner.');

            return (new FakeComprobanteScanner)->scan($absoluteOrStoragePath);
        }

        if (! file_exists($absoluteOrStoragePath)) {
            return new ComprobanteScanResult(
                esComprobante: false,
                mensajeError: 'El archivo físico del comprobante no existe en disco.'
            );
        }

        try {
            $mimeType = mime_content_type($absoluteOrStoragePath) ?: 'image/jpeg';
            $base64Data = base64_encode(file_get_contents($absoluteOrStoragePath));

            $prompt = 'Eres un auditor de transacciones bancarias. Analiza la imagen y extrae los datos del comprobante de transferencia o pago. '
                .'Responde EXCLUSIVAMENTE con un objeto JSON válido con las siguientes claves: '
                .'es_comprobante (bool), monto (float o null si no se identifica con certeza), '
                .'banco (string o null), referencia (string o null), confianza (float entre 0.0 y 1.0). '
                .'Si la imagen no es un recibo o transferencia bancaria, coloca es_comprobante en false y monto en null.';

            $endpoint = sprintf(
                'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
                $this->model,
                $this->apiKey
            );

            $response = Http::timeout(20)->post($endpoint, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => $base64Data,
                                ],
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'response_mime_type' => 'application/json',
                    'temperature' => 0.1,
                ],
            ]);

            if (! $response->successful()) {
                Log::error('[GeminiComprobanteScanner] Error HTTP desde API Gemini: '.$response->body());

                return new ComprobanteScanResult(
                    esComprobante: false,
                    confianza: 0.0,
                    mensajeError: 'Error en servicio de escaneo inteligente ('.$response->status().')'
                );
            }

            $jsonBody = $response->json();
            $rawText = $jsonBody['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            $parsed = json_decode($rawText, true) ?: [];

            $esComprobante = (bool) ($parsed['es_comprobante'] ?? false);
            $monto = isset($parsed['monto']) && is_numeric($parsed['monto']) ? (float) $parsed['monto'] : null;
            $banco = ! empty($parsed['banco']) ? (string) $parsed['banco'] : null;
            $referencia = ! empty($parsed['referencia']) ? (string) $parsed['referencia'] : null;
            $confianza = isset($parsed['confianza']) && is_numeric($parsed['confianza']) ? (float) $parsed['confianza'] : 0.85;

            return new ComprobanteScanResult(
                esComprobante: $esComprobante,
                monto: $monto,
                banco: $banco,
                referencia: $referencia,
                confianza: $confianza,
                rawPayload: $parsed
            );
        } catch (Throwable $e) {
            Log::error('[GeminiComprobanteScanner] Excepción al procesar imagen: '.$e->getMessage());

            return new ComprobanteScanResult(
                esComprobante: false,
                confianza: 0.0,
                mensajeError: 'Excepción al escanear comprobante: '.$e->getMessage()
            );
        }
    }
}

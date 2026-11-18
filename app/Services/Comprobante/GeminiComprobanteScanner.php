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
        $this->apiKey = $apiKey !== null ? $apiKey : config('services.gemini.key');
        $this->model = config('services.gemini.model', 'gemini-2.5-flash');
    }

    /**
     * Escanea el comprobante utilizando la API Multimodal de Google Gemini.
     */
    public function scan(string $absoluteOrStoragePath): ComprobanteScanResult
    {
        if (empty($this->apiKey)) {
            Log::warning('[GeminiComprobanteScanner] No se configuró GEMINI_API_KEY.');

            return new ComprobanteScanResult(
                esComprobante: false,
                confianza: 0.0,
                mensajeError: 'Servicio de escaneo no configurado. El acreedor deberá verificar el comprobante manualmente.'
            );
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

            $prompt = 'Eres un auditor financiero experto en pagos y comprobantes bancarios (transferencias bancarias, confirmaciones de pago con QR, recibos digitales de Yape, Simple QR, BNB, Banco Unión, BCP, Banco Mercantil, etc.). '
                .'Analiza la imagen minuciosamente. Responde EXCLUSIVAMENTE con un objeto JSON válido con las siguientes claves: '
                .'es_comprobante (bool: true si es un comprobante, recibo de transferencia o confirmación de pago QR; false si es una imagen ajena o QR sin datos de pago), '
                .'monto (float o null: monto numérico positivo pagado/transferido), '
                .'banco (string o null: entidad bancaria, pasarela o billetera digital), '
                .'referencia (string o null: código de operación o número de referencia), '
                .'confianza (float entre 0.0 y 1.0 representando tu certidumbre). '
                .'Si la imagen no corresponde a una transacción financiera válida, coloca es_comprobante en false y monto en null.';

            $endpoint = sprintf(
                'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
                $this->model,
                $this->apiKey
            );

            $payload = [
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
            ];

            // withoutVerifying previene el error cURL 60 en entornos sin CA bundle local configurado
            $response = Http::withoutVerifying()->timeout(25)->post($endpoint, $payload);

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
            $cleanJson = trim(preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($rawText)));
            $parsed = json_decode($cleanJson, true) ?: [];

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

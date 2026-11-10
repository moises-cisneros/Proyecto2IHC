<?php

namespace App\Contracts;

use App\DTOs\ComprobanteScanResult;

interface ComprobanteScannerInterface
{
    /**
     * Escanea un archivo de imagen de comprobante bancario y extrae sus datos estructurados.
     *
     * @param  string  $absoluteOrStoragePath  Ruta absoluta o relativa al disco del archivo de imagen.
     * @return ComprobanteScanResult Resultado estructurado con monto, banco, referencia y nivel de confianza.
     */
    public function scan(string $absoluteOrStoragePath): ComprobanteScanResult;
}

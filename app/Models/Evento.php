<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    use HasFactory;

    protected $table = 'eventos';

    protected $fillable = [
        'evento',
        'props',
    ];

    protected $casts = [
        'props' => 'array',
    ];

    /**
     * Helper de conveniencia para registrar eventos de interacción.
     *
     * @param  array<string, mixed>  $props
     */
    public static function registrar(string $evento, array $props = []): self
    {
        return self::create([
            'evento' => $evento,
            'props' => empty($props) ? null : $props,
        ]);
    }

    /**
     * Retorna la fecha y hora formateada en la zona horaria de Bolivia (America/La_Paz, UTC-4).
     */
    public function getFechaBoliviaAttribute(): string
    {
        return $this->created_at
            ? $this->created_at->timezone('America/La_Paz')->format('Y-m-d H:i:s')
            : '';
    }

    /**
     * Retorna solo la hora formateada en la zona horaria de Bolivia (HH:MM:SS).
     */
    public function getHoraBoliviaAttribute(): string
    {
        return $this->created_at
            ? $this->created_at->timezone('America/La_Paz')->format('H:i:s')
            : '';
    }

    /**
     * Calcula métricas agregadas del embudo de creación y confirmación de planes.
     *
     * @return array{
     *     iniciados: int,
     *     guardados: int,
     *     confirmados: int,
     *     tasa_conversion: float,
     *     tasa_guardado: float,
     *     total_eventos: int
     * }
     */
    public static function metricasEmbudo(): array
    {
        $conteoPorEvento = self::selectRaw('evento, COUNT(*) as total')
            ->whereIn('evento', ['plan_creacion_iniciada', 'plan_guardado', 'plan_confirmado'])
            ->groupBy('evento')
            ->pluck('total', 'evento')
            ->all();

        $iniciados = (int) ($conteoPorEvento['plan_creacion_iniciada'] ?? 0);
        $guardados = (int) ($conteoPorEvento['plan_guardado'] ?? 0);
        $confirmados = (int) ($conteoPorEvento['plan_confirmado'] ?? 0);

        $tasaConversion = $iniciados > 0
            ? round(($confirmados / $iniciados) * 100, 1)
            : 0.0;

        $tasaGuardado = $iniciados > 0
            ? round(($guardados / $iniciados) * 100, 1)
            : 0.0;

        $totalEventos = self::count();

        return [
            'iniciados' => $iniciados,
            'guardados' => $guardados,
            'confirmados' => $confirmados,
            'tasa_conversion' => $tasaConversion,
            'tasa_guardado' => $tasaGuardado,
            'total_eventos' => $totalEventos,
        ];
    }
}

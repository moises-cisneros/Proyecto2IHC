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
     * Calcula una tasa porcentual segura redondeada a 1 decimal sin riesgo de división por cero.
     */
    protected static function calcularPorcentaje(int $numerador, int $denominador): float
    {
        if ($denominador <= 0) {
            return 0.0;
        }

        return round(($numerador / $denominador) * 100, 1);
    }

    /**
     * Calcula la tasa de abandono (drop-off) segura entre dos pasos consecutivos.
     */
    protected static function calcularDropOff(int $pasoPrevio, int $pasoSiguiente): float
    {
        if ($pasoPrevio <= 0) {
            return 0.0;
        }

        $abandono = max(0, $pasoPrevio - $pasoSiguiente);

        return round(($abandono / $pasoPrevio) * 100, 1);
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
     *     drop_off_guardado: float,
     *     drop_off_confirmacion: float,
     *     total_eventos: int
     * }
     */
    public static function metricasEmbudoCreacion(): array
    {
        $conteoPorEvento = self::selectRaw('evento, COUNT(*) as total')
            ->whereIn('evento', ['plan_creacion_iniciada', 'plan_guardado', 'plan_confirmado'])
            ->groupBy('evento')
            ->pluck('total', 'evento')
            ->all();

        $iniciados = (int) ($conteoPorEvento['plan_creacion_iniciada'] ?? 0);
        $guardados = (int) ($conteoPorEvento['plan_guardado'] ?? 0);
        $confirmados = (int) ($conteoPorEvento['plan_confirmado'] ?? 0);

        return [
            'iniciados' => $iniciados,
            'guardados' => $guardados,
            'confirmados' => $confirmados,
            'tasa_conversion' => self::calcularPorcentaje($confirmados, $iniciados),
            'tasa_guardado' => self::calcularPorcentaje($guardados, $iniciados),
            'drop_off_guardado' => self::calcularDropOff($iniciados, $guardados),
            'drop_off_confirmacion' => self::calcularDropOff($guardados, $confirmados),
            'total_eventos' => self::count(),
        ];
    }

    /**
     * Alias retrocompatible con implementación previa.
     *
     * @return array<string, mixed>
     */
    public static function metricasEmbudo(): array
    {
        return self::metricasEmbudoCreacion();
    }

    /**
     * Calcula métricas del embudo de deliberación y votación colaborativa.
     *
     * @return array{
     *     iniciadas: int,
     *     propuestas: int,
     *     votos: int,
     *     tasa_conversion: float,
     *     drop_off_propuestas: float,
     *     drop_off_votos: float
     * }
     */
    public static function metricasEmbudoVotacion(): array
    {
        $conteo = self::selectRaw('evento, COUNT(*) as total')
            ->whereIn('evento', ['votacion_iniciada', 'propuesta_creada', 'voto_emitido'])
            ->groupBy('evento')
            ->pluck('total', 'evento')
            ->all();

        $iniciadas = (int) ($conteo['votacion_iniciada'] ?? 0);
        $propuestas = (int) ($conteo['propuesta_creada'] ?? 0);
        $votos = (int) ($conteo['voto_emitido'] ?? 0);

        return [
            'iniciadas' => $iniciadas,
            'propuestas' => $propuestas,
            'votos' => $votos,
            'tasa_conversion' => self::calcularPorcentaje($votos, $iniciadas),
            'drop_off_propuestas' => self::calcularDropOff($iniciadas, $propuestas),
            'drop_off_votos' => self::calcularDropOff($propuestas, $votos),
        ];
    }

    /**
     * Calcula métricas del embudo de invitaciones a planes.
     *
     * @return array{
     *     iniciadas: int,
     *     enviadas: int,
     *     aceptadas: int,
     *     rechazadas: int,
     *     tasa_conversion: float,
     *     drop_off_envio: float,
     *     drop_off_aceptacion: float
     * }
     */
    public static function metricasEmbudoInvitaciones(): array
    {
        $conteo = self::selectRaw('evento, COUNT(*) as total')
            ->whereIn('evento', ['invitacion_iniciada', 'invitacion_enviada', 'invitacion_aceptada', 'invitacion_rechazada'])
            ->groupBy('evento')
            ->pluck('total', 'evento')
            ->all();

        $iniciadas = (int) ($conteo['invitacion_iniciada'] ?? 0);
        $enviadas = (int) ($conteo['invitacion_enviada'] ?? 0);
        $aceptadas = (int) ($conteo['invitacion_aceptada'] ?? 0);
        $rechazadas = (int) ($conteo['invitacion_rechazada'] ?? 0);

        return [
            'iniciadas' => $iniciadas,
            'enviadas' => $enviadas,
            'aceptadas' => $aceptadas,
            'rechazadas' => $rechazadas,
            'tasa_conversion' => self::calcularPorcentaje($aceptadas, $iniciadas),
            'drop_off_envio' => self::calcularDropOff($iniciadas, $enviadas),
            'drop_off_aceptacion' => self::calcularDropOff($enviadas, $aceptadas),
        ];
    }

    /**
     * Calcula métricas del embudo de gastos, pagos y conciliaciones.
     *
     * @return array{
     *     gastos_iniciados: int,
     *     gastos_registrados: int,
     *     pagos_iniciados: int,
     *     pagos_confirmados: int,
     *     pagos_rechazados: int,
     *     tasa_conversion: float,
     *     drop_off_gastos: float,
     *     drop_off_pagos: float
     * }
     */
    public static function metricasEmbudoGastos(): array
    {
        $conteo = self::selectRaw('evento, COUNT(*) as total')
            ->whereIn('evento', ['gasto_iniciado', 'gasto_registrado', 'pago_iniciado', 'pago_confirmado', 'pago_rechazado'])
            ->groupBy('evento')
            ->pluck('total', 'evento')
            ->all();

        $gastosIniciados = (int) ($conteo['gasto_iniciado'] ?? 0);
        $gastosRegistrados = (int) ($conteo['gasto_registrado'] ?? 0);
        $pagosIniciados = (int) ($conteo['pago_iniciado'] ?? 0);
        $pagosConfirmados = (int) ($conteo['pago_confirmado'] ?? 0);
        $pagosRechazados = (int) ($conteo['pago_rechazado'] ?? 0);

        return [
            'gastos_iniciados' => $gastosIniciados,
            'gastos_registrados' => $gastosRegistrados,
            'pagos_iniciados' => $pagosIniciados,
            'pagos_confirmados' => $pagosConfirmados,
            'pagos_rechazados' => $pagosRechazados,
            'tasa_conversion' => self::calcularPorcentaje($pagosConfirmados, $gastosIniciados),
            'drop_off_gastos' => self::calcularDropOff($gastosIniciados, $gastosRegistrados),
            'drop_off_pagos' => self::calcularDropOff($pagosIniciados, $pagosConfirmados),
        ];
    }

    /**
     * Calcula métricas del embudo de retiro voluntario de participantes y bloqueos por deuda.
     *
     * @return array{
     *     iniciados: int,
     *     confirmados: int,
     *     bloqueados_deuda: int,
     *     tasa_conversion: float,
     *     drop_off: float
     * }
     */
    public static function metricasEmbudoRetiro(): array
    {
        $conteo = self::selectRaw('evento, COUNT(*) as total')
            ->whereIn('evento', ['retiro_iniciado', 'retiro_confirmado', 'retiro_bloqueado_deuda'])
            ->groupBy('evento')
            ->pluck('total', 'evento')
            ->all();

        // También contemplamos error_freno_tarea con motivo deuda_pendiente
        $bloqueadosProps = self::where('evento', 'error_freno_tarea')
            ->where('props->motivo', 'deuda_pendiente')
            ->count();

        $iniciados = (int) ($conteo['retiro_iniciado'] ?? 0);
        $confirmados = (int) ($conteo['retiro_confirmado'] ?? 0);
        $bloqueadosDeuda = (int) ($conteo['retiro_bloqueado_deuda'] ?? 0) + $bloqueadosProps;

        return [
            'iniciados' => $iniciados,
            'confirmados' => $confirmados,
            'bloqueados_deuda' => $bloqueadosDeuda,
            'tasa_conversion' => self::calcularPorcentaje($confirmados, $iniciados),
            'drop_off' => self::calcularDropOff($iniciados, $confirmados),
        ];
    }

    /**
     * Calcula métricas del embudo de búsquedas y filtros en catálogo.
     *
     * @return array{
     *     busquedas: int,
     *     filtros_aplicados: int,
     *     sin_resultados: int,
     *     tasa_efectividad: float,
     *     tasa_sin_resultados: float
     * }
     */
    public static function metricasEmbudoFiltros(): array
    {
        $conteo = self::selectRaw('evento, COUNT(*) as total')
            ->whereIn('evento', ['busqueda_realizada', 'filtro_aplicado', 'busqueda_sin_resultados'])
            ->groupBy('evento')
            ->pluck('total', 'evento')
            ->all();

        $busquedas = (int) ($conteo['busqueda_realizada'] ?? 0);
        $filtros = (int) ($conteo['filtro_aplicado'] ?? 0);
        $sinResultados = (int) ($conteo['busqueda_sin_resultados'] ?? 0);

        $tasaEfectividad = $busquedas > 0
            ? round((max(0, $busquedas - $sinResultados) / $busquedas) * 100, 1)
            : 0.0;

        return [
            'busquedas' => $busquedas,
            'filtros_aplicados' => $filtros,
            'sin_resultados' => $sinResultados,
            'tasa_efectividad' => $tasaEfectividad,
            'tasa_sin_resultados' => self::calcularPorcentaje($sinResultados, $busquedas),
        ];
    }

    /**
     * Retorna la agregación completa de los 6 macroembudos junto con errores de interacción.
     *
     * @return array{
     *     creacion: array<string, mixed>,
     *     votacion: array<string, mixed>,
     *     invitaciones: array<string, mixed>,
     *     gastos: array<string, mixed>,
     *     retiro: array<string, mixed>,
     *     filtros: array<string, mixed>,
     *     total_eventos: int,
     *     errores_frecuentes: array<string, int>
     * }
     */
    public static function metricasTodosLosEmbudos(): array
    {
        $erroresPorTipo = self::where('evento', 'like', 'error_%')
            ->selectRaw('evento, COUNT(*) as total')
            ->groupBy('evento')
            ->pluck('total', 'evento')
            ->all();

        $erroresPorMotivo = self::whereNotNull('props->motivo')
            ->pluck('props')
            ->map(fn ($props) => is_array($props) ? ($props['motivo'] ?? null) : null)
            ->filter()
            ->countBy()
            ->all();

        $erroresConsolidados = array_merge($erroresPorTipo, $erroresPorMotivo);

        return [
            'creacion' => self::metricasEmbudoCreacion(),
            'votacion' => self::metricasEmbudoVotacion(),
            'invitaciones' => self::metricasEmbudoInvitaciones(),
            'gastos' => self::metricasEmbudoGastos(),
            'retiro' => self::metricasEmbudoRetiro(),
            'filtros' => self::metricasEmbudoFiltros(),
            'total_eventos' => self::count(),
            'errores_frecuentes' => $erroresConsolidados,
        ];
    }
}

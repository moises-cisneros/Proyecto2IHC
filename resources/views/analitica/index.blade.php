@extends('layouts.app')

@section('titulo', 'Analítica de Interacción')

@section('contenido')
    <div class="analitica-cabecera" style="margin-bottom: var(--space-6);">
        <div
            style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: var(--space-3);">
            <div>
                <h1
                    style="font-size: var(--font-size-2xl); font-weight: var(--font-weight-bold); margin: 0 0 var(--space-1) 0; color: var(--color-foreground);">
                    Analítica de Interacción y Telemetría
                </h1>
                <p style="margin: 0; color: var(--color-muted); font-size: var(--font-size-base);">
                    Mapeo integral de los 6 macroembudos de usuario y puntos de freno en Planazo.
                </p>
            </div>
            <span
                style="display: inline-flex; align-items: center; gap: var(--space-2); padding: var(--space-1) var(--space-3); background: var(--color-success-bg); border: 1px solid var(--color-success-border); color: var(--color-success); border-radius: var(--radius-pill); font-size: var(--font-size-sm); font-weight: var(--font-weight-bold);">
                <span
                    style="width: 8px; height: 8px; border-radius: var(--radius-circle); background: var(--color-success);"></span>
                Desarrollo · Hora (UTC-4)
            </span>
        </div>
    </div>

    {{-- Cuadrícula de los 6 Embudos de Interacción --}}
    <section aria-labelledby="titulo-embudos" style="margin-bottom: var(--space-8);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
            <h2 id="titulo-embudos"
                style="font-size: var(--font-size-xl); font-weight: var(--font-weight-bold); margin: 0; color: var(--color-foreground);">
                Los 6 Embudos de Interacción
            </h2>
            <span style="font-size: var(--font-size-sm); color: var(--color-muted);">
                Total histórico: {{ $metricas['total_eventos'] ?? 0 }} eventos registrados
            </span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: var(--space-5);">
            {{-- Embudo 1: Creación y Confirmación de Planes --}}
            <div class="tarjeta-embudo">
                <div
                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                    <div>
                        <span class="chip-embudo-num">Embudo 1</span>
                        <h3 class="embudo-titulo">Creación y Confirmación</h3>
                    </div>
                    <div class="caja-porcentaje-terminan">
                        <div class="badge-conversion" id="conversion-creacion">
                            {{ $metricas['creacion']['tasa_conversion'] ?? ($metricas['tasa_conversion'] ?? 0) }}%</div>
                        <div class="terminan-label">terminan</div>
                    </div>
                </div>

                <div class="embudo-pasos-horizontal">
                    <div class="paso-columna">
                        <div class="paso-valor" id="creacion-iniciados">
                            {{ $metricas['creacion']['iniciados'] ?? ($metricas['iniciados'] ?? 0) }}</div>
                        <span id="contador-iniciados"
                            class="sr-only">{{ $metricas['creacion']['iniciados'] ?? ($metricas['iniciados'] ?? 0) }}</span>
                        <div class="paso-etiqueta">plan_creacion_iniciada</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="creacion-guardados">
                            {{ $metricas['creacion']['guardados'] ?? ($metricas['guardados'] ?? 0) }}</div>
                        <span id="contador-guardados"
                            class="sr-only">{{ $metricas['creacion']['guardados'] ?? ($metricas['guardados'] ?? 0) }}</span>
                        <div class="paso-etiqueta">plan_guardado</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="creacion-confirmados">
                            {{ $metricas['creacion']['confirmados'] ?? ($metricas['confirmados'] ?? 0) }}</div>
                        <span id="contador-confirmados"
                            class="sr-only">{{ $metricas['creacion']['confirmados'] ?? ($metricas['confirmados'] ?? 0) }}</span>
                        <div class="paso-etiqueta">plan_confirmado</div>
                    </div>
                </div>

                {{-- Barra de Progreso Brand-Bar oficial --}}
                <div class="barra-contenedor" role="progressbar"
                    aria-valuenow="{{ $metricas['creacion']['tasa_conversion'] ?? 0 }}" aria-valuemin="0"
                    aria-valuemax="100">
                    <div class="brand-bar" style="width: {{ min(100, $metricas['creacion']['tasa_conversion'] ?? 0) }}%;">
                    </div>
                </div>
            </div>

            {{-- Embudo 2: Deliberación y Votación --}}
            <div class="tarjeta-embudo">
                <div
                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                    <div>
                        <span class="chip-embudo-num">Embudo 2</span>
                        <h3 class="embudo-titulo">Deliberación y Votación</h3>
                    </div>
                    <div class="caja-porcentaje-terminan">
                        <div class="badge-conversion" id="conversion-votacion">
                            {{ $metricas['votacion']['tasa_conversion'] ?? 0 }}%</div>
                        <div class="terminan-label">terminan</div>
                    </div>
                </div>

                <div class="embudo-pasos-horizontal">
                    <div class="paso-columna">
                        <div class="paso-valor" id="votacion-iniciadas">{{ $metricas['votacion']['iniciadas'] ?? 0 }}</div>
                        <div class="paso-etiqueta">sala_abierta</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="votacion-propuestas">{{ $metricas['votacion']['propuestas'] ?? 0 }}
                        </div>
                        <div class="paso-etiqueta">opcion_propuesta</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="votacion-votos">{{ $metricas['votacion']['votos'] ?? 0 }}</div>
                        <div class="paso-etiqueta">voto_emitido</div>
                    </div>
                </div>

                <div class="barra-contenedor" role="progressbar"
                    aria-valuenow="{{ $metricas['votacion']['tasa_conversion'] ?? 0 }}" aria-valuemin="0"
                    aria-valuemax="100">
                    <div class="brand-bar" style="width: {{ min(100, $metricas['votacion']['tasa_conversion'] ?? 0) }}%;">
                    </div>
                </div>
            </div>

            {{-- Embudo 3: Invitaciones y Convocatoria --}}
            <div class="tarjeta-embudo">
                <div
                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                    <div>
                        <span class="chip-embudo-num">Embudo 3</span>
                        <h3 class="embudo-titulo">Invitaciones y Convocatoria</h3>
                    </div>
                    <div class="caja-porcentaje-terminan">
                        <div class="badge-conversion" id="conversion-invitaciones">
                            {{ $metricas['invitaciones']['tasa_conversion'] ?? 0 }}%</div>
                        <div class="terminan-label">terminan</div>
                    </div>
                </div>

                <div class="embudo-pasos-horizontal">
                    <div class="paso-columna">
                        <div class="paso-valor" id="invitaciones-enviadas">{{ $metricas['invitaciones']['enviadas'] ?? 0 }}
                        </div>
                        <div class="paso-etiqueta">invitacion_enviada</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="invitaciones-aceptadas">
                            {{ $metricas['invitaciones']['aceptadas'] ?? 0 }}</div>
                        <div class="paso-etiqueta">invitacion_aceptada</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="invitaciones-rechazadas">
                            {{ $metricas['invitaciones']['rechazadas'] ?? 0 }}</div>
                        <div class="paso-etiqueta">invitacion_rechazada</div>
                    </div>
                </div>

                <div class="barra-contenedor" role="progressbar"
                    aria-valuenow="{{ $metricas['invitaciones']['tasa_conversion'] ?? 0 }}" aria-valuemin="0"
                    aria-valuemax="100">
                    <div class="brand-bar"
                        style="width: {{ min(100, $metricas['invitaciones']['tasa_conversion'] ?? 0) }}%;"></div>
                </div>
            </div>

            {{-- Embudo 4: Gastos y Liquidación --}}
            <div class="tarjeta-embudo">
                <div
                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                    <div>
                        <span class="chip-embudo-num">Embudo 4</span>
                        <h3 class="embudo-titulo">Gastos y Liquidación</h3>
                    </div>
                    <div class="caja-porcentaje-terminan">
                        <div class="badge-conversion" id="conversion-gastos">
                            {{ $metricas['gastos']['tasa_conversion'] ?? 0 }}%</div>
                        <div class="terminan-label">terminan</div>
                    </div>
                </div>

                <div class="embudo-pasos-horizontal">
                    <div class="paso-columna">
                        <div class="paso-valor" id="gastos-registrados">
                            {{ $metricas['gastos']['gastos_registrados'] ?? 0 }}
                        </div>
                        <div class="paso-etiqueta">gasto_registrado</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="pagos-iniciados">{{ $metricas['gastos']['pagos_iniciados'] ?? 0 }}
                        </div>
                        <div class="paso-etiqueta">pago_iniciado</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="pagos-confirmados">
                            {{ $metricas['gastos']['pagos_confirmados'] ?? 0 }}
                        </div>
                        <div class="paso-etiqueta">pago_confirmado</div>
                    </div>
                </div>

                <div class="barra-contenedor" role="progressbar"
                    aria-valuenow="{{ $metricas['gastos']['tasa_conversion'] ?? 0 }}" aria-valuemin="0"
                    aria-valuemax="100">
                    <div class="brand-bar" style="width: {{ min(100, $metricas['gastos']['tasa_conversion'] ?? 0) }}%;">
                    </div>
                </div>
            </div>

            {{-- Embudo 5: Retiro de Participantes --}}
            <div class="tarjeta-embudo">
                <div
                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                    <div>
                        <span class="chip-embudo-num">Embudo 5</span>
                        <h3 class="embudo-titulo">Retiro de Participantes</h3>
                    </div>
                    <div class="caja-porcentaje-terminan">
                        <div class="badge-conversion" id="conversion-retiro">
                            {{ $metricas['retiro']['tasa_conversion'] ?? 0 }}%</div>
                        <div class="terminan-label">terminan</div>
                    </div>
                </div>

                <div class="embudo-pasos-horizontal">
                    <div class="paso-columna">
                        <div class="paso-valor" id="retiro-iniciados">{{ $metricas['retiro']['iniciados'] ?? 0 }}</div>
                        <div class="paso-etiqueta">retiro_iniciado</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="retiro-confirmados">{{ $metricas['retiro']['confirmados'] ?? 0 }}
                        </div>
                        <div class="paso-etiqueta">retiro_confirmado</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="retiro-bloqueados" style="color: var(--color-destructive);">
                            {{ $metricas['retiro']['bloqueados_deuda'] ?? 0 }}</div>
                        <div class="paso-etiqueta" style="color: var(--color-destructive);">bloqueo_deuda</div>
                    </div>
                </div>

                <div class="barra-contenedor" role="progressbar"
                    aria-valuenow="{{ $metricas['retiro']['tasa_conversion'] ?? 0 }}" aria-valuemin="0"
                    aria-valuemax="100">
                    <div class="brand-bar" style="width: {{ min(100, $metricas['retiro']['tasa_conversion'] ?? 0) }}%;">
                    </div>
                </div>
            </div>

            {{-- Embudo 6: Búsquedas y Filtros --}}
            <div class="tarjeta-embudo">
                <div
                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-4);">
                    <div>
                        <span class="chip-embudo-num">Embudo 6</span>
                        <h3 class="embudo-titulo">Búsquedas y Filtros</h3>
                    </div>
                    <div class="caja-porcentaje-terminan">
                        <div class="badge-conversion" id="conversion-filtros">
                            {{ $metricas['filtros']['tasa_efectividad'] ?? 0 }}%</div>
                        <div class="terminan-label">terminan</div>
                    </div>
                </div>

                <div class="embudo-pasos-horizontal">
                    <div class="paso-columna">
                        <div class="paso-valor" id="filtros-busquedas">{{ $metricas['filtros']['busquedas'] ?? 0 }}</div>
                        <div class="paso-etiqueta">busqueda_realizada</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="filtros-aplicados">
                            {{ $metricas['filtros']['filtros_aplicados'] ?? 0 }}
                        </div>
                        <div class="paso-etiqueta">filtro_aplicado</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="filtros-sin-resultados" style="color: var(--color-warning);">
                            {{ $metricas['filtros']['sin_resultados'] ?? 0 }}</div>
                        <div class="paso-etiqueta" style="color: var(--color-warning);">sin_resultados</div>
                    </div>
                </div>

                <div class="barra-contenedor" role="progressbar"
                    aria-valuenow="{{ $metricas['filtros']['tasa_efectividad'] ?? 0 }}" aria-valuemin="0"
                    aria-valuemax="100">
                    <div class="brand-bar"
                        style="width: {{ min(100, $metricas['filtros']['tasa_efectividad'] ?? 0) }}%;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Errores de Interacción y Puntos de Freno Frecuentes --}}
    @if (!empty($metricas['errores_frecuentes']))
        <section aria-labelledby="titulo-errores" style="margin-bottom: var(--space-8);">
            <h2 id="titulo-errores"
                style="font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); margin-bottom: var(--space-3); color: var(--color-foreground);">
                Puntos de Freno y Errores de Interacción Recientes
            </h2>
            <div
                style="background: var(--color-destructive-bg); border: 1px solid var(--color-destructive-border); border-radius: var(--radius-lg); padding: var(--space-4) var(--space-5);">
                <p
                    style="margin: 0 0 var(--space-2) 0; color: var(--color-destructive); font-size: var(--font-size-md); font-weight: var(--font-weight-semibold);">
                    Fricciones detectadas por el sistema con lista fija de motivos:
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: var(--space-2);">
                    @foreach ($metricas['errores_frecuentes'] as $motivo => $total)
                        <span
                            style="display: inline-flex; align-items: center; gap: var(--space-1); padding: var(--space-1) var(--space-2); background: var(--color-surface); border: 1px solid var(--color-destructive-border); border-radius: var(--radius-pill); font-size: var(--font-size-sm); color: var(--color-destructive); font-weight: var(--font-weight-semibold);">
                            <code>{{ $motivo }}</code>: <strong>{{ $total }}</strong>
                        </span>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Listado de Últimos Eventos Registrados --}}
    <section aria-labelledby="titulo-ultimos-eventos" style="margin-bottom: var(--space-8);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--space-4);">
            <h2 id="titulo-ultimos-eventos"
                style="font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); margin: 0; color: var(--color-foreground);">
                Últimos 25 Eventos Registrados
            </h2>
            <span style="font-size: var(--font-size-sm); color: var(--color-muted);">
                Hora oficial de Bolivia (UTC-4)
            </span>
        </div>

        @if ($ultimosEventos->isEmpty())
            <div
                style="background: var(--color-surface); border: 1px dashed var(--color-border); border-radius: var(--radius-lg); padding: var(--space-8) var(--space-5); text-align: center; color: var(--color-muted);">
                <p
                    style="font-size: var(--font-size-base); margin: 0 0 var(--space-1) 0; font-weight: var(--font-weight-semibold);">
                    No hay eventos registrados todavía.</p>
                <p style="font-size: var(--font-size-sm); margin: 0;">Interactúa con los flujos de creación, votación o
                    gastos
                    para observar la telemetría.</p>
            </div>
        @else
            <div
                style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); overflow-x: auto; box-shadow: var(--shadow-sm); width: 100%;">
                <table
                    style="width: 100%; table-layout: fixed; border-collapse: collapse; text-align: left; font-size: var(--font-size-md);">
                    <colgroup>
                        <col style="width: 170px;">
                        <col style="width: 210px;">
                        <col style="width: auto;">
                    </colgroup>
                    <thead style="background: var(--color-surface-soft); border-bottom: 1px solid var(--color-border);">
                        <tr>
                            <th
                                style="padding: var(--space-3) var(--space-4); font-weight: var(--font-weight-bold); color: var(--color-foreground); width: 170px;">
                                Hora</th>
                            <th
                                style="padding: var(--space-3) var(--space-4); font-weight: var(--font-weight-bold); color: var(--color-foreground); width: 210px;">
                                Evento</th>
                            <th
                                style="padding: var(--space-3) var(--space-4); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                                Propiedades de la Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ultimosEventos as $ev)
                            <tr style="border-bottom: 1px solid var(--color-neutral-bg);">
                                <td
                                    style="padding: var(--space-3) var(--space-4); font-family: monospace; font-size: var(--font-size-sm); color: var(--color-muted); white-space: nowrap; vertical-align: top;">
                                    {{ $ev->fecha_bolivia }}
                                </td>
                                <td
                                    style="padding: var(--space-3) var(--space-4); white-space: nowrap; vertical-align: top;">
                                    <span
                                        style="display: inline-block; padding: var(--space-1) var(--space-2); border-radius: var(--radius-sm); font-size: var(--font-size-xs); font-weight: var(--font-weight-semibold); background: var(--color-warning-bg); color: var(--color-warning);">
                                        {{ $ev->evento }}
                                    </span>
                                </td>
                                <td
                                    style="padding: var(--space-3) var(--space-4); font-family: monospace; font-size: var(--font-size-xs); color: var(--color-foreground); word-break: break-all; overflow-wrap: anywhere; white-space: normal; line-height: 1.45; vertical-align: top;">
                                    @if (!empty($ev->props))
                                        <code
                                            style="word-break: break-all; overflow-wrap: anywhere; white-space: normal; font-family: inherit; font-size: inherit; color: inherit;">{{ json_encode($ev->props, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code>
                                    @else
                                        <span style="color: var(--color-muted);">(sin propiedades adicionales)</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection

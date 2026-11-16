@extends('layouts.app')

@section('titulo', 'Analítica de Interacción')

@section('contenido')
    <div class="analitica-cabecera">
        <div class="analitica-cabecera-fila">
            <div>
                <h1 class="analitica-titulo">
                    Analítica de Interacción y Telemetría
                </h1>
                <p class="analitica-subtitulo">
                    Mapeo integral de los 6 macroembudos de usuario y puntos de freno en Planazo.
                </p>
            </div>
            <span class="analitica-entorno">
                <span class="analitica-entorno-punto"></span>
                Desarrollo · Hora (UTC-4)
            </span>
        </div>
    </div>

    {{-- Cuadrícula de los 6 Embudos de Interacción --}}
    <section aria-labelledby="titulo-embudos" class="analitica-seccion">
        <div class="analitica-seccion-cabecera">
            <h2 id="titulo-embudos" class="analitica-seccion-titulo">
                Los 6 Embudos de Interacción
            </h2>
            <span class="analitica-seccion-meta">
                Total histórico: {{ $metricas['total_eventos'] ?? 0 }} eventos registrados
            </span>
        </div>

        <div class="analitica-embudos-grid">
            {{-- Embudo 1: Creación y Confirmación de Planes --}}
            <div class="tarjeta-embudo">
                <div class="analitica-embudo-cabecera">
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
                <div class="analitica-embudo-cabecera">
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
                <div class="analitica-embudo-cabecera">
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
                <div class="analitica-embudo-cabecera">
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
                <div class="analitica-embudo-cabecera">
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
                        <div class="paso-valor analitica-texto-destructivo" id="retiro-bloqueados">
                            {{ $metricas['retiro']['bloqueados_deuda'] ?? 0 }}</div>
                        <div class="paso-etiqueta analitica-texto-destructivo">bloqueo_deuda</div>
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
                <div class="analitica-embudo-cabecera">
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
                        <div class="paso-valor analitica-texto-advertencia" id="filtros-sin-resultados">
                            {{ $metricas['filtros']['sin_resultados'] ?? 0 }}</div>
                        <div class="paso-etiqueta analitica-texto-advertencia">sin_resultados</div>
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
        <section aria-labelledby="titulo-errores" class="analitica-seccion">
            <h2 id="titulo-errores" class="analitica-errores-titulo">
                Puntos de Freno y Errores de Interacción Recientes
            </h2>
            <div class="analitica-errores-caja">
                <p class="analitica-errores-intro">
                    Fricciones detectadas por el sistema con lista fija de motivos:
                </p>
                <div class="analitica-errores-lista">
                    @foreach ($metricas['errores_frecuentes'] as $motivo => $total)
                        <span class="analitica-errores-chip">
                            <code>{{ $motivo }}</code>: <strong>{{ $total }}</strong>
                        </span>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Listado de Últimos Eventos Registrados --}}
    <section aria-labelledby="titulo-ultimos-eventos" class="analitica-seccion">
        <div class="analitica-seccion-cabecera">
            <h2 id="titulo-ultimos-eventos" class="analitica-seccion-titulo-menor">
                Últimos 25 Eventos Registrados
            </h2>
            <span class="analitica-seccion-meta">
                Hora oficial de Bolivia (UTC-4)
            </span>
        </div>

        @if ($ultimosEventos->isEmpty())
            <div class="analitica-vacio">
                <p class="analitica-vacio-titulo">
                    No hay eventos registrados todavía.</p>
                <p class="analitica-vacio-texto">Interactúa con los flujos de creación, votación o
                    gastos
                    para observar la telemetría.</p>
            </div>
        @else
            <div class="analitica-tabla-contenedor">
                <table class="analitica-tabla">
                    <colgroup>
                        <col class="analitica-col-hora">
                        <col class="analitica-col-evento">
                        <col class="analitica-col-props">
                    </colgroup>
                    <thead class="analitica-tabla-cabecera">
                        <tr>
                            <th class="analitica-tabla-th">
                                Hora</th>
                            <th class="analitica-tabla-th">
                                Evento</th>
                            <th class="analitica-tabla-th">
                                Propiedades de la Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ultimosEventos as $ev)
                            <tr class="analitica-tabla-fila">
                                <td class="analitica-celda-hora">
                                    {{ $ev->fecha_bolivia }}
                                </td>
                                <td class="analitica-celda-evento">
                                    <span class="analitica-evento-badge">
                                        {{ $ev->evento }}
                                    </span>
                                </td>
                                <td class="analitica-celda-props">
                                    @if (!empty($ev->props))
                                        <code
                                            class="analitica-props-codigo">{{ json_encode($ev->props, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code>
                                    @else
                                        <span class="analitica-props-vacio">(sin propiedades adicionales)</span>
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

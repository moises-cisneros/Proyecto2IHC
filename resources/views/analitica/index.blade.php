@extends('layouts.app')

@section('titulo', 'Analítica de Interacción')

@section('contenido')
    <div class="analitica-cabecera" style="margin-bottom: 28px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1 style="font-size: 26px; font-weight: 800; margin: 0 0 6px 0; color: #1c1c1a;">
                    Analítica de Interacción y Telemetría
                </h1>
                <p style="margin: 0; color: #6f6f6a; font-size: 15px;">
                    Mapeo integral de los 6 macroembudos de usuario y puntos de freno en Planazo.
                </p>
            </div>
            <span style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; background: #edf7ef; border: 1px solid #bbf7d0; color: #25663a; border-radius: 9999px; font-size: 13px; font-weight: 700;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #25663a;"></span>
                Desarrollo · Hora (UTC-4)
            </span>
        </div>
    </div>

    {{-- Cuadrícula de los 6 Embudos de Interacción --}}
    <section aria-labelledby="titulo-embudos" style="margin-bottom: 32px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h2 id="titulo-embudos" style="font-size: 20px; font-weight: 700; margin: 0; color: #1c1c1a;">
                Los 6 Embudos de Interacción
            </h2>
            <span style="font-size: 13px; color: #6f6f6a;">
                Total histórico: {{ $metricas['total_eventos'] ?? 0 }} eventos registrados
            </span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 20px;">
            {{-- Embudo 1: Creación y Confirmación de Planes --}}
            <div class="tarjeta-embudo">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                    <div>
                        <span class="chip-embudo-num">Embudo 1</span>
                        <h3 class="embudo-titulo">Creación y Confirmación</h3>
                    </div>
                    <div class="caja-porcentaje-terminan">
                        <div class="badge-conversion" id="conversion-creacion">{{ $metricas['creacion']['tasa_conversion'] ?? $metricas['tasa_conversion'] ?? 0 }}%</div>
                        <div class="terminan-label">terminan</div>
                    </div>
                </div>

                <div class="embudo-pasos-horizontal">
                    <div class="paso-columna">
                        <div class="paso-valor" id="creacion-iniciados">{{ $metricas['creacion']['iniciados'] ?? $metricas['iniciados'] ?? 0 }}</div>
                        <span id="contador-iniciados" class="sr-only">{{ $metricas['creacion']['iniciados'] ?? $metricas['iniciados'] ?? 0 }}</span>
                        <div class="paso-etiqueta">plan_creacion_iniciada</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="creacion-guardados">{{ $metricas['creacion']['guardados'] ?? $metricas['guardados'] ?? 0 }}</div>
                        <span id="contador-guardados" class="sr-only">{{ $metricas['creacion']['guardados'] ?? $metricas['guardados'] ?? 0 }}</span>
                        <div class="paso-etiqueta">plan_guardado</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="creacion-confirmados">{{ $metricas['creacion']['confirmados'] ?? $metricas['confirmados'] ?? 0 }}</div>
                        <span id="contador-confirmados" class="sr-only">{{ $metricas['creacion']['confirmados'] ?? $metricas['confirmados'] ?? 0 }}</span>
                        <div class="paso-etiqueta">plan_confirmado</div>
                    </div>
                </div>

                {{-- Barra de Progreso Brand-Bar oficial --}}
                <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['creacion']['tasa_conversion'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="brand-bar" style="width: {{ min(100, $metricas['creacion']['tasa_conversion'] ?? 0) }}%;"></div>
                </div>
            </div>

            {{-- Embudo 2: Deliberación y Votación --}}
            <div class="tarjeta-embudo">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                    <div>
                        <span class="chip-embudo-num">Embudo 2</span>
                        <h3 class="embudo-titulo">Deliberación y Votación</h3>
                    </div>
                    <div class="caja-porcentaje-terminan">
                        <div class="badge-conversion" id="conversion-votacion">{{ $metricas['votacion']['tasa_conversion'] ?? 0 }}%</div>
                        <div class="terminan-label">terminan</div>
                    </div>
                </div>

                <div class="embudo-pasos-horizontal">
                    <div class="paso-columna">
                        <div class="paso-valor" id="votacion-iniciadas">{{ $metricas['votacion']['iniciadas'] ?? 0 }}</div>
                        <div class="paso-etiqueta">sala_abierta</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="votacion-propuestas">{{ $metricas['votacion']['propuestas'] ?? 0 }}</div>
                        <div class="paso-etiqueta">opcion_propuesta</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="votacion-votos">{{ $metricas['votacion']['votos'] ?? 0 }}</div>
                        <div class="paso-etiqueta">voto_emitido</div>
                    </div>
                </div>

                <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['votacion']['tasa_conversion'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="brand-bar" style="width: {{ min(100, $metricas['votacion']['tasa_conversion'] ?? 0) }}%;"></div>
                </div>
            </div>

            {{-- Embudo 3: Invitaciones y Convocatoria --}}
            <div class="tarjeta-embudo">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                    <div>
                        <span class="chip-embudo-num">Embudo 3</span>
                        <h3 class="embudo-titulo">Invitaciones y Convocatoria</h3>
                    </div>
                    <div class="caja-porcentaje-terminan">
                        <div class="badge-conversion" id="conversion-invitaciones">{{ $metricas['invitaciones']['tasa_conversion'] ?? 0 }}%</div>
                        <div class="terminan-label">terminan</div>
                    </div>
                </div>

                <div class="embudo-pasos-horizontal">
                    <div class="paso-columna">
                        <div class="paso-valor" id="invitaciones-enviadas">{{ $metricas['invitaciones']['enviadas'] ?? 0 }}</div>
                        <div class="paso-etiqueta">invitacion_enviada</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="invitaciones-aceptadas">{{ $metricas['invitaciones']['aceptadas'] ?? 0 }}</div>
                        <div class="paso-etiqueta">invitacion_aceptada</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="invitaciones-rechazadas">{{ $metricas['invitaciones']['rechazadas'] ?? 0 }}</div>
                        <div class="paso-etiqueta">invitacion_rechazada</div>
                    </div>
                </div>

                <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['invitaciones']['tasa_conversion'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="brand-bar" style="width: {{ min(100, $metricas['invitaciones']['tasa_conversion'] ?? 0) }}%;"></div>
                </div>
            </div>

            {{-- Embudo 4: Gastos y Liquidación --}}
            <div class="tarjeta-embudo">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                    <div>
                        <span class="chip-embudo-num">Embudo 4</span>
                        <h3 class="embudo-titulo">Gastos y Liquidación</h3>
                    </div>
                    <div class="caja-porcentaje-terminan">
                        <div class="badge-conversion" id="conversion-gastos">{{ $metricas['gastos']['tasa_conversion'] ?? 0 }}%</div>
                        <div class="terminan-label">terminan</div>
                    </div>
                </div>

                <div class="embudo-pasos-horizontal">
                    <div class="paso-columna">
                        <div class="paso-valor" id="gastos-registrados">{{ $metricas['gastos']['gastos_registrados'] ?? 0 }}</div>
                        <div class="paso-etiqueta">gasto_registrado</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="pagos-iniciados">{{ $metricas['gastos']['pagos_iniciados'] ?? 0 }}</div>
                        <div class="paso-etiqueta">pago_iniciado</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="pagos-confirmados">{{ $metricas['gastos']['pagos_confirmados'] ?? 0 }}</div>
                        <div class="paso-etiqueta">pago_confirmado</div>
                    </div>
                </div>

                <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['gastos']['tasa_conversion'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="brand-bar" style="width: {{ min(100, $metricas['gastos']['tasa_conversion'] ?? 0) }}%;"></div>
                </div>
            </div>

            {{-- Embudo 5: Retiro de Participantes --}}
            <div class="tarjeta-embudo">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                    <div>
                        <span class="chip-embudo-num">Embudo 5</span>
                        <h3 class="embudo-titulo">Retiro de Participantes</h3>
                    </div>
                    <div class="caja-porcentaje-terminan">
                        <div class="badge-conversion" id="conversion-retiro">{{ $metricas['retiro']['tasa_conversion'] ?? 0 }}%</div>
                        <div class="terminan-label">terminan</div>
                    </div>
                </div>

                <div class="embudo-pasos-horizontal">
                    <div class="paso-columna">
                        <div class="paso-valor" id="retiro-iniciados">{{ $metricas['retiro']['iniciados'] ?? 0 }}</div>
                        <div class="paso-etiqueta">retiro_iniciado</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="retiro-confirmados">{{ $metricas['retiro']['confirmados'] ?? 0 }}</div>
                        <div class="paso-etiqueta">retiro_confirmado</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="retiro-bloqueados" style="color: #c0392b;">{{ $metricas['retiro']['bloqueados_deuda'] ?? 0 }}</div>
                        <div class="paso-etiqueta" style="color: #c0392b;">bloqueo_deuda</div>
                    </div>
                </div>

                <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['retiro']['tasa_conversion'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="brand-bar" style="width: {{ min(100, $metricas['retiro']['tasa_conversion'] ?? 0) }}%;"></div>
                </div>
            </div>

            {{-- Embudo 6: Búsquedas y Filtros --}}
            <div class="tarjeta-embudo">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                    <div>
                        <span class="chip-embudo-num">Embudo 6</span>
                        <h3 class="embudo-titulo">Búsquedas y Filtros</h3>
                    </div>
                    <div class="caja-porcentaje-terminan">
                        <div class="badge-conversion" id="conversion-filtros">{{ $metricas['filtros']['tasa_efectividad'] ?? 0 }}%</div>
                        <div class="terminan-label">terminan</div>
                    </div>
                </div>

                <div class="embudo-pasos-horizontal">
                    <div class="paso-columna">
                        <div class="paso-valor" id="filtros-busquedas">{{ $metricas['filtros']['busquedas'] ?? 0 }}</div>
                        <div class="paso-etiqueta">busqueda_realizada</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="filtros-aplicados">{{ $metricas['filtros']['filtros_aplicados'] ?? 0 }}</div>
                        <div class="paso-etiqueta">filtro_aplicado</div>
                    </div>
                    <div class="paso-columna">
                        <div class="paso-valor" id="filtros-sin-resultados" style="color: #8a6100;">{{ $metricas['filtros']['sin_resultados'] ?? 0 }}</div>
                        <div class="paso-etiqueta" style="color: #8a6100;">sin_resultados</div>
                    </div>
                </div>

                <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['filtros']['tasa_efectividad'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="brand-bar" style="width: {{ min(100, $metricas['filtros']['tasa_efectividad'] ?? 0) }}%;"></div>
                </div>
            </div>
        </div>
    </section>

    {{-- Errores de Interacción y Puntos de Freno Frecuentes --}}
    @if (!empty($metricas['errores_frecuentes']))
        <section aria-labelledby="titulo-errores" style="margin-bottom: 32px;">
            <h2 id="titulo-errores" style="font-size: 18px; font-weight: 700; margin-bottom: 12px; color: #1c1c1a;">
                Puntos de Freno y Errores de Interacción Recientes
            </h2>
            <div style="background: #fbeceb; border: 1px solid #f5c6cb; border-radius: 12px; padding: 16px 20px;">
                <p style="margin: 0 0 10px 0; color: #c0392b; font-size: 14px; font-weight: 600;">
                    Fricciones detectadas por el sistema con lista fija de motivos:
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    @foreach ($metricas['errores_frecuentes'] as $motivo => $total)
                        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: #ffffff; border: 1px solid #f5c6cb; border-radius: 9999px; font-size: 13px; color: #c0392b; font-weight: 600;">
                            <code>{{ $motivo }}</code>: <strong>{{ $total }}</strong>
                        </span>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Listado de Últimos Eventos Registrados --}}
    <section aria-labelledby="titulo-ultimos-eventos" style="margin-bottom: 36px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h2 id="titulo-ultimos-eventos" style="font-size: 18px; font-weight: 700; margin: 0; color: #1c1c1a;">
                Últimos 25 Eventos Registrados
            </h2>
            <span style="font-size: 13px; color: #6f6f6a;">
                Hora oficial de Bolivia (UTC-4)
            </span>
        </div>

        @if ($ultimosEventos->isEmpty())
            <div style="background: #ffffff; border: 1px dashed #e3e3df; border-radius: 12px; padding: 36px 20px; text-align: center; color: #6f6f6a;">
                <p style="font-size: 15px; margin: 0 0 6px 0; font-weight: 600;">No hay eventos registrados todavía.</p>
                <p style="font-size: 13px; margin: 0;">Interactúa con los flujos de creación, votación o gastos para observar la telemetría.</p>
            </div>
        @else
            <div style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 12px; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%;">
                <table style="width: 100%; table-layout: fixed; border-collapse: collapse; text-align: left; font-size: 14px;">
                    <colgroup>
                        <col style="width: 170px;">
                        <col style="width: 210px;">
                        <col style="width: auto;">
                    </colgroup>
                    <thead style="background: #fbfbf9; border-bottom: 1px solid #e3e3df;">
                        <tr>
                            <th style="padding: 12px 16px; font-weight: 700; color: #1c1c1a; width: 170px;">Hora</th>
                            <th style="padding: 12px 16px; font-weight: 700; color: #1c1c1a; width: 210px;">Evento</th>
                            <th style="padding: 12px 16px; font-weight: 700; color: #1c1c1a;">Propiedades de la Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ultimosEventos as $ev)
                            <tr style="border-bottom: 1px solid #f0f0ed;">
                                <td style="padding: 12px 16px; font-family: monospace; font-size: 13px; color: #6f6f6a; white-space: nowrap; vertical-align: top;">
                                    {{ $ev->fecha_bolivia }}
                                </td>
                                <td style="padding: 12px 16px; white-space: nowrap; vertical-align: top;">
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background: #fff4d6; color: #8a6100;">
                                        {{ $ev->evento }}
                                    </span>
                                </td>
                                <td style="padding: 12px 16px; font-family: monospace; font-size: 12px; color: #1c1c1a; word-break: break-all; overflow-wrap: anywhere; white-space: normal; line-height: 1.45; vertical-align: top;">
                                    @if (!empty($ev->props))
                                        <code style="word-break: break-all; overflow-wrap: anywhere; white-space: normal; font-family: inherit; font-size: inherit; color: inherit;">{{ json_encode($ev->props, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code>
                                    @else
                                        <span style="color: #6f6f6a;">(sin propiedades adicionales)</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <style>
        .tarjeta-embudo {
            background: #ffffff;
            border: 1px solid #e3e3df;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 16px;
        }
        .chip-embudo-num {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            color: #8a6100;
            background: #fff4d6;
            padding: 2px 8px;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .embudo-titulo {
            font-size: 17px;
            font-weight: 700;
            margin: 4px 0 0 0;
            color: #1c1c1a;
        }
        .caja-porcentaje-terminan {
            background: #edf7ef;
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 8px 14px;
            text-align: center;
            min-width: 80px;
            flex-shrink: 0;
        }
        .badge-conversion {
            font-size: 22px;
            font-weight: 800;
            color: #25663a;
            line-height: 1;
        }
        .terminan-label {
            font-size: 11px;
            font-weight: 700;
            color: #25663a;
            margin-top: 3px;
            text-transform: lowercase;
        }
        .embudo-pasos-horizontal {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .paso-columna {
            min-width: 70px;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .paso-valor {
            font-size: 26px;
            font-weight: 800;
            color: #1c1c1a;
            line-height: 1;
            text-align: center;
        }
        .paso-etiqueta {
            font-size: 11px;
            color: #6f6f6a;
            font-weight: 600;
            margin-top: 5px;
            font-family: monospace;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
        }
        .barra-contenedor {
            width: 100%;
            height: 8px;
            background: #f6f6f4;
            border: 1px solid #e3e3df;
            border-radius: 9999px;
            overflow: hidden;
        }
        .brand-bar {
            height: 100%;
            background: #ffe683;
            border-radius: 9999px;
            transition: width 0.3s ease;
        }
    </style>
@endsection

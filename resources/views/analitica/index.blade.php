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
                Solo Desarrollo · Hora Boliviana (UTC-4)
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

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
            {{-- Embudo 1: Creación y Confirmación de Planes --}}
            <div class="tarjeta-embudo">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <span style="font-size: 12px; font-weight: 700; color: #6f6f6a; text-transform: uppercase; letter-spacing: 0.5px;">Embudo 1</span>
                        <h3 style="font-size: 17px; font-weight: 700; margin: 2px 0 0 0; color: #1c1c1a;">Creación y Confirmación</h3>
                    </div>
                    <span class="badge-conversion" id="conversion-creacion">{{ $metricas['creacion']['tasa_conversion'] ?? $metricas['tasa_conversion'] ?? 0 }}% conv.</span>
                </div>

                <div class="embudo-pasos">
                    <div class="embudo-fila">
                        <span class="paso-etiqueta">1. Inicios:</span>
                        <strong class="paso-valor" id="creacion-iniciados">{{ $metricas['creacion']['iniciados'] ?? $metricas['iniciados'] ?? 0 }}</strong>
                    </div>
                    <div class="embudo-fila">
                        <span class="paso-etiqueta">2. Guardados:</span>
                        <strong class="paso-valor" id="creacion-guardados">{{ $metricas['creacion']['guardados'] ?? $metricas['guardados'] ?? 0 }}</strong>
                        <span class="paso-dropoff">(Drop-off: {{ $metricas['creacion']['drop_off_guardado'] ?? 0 }}%)</span>
                    </div>
                    <div class="embudo-fila">
                        <span class="paso-etiqueta">3. Confirmados:</span>
                        <strong class="paso-valor" id="creacion-confirmados">{{ $metricas['creacion']['confirmados'] ?? $metricas['confirmados'] ?? 0 }}</strong>
                        <span class="paso-dropoff">(Drop-off: {{ $metricas['creacion']['drop_off_confirmacion'] ?? 0 }}%)</span>
                    </div>
                </div>

                {{-- Barra de Progreso Brand-Bar --}}
                <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['creacion']['tasa_conversion'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="brand-bar" style="width: {{ min(100, $metricas['creacion']['tasa_conversion'] ?? 0) }}%;"></div>
                </div>
            </div>

            {{-- Embudo 2: Deliberación y Votación --}}
            <div class="tarjeta-embudo">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <span style="font-size: 12px; font-weight: 700; color: #6f6f6a; text-transform: uppercase; letter-spacing: 0.5px;">Embudo 2</span>
                        <h3 style="font-size: 17px; font-weight: 700; margin: 2px 0 0 0; color: #1c1c1a;">Deliberación y Votación</h3>
                    </div>
                    <span class="badge-conversion" id="conversion-votacion">{{ $metricas['votacion']['tasa_conversion'] ?? 0 }}% conv.</span>
                </div>

                <div class="embudo-pasos">
                    <div class="embudo-fila">
                        <span class="paso-etiqueta">1. Salas iniciadas:</span>
                        <strong class="paso-valor" id="votacion-iniciadas">{{ $metricas['votacion']['iniciadas'] ?? 0 }}</strong>
                    </div>
                    <div class="embudo-fila">
                        <span class="paso-etiqueta">2. Propuestas:</span>
                        <strong class="paso-valor" id="votacion-propuestas">{{ $metricas['votacion']['propuestas'] ?? 0 }}</strong>
                        <span class="paso-dropoff">(Drop-off: {{ $metricas['votacion']['drop_off_propuestas'] ?? 0 }}%)</span>
                    </div>
                    <div class="embudo-fila">
                        <span class="paso-etiqueta">3. Votos emitidos:</span>
                        <strong class="paso-valor" id="votacion-votos">{{ $metricas['votacion']['votos'] ?? 0 }}</strong>
                        <span class="paso-dropoff">(Drop-off: {{ $metricas['votacion']['drop_off_votos'] ?? 0 }}%)</span>
                    </div>
                </div>

                <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['votacion']['tasa_conversion'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="brand-bar" style="width: {{ min(100, $metricas['votacion']['tasa_conversion'] ?? 0) }}%;"></div>
                </div>
            </div>

            {{-- Embudo 3: Invitaciones a Planes --}}
            <div class="tarjeta-embudo">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <span style="font-size: 12px; font-weight: 700; color: #6f6f6a; text-transform: uppercase; letter-spacing: 0.5px;">Embudo 3</span>
                        <h3 style="font-size: 17px; font-weight: 700; margin: 2px 0 0 0; color: #1c1c1a;">Invitaciones y Convocatoria</h3>
                    </div>
                    <span class="badge-conversion" id="conversion-invitaciones">{{ $metricas['invitaciones']['tasa_conversion'] ?? 0 }}% conv.</span>
                </div>

                <div class="embudo-pasos">
                    <div class="embudo-fila">
                        <span class="paso-etiqueta">1. Enviadas:</span>
                        <strong class="paso-valor" id="invitaciones-enviadas">{{ $metricas['invitaciones']['enviadas'] ?? 0 }}</strong>
                    </div>
                    <div class="embudo-fila">
                        <span class="paso-etiqueta">2. Aceptadas:</span>
                        <strong class="paso-valor" id="invitaciones-aceptadas">{{ $metricas['invitaciones']['aceptadas'] ?? 0 }}</strong>
                        <span class="paso-dropoff">(Drop-off: {{ $metricas['invitaciones']['drop_off_aceptacion'] ?? 0 }}%)</span>
                    </div>
                    <div class="embudo-fila">
                        <span class="paso-etiqueta">3. Rechazadas:</span>
                        <strong class="paso-valor" id="invitaciones-rechazadas">{{ $metricas['invitaciones']['rechazadas'] ?? 0 }}</strong>
                    </div>
                </div>

                <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['invitaciones']['tasa_conversion'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="brand-bar" style="width: {{ min(100, $metricas['invitaciones']['tasa_conversion'] ?? 0) }}%;"></div>
                </div>
            </div>

            {{-- Embudo 4: Gastos y Conciliación de Pagos --}}
            <div class="tarjeta-embudo">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <span style="font-size: 12px; font-weight: 700; color: #6f6f6a; text-transform: uppercase; letter-spacing: 0.5px;">Embudo 4</span>
                        <h3 style="font-size: 17px; font-weight: 700; margin: 2px 0 0 0; color: #1c1c1a;">Gastos y Liquidación</h3>
                    </div>
                    <span class="badge-conversion" id="conversion-gastos">{{ $metricas['gastos']['tasa_conversion'] ?? 0 }}% conv.</span>
                </div>

                <div class="embudo-pasos">
                    <div class="embudo-fila">
                        <span class="paso-etiqueta">1. Gastos registrados:</span>
                        <strong class="paso-valor" id="gastos-registrados">{{ $metricas['gastos']['gastos_registrados'] ?? 0 }}</strong>
                    </div>
                    <div class="embudo-fila">
                        <span class="paso-etiqueta">2. Pagos iniciados:</span>
                        <strong class="paso-valor" id="pagos-iniciados">{{ $metricas['gastos']['pagos_iniciados'] ?? 0 }}</strong>
                    </div>
                    <div class="embudo-fila">
                        <span class="paso-etiqueta">3. Pagos confirmados:</span>
                        <strong class="paso-valor" id="pagos-confirmados">{{ $metricas['gastos']['pagos_confirmados'] ?? 0 }}</strong>
                        <span class="paso-dropoff">(Drop-off: {{ $metricas['gastos']['drop_off_pagos'] ?? 0 }}%)</span>
                    </div>
                </div>

                <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['gastos']['tasa_conversion'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="brand-bar" style="width: {{ min(100, $metricas['gastos']['tasa_conversion'] ?? 0) }}%;"></div>
                </div>
            </div>

            {{-- Embudo 5: Retiro Voluntario y Bloqueos por Deuda --}}
            <div class="tarjeta-embudo">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <span style="font-size: 12px; font-weight: 700; color: #6f6f6a; text-transform: uppercase; letter-spacing: 0.5px;">Embudo 5</span>
                        <h3 style="font-size: 17px; font-weight: 700; margin: 2px 0 0 0; color: #1c1c1a;">Retiro de Participantes</h3>
                    </div>
                    <span class="badge-conversion" id="conversion-retiro">{{ $metricas['retiro']['tasa_conversion'] ?? 0 }}% conv.</span>
                </div>

                <div class="embudo-pasos">
                    <div class="embudo-fila">
                        <span class="paso-etiqueta">1. Retiros iniciados:</span>
                        <strong class="paso-valor" id="retiro-iniciados">{{ $metricas['retiro']['iniciados'] ?? 0 }}</strong>
                    </div>
                    <div class="embudo-fila">
                        <span class="paso-etiqueta">2. Retiros confirmados:</span>
                        <strong class="paso-valor" id="retiro-confirmados">{{ $metricas['retiro']['confirmados'] ?? 0 }}</strong>
                    </div>
                    <div class="embudo-fila">
                        <span class="paso-etiqueta" style="color: #c0392b;">Bloqueados por deuda:</span>
                        <strong class="paso-valor" style="color: #c0392b;" id="retiro-bloqueados">{{ $metricas['retiro']['bloqueados_deuda'] ?? 0 }}</strong>
                    </div>
                </div>

                <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['retiro']['tasa_conversion'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="brand-bar" style="width: {{ min(100, $metricas['retiro']['tasa_conversion'] ?? 0) }}%;"></div>
                </div>
            </div>

            {{-- Embudo 6: Búsquedas y Filtros en Catálogo --}}
            <div class="tarjeta-embudo">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <span style="font-size: 12px; font-weight: 700; color: #6f6f6a; text-transform: uppercase; letter-spacing: 0.5px;">Embudo 6</span>
                        <h3 style="font-size: 17px; font-weight: 700; margin: 2px 0 0 0; color: #1c1c1a;">Búsquedas y Filtros</h3>
                    </div>
                    <span class="badge-conversion" id="conversion-filtros">{{ $metricas['filtros']['tasa_efectividad'] ?? 0 }}% éxito</span>
                </div>

                <div class="embudo-pasos">
                    <div class="embudo-fila">
                        <span class="paso-etiqueta">1. Búsquedas realizadas:</span>
                        <strong class="paso-valor" id="filtros-busquedas">{{ $metricas['filtros']['busquedas'] ?? 0 }}</strong>
                    </div>
                    <div class="embudo-fila">
                        <span class="paso-etiqueta">2. Filtros aplicados:</span>
                        <strong class="paso-valor" id="filtros-aplicados">{{ $metricas['filtros']['filtros_aplicados'] ?? 0 }}</strong>
                    </div>
                    <div class="embudo-fila">
                        <span class="paso-etiqueta" style="color: #854d0e;">Sin resultados:</span>
                        <strong class="paso-valor" style="color: #854d0e;" id="filtros-sin-resultados">{{ $metricas['filtros']['sin_resultados'] ?? 0 }}</strong>
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
    <section aria-labelledby="titulo-ultimos-eventos">
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
            <div style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                    <thead style="background: #fbfbf9; border-bottom: 1px solid #e3e3df;">
                        <tr>
                            <th style="padding: 12px 16px; font-weight: 700; color: #1c1c1a;">Hora (Bolivia)</th>
                            <th style="padding: 12px 16px; font-weight: 700; color: #1c1c1a;">Evento</th>
                            <th style="padding: 12px 16px; font-weight: 700; color: #1c1c1a;">Propiedades de la Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ultimosEventos as $ev)
                            <tr style="border-bottom: 1px solid #f0f0ed;">
                                <td style="padding: 12px 16px; font-family: monospace; font-size: 13px; color: #6f6f6a; white-space: nowrap;">
                                    {{ $ev->fecha_bolivia }}
                                </td>
                                <td style="padding: 12px 16px; white-space: nowrap;">
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background: #fff4d6; color: #8a6100;">
                                        {{ $ev->evento }}
                                    </span>
                                </td>
                                <td style="padding: 12px 16px; font-family: monospace; font-size: 12px; color: #1c1c1a;">
                                    @if (!empty($ev->props))
                                        <code>{{ json_encode($ev->props, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code>
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
        }
        .badge-conversion {
            display: inline-block;
            padding: 4px 10px;
            background: #edf7ef;
            color: #25663a;
            border: 1px solid #bbf7d0;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
        }
        .embudo-pasos {
            margin: 14px 0 16px 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .embudo-fila {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 14px;
        }
        .paso-etiqueta {
            color: #6f6f6a;
        }
        .paso-valor {
            color: #1c1c1a;
            font-weight: 700;
        }
        .paso-dropoff {
            font-size: 12px;
            color: #c0392b;
            font-weight: 600;
            margin-left: 6px;
        }
        .barra-contenedor {
            width: 100%;
            height: 10px;
            background: #f6f6f4;
            border: 1px solid #e3e3df;
            border-radius: 9999px;
            overflow: hidden;
            margin-top: 8px;
        }
        .brand-bar {
            height: 100%;
            background: #ffe683;
            border-radius: 9999px;
            transition: width 0.3s ease;
        }
    </style>
@endsection

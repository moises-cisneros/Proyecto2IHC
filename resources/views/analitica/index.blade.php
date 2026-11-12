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

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px;">
            {{-- Embudo 1: Creación y Confirmación de Planes --}}
            <div class="tarjeta-embudo">
                <div class="tarjeta-embudo-header">
                    <div class="header-terminal-tag">
                        <span class="punto-activo"></span>
                        <code>/analitica · Creación y Confirmación (solo desarrollo)</code>
                    </div>
                    <span class="badge-embudo-num">EMBUDO 1</span>
                </div>

                <div class="tarjeta-embudo-cuerpo">
                    <h3 class="embudo-titulo">Creación y Confirmación</h3>

                    <div class="embudo-metricas-fila">
                        <div class="pasos-horizontales">
                            <div class="paso-bloque">
                                <div class="paso-valor" id="creacion-iniciados">{{ $metricas['creacion']['iniciados'] ?? $metricas['iniciados'] ?? 0 }}</div>
                                <span id="contador-iniciados" class="sr-only">{{ $metricas['creacion']['iniciados'] ?? $metricas['iniciados'] ?? 0 }}</span>
                                <div class="paso-etiqueta">plan_creacion_iniciada</div>
                            </div>
                            <div class="paso-bloque">
                                <div class="paso-valor" id="creacion-guardados">{{ $metricas['creacion']['guardados'] ?? $metricas['guardados'] ?? 0 }}</div>
                                <span id="contador-guardados" class="sr-only">{{ $metricas['creacion']['guardados'] ?? $metricas['guardados'] ?? 0 }}</span>
                                <div class="paso-etiqueta">plan_guardado</div>
                            </div>
                            <div class="paso-bloque">
                                <div class="paso-valor" id="creacion-confirmados">{{ $metricas['creacion']['confirmados'] ?? $metricas['confirmados'] ?? 0 }}</div>
                                <span id="contador-confirmados" class="sr-only">{{ $metricas['creacion']['confirmados'] ?? $metricas['confirmados'] ?? 0 }}</span>
                                <div class="paso-etiqueta">plan_confirmado</div>
                            </div>
                        </div>

                        {{-- Caja destacada de porcentaje que termina --}}
                        <div class="caja-porcentaje-terminan">
                            <div class="badge-conversion" id="conversion-creacion">{{ $metricas['creacion']['tasa_conversion'] ?? $metricas['tasa_conversion'] ?? 0 }}%</div>
                            <div class="terminan-label">terminan</div>
                        </div>
                    </div>

                    {{-- Barra de Progreso Brand-Bar --}}
                    <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['creacion']['tasa_conversion'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="brand-bar" style="width: {{ min(100, $metricas['creacion']['tasa_conversion'] ?? 0) }}%;"></div>
                    </div>
                </div>
            </div>

            {{-- Embudo 2: Deliberación y Votación --}}
            <div class="tarjeta-embudo">
                <div class="tarjeta-embudo-header">
                    <div class="header-terminal-tag">
                        <span class="punto-activo"></span>
                        <code>/analitica · Deliberación y Votación (solo desarrollo)</code>
                    </div>
                    <span class="badge-embudo-num">EMBUDO 2</span>
                </div>

                <div class="tarjeta-embudo-cuerpo">
                    <h3 class="embudo-titulo">Deliberación y Votación</h3>

                    <div class="embudo-metricas-fila">
                        <div class="pasos-horizontales">
                            <div class="paso-bloque">
                                <div class="paso-valor" id="votacion-iniciadas">{{ $metricas['votacion']['iniciadas'] ?? 0 }}</div>
                                <div class="paso-etiqueta">sala_abierta</div>
                            </div>
                            <div class="paso-bloque">
                                <div class="paso-valor" id="votacion-propuestas">{{ $metricas['votacion']['propuestas'] ?? 0 }}</div>
                                <div class="paso-etiqueta">opcion_propuesta</div>
                            </div>
                            <div class="paso-bloque">
                                <div class="paso-valor" id="votacion-votos">{{ $metricas['votacion']['votos'] ?? 0 }}</div>
                                <div class="paso-etiqueta">voto_emitido</div>
                            </div>
                        </div>

                        <div class="caja-porcentaje-terminan">
                            <div class="badge-conversion" id="conversion-votacion">{{ $metricas['votacion']['tasa_conversion'] ?? 0 }}%</div>
                            <div class="terminan-label">terminan</div>
                        </div>
                    </div>

                    <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['votacion']['tasa_conversion'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="brand-bar" style="width: {{ min(100, $metricas['votacion']['tasa_conversion'] ?? 0) }}%;"></div>
                    </div>
                </div>
            </div>

            {{-- Embudo 3: Invitaciones y Convocatoria --}}
            <div class="tarjeta-embudo">
                <div class="tarjeta-embudo-header">
                    <div class="header-terminal-tag">
                        <span class="punto-activo"></span>
                        <code>/analitica · Invitaciones y Convocatoria (solo desarrollo)</code>
                    </div>
                    <span class="badge-embudo-num">EMBUDO 3</span>
                </div>

                <div class="tarjeta-embudo-cuerpo">
                    <h3 class="embudo-titulo">Invitaciones y Convocatoria</h3>

                    <div class="embudo-metricas-fila">
                        <div class="pasos-horizontales">
                            <div class="paso-bloque">
                                <div class="paso-valor" id="invitaciones-enviadas">{{ $metricas['invitaciones']['enviadas'] ?? 0 }}</div>
                                <div class="paso-etiqueta">invitacion_enviada</div>
                            </div>
                            <div class="paso-bloque">
                                <div class="paso-valor" id="invitaciones-aceptadas">{{ $metricas['invitaciones']['aceptadas'] ?? 0 }}</div>
                                <div class="paso-etiqueta">invitacion_aceptada</div>
                            </div>
                            <div class="paso-bloque">
                                <div class="paso-valor" id="invitaciones-rechazadas">{{ $metricas['invitaciones']['rechazadas'] ?? 0 }}</div>
                                <div class="paso-etiqueta">invitacion_rechazada</div>
                            </div>
                        </div>

                        <div class="caja-porcentaje-terminan">
                            <div class="badge-conversion" id="conversion-invitaciones">{{ $metricas['invitaciones']['tasa_conversion'] ?? 0 }}%</div>
                            <div class="terminan-label">terminan</div>
                        </div>
                    </div>

                    <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['invitaciones']['tasa_conversion'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="brand-bar" style="width: {{ min(100, $metricas['invitaciones']['tasa_conversion'] ?? 0) }}%;"></div>
                    </div>
                </div>
            </div>

            {{-- Embudo 4: Gastos y Liquidación --}}
            <div class="tarjeta-embudo">
                <div class="tarjeta-embudo-header">
                    <div class="header-terminal-tag">
                        <span class="punto-activo"></span>
                        <code>/analitica · Gastos y Liquidación (solo desarrollo)</code>
                    </div>
                    <span class="badge-embudo-num">EMBUDO 4</span>
                </div>

                <div class="tarjeta-embudo-cuerpo">
                    <h3 class="embudo-titulo">Gastos y Liquidación</h3>

                    <div class="embudo-metricas-fila">
                        <div class="pasos-horizontales">
                            <div class="paso-bloque">
                                <div class="paso-valor" id="gastos-registrados">{{ $metricas['gastos']['gastos_registrados'] ?? 0 }}</div>
                                <div class="paso-etiqueta">gasto_registrado</div>
                            </div>
                            <div class="paso-bloque">
                                <div class="paso-valor" id="pagos-iniciados">{{ $metricas['gastos']['pagos_iniciados'] ?? 0 }}</div>
                                <div class="paso-etiqueta">pago_iniciado</div>
                            </div>
                            <div class="paso-bloque">
                                <div class="paso-valor" id="pagos-confirmados">{{ $metricas['gastos']['pagos_confirmados'] ?? 0 }}</div>
                                <div class="paso-etiqueta">pago_confirmado</div>
                            </div>
                        </div>

                        <div class="caja-porcentaje-terminan">
                            <div class="badge-conversion" id="conversion-gastos">{{ $metricas['gastos']['tasa_conversion'] ?? 0 }}%</div>
                            <div class="terminan-label">terminan</div>
                        </div>
                    </div>

                    <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['gastos']['tasa_conversion'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="brand-bar" style="width: {{ min(100, $metricas['gastos']['tasa_conversion'] ?? 0) }}%;"></div>
                    </div>
                </div>
            </div>

            {{-- Embudo 5: Retiro de Participantes --}}
            <div class="tarjeta-embudo">
                <div class="tarjeta-embudo-header">
                    <div class="header-terminal-tag">
                        <span class="punto-activo"></span>
                        <code>/analitica · Retiro de Participantes (solo desarrollo)</code>
                    </div>
                    <span class="badge-embudo-num">EMBUDO 5</span>
                </div>

                <div class="tarjeta-embudo-cuerpo">
                    <h3 class="embudo-titulo">Retiro de Participantes</h3>

                    <div class="embudo-metricas-fila">
                        <div class="pasos-horizontales">
                            <div class="paso-bloque">
                                <div class="paso-valor" id="retiro-iniciados">{{ $metricas['retiro']['iniciados'] ?? 0 }}</div>
                                <div class="paso-etiqueta">retiro_iniciado</div>
                            </div>
                            <div class="paso-bloque">
                                <div class="paso-valor" id="retiro-confirmados">{{ $metricas['retiro']['confirmados'] ?? 0 }}</div>
                                <div class="paso-etiqueta">retiro_confirmado</div>
                            </div>
                            <div class="paso-bloque">
                                <div class="paso-valor" id="retiro-bloqueados" style="color: #c0392b;">{{ $metricas['retiro']['bloqueados_deuda'] ?? 0 }}</div>
                                <div class="paso-etiqueta" style="color: #c0392b;">bloqueo_deuda</div>
                            </div>
                        </div>

                        <div class="caja-porcentaje-terminan">
                            <div class="badge-conversion" id="conversion-retiro">{{ $metricas['retiro']['tasa_conversion'] ?? 0 }}%</div>
                            <div class="terminan-label">terminan</div>
                        </div>
                    </div>

                    <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['retiro']['tasa_conversion'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="brand-bar" style="width: {{ min(100, $metricas['retiro']['tasa_conversion'] ?? 0) }}%;"></div>
                    </div>
                </div>
            </div>

            {{-- Embudo 6: Búsquedas y Filtros --}}
            <div class="tarjeta-embudo">
                <div class="tarjeta-embudo-header">
                    <div class="header-terminal-tag">
                        <span class="punto-activo"></span>
                        <code>/analitica · Búsquedas y Filtros (solo desarrollo)</code>
                    </div>
                    <span class="badge-embudo-num">EMBUDO 6</span>
                </div>

                <div class="tarjeta-embudo-cuerpo">
                    <h3 class="embudo-titulo">Búsquedas y Filtros</h3>

                    <div class="embudo-metricas-fila">
                        <div class="pasos-horizontales">
                            <div class="paso-bloque">
                                <div class="paso-valor" id="filtros-busquedas">{{ $metricas['filtros']['busquedas'] ?? 0 }}</div>
                                <div class="paso-etiqueta">busqueda_realizada</div>
                            </div>
                            <div class="paso-bloque">
                                <div class="paso-valor" id="filtros-aplicados">{{ $metricas['filtros']['filtros_aplicados'] ?? 0 }}</div>
                                <div class="paso-etiqueta">filtro_aplicado</div>
                            </div>
                            <div class="paso-bloque">
                                <div class="paso-valor" id="filtros-sin-resultados" style="color: #854d0e;">{{ $metricas['filtros']['sin_resultados'] ?? 0 }}</div>
                                <div class="paso-etiqueta" style="color: #854d0e;">sin_resultados</div>
                            </div>
                        </div>

                        <div class="caja-porcentaje-terminan">
                            <div class="badge-conversion" id="conversion-filtros">{{ $metricas['filtros']['tasa_efectividad'] ?? 0 }}%</div>
                            <div class="terminan-label">terminan</div>
                        </div>
                    </div>

                    <div class="barra-contenedor" role="progressbar" aria-valuenow="{{ $metricas['filtros']['tasa_efectividad'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="brand-bar" style="width: {{ min(100, $metricas['filtros']['tasa_efectividad'] ?? 0) }}%;"></div>
                    </div>
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

    {{-- Listado de Últimos Eventos Registrados estilo Clase 21 --}}
    <section aria-labelledby="titulo-ultimos-eventos" style="margin-bottom: 36px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
            <h2 id="titulo-ultimos-eventos" style="font-size: 20px; font-weight: 700; margin: 0; color: #1c1c1a;">
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
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach ($ultimosEventos as $ev)
                    <div style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 12px; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                        <div style="min-width: 0; flex: 1;">
                            <div style="font-weight: 700; font-size: 14px; color: #1c1c1a; font-family: monospace;">
                                {{ $ev->evento }}
                            </div>
                            <div style="font-size: 12px; color: #6f6f6a; margin-top: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                @if (!empty($ev->props))
                                    @php
                                        $propsList = [];
                                        foreach ($ev->props as $key => $val) {
                                            if (is_scalar($val)) {
                                                $propsList[] = "{$key}: {$val}";
                                            }
                                        }
                                        $propsText = implode(' · ', $propsList);
                                    @endphp
                                    @if ($propsText)
                                        <span>{{ $propsText }}</span> ·
                                    @endif
                                @endif
                                <span style="color: #8c8c87;">{{ $ev->fecha_bolivia }}</span>
                            </div>
                        </div>
                        <span style="font-size: 11px; padding: 3px 9px; border-radius: 9999px; background: #fff4d6; color: #8a6100; font-weight: 600; font-family: monospace; flex-shrink: 0;">
                            telemetría
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Reflexión de IHC y Decisiones de Diseño (Clase 21) --}}
    <section aria-labelledby="titulo-reflexion-diseno" style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 14px; padding: 22px 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <h2 id="titulo-reflexion-diseno" style="font-size: 18px; font-weight: 700; margin: 0 0 8px 0; color: #1c1c1a;">
            ¿Qué decisión de diseño cambiaría si supiera esto?
        </h2>
        <p style="margin: 0; font-size: 14px; color: #6f6f6a; line-height: 1.5;">
            La telemetría cuantitativa de los 6 macroembudos identifica cuellos de botella y abandonos en tiempo real: optimizar formularios extensos, asistir selecciones complejas con valores predeterminados (Ley de Hick) y brindar retroalimentación oportuna para prevenir errores antes del envío (Nielsen H5).
        </p>
    </section>

    <style>
        .tarjeta-embudo {
            background: #ffffff;
            border: 1px solid #e3e3df;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
        }
        .tarjeta-embudo-header {
            background: #1c1c1a;
            color: #ffffff;
            padding: 11px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #2e2e2c;
        }
        .header-terminal-tag {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-family: monospace;
            color: #f4f4f5;
        }
        .punto-activo {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #a3e635;
            display: inline-block;
        }
        .badge-embudo-num {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.12);
            color: #ffe683;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .tarjeta-embudo-cuerpo {
            padding: 20px 22px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            flex: 1;
            gap: 16px;
        }
        .embudo-titulo {
            font-size: 17px;
            font-weight: 700;
            margin: 0;
            color: #1c1c1a;
        }
        .embudo-metricas-fila {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 16px;
            flex-wrap: wrap;
        }
        .pasos-horizontales {
            display: flex;
            gap: 24px;
            align-items: flex-end;
            flex-wrap: wrap;
            flex: 1;
        }
        .paso-bloque {
            min-width: 65px;
        }
        .paso-valor {
            font-size: 28px;
            font-weight: 800;
            color: #1c1c1a;
            line-height: 1;
        }
        .paso-etiqueta {
            font-size: 11px;
            color: #6f6f6a;
            font-weight: 600;
            margin-top: 5px;
            font-family: monospace;
        }
        .caja-porcentaje-terminan {
            background: #ecfccb;
            border: 1.5px solid #bef264;
            border-radius: 12px;
            padding: 10px 18px;
            text-align: center;
            min-width: 90px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            flex-shrink: 0;
        }
        .badge-conversion {
            font-size: 26px;
            font-weight: 900;
            color: #365314;
            line-height: 1;
        }
        .terminan-label {
            font-size: 11px;
            font-weight: 700;
            color: #4d7c0f;
            margin-top: 3px;
            letter-spacing: 0.3px;
            text-transform: lowercase;
        }
        .barra-contenedor {
            width: 100%;
            height: 8px;
            background: #f4f4f2;
            border: 1px solid #e3e3df;
            border-radius: 9999px;
            overflow: hidden;
            margin-top: 4px;
        }
        .brand-bar {
            height: 100%;
            background: #ffe683;
            border-radius: 9999px;
            transition: width 0.3s ease;
        }
    </style>
@endsection

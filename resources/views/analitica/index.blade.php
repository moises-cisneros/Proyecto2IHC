@extends('layouts.app')

@section('titulo', 'Analítica de Interacción')

@section('contenido')
    <div class="analitica-cabecera" style="margin-bottom: 28px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1 style="font-size: 26px; font-weight: 800; margin: 0 0 6px 0; color: #1a1a19;">
                    Analítica de Interacción
                </h1>
                <p style="margin: 0; color: #6f6f6a; font-size: 15px;">
                    Telemetría del embudo del flujo principal: creación y confirmación de planes.
                </p>
            </div>
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; border-radius: 9999px; font-size: 13px; font-weight: 700;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;"></span>
                Solo Desarrollo · Hora Boliviana (UTC-4)
            </span>
        </div>
    </div>

    {{-- Tarjetas del Embudo (Funnel) --}}
    <section aria-labelledby="titulo-embudo" style="margin-bottom: 32px;">
        <h2 id="titulo-embudo" style="font-size: 18px; font-weight: 700; margin-bottom: 16px; color: #1a1a19;">
            Embudo del Flujo Principal
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            {{-- Paso 1: Inicio --}}
            <div class="tarjeta-metrica" style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 13px; font-weight: 600; color: #6f6f6a; text-transform: uppercase; letter-spacing: 0.5px;">
                    1. Inicio de Creación
                </div>
                <div style="font-size: 34px; font-weight: 800; color: #1a1a19; margin: 8px 0 4px 0;" id="contador-iniciados">
                    {{ $metricas['iniciados'] }}
                </div>
                <code style="font-size: 12px; color: #0284c7; background: #e0f2fe; padding: 2px 6px; border-radius: 4px;">
                    plan_creacion_iniciada
                </code>
            </div>

            {{-- Paso 2: Intermedio --}}
            <div class="tarjeta-metrica" style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 13px; font-weight: 600; color: #6f6f6a; text-transform: uppercase; letter-spacing: 0.5px;">
                    2. Plan Guardado
                </div>
                <div style="font-size: 34px; font-weight: 800; color: #1a1a19; margin: 8px 0 4px 0;" id="contador-guardados">
                    {{ $metricas['guardados'] }}
                </div>
                <code style="font-size: 12px; color: #854d0e; background: #fef9c3; padding: 2px 6px; border-radius: 4px;">
                    plan_guardado
                </code>
            </div>

            {{-- Paso 3: Éxito --}}
            <div class="tarjeta-metrica" style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 13px; font-weight: 600; color: #6f6f6a; text-transform: uppercase; letter-spacing: 0.5px;">
                    3. Plan Confirmado
                </div>
                <div style="font-size: 34px; font-weight: 800; color: #1a1a19; margin: 8px 0 4px 0;" id="contador-confirmados">
                    {{ $metricas['confirmados'] }}
                </div>
                <code style="font-size: 12px; color: #15803d; background: #dcfce7; padding: 2px 6px; border-radius: 4px;">
                    plan_confirmado
                </code>
            </div>

            {{-- Tasa de Éxito / Conversión --}}
            <div class="tarjeta-metrica" style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div style="font-size: 13px; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 0.5px;">
                    Tasa de Conversión
                </div>
                <div style="font-size: 34px; font-weight: 800; color: #065f46; margin: 8px 0 4px 0;" id="tasa-conversion">
                    {{ $metricas['tasa_conversion'] }}%
                </div>
                <div style="font-size: 13px; font-weight: 600; color: #059669;">
                    terminan el embudo
                </div>
            </div>
        </div>
    </section>

    {{-- Bloque Pedagógico: Decisión de Diseño (Clase 21) --}}
    <section aria-labelledby="titulo-decision" style="margin-bottom: 32px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 12px; padding: 20px;">
        <h2 id="titulo-decision" style="font-size: 17px; font-weight: 700; margin: 0 0 10px 0; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 16v-4"></path>
                <path d="M12 8h.01"></path>
            </svg>
            ¿Qué decisión de diseño cambiaría si supiera esto?
        </h2>
        <p style="margin: 0 0 10px 0; color: #334155; font-size: 14px; line-height: 1.6;">
            Si la analítica de interacción muestra que, por ejemplo, el <strong>{{ 100 - $metricas['tasa_conversion'] }}% de los usuarios</strong> inicia la creación de un plan pero no llega a confirmarlo, significa que la fricción ocurre en la fase intermedia de coordinación o votación previa a la resolución definitiva.
        </p>
        <p style="margin: 0; color: #334155; font-size: 14px; line-height: 1.6;">
            <strong>Decisión ergonómica (IHC):</strong> En lugar de esperar pasivamente a que los participantes voten antes de la fecha límite, la interfaz debe:
            (1) Notificar proactivamente el plazo de cierre al organizador antes de que expire,
            (2) Precargar opciones populares para acortar el tiempo de decisión (Ley de Hick), y
            (3) Destacar visualmente el botón de confirmación en la sala una vez alcanzada la mayoría simple.
        </p>
    </section>

    {{-- Listado de Últimos Eventos --}}
    <section aria-labelledby="titulo-ultimos-eventos">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h2 id="titulo-ultimos-eventos" style="font-size: 18px; font-weight: 700; margin: 0; color: #1a1a19;">
                Últimos Eventos Registrados
            </h2>
            <span style="font-size: 13px; color: #6f6f6a;">
                Total histórico: {{ $metricas['total_eventos'] }} eventos
            </span>
        </div>

        @if ($ultimosEventos->isEmpty())
            <div style="background: #ffffff; border: 1px dashed #d1d1cd; border-radius: 12px; padding: 36px 20px; text-align: center; color: #6f6f6a;">
                <p style="font-size: 15px; margin: 0 0 6px 0; font-weight: 600;">No hay eventos registrados todavía.</p>
                <p style="font-size: 13px; margin: 0;">Inicia la creación de un plan o confirma uno para observar el flujo en tiempo real.</p>
            </div>
        @else
            <div style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                    <thead style="background: #fbfbf9; border-bottom: 1px solid #e3e3df;">
                        <tr>
                            <th style="padding: 12px 16px; font-weight: 700; color: #1a1a19;">Hora (Bolivia)</th>
                            <th style="padding: 12px 16px; font-weight: 700; color: #1a1a19;">Evento</th>
                            <th style="padding: 12px 16px; font-weight: 700; color: #1a1a19;">Propiedades de la Acción (Sin PII)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ultimosEventos as $ev)
                            <tr style="border-bottom: 1px solid #f0f0ed;">
                                <td style="padding: 12px 16px; font-family: monospace; font-size: 13px; color: #57534e; white-space: nowrap;">
                                    {{ $ev->fecha_bolivia }}
                                </td>
                                <td style="padding: 12px 16px; white-space: nowrap;">
                                    @if ($ev->evento === 'plan_creacion_iniciada')
                                        <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background: #e0f2fe; color: #0369a1;">
                                            plan_creacion_iniciada
                                        </span>
                                    @elseif ($ev->evento === 'plan_guardado')
                                        <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background: #fef9c3; color: #854d0e;">
                                            plan_guardado
                                        </span>
                                    @elseif ($ev->evento === 'plan_confirmado')
                                        <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background: #dcfce7; color: #15803d;">
                                            plan_confirmado
                                        </span>
                                    @else
                                        <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; background: #f3f4f6; color: #374151;">
                                            {{ $ev->evento }}
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 12px 16px; font-family: monospace; font-size: 12px; color: #475569;">
                                    @if (!empty($ev->props))
                                        <code>{{ json_encode($ev->props, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code>
                                    @else
                                        <span style="color: #a8a29e;">(sin propiedades adicionales)</span>
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

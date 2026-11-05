<div class="seccion-votacion" style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 16px; padding: 24px;">
    {{-- Banner accesible de veredicto público cuando el plan está confirmado (CA-04, Nielsen H1 y H5) --}}
    @if ($plan->estado === 'confirmado')
        <div role="status"
             class="banner-veredicto-confirmado"
             style="background: #edf7ef; border: 1px solid #c2e2c8; border-left: 5px solid #25663a; border-radius: 12px; padding: 18px 22px; margin-bottom: 24px; color: #1c1c1a;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                <svg style="width: 24px; height: 24px; color: #25663a; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span style="font-size: 17px; font-weight: 700; color: #25663a;">
                    ¡Plan confirmado! Deliberación y votación finalizadas.
                </span>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 24px; margin-top: 6px; font-size: 15px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 18px;" aria-hidden="true">📅</span>
                    <div>
                        <span style="font-weight: 600; color: #4a4a49;">Fecha acordada:</span>
                        @php
                            $fechaTexto = $plan->opcionFechaGanadora?->valor;
                            if ($fechaTexto) {
                                try {
                                    $fechaTexto = \Carbon\Carbon::parse($fechaTexto)->translatedFormat('d/m/Y H:i \h\r\s');
                                } catch (\Throwable $e) {}
                            }
                        @endphp
                        <span style="font-weight: 700; color: #1c1c1a;">
                            {{ $fechaTexto ?? ($plan->fecha_limite ? $plan->fecha_limite->format('d/m/Y') : 'Acordada') }}
                        </span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 18px;" aria-hidden="true">📍</span>
                    <div>
                        <span style="font-weight: 600; color: #4a4a49;">Lugar acordado:</span>
                        <span style="font-weight: 700; color: #1c1c1a;">
                            {{ $plan->opcionLugarGanadora?->valor ?? $plan->lugar ?? 'Por definir' }}
                        </span>
                    </div>
                </div>
            </div>

            @if ($plan->motivo_desempate)
                <div style="margin-top: 14px; padding: 12px 16px; background: #ffffff; border: 1px solid #c2e2c8; border-radius: 8px; font-size: 14px; color: #1c1c1a;">
                    <span style="font-weight: 700; color: #25663a;">Decisión del organizador:</span>
                    <span>&laquo;{{ $plan->motivo_desempate }}&raquo;</span>
                </div>
            @endif
        </div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="margin: 0 0 4px 0; font-size: 20px; font-weight: 700; color: #1c1c1a;">Sala de Votación y Propuestas</h2>
            <p style="margin: 0; color: #6f6f6a; font-size: 14px;">Elige entre fechas y lugares sugeridos o propón una nueva alternativa.</p>
        </div>

        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            @can('confirmar', $plan)
                <button type="button"
                        class="boton"
                        style="min-height: 44px; min-width: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 10px 18px; font-weight: 600; border-radius: 50px; background: #25663a; color: #ffffff;"
                        onclick="document.getElementById('modal-confirmar-plan').showModal()"
                        aria-label="Cerrar votación y confirmar el plan">
                    ✓ Cerrar votación y confirmar
                </button>
            @endcan

            @can('proponer', $plan)
                <button type="button"
                        class="boton {{ Auth::user()->can('confirmar', $plan) ? 'boton-claro' : '' }}"
                        style="min-height: 44px; min-width: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 10px 18px; font-weight: 600; border-radius: 50px;"
                        onclick="abrirModalConTipo('{{ $pestanaActiva === 'fechas' ? 'fecha' : 'lugar' }}')"
                        aria-label="Proponer nueva fecha o lugar">
                    + Proponer opción
                </button>
            @endcan
        </div>
    </div>

    {{-- Pestañas semánticas de Hick: n=2 alternativas equiprobables (Fechas vs Lugares) con formato píldora y resaltado cálido #ffe683 --}}
    <div class="pestanas-hick"
         role="tablist"
         aria-label="Categorías de decisión colaborativa"
         style="display: flex; gap: 12px; border-bottom: 1px solid #e3e3df; padding-bottom: 12px; margin-bottom: 24px;">
        
        <a href="{{ route('planes.detalle', ['plan' => $plan, 'tab' => 'fechas']) }}"
           role="tab"
           aria-selected="{{ $pestanaActiva === 'fechas' ? 'true' : 'false' }}"
           class="boton {{ $pestanaActiva === 'fechas' ? '' : 'boton-claro' }}"
           style="min-height: 44px; min-width: 44px; padding: 10px 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 50px; background: {{ $pestanaActiva === 'fechas' ? '#ffe683' : '#ffffff' }}; color: #1c1c1a; border: 1px solid {{ $pestanaActiva === 'fechas' ? '#ffe683' : '#e3e3df' }};">
            📅 Fechas propuestas ({{ $fechas->count() }})
        </a>

        <a href="{{ route('planes.detalle', ['plan' => $plan, 'tab' => 'lugares']) }}"
           role="tab"
           aria-selected="{{ $pestanaActiva === 'lugares' ? 'true' : 'false' }}"
           class="boton {{ $pestanaActiva === 'lugares' ? '' : 'boton-claro' }}"
           style="min-height: 44px; min-width: 44px; padding: 10px 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 50px; background: {{ $pestanaActiva === 'lugares' ? '#ffe683' : '#ffffff' }}; color: #1c1c1a; border: 1px solid {{ $pestanaActiva === 'lugares' ? '#ffe683' : '#e3e3df' }};">
            📍 Lugares propuestos ({{ $lugares->count() }})
        </a>
    </div>

    {{-- Contenido de la pestaña activa --}}
    @php
        $opcionesMostradas = ($pestanaActiva === 'fechas') ? $fechas : $lugares;
    @endphp

    @if ($opcionesMostradas->isEmpty())
        <div style="background: #fbfbf9; border: 1px dashed #d1d1cd; border-radius: 12px; padding: 36px 20px; text-align: center;">
            <p style="margin: 0 0 12px 0; color: #6f6f6a; font-size: 15px;">
                No hay {{ $pestanaActiva === 'fechas' ? 'fechas propuestas' : 'lugares propuestos' }} todavía.
            </p>
            @can('proponer', $plan)
                <button type="button"
                        class="boton"
                        style="min-height: 44px; min-width: 44px; padding: 10px 18px; border-radius: 50px;"
                        onclick="abrirModalConTipo('{{ $pestanaActiva === 'fechas' ? 'fecha' : 'lugar' }}')">
                    Sé el primero en proponer {{ $pestanaActiva === 'fechas' ? 'una fecha' : 'un lugar' }}
                </button>
            @endcan
        </div>
    @else
        <div class="grid-opciones" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
            @foreach ($opcionesMostradas as $opcion)
                @php
                    $haVotado = $opcion->votos->contains('user_id', auth()->id());
                    $totalVotos = $opcion->votos->count();
                    $textoValor = $opcion->valor;
                    if ($opcion->tipo === 'fecha') {
                        try {
                            $fechaParsed = \Carbon\Carbon::parse($opcion->valor);
                            $textoValor = $fechaParsed->translatedFormat('d/m/Y H:i \h\r\s');
                        } catch (\Throwable $e) {
                            $textoValor = $opcion->valor;
                        }
                    }
                @endphp
                <div class="tarjeta-opcion" style="background: #ffffff; border: 1px solid {{ $haVotado ? '#1c1c1a' : '#e3e3df' }}; border-radius: 12px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; gap: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 6px;">
                            <span style="font-size: 17px; font-weight: 700; color: #1c1c1a; word-break: break-word;">
                                {{ $textoValor }}
                            </span>
                            <span class="badge-votos" style="background: {{ $haVotado ? '#e8f5e9' : '#f3f4f6' }}; color: {{ $haVotado ? '#2e7d32' : '#374151' }}; font-weight: 700; font-size: 13px; padding: 4px 10px; border-radius: 9999px; white-space: nowrap;">
                                {{ $totalVotos }} {{ $totalVotos === 1 ? 'voto' : 'votos' }}
                            </span>
                        </div>

                        @if ($opcion->descripcion)
                            <p style="margin: 0 0 10px 0; font-size: 13px; color: #6f6f6a; line-height: 1.4;">
                                {{ $opcion->descripcion }}
                            </p>
                        @endif

                        <div style="font-size: 12px; color: #8e8e88;">
                            Propuesto por: <span style="font-weight: 600; color: #575752;">{{ $opcion->user->name ?? $opcion->user->email ?? 'Colaborador' }}</span>
                        </div>
                    </div>

                    {{-- Botón de votación ergonómico (Fitts min-h-[44px]) o aviso accesible de votación cerrada --}}
                    @can('votar', $plan)
                        <form method="POST" action="{{ route('opciones.votar', $opcion) }}" style="margin: 0;">
                            @csrf
                            <button type="submit"
                                    class="boton {{ $haVotado ? 'boton-claro' : '' }}"
                                    style="width: 100%; min-height: 44px; border-radius: 50px; display: inline-flex; align-items: center; justify-content: center; font-weight: 600; gap: 8px;"
                                    aria-label="{{ $haVotado ? 'Retirar voto de ' . $textoValor : 'Votar por ' . $textoValor }}">
                                @if ($haVotado)
                                    ✓ Votado (clic para retirar)
                                @else
                                    Votar por esta opción
                                @endif
                            </button>
                        </form>
                    @else
                        @if ($plan->estado === 'confirmado')
                            <div style="font-size: 13px; color: #312e81; text-align: center; padding: 10px 16px; background: #eef2ff; border-radius: 50px; font-weight: 600; border: 1px solid #c7d2fe;">
                                Votación cerrada (plan confirmado)
                            </div>
                        @elseif ($plan->estado === 'cancelado')
                            <div style="font-size: 13px; color: #71717a; text-align: center; padding: 10px 16px; background: #f4f4f5; border-radius: 50px; font-weight: 500;">
                                Votación cerrada (plan cancelado)
                            </div>
                        @else
                            <div style="font-size: 13px; color: #71717a; text-align: center; padding: 10px 16px; background: #f4f4f5; border-radius: 50px; font-weight: 500;">
                                Votación cerrada
                            </div>
                        @endif
                    @endcan
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Modal accesible para proponer opción (Prevención de errores Nielsen H5 con selector estructurado) --}}
@can('proponer', $plan)
<dialog id="modal-proponer-opcion" style="border: none; border-radius: 16px; padding: 28px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
    <form method="POST" action="{{ route('planes.opciones.guardar', $plan) }}">
        @csrf
        <h2 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #1c1c1a;">Proponer nueva opción</h2>
        <p style="margin: 0 0 20px 0; font-size: 14px; color: #6f6f6a;">Sugiere una fecha o un lugar para que el grupo lo vote.</p>

        <div style="margin-bottom: 16px;">
            <label for="select-tipo-opcion" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Tipo de sugerencia
            </label>
            <select id="select-tipo-opcion"
                    name="tipo"
                    required
                    onchange="actualizarInputPropuesta(this.value)"
                    style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; box-sizing: border-box; min-height: 44px;">
                <option value="fecha" {{ $pestanaActiva === 'fechas' ? 'selected' : '' }}>Fecha y hora</option>
                <option value="lugar" {{ $pestanaActiva === 'lugares' ? 'selected' : '' }}>Lugar o destino</option>
            </select>
        </div>

        {{-- Campo estructurado fecha/hora con confirmación explícita (Nielsen H3 y H5 - Feedback UniSport) --}}
        <div id="contenedor-campo-fecha" style="margin-bottom: 16px; {{ $pestanaActiva === 'lugares' ? 'display: none;' : '' }}">
            <label for="input-fecha-opcion" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Fecha y hora propuesta
            </label>
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <input type="datetime-local"
                       id="input-fecha-opcion"
                       min="{{ now()->format('Y-m-d\TH:i') }}"
                       {{ $pestanaActiva === 'fechas' ? 'name="valor"' : '' }}
                       onchange="retenerFechaTemporal(this.value)"
                       style="flex: 1; min-width: 220px; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; box-sizing: border-box; min-height: 44px;">
                <button type="button"
                        id="btn-aceptar-fecha"
                        class="boton boton-claro"
                        onclick="confirmarFechaSeleccionada()"
                        style="min-height: 44px; min-width: 44px; padding: 10px 16px; border-radius: 10px; font-weight: 600; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;">
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Aceptar fecha
                </button>
            </div>
            <div id="feedback-fecha-confirmada" style="display: none; margin-top: 8px; padding: 8px 12px; background: #e8f5e9; border: 1px solid #a5d6a7; border-radius: 8px; font-size: 13px; color: #1b5e20; font-weight: 600; align-items: center; gap: 6px;">
                <span>✓ Fecha confirmada:</span>
                <span id="texto-fecha-confirmada" style="font-weight: 700;"></span>
            </div>
            <p style="margin: 4px 0 0 0; font-size: 12px; color: #6f6f6a;">Selecciona el momento exacto y pulsa «Aceptar fecha» para fijar tu elección.</p>
        </div>

        {{-- Campo texto para lugar o destino --}}
        <div id="contenedor-campo-lugar" style="margin-bottom: 16px; {{ $pestanaActiva === 'fechas' ? 'display: none;' : '' }}">
            <label for="input-lugar-opcion" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Lugar o destino propuesto
            </label>
            <input type="text"
                   id="input-lugar-opcion"
                   placeholder="Ej. Restaurante Central o Parque del Retiro"
                   {{ $pestanaActiva === 'lugares' ? 'name="valor"' : '' }}
                   style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; box-sizing: border-box; min-height: 44px;">
            <p style="margin: 4px 0 0 0; font-size: 12px; color: #6f6f6a;">Ingresa la dirección o nombre del lugar recomendado.</p>
        </div>

        <div style="margin-bottom: 20px;">
            <label for="input-descripcion-opcion" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Detalles adicionales (opcional)
            </label>
            <textarea id="input-descripcion-opcion"
                      name="descripcion"
                      rows="3"
                      placeholder="Información extra, enlaces o notas"
                      style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; box-sizing: border-box;"></textarea>
        </div>

        {{-- Separación antagónica de al menos 12px (gap-3) según Ley de Fitts --}}
        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
            <button type="button"
                    class="boton boton-claro"
                    style="min-height: 44px; min-width: 44px; padding: 10px 18px; border-radius: 50px;"
                    onclick="document.getElementById('modal-proponer-opcion').close()">
                Cancelar
            </button>
            <button type="submit"
                    class="boton"
                    style="min-height: 44px; min-width: 44px; padding: 10px 22px; border-radius: 50px;">
                Guardar propuesta
            </button>
        </div>
    </form>
</dialog>

<script>
    var fechaSeleccionadaRetenida = '';

    function retenerFechaTemporal(val) {
        if (val) {
            fechaSeleccionadaRetenida = val;
        }
    }

    function confirmarFechaSeleccionada() {
        var inputFecha = document.getElementById('input-fecha-opcion');
        var feedbackDiv = document.getElementById('feedback-fecha-confirmada');
        var textoSpan = document.getElementById('texto-fecha-confirmada');

        if (!inputFecha || !inputFecha.value) {
            alert('Por favor selecciona una fecha y hora antes de pulsar Aceptar fecha.');
            return;
        }

        fechaSeleccionadaRetenida = inputFecha.value;
        if (feedbackDiv && textoSpan) {
            textoSpan.textContent = inputFecha.value.replace('T', ' ');
            feedbackDiv.style.display = 'flex';
        }
    }

    function actualizarInputPropuesta(tipo) {
        var contenedorFecha = document.getElementById('contenedor-campo-fecha');
        var inputFecha = document.getElementById('input-fecha-opcion');
        var contenedorLugar = document.getElementById('contenedor-campo-lugar');
        var inputLugar = document.getElementById('input-lugar-opcion');

        if (tipo === 'fecha') {
            if (contenedorFecha) contenedorFecha.style.display = 'block';
            if (inputFecha) {
                inputFecha.setAttribute('name', 'valor');
                inputFecha.required = true;
                if (fechaSeleccionadaRetenida && !inputFecha.value) {
                    inputFecha.value = fechaSeleccionadaRetenida;
                }
            }
            if (contenedorLugar) contenedorLugar.style.display = 'none';
            if (inputLugar) {
                inputLugar.removeAttribute('name');
                inputLugar.required = false;
            }
        } else {
            if (contenedorFecha) contenedorFecha.style.display = 'none';
            if (inputFecha) {
                inputFecha.removeAttribute('name');
                inputFecha.required = false;
            }
            if (contenedorLugar) contenedorLugar.style.display = 'block';
            if (inputLugar) {
                inputLugar.setAttribute('name', 'valor');
                inputLugar.required = true;
            }
        }
    }

    function abrirModalConTipo(tipo) {
        var select = document.getElementById('select-tipo-opcion');
        if (select) {
            select.value = tipo;
        }
        actualizarInputPropuesta(tipo);
        var modal = document.getElementById('modal-proponer-opcion');
        if (modal) {
            modal.showModal();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var select = document.getElementById('select-tipo-opcion');
        if (select) {
            actualizarInputPropuesta(select.value);
        }
    });
</script>
@endcan

{{-- Modal accesible para confirmar plan y resolver empates (Nielsen H3, H5, Hick) --}}
@can('confirmar', $plan)
@php
    $empateFechas = $plan->tieneEmpateFechas();
    $empateLugares = $plan->tieneEmpateLugares();
    $hayEmpate = $empateFechas || $empateLugares;
    $candidatosFecha = $plan->obtenerPropuestasEmpatadas('fecha');
    $candidatosLugar = $plan->obtenerPropuestasEmpatadas('lugar');
@endphp
<dialog id="modal-confirmar-plan" style="border: none; border-radius: 16px; padding: 28px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
    <form method="POST" action="{{ route('planes.confirmar', $plan) }}">
        @csrf
        <h2 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #1c1c1a;">
            Confirmar Plan y Cerrar Votación
        </h2>
        <p style="margin: 0 0 20px 0; font-size: 14px; color: #6f6f6a; line-height: 1.5;">
            Al confirmar el plan, se cerrará la votación para todos los colaboradores y se formalizará la fecha y lugar definitivos.
        </p>

        {{-- Bloque Selección de Fecha --}}
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 8px;">
                📅 Fecha definitiva
            </label>
            @if ($candidatosFecha->isEmpty())
                <div style="padding: 10px 14px; background: #fbfbf9; border: 1px solid #e3e3df; border-radius: 8px; font-size: 14px; color: #6f6f6a;">
                    No hay fechas propuestas en la sala (se conservará la fecha límite del plan).
                </div>
            @elseif ($empateFechas)
                <div style="padding: 10px 14px; background: #fff4d6; border: 1px solid #ffe683; border-radius: 8px; margin-bottom: 10px; font-size: 13px; color: #8a6100; font-weight: 600;">
                    ⚖️ Existe un empate en las fechas más votadas. Elige la opción ganadora:
                </div>
                <select name="propuesta_fecha_id" required style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; min-height: 44px; box-sizing: border-box;">
                    <option value="">-- Selecciona la fecha definitiva --</option>
                    @foreach ($candidatosFecha as $cf)
                        @php
                            $textoF = $cf->valor;
                            try {
                                $textoF = \Carbon\Carbon::parse($cf->valor)->translatedFormat('d/m/Y H:i \h\r\s');
                            } catch (\Throwable $e) {}
                        @endphp
                        <option value="{{ $cf->id }}">
                            {{ $textoF }} ({{ $cf->votos->count() }} votos)
                        </option>
                    @endforeach
                </select>
            @else
                @php
                    $ganadorF = $candidatosFecha->first();
                    $textoGanadorF = $ganadorF->valor;
                    try {
                        $textoGanadorF = \Carbon\Carbon::parse($ganadorF->valor)->translatedFormat('d/m/Y H:i \h\r\s');
                    } catch (\Throwable $e) {}
                @endphp
                <input type="hidden" name="propuesta_fecha_id" value="{{ $ganadorF->id }}">
                <div style="padding: 12px 16px; background: #edf7ef; border: 1px solid #c2e2c8; border-radius: 10px; font-size: 14px; color: #25663a; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600;">✓ Ganador indiscutido: {{ $textoGanadorF }}</span>
                    <span style="font-weight: 700; background: #ffffff; padding: 2px 10px; border-radius: 50px; font-size: 12px;">
                        {{ $ganadorF->votos->count() }} votos
                    </span>
                </div>
            @endif
        </div>

        {{-- Bloque Selección de Lugar --}}
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 8px;">
                📍 Lugar o destino definitivo
            </label>
            @if ($candidatosLugar->isEmpty())
                <div style="padding: 10px 14px; background: #fbfbf9; border: 1px solid #e3e3df; border-radius: 8px; font-size: 14px; color: #6f6f6a;">
                    No hay lugares propuestos en la sala (se conservará el lugar registrado en el plan).
                </div>
            @elseif ($empateLugares)
                <div style="padding: 10px 14px; background: #fff4d6; border: 1px solid #ffe683; border-radius: 8px; margin-bottom: 10px; font-size: 13px; color: #8a6100; font-weight: 600;">
                    ⚖️ Existe un empate en los lugares más votados. Elige la opción ganadora:
                </div>
                <select name="propuesta_lugar_id" required style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; min-height: 44px; box-sizing: border-box;">
                    <option value="">-- Selecciona el lugar definitivo --</option>
                    @foreach ($candidatosLugar as $cl)
                        <option value="{{ $cl->id }}">
                            {{ $cl->valor }} ({{ $cl->votos->count() }} votos)
                        </option>
                    @endforeach
                </select>
            @else
                @php
                    $ganadorL = $candidatosLugar->first();
                @endphp
                <input type="hidden" name="propuesta_lugar_id" value="{{ $ganadorL->id }}">
                <div style="padding: 12px 16px; background: #edf7ef; border: 1px solid #c2e2c8; border-radius: 10px; font-size: 14px; color: #25663a; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 600;">✓ Ganador indiscutido: {{ $ganadorL->valor }}</span>
                    <span style="font-weight: 700; background: #ffffff; padding: 2px 10px; border-radius: 50px; font-size: 12px;">
                        {{ $ganadorL->votos->count() }} votos
                    </span>
                </div>
            @endif
        </div>

        {{-- Campo Motivo de Desempate (obligatorio si hay empate, opcional si no) --}}
        <div style="margin-bottom: 24px;">
            <label for="motivo_desempate" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Motivo del desempate {{ $hayEmpate ? '(obligatorio)' : '(opcional)' }}
            </label>
            <textarea id="motivo_desempate"
                      name="motivo_desempate"
                      rows="3"
                      minlength="5"
                      placeholder="{{ $hayEmpate ? 'Explica a los colaboradores el motivo por el cual elegiste esta opción...' : 'Comentario o aclaración para los colaboradores (opcional)' }}"
                      {{ $hayEmpate ? 'required' : '' }}
                      style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; box-sizing: border-box;"></textarea>
            @if ($hayEmpate)
                <p style="margin: 4px 0 0 0; font-size: 12px; color: #8a6100;">
                    Debido al empate, se requiere registrar una justificación clara (mínimo 5 caracteres) visible públicamente.
                </p>
            @endif
        </div>

        {{-- Separación antagónica de botones >= 12px (gap-3, Ley de Fitts) --}}
        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <button type="button"
                    class="boton boton-claro"
                    style="min-height: 44px; min-width: 44px; padding: 10px 18px; border-radius: 50px;"
                    onclick="document.getElementById('modal-confirmar-plan').close()">
                Cancelar
            </button>
            <button type="submit"
                    class="boton"
                    style="min-height: 44px; min-width: 44px; padding: 10px 22px; border-radius: 50px; background: #25663a; color: #ffffff;">
                Confirmar y cerrar votación
            </button>
        </div>
    </form>
</dialog>
@endcan

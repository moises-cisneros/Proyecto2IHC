<div class="seccion-votacion"
    style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl); padding: var(--space-6);">
    {{-- Banner accesible de veredicto público cuando el plan está confirmado (CA-04, Nielsen H1 y H5) --}}
    @if ($plan->estado === 'confirmado')
        <div role="status" class="banner-veredicto-confirmado"
            style="background: var(--color-success-bg); border: 1px solid var(--color-success-border); border-left: 5px solid var(--color-success); border-radius: var(--radius-lg); padding: var(--space-4) var(--space-5); margin-bottom: var(--space-6); color: var(--color-foreground);">
            <div style="display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-3);">
                <svg style="width: 24px; height: 24px; color: var(--color-success); flex-shrink: 0;" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span
                    style="font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); color: var(--color-success);">
                    ¡Plan confirmado! Deliberación y votación finalizadas.
                </span>
            </div>
            <p style="margin: 0 0 var(--space-3) 0; color: var(--color-success); font-size: var(--font-size-md);">
                Votación finalizada. Este plan ha sido confirmado y la deliberación se encuentra cerrada.
            </p>

            <div
                style="display: flex; flex-wrap: wrap; gap: var(--space-6); margin-top: var(--space-1); font-size: var(--font-size-base);">
                <div style="display: flex; align-items: center; gap: var(--space-2);">
                    <x-icono nombre="calendario" style="width: 20px; height: 20px; color: var(--color-success);" />
                    <div>
                        <span style="font-weight: var(--font-weight-semibold); color: var(--color-primary-hover);">Fecha
                            acordada:</span>
                        @php
                            $fechaTexto = $plan->propuestaFechaGanadora?->valor;
                            if ($fechaTexto) {
                                try {
                                    $fechaTexto = \Carbon\Carbon::parse($fechaTexto)->translatedFormat(
                                        'd/m/Y H:i \h\r\s',
                                    );
                                } catch (\Throwable $e) {
                                }
                            }
                        @endphp
                        <span style="font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                            {{ $fechaTexto ?? ($plan->fecha_limite ? $plan->fecha_limite->format('d/m/Y') : 'Acordada') }}
                        </span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: var(--space-2);">
                    <x-icono nombre="ubicacion" style="width: 20px; height: 20px; color: var(--color-success);" />
                    <div>
                        <span style="font-weight: var(--font-weight-semibold); color: var(--color-primary-hover);">Lugar
                            acordado:</span>
                        <span style="font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                            {{ $plan->propuestaLugarGanadora?->valor ?? ($plan->lugar ?? 'Por definir') }}
                        </span>
                    </div>
                </div>
            </div>

            @if ($plan->motivo_desempate)
                <div
                    style="margin-top: var(--space-3); padding: var(--space-3) var(--space-4); background: var(--color-surface); border: 1px solid var(--color-success-border); border-radius: var(--radius-sm); font-size: var(--font-size-md); color: var(--color-foreground);">
                    <span style="font-weight: var(--font-weight-bold); color: var(--color-success);">Decisión del
                        organizador:</span>
                    <span>&laquo;{{ $plan->motivo_desempate }}&raquo;</span>
                </div>
            @endif
        </div>
    @endif

    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-5); flex-wrap: wrap; gap: var(--space-3);">
        <div>
            <h2
                style="margin: 0 0 var(--space-1) 0; font-size: var(--font-size-xl); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                Sala de Votación y Propuestas</h2>
            <p style="margin: 0; color: var(--color-muted); font-size: var(--font-size-md);">Elige entre fechas y
                lugares sugeridos o propón una nueva alternativa.</p>
        </div>

        <div style="display: flex; gap: var(--space-3); align-items: center; flex-wrap: wrap;">
            @can('confirmar', $plan)
                <button type="button" class="boton"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); display: inline-flex; align-items: center; justify-content: center; padding: var(--space-2) var(--space-4); font-weight: var(--font-weight-semibold); border-radius: var(--radius-pill); background: var(--color-success); color: var(--color-surface);"
                    onclick="document.getElementById('modal-confirmar-plan').showModal()"
                    aria-label="Cerrar votación y confirmar">
                    <x-icono nombre="check"
                        style="width: 18px; height: 18px; display: inline-block; vertical-align: middle; margin-right: var(--space-1);" />
                    Cerrar votación y confirmar
                </button>
            @endcan

            @can('proponer', $plan)
                <button type="button" class="boton {{ Auth::user()->can('confirmar', $plan) ? 'boton-claro' : '' }}"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); display: inline-flex; align-items: center; justify-content: center; padding: var(--space-2) var(--space-4); font-weight: var(--font-weight-semibold); border-radius: var(--radius-pill);"
                    onclick="abrirModalConTipo('{{ $pestanaActiva === 'fechas' ? 'fecha' : 'lugar' }}')"
                    aria-label="Proponer nueva fecha o lugar">
                    + Proponer opción
                </button>
            @endcan
        </div>
    </div>

    {{-- Pestañas semánticas de Hick: n=2 alternativas equiprobables (Fechas vs Lugares) con formato píldora y resaltado cálido var(--color-brand) --}}
    <div class="pestanas-hick" role="tablist" aria-label="Categorías de decisión colaborativa"
        style="display: flex; gap: var(--space-3); border-bottom: 1px solid var(--color-border); padding-bottom: var(--space-3); margin-bottom: var(--space-6);">

        <a href="{{ route('planes.detalle', ['plan' => $plan, 'tab' => 'fechas']) }}" role="tab"
            aria-selected="{{ $pestanaActiva === 'fechas' ? 'true' : 'false' }}"
            class="boton {{ $pestanaActiva === 'fechas' ? '' : 'boton-claro' }}"
            style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-5); display: inline-flex; align-items: center; justify-content: center; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); text-decoration: none; border-radius: var(--radius-pill); background: {{ $pestanaActiva === 'fechas' ? 'var(--color-brand)' : 'var(--color-surface)' }}; color: var(--color-foreground); border: 1px solid {{ $pestanaActiva === 'fechas' ? 'var(--color-brand)' : 'var(--color-border)' }};">
            <x-icono nombre="calendario"
                style="width: 18px; height: 18px; display: inline-block; vertical-align: middle; margin-right: var(--space-1);" />
            Fechas propuestas ({{ $fechas->count() }})
        </a>

        <a href="{{ route('planes.detalle', ['plan' => $plan, 'tab' => 'lugares']) }}" role="tab"
            aria-selected="{{ $pestanaActiva === 'lugares' ? 'true' : 'false' }}"
            class="boton {{ $pestanaActiva === 'lugares' ? '' : 'boton-claro' }}"
            style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-5); display: inline-flex; align-items: center; justify-content: center; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); text-decoration: none; border-radius: var(--radius-pill); background: {{ $pestanaActiva === 'lugares' ? 'var(--color-brand)' : 'var(--color-surface)' }}; color: var(--color-foreground); border: 1px solid {{ $pestanaActiva === 'lugares' ? 'var(--color-brand)' : 'var(--color-border)' }};">
            <x-icono nombre="ubicacion"
                style="width: 18px; height: 18px; display: inline-block; vertical-align: middle; margin-right: var(--space-1);" />
            Lugares propuestos ({{ $lugares->count() }})
        </a>

        <a href="{{ route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']) }}" role="tab"
            aria-selected="{{ $pestanaActiva === 'gastos' ? 'true' : 'false' }}"
            class="boton {{ $pestanaActiva === 'gastos' ? '' : 'boton-claro' }}"
            style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-5); display: inline-flex; align-items: center; justify-content: center; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); text-decoration: none; border-radius: var(--radius-pill); background: {{ $pestanaActiva === 'gastos' ? 'var(--color-brand)' : 'var(--color-surface)' }}; color: var(--color-foreground); border: 1px solid {{ $pestanaActiva === 'gastos' ? 'var(--color-brand)' : 'var(--color-border)' }};">
            <x-icono nombre="dinero"
                style="width: 18px; height: 18px; display: inline-block; vertical-align: middle; margin-right: var(--space-1);" />
            Gastos y Liquidación ({{ $gastos->count() }})
        </a>
    </div>

    {{-- Contenido de la pestaña activa --}}
    @if ($pestanaActiva === 'gastos')
        @include('planes._gastos_liquidacion')
    @else
        @php
            $opcionesMostradas = $pestanaActiva === 'fechas' ? $fechas : $lugares;
        @endphp

        @if ($opcionesMostradas->isEmpty())
            <div
                style="background: var(--color-surface-soft); border: 1px dashed var(--color-border-input); border-radius: var(--radius-lg); padding: var(--space-8) var(--space-5); text-align: center;">
                <p style="margin: 0 0 var(--space-3) 0; color: var(--color-muted); font-size: var(--font-size-base);">
                    No hay {{ $pestanaActiva === 'fechas' ? 'fechas propuestas' : 'lugares propuestos' }} todavía.
                </p>
                @can('proponer', $plan)
                    <button type="button" class="boton"
                        style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-4); border-radius: var(--radius-pill);"
                        onclick="abrirModalConTipo('{{ $pestanaActiva === 'fechas' ? 'fecha' : 'lugar' }}')">
                        Sé el primero en proponer {{ $pestanaActiva === 'fechas' ? 'una fecha' : 'un lugar' }}
                    </button>
                @endcan
            </div>
        @else
            <div class="grid-opciones"
                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--space-4);">
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
                    <div class="tarjeta-opcion"
                        style="background: var(--color-surface); border: 1px solid {{ $haVotado ? 'var(--color-foreground)' : 'var(--color-border)' }}; border-radius: var(--radius-lg); padding: var(--space-4); display: flex; flex-direction: column; justify-content: space-between; gap: var(--space-3); box-shadow: var(--shadow-sm);">
                        <div>
                            <div
                                style="display: flex; justify-content: space-between; align-items: flex-start; gap: var(--space-2); margin-bottom: var(--space-1);">
                                <span
                                    style="font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); color: var(--color-foreground); word-break: break-word;">
                                    {{ $textoValor }}
                                </span>
                                <span class="badge-votos"
                                    style="background: {{ $haVotado ? 'var(--color-success-bg)' : 'var(--color-neutral-bg)' }}; color: {{ $haVotado ? 'var(--color-success)' : 'var(--color-muted)' }}; font-weight: var(--font-weight-bold); font-size: var(--font-size-sm); padding: var(--space-1) var(--space-2); border-radius: var(--radius-pill); white-space: nowrap;">
                                    {{ $totalVotos }} {{ $totalVotos === 1 ? 'voto' : 'votos' }}
                                </span>
                            </div>

                            @if ($opcion->descripcion)
                                <p
                                    style="margin: 0 0 var(--space-2) 0; font-size: var(--font-size-sm); color: var(--color-muted); line-height: 1.4;">
                                    {{ $opcion->descripcion }}
                                </p>
                            @endif

                            <div style="font-size: var(--font-size-xs); color: var(--color-muted);">
                                Propuesto por: <span
                                    style="font-weight: var(--font-weight-semibold); color: var(--color-muted);">{{ $opcion->user->name ?? ($opcion->user->email ?? 'Colaborador') }}</span>
                            </div>
                        </div>

                        {{-- Botón de votación ergonómico (Fitts min-h-[44px]) o aviso accesible de votación cerrada --}}
                        @can('votar', $plan)
                            <form method="POST" action="{{ route('opciones.votar', $opcion) }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="boton {{ $haVotado ? 'boton-claro' : '' }}"
                                    style="width: 100%; min-height: var(--touch-min); border-radius: var(--radius-pill); display: inline-flex; align-items: center; justify-content: center; font-weight: var(--font-weight-semibold); gap: var(--space-2);"
                                    aria-label="{{ $haVotado ? 'Retirar voto de ' . $textoValor : 'Votar por ' . $textoValor }}">
                                    @if ($haVotado)
                                        <x-icono nombre="check"
                                            style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: var(--space-1);" />
                                        Votado (clic para retirar)
                                    @else
                                        Votar por esta opción
                                    @endif
                                </button>
                            </form>
                        @else
                            @if ($plan->estado === 'confirmado')
                                <div
                                    style="font-size: var(--font-size-sm); color: var(--color-foreground); text-align: center; padding: var(--space-2) var(--space-4); background: var(--color-neutral-bg); border-radius: var(--radius-pill); font-weight: var(--font-weight-semibold); border: 1px solid var(--color-border);">
                                    Votación cerrada (plan confirmado)
                                </div>
                            @elseif ($plan->estado === 'cancelado')
                                <div
                                    style="font-size: var(--font-size-sm); color: var(--color-muted); text-align: center; padding: var(--space-2) var(--space-4); background: var(--color-neutral-bg); border-radius: var(--radius-pill); font-weight: var(--font-weight-medium);">
                                    Votación cerrada (plan cancelado)
                                </div>
                            @else
                                <div
                                    style="font-size: var(--font-size-sm); color: var(--color-muted); text-align: center; padding: var(--space-2) var(--space-4); background: var(--color-neutral-bg); border-radius: var(--radius-pill); font-weight: var(--font-weight-medium);">
                                    Votación cerrada
                                </div>
                            @endif
                        @endcan
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>

{{-- Modal accesible para proponer opción (Prevención de errores Nielsen H5 con selector estructurado) --}}
@can('proponer', $plan)
    <dialog id="modal-proponer-opcion"
        style="border: none; border-radius: var(--radius-xl); padding: var(--space-6); width: 100%; max-width: 480px; box-shadow: var(--shadow-md);">
        <form method="POST" action="{{ route('planes.opciones.guardar', $plan) }}">
            @csrf
            <h2
                style="margin: 0 0 var(--space-2) 0; font-size: var(--font-size-xl); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                Proponer nueva opción</h2>
            <p style="margin: 0 0 var(--space-5) 0; font-size: var(--font-size-md); color: var(--color-muted);">Sugiere una
                fecha o un lugar para que el grupo lo vote.</p>

            <div style="margin-bottom: var(--space-4);">
                <label for="select-tipo-opcion"
                    style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                    Tipo de sugerencia
                </label>
                <select id="select-tipo-opcion" name="tipo" required onchange="actualizarInputPropuesta(this.value)"
                    style="width: 100%; padding: var(--space-3) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md); box-sizing: border-box; min-height: var(--touch-min);">
                    <option value="fecha" {{ $pestanaActiva === 'fechas' ? 'selected' : '' }}>Fecha y hora</option>
                    <option value="lugar" {{ $pestanaActiva === 'lugares' ? 'selected' : '' }}>Lugar o destino</option>
                </select>
            </div>

            {{-- Campo estructurado fecha/hora con confirmación explícita (Nielsen H3 y H5 - Feedback UniSport) --}}
            <div id="contenedor-campo-fecha"
                style="margin-bottom: var(--space-4); {{ $pestanaActiva === 'lugares' ? 'display: none;' : '' }}">
                <label for="input-fecha-opcion"
                    style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                    Fecha y hora propuesta
                </label>
                <div style="display: flex; gap: var(--space-2); align-items: center; flex-wrap: wrap;">
                    <input type="datetime-local" id="input-fecha-opcion" min="{{ now()->format('Y-m-d\TH:i') }}"
                        {{ $pestanaActiva === 'fechas' ? 'name="valor"' : '' }}
                        onchange="retenerFechaTemporal(this.value)"
                        style="flex: 1; min-width: 220px; padding: var(--space-3) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md); box-sizing: border-box; min-height: var(--touch-min);">
                    <button type="button" id="btn-aceptar-fecha" class="boton boton-claro"
                        onclick="confirmarFechaSeleccionada()"
                        style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-4); border-radius: var(--radius-md); font-weight: var(--font-weight-semibold); font-size: var(--font-size-sm); display: inline-flex; align-items: center; gap: var(--space-1); white-space: nowrap;">
                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Aceptar fecha
                    </button>
                </div>
                <div id="feedback-fecha-confirmada"
                    style="display: none; margin-top: var(--space-2); padding: var(--space-2) var(--space-3); background: var(--color-success-bg); border: 1px solid var(--color-success-border); border-radius: var(--radius-sm); font-size: var(--font-size-sm); color: var(--color-success); font-weight: var(--font-weight-semibold); align-items: center; gap: var(--space-1);">
                    <span style="display: inline-flex; align-items: center; gap: var(--space-1);"><x-icono nombre="check"
                            style="width: 16px; height: 16px;" /> Fecha confirmada:</span>
                    <span id="texto-fecha-confirmada" style="font-weight: var(--font-weight-bold);"></span>
                </div>
                <p style="margin: var(--space-1) 0 0 0; font-size: var(--font-size-xs); color: var(--color-muted);">
                    Selecciona el momento exacto y pulsa «Aceptar fecha» para fijar tu elección.</p>
            </div>

            {{-- Campo texto para lugar o destino --}}
            <div id="contenedor-campo-lugar"
                style="margin-bottom: var(--space-4); {{ $pestanaActiva === 'fechas' ? 'display: none;' : '' }}">
                <label for="input-lugar-opcion"
                    style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                    Lugar o destino propuesto
                </label>
                <input type="text" id="input-lugar-opcion" placeholder="Ej. Restaurante Central o Parque del Retiro"
                    {{ $pestanaActiva === 'lugares' ? 'name="valor"' : '' }}
                    style="width: 100%; padding: var(--space-3) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md); box-sizing: border-box; min-height: var(--touch-min);">
                <p style="margin: var(--space-1) 0 0 0; font-size: var(--font-size-xs); color: var(--color-muted);">Ingresa
                    la dirección o nombre del lugar recomendado.</p>
            </div>

            <div style="margin-bottom: var(--space-5);">
                <label for="input-descripcion-opcion"
                    style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                    Detalles adicionales (opcional)
                </label>
                <textarea id="input-descripcion-opcion" name="descripcion" rows="3"
                    placeholder="Información extra, enlaces o notas"
                    style="width: 100%; padding: var(--space-3) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md); box-sizing: border-box;"></textarea>
            </div>

            {{-- Separación antagónica de al menos 12px (gap-3) según Ley de Fitts --}}
            <div style="display: flex; justify-content: flex-end; gap: var(--space-3); margin-top: var(--space-6);">
                <button type="button" class="boton boton-claro"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-4); border-radius: var(--radius-pill);"
                    onclick="document.getElementById('modal-proponer-opcion').close()">
                    Cancelar
                </button>
                <button type="submit" class="boton"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-5); border-radius: var(--radius-pill);">
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
    <dialog id="modal-confirmar-plan"
        style="border: none; border-radius: var(--radius-xl); padding: var(--space-6); width: 100%; max-width: 520px; box-shadow: var(--shadow-md);">
        <form method="POST" action="{{ route('planes.confirmar', $plan) }}">
            @csrf
            <h2
                style="margin: 0 0 var(--space-2) 0; font-size: var(--font-size-xl); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                Confirmar Plan y Cerrar Votación
            </h2>
            <p
                style="margin: 0 0 var(--space-5) 0; font-size: var(--font-size-md); color: var(--color-muted); line-height: 1.5;">
                Al confirmar el plan, se cerrará la votación para todos los colaboradores y se formalizará la fecha y lugar
                definitivos.
            </p>

            {{-- Bloque Selección de Fecha --}}
            <div style="margin-bottom: var(--space-5);">
                <label
                    style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-2);">
                    <x-icono nombre="calendario"
                        style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: var(--space-1);" />
                    Fecha definitiva
                </label>
                @if ($candidatosFecha->isEmpty())
                    <div
                        style="padding: var(--space-2) var(--space-3); background: var(--color-surface-soft); border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-size: var(--font-size-md); color: var(--color-muted);">
                        No hay fechas propuestas en la sala (se conservará la fecha límite del plan).
                    </div>
                @elseif ($empateFechas)
                    <div
                        style="padding: var(--space-2) var(--space-3); background: var(--color-warning-bg); border: 1px solid var(--color-brand); border-radius: var(--radius-sm); margin-bottom: var(--space-2); font-size: var(--font-size-sm); color: var(--color-warning); font-weight: var(--font-weight-semibold);">
                        <x-icono nombre="balanza"
                            style="width: 18px; height: 18px; display: inline-block; vertical-align: middle; margin-right: var(--space-1);" />
                        Existe un empate en las fechas más votadas. Elige la opción ganadora:
                    </div>
                    <select name="propuesta_fecha_id" required
                        style="width: 100%; padding: var(--space-3) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md); min-height: var(--touch-min); box-sizing: border-box;">
                        <option value="">-- Selecciona la fecha definitiva --</option>
                        @foreach ($candidatosFecha as $cf)
                            @php
                                $textoF = $cf->valor;
                                try {
                                    $textoF = \Carbon\Carbon::parse($cf->valor)->translatedFormat('d/m/Y H:i \h\r\s');
                                } catch (\Throwable $e) {
                                }
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
                            $textoGanadorF = \Carbon\Carbon::parse($ganadorF->valor)->translatedFormat(
                                'd/m/Y H:i \h\r\s',
                            );
                        } catch (\Throwable $e) {
                        }
                    @endphp
                    <input type="hidden" name="propuesta_fecha_id" value="{{ $ganadorF->id }}">
                    <div
                        style="padding: var(--space-3) var(--space-4); background: var(--color-success-bg); border: 1px solid var(--color-success-border); border-radius: var(--radius-md); font-size: var(--font-size-md); color: var(--color-success); display: flex; justify-content: space-between; align-items: center;">
                        <span
                            style="font-weight: var(--font-weight-semibold); display: inline-flex; align-items: center; gap: var(--space-1);"><x-icono
                                nombre="check" style="width: 16px; height: 16px;" /> Ganador indiscutido:
                            {{ $textoGanadorF }}</span>
                        <span
                            style="font-weight: var(--font-weight-bold); background: var(--color-surface); padding: var(--space-1) var(--space-2); border-radius: var(--radius-pill); font-size: var(--font-size-xs);">
                            {{ $ganadorF->votos->count() }} votos
                        </span>
                    </div>
                @endif
            </div>

            {{-- Bloque Selección de Lugar --}}
            <div style="margin-bottom: var(--space-5);">
                <label
                    style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-2);">
                    <x-icono nombre="ubicacion"
                        style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: var(--space-1);" />
                    Lugar o destino definitivo
                </label>
                @if ($candidatosLugar->isEmpty())
                    <div
                        style="padding: var(--space-2) var(--space-3); background: var(--color-surface-soft); border: 1px solid var(--color-border); border-radius: var(--radius-sm); font-size: var(--font-size-md); color: var(--color-muted);">
                        No hay lugares propuestos en la sala (se conservará el lugar registrado en el plan).
                    </div>
                @elseif ($empateLugares)
                    <div
                        style="padding: var(--space-2) var(--space-3); background: var(--color-warning-bg); border: 1px solid var(--color-brand); border-radius: var(--radius-sm); margin-bottom: var(--space-2); font-size: var(--font-size-sm); color: var(--color-warning); font-weight: var(--font-weight-semibold);">
                        <x-icono nombre="balanza"
                            style="width: 18px; height: 18px; display: inline-block; vertical-align: middle; margin-right: var(--space-1);" />
                        Existe un empate en los lugares más votados. Elige la opción ganadora:
                    </div>
                    <select name="propuesta_lugar_id" required
                        style="width: 100%; padding: var(--space-3) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md); min-height: var(--touch-min); box-sizing: border-box;">
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
                    <div
                        style="padding: var(--space-3) var(--space-4); background: var(--color-success-bg); border: 1px solid var(--color-success-border); border-radius: var(--radius-md); font-size: var(--font-size-md); color: var(--color-success); display: flex; justify-content: space-between; align-items: center;">
                        <span
                            style="font-weight: var(--font-weight-semibold); display: inline-flex; align-items: center; gap: var(--space-1);"><x-icono
                                nombre="check" style="width: 16px; height: 16px;" /> Ganador indiscutido:
                            {{ $ganadorL->valor }}</span>
                        <span
                            style="font-weight: var(--font-weight-bold); background: var(--color-surface); padding: var(--space-1) var(--space-2); border-radius: var(--radius-pill); font-size: var(--font-size-xs);">
                            {{ $ganadorL->votos->count() }} votos
                        </span>
                    </div>
                @endif
            </div>

            {{-- Campo Motivo de Desempate (obligatorio si hay empate, opcional si no) --}}
            <div style="margin-bottom: var(--space-6);">
                <label for="motivo_desempate"
                    style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                    Motivo del desempate {{ $hayEmpate ? '(obligatorio)' : '(opcional)' }}
                </label>
                <textarea id="motivo_desempate" name="motivo_desempate" rows="3" minlength="5"
                    placeholder="{{ $hayEmpate ? 'Explica a los colaboradores el motivo por el cual elegiste esta opción...' : 'Comentario o aclaración para los colaboradores (opcional)' }}"
                    {{ $hayEmpate ? 'required' : '' }}
                    style="width: 100%; padding: var(--space-3) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md); box-sizing: border-box;"></textarea>
                @if ($hayEmpate)
                    <p style="margin: var(--space-1) 0 0 0; font-size: var(--font-size-xs); color: var(--color-warning);">
                        Debido al empate, se requiere registrar una justificación clara (mínimo 5 caracteres) visible
                        públicamente.
                    </p>
                @endif
            </div>

            {{-- Separación antagónica de botones >= 12px (gap-3, Ley de Fitts) --}}
            <div style="display: flex; justify-content: flex-end; gap: var(--space-3);">
                <button type="button" class="boton boton-claro"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-4); border-radius: var(--radius-pill);"
                    onclick="document.getElementById('modal-confirmar-plan').close()">
                    Cancelar
                </button>
                <button type="submit" class="boton"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-5); border-radius: var(--radius-pill); background: var(--color-success); color: var(--color-surface);">
                    Confirmar y cerrar votación
                </button>
            </div>
        </form>
    </dialog>
@endcan

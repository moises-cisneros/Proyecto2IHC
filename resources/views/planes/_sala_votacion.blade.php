<div class="tarjeta votacion-sala">
    {{-- Banner accesible de veredicto público cuando el plan está confirmado (CA-04, Nielsen H1 y H5) --}}
    @if ($plan->estado === 'confirmado')
        <div role="status" class="votacion-veredicto">
            <div class="votacion-veredicto-titulo-fila">
                <svg class="icono-svg icono-lg votacion-icono-exito" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="votacion-veredicto-titulo">
                    ¡Plan confirmado! Deliberación y votación finalizadas.
                </span>
            </div>
            <p class="votacion-veredicto-mensaje">
                Votación finalizada. Este plan ha sido confirmado y la deliberación se encuentra cerrada.
            </p>

            <div class="votacion-veredicto-datos">
                <div class="votacion-veredicto-dato">
                    <x-icono nombre="calendario" class="votacion-icono-exito" />
                    <div>
                        <span class="votacion-veredicto-etiqueta">Fecha
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
                        <span class="votacion-veredicto-valor">
                            {{ $fechaTexto ?? ($plan->fecha_limite ? $plan->fecha_limite->format('d/m/Y') : 'Acordada') }}
                        </span>
                    </div>
                </div>

                <div class="votacion-veredicto-dato">
                    <x-icono nombre="ubicacion" class="votacion-icono-exito" />
                    <div>
                        <span class="votacion-veredicto-etiqueta">Lugar
                            acordado:</span>
                        <span class="votacion-veredicto-valor">
                            {{ $plan->propuestaLugarGanadora?->valor ?? ($plan->lugar ?? 'Por definir') }}
                        </span>
                    </div>
                </div>
            </div>

            @if ($plan->motivo_desempate)
                <div class="votacion-veredicto-desempate">
                    <span class="votacion-veredicto-decision">Decisión del
                        organizador:</span>
                    <span>&laquo;{{ $plan->motivo_desempate }}&raquo;</span>
                </div>
            @endif
        </div>
    @endif

    <div class="votacion-encabezado">
        <div>
            <h2 class="votacion-titulo">
                Sala de Votación y Propuestas</h2>
            <p class="votacion-subtitulo">Elige entre fechas y
                lugares sugeridos o propón una nueva alternativa.</p>
        </div>

        <div class="votacion-acciones">
            @can('confirmar', $plan)
                <button type="button" class="boton votacion-boton votacion-boton-confirmar"
                    onclick="document.getElementById('modal-confirmar-plan').showModal()"
                    aria-label="Cerrar votación y confirmar">
                    <x-icono nombre="check" class="votacion-icono-inicio" />
                    Cerrar votación y confirmar
                </button>
            @endcan

            @can('proponer', $plan)
                <button type="button"
                    class="boton votacion-boton {{ Auth::user()->can('confirmar', $plan) ? 'boton-claro' : '' }}"
                    onclick="abrirModalConTipo('{{ $pestanaActiva === 'fechas' ? 'fecha' : 'lugar' }}')"
                    aria-label="Proponer nueva fecha o lugar">
                    + Proponer opción
                </button>
            @endcan
        </div>
    </div>

    {{-- Pestañas semánticas de Hick: n=2 alternativas equiprobables (Fechas vs Lugares) con formato píldora y resaltado cálido var(--color-brand) --}}
    <div class="votacion-pestanas" role="tablist" aria-label="Categorías de decisión colaborativa">

        <a href="{{ route('planes.detalle', ['plan' => $plan, 'tab' => 'fechas']) }}" role="tab"
            aria-selected="{{ $pestanaActiva === 'fechas' ? 'true' : 'false' }}"
            class="boton votacion-pestana {{ $pestanaActiva === 'fechas' ? 'votacion-pestana-activa' : 'boton-claro' }}">
            <x-icono nombre="calendario" class="votacion-icono-inicio" />
            Fechas propuestas ({{ $fechas->count() }})
        </a>

        <a href="{{ route('planes.detalle', ['plan' => $plan, 'tab' => 'lugares']) }}" role="tab"
            aria-selected="{{ $pestanaActiva === 'lugares' ? 'true' : 'false' }}"
            class="boton votacion-pestana {{ $pestanaActiva === 'lugares' ? 'votacion-pestana-activa' : 'boton-claro' }}">
            <x-icono nombre="ubicacion" class="votacion-icono-inicio" />
            Lugares propuestos ({{ $lugares->count() }})
        </a>

        <a href="{{ route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']) }}" role="tab"
            aria-selected="{{ $pestanaActiva === 'gastos' ? 'true' : 'false' }}"
            class="boton votacion-pestana {{ $pestanaActiva === 'gastos' ? 'votacion-pestana-activa' : 'boton-claro' }}">
            <x-icono nombre="dinero" class="votacion-icono-inicio" />
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
            <div class="votacion-vacio">
                <p class="votacion-vacio-texto">
                    No hay {{ $pestanaActiva === 'fechas' ? 'fechas propuestas' : 'lugares propuestos' }} todavía.
                </p>
                @can('proponer', $plan)
                    <button type="button" class="boton"
                        onclick="abrirModalConTipo('{{ $pestanaActiva === 'fechas' ? 'fecha' : 'lugar' }}')">
                        Sé el primero en proponer {{ $pestanaActiva === 'fechas' ? 'una fecha' : 'un lugar' }}
                    </button>
                @endcan
            </div>
        @else
            <div class="votacion-opciones">
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
                    <div class="tarjeta votacion-opcion {{ $haVotado ? 'votacion-opcion-votada' : '' }}">
                        <div>
                            <div class="votacion-opcion-cabecera">
                                <span class="votacion-opcion-valor">
                                    {{ $textoValor }}
                                </span>
                                <span
                                    class="badge-votos votacion-badge-votos {{ $haVotado ? 'votacion-badge-votos-propio' : '' }}">
                                    {{ $totalVotos }} {{ $totalVotos === 1 ? 'voto' : 'votos' }}
                                </span>
                            </div>

                            @if ($opcion->descripcion)
                                <p class="votacion-opcion-descripcion">
                                    {{ $opcion->descripcion }}
                                </p>
                            @endif

                            <div class="votacion-opcion-autor">
                                Propuesto por: <span
                                    class="votacion-opcion-autor-nombre">{{ $opcion->user->name ?? ($opcion->user->email ?? 'Colaborador') }}</span>
                            </div>
                        </div>

                        {{-- Botón de votación ergonómico (Fitts min-h-[44px]) o aviso accesible de votación cerrada --}}
                        @can('votar', $plan)
                            <form method="POST" action="{{ route('opciones.votar', $opcion) }}"
                                class="votacion-formulario-voto">
                                @csrf
                                <button type="submit"
                                    class="boton votacion-boton-voto {{ $haVotado ? 'boton-claro' : '' }}"
                                    aria-label="{{ $haVotado ? 'Retirar voto de ' . $textoValor : 'Votar por ' . $textoValor }}">
                                    @if ($haVotado)
                                        <x-icono nombre="check" class="votacion-icono-voto" />
                                        Votado (clic para retirar)
                                    @else
                                        Votar por esta opción
                                    @endif
                                </button>
                            </form>
                        @else
                            @if ($plan->estado === 'confirmado')
                                <div class="votacion-aviso-cerrado votacion-aviso-cerrado-confirmado">
                                    Votación cerrada (plan confirmado)
                                </div>
                            @elseif ($plan->estado === 'cancelado')
                                <div class="votacion-aviso-cerrado">
                                    Votación cerrada (plan cancelado)
                                </div>
                            @else
                                <div class="votacion-aviso-cerrado">
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
    <dialog id="modal-proponer-opcion" class="dialogo-md">
        <form method="POST" action="{{ route('planes.opciones.guardar', $plan) }}">
            @csrf
            <h2 class="votacion-dialogo-titulo">
                Proponer nueva opción</h2>
            <p class="votacion-dialogo-texto">Sugiere una
                fecha o un lugar para que el grupo lo vote.</p>

            <div class="campo">
                <label for="select-tipo-opcion">
                    Tipo de sugerencia
                </label>
                <select id="select-tipo-opcion" name="tipo" required onchange="actualizarInputPropuesta(this.value)">
                    <option value="fecha" {{ $pestanaActiva === 'fechas' ? 'selected' : '' }}>Fecha y hora</option>
                    <option value="lugar" {{ $pestanaActiva === 'lugares' ? 'selected' : '' }}>Lugar o destino</option>
                </select>
            </div>

            {{-- Campo estructurado fecha/hora con confirmación explícita (Nielsen H3 y H5 - Feedback UniSport) --}}
            <div id="contenedor-campo-fecha" class="campo {{ $pestanaActiva === 'lugares' ? 'votacion-oculto' : '' }}">
                <label for="input-fecha-opcion">
                    Fecha y hora propuesta
                </label>
                <div class="votacion-fecha-fila">
                    <input type="datetime-local" id="input-fecha-opcion" min="{{ now()->format('Y-m-d\TH:i') }}"
                        {{ $pestanaActiva === 'fechas' ? 'name="valor"' : '' }}
                        onchange="retenerFechaTemporal(this.value)">
                    <button type="button" id="btn-aceptar-fecha" class="boton boton-claro votacion-boton-aceptar-fecha"
                        onclick="confirmarFechaSeleccionada()">
                        <svg class="icono-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Aceptar fecha
                    </button>
                </div>
                <div id="feedback-fecha-confirmada" class="votacion-feedback-fecha">
                    <span class="votacion-feedback-etiqueta"><x-icono nombre="check" tamano="sm" /> Fecha
                        confirmada:</span>
                    <span id="texto-fecha-confirmada" class="votacion-feedback-valor"></span>
                </div>
                <p class="campo-ayuda">
                    Selecciona el momento exacto y pulsa «Aceptar fecha» para fijar tu elección.</p>
            </div>

            {{-- Campo texto para lugar o destino --}}
            <div id="contenedor-campo-lugar" class="campo {{ $pestanaActiva === 'fechas' ? 'votacion-oculto' : '' }}">
                <label for="input-lugar-opcion">
                    Lugar o destino propuesto
                </label>
                <input type="text" id="input-lugar-opcion" placeholder="Ej. Restaurante Central o Parque del Retiro"
                    {{ $pestanaActiva === 'lugares' ? 'name="valor"' : '' }}>
                <p class="campo-ayuda">Ingresa
                    la dirección o nombre del lugar recomendado.</p>
            </div>

            <div class="campo">
                <label for="input-descripcion-opcion">
                    Detalles adicionales (opcional)
                </label>
                <textarea id="input-descripcion-opcion" name="descripcion" rows="3"
                    placeholder="Información extra, enlaces o notas"></textarea>
            </div>

            {{-- Separación antagónica de al menos 12px (gap-3) según Ley de Fitts --}}
            <div class="dialogo-botones-derecha">
                <button type="button" class="boton boton-claro boton-simetrico"
                    onclick="document.getElementById('modal-proponer-opcion').close()">
                    Cancelar
                </button>
                <button type="submit" class="boton boton-simetrico">
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
                feedbackDiv.classList.add('votacion-visible');
            }
        }

        function actualizarInputPropuesta(tipo) {
            var contenedorFecha = document.getElementById('contenedor-campo-fecha');
            var inputFecha = document.getElementById('input-fecha-opcion');
            var contenedorLugar = document.getElementById('contenedor-campo-lugar');
            var inputLugar = document.getElementById('input-lugar-opcion');

            if (tipo === 'fecha') {
                if (contenedorFecha) contenedorFecha.classList.remove('votacion-oculto');
                if (inputFecha) {
                    inputFecha.setAttribute('name', 'valor');
                    inputFecha.required = true;
                    if (fechaSeleccionadaRetenida && !inputFecha.value) {
                        inputFecha.value = fechaSeleccionadaRetenida;
                    }
                }
                if (contenedorLugar) contenedorLugar.classList.add('votacion-oculto');
                if (inputLugar) {
                    inputLugar.removeAttribute('name');
                    inputLugar.required = false;
                }
            } else {
                if (contenedorFecha) contenedorFecha.classList.add('votacion-oculto');
                if (inputFecha) {
                    inputFecha.removeAttribute('name');
                    inputFecha.required = false;
                }
                if (contenedorLugar) contenedorLugar.classList.remove('votacion-oculto');
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
    <dialog id="modal-confirmar-plan" class="dialogo-lg">
        <form method="POST" action="{{ route('planes.confirmar', $plan) }}">
            @csrf
            <h2 class="votacion-dialogo-titulo">
                Confirmar Plan y Cerrar Votación
            </h2>
            <p class="votacion-dialogo-texto">
                Al confirmar el plan, se cerrará la votación para todos los colaboradores y se formalizará la fecha y lugar
                definitivos.
            </p>

            {{-- Bloque Selección de Fecha --}}
            <div class="campo">
                <label>
                    <x-icono nombre="calendario" tamano="sm" class="votacion-icono-inicio" />
                    Fecha definitiva
                </label>
                @if ($candidatosFecha->isEmpty())
                    <div class="votacion-nota-vacia">
                        No hay fechas propuestas en la sala (se conservará la fecha límite del plan).
                    </div>
                @elseif ($empateFechas)
                    <div class="votacion-nota-empate">
                        <x-icono nombre="balanza" class="votacion-icono-aviso" />
                        Existe un empate en las fechas más votadas. Elige la opción ganadora:
                    </div>
                    <select name="propuesta_fecha_id" required>
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
                    <div class="votacion-ganador">
                        <span class="votacion-ganador-texto"><x-icono nombre="check" tamano="sm" /> Ganador
                            indiscutido:
                            {{ $textoGanadorF }}</span>
                        <span class="votacion-ganador-votos">
                            {{ $ganadorF->votos->count() }} votos
                        </span>
                    </div>
                @endif
            </div>

            {{-- Bloque Selección de Lugar --}}
            <div class="campo">
                <label>
                    <x-icono nombre="ubicacion" tamano="sm" class="votacion-icono-inicio" />
                    Lugar o destino definitivo
                </label>
                @if ($candidatosLugar->isEmpty())
                    <div class="votacion-nota-vacia">
                        No hay lugares propuestos en la sala (se conservará el lugar registrado en el plan).
                    </div>
                @elseif ($empateLugares)
                    <div class="votacion-nota-empate">
                        <x-icono nombre="balanza" class="votacion-icono-aviso" />
                        Existe un empate en los lugares más votados. Elige la opción ganadora:
                    </div>
                    <select name="propuesta_lugar_id" required>
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
                    <div class="votacion-ganador">
                        <span class="votacion-ganador-texto"><x-icono nombre="check" tamano="sm" /> Ganador
                            indiscutido:
                            {{ $ganadorL->valor }}</span>
                        <span class="votacion-ganador-votos">
                            {{ $ganadorL->votos->count() }} votos
                        </span>
                    </div>
                @endif
            </div>

            {{-- Campo Motivo de Desempate (obligatorio si hay empate, opcional si no) --}}
            <div class="campo">
                <label for="motivo_desempate">
                    Motivo del desempate {{ $hayEmpate ? '(obligatorio)' : '(opcional)' }}
                </label>
                <textarea id="motivo_desempate" name="motivo_desempate" rows="3" minlength="5"
                    placeholder="{{ $hayEmpate ? 'Explica a los colaboradores el motivo por el cual elegiste esta opción...' : 'Comentario o aclaración para los colaboradores (opcional)' }}"
                    {{ $hayEmpate ? 'required' : '' }}></textarea>
                @if ($hayEmpate)
                    <p class="campo-ayuda votacion-ayuda-empate">
                        Debido al empate, se requiere registrar una justificación clara (mínimo 5 caracteres) visible
                        públicamente.
                    </p>
                @endif
            </div>

            {{-- Separación antagónica de botones >= 12px (gap-3, Ley de Fitts) --}}
            <div class="dialogo-botones-derecha">
                <button type="button" class="boton boton-claro boton-simetrico"
                    onclick="document.getElementById('modal-confirmar-plan').close()">
                    Cancelar
                </button>
                <button type="submit" class="boton boton-simetrico votacion-boton-confirmar">
                    Confirmar y cerrar votación
                </button>
            </div>
        </form>
    </dialog>
@endcan

<div class="gastos-seccion">

    {{-- Fitts: touch targets (min-height: var(--touch-min), min-width: var(--touch-min)) and antagonistic
         action spacing (gap: var(--space-3)) are enforced by the gastos-* classes in the stylesheet fragment. --}}

    {{-- Banner de métricas generales y balance personal --}}
    @php
        $miId = auth()->id();
        $miUsuario = auth()->user();
        $miBalance = $saldos['balances'][$miId] ?? null;
        $misDeudas = collect($saldos['deudas'])->where('deudor_id', $miId)->values();
        $misCobros = collect($saldos['deudas'])->where('acreedor_id', $miId)->values();
        $totalNeto = $miBalance['neto'] ?? 0.0;
        $tonoBalance =
            $totalNeto > 0 ? 'gastos-tono-exito' : ($totalNeto < 0 ? 'gastos-tono-peligro' : 'gastos-tono-neutro');
        $participantesPlan = collect([$plan->user])
            ->merge($plan->participantes)
            ->unique('id')
            ->values();
        $deudasPorAcreedor = $misDeudas->mapWithKeys(function ($d) {
            return [$d['acreedor_id'] => (float) $d['monto']];
        });
        $datosCobroParticipantes = $participantesPlan->mapWithKeys(function ($u) {
            return [
                $u->id => [
                    'nombre' => $u->name,
                    'alias' => $u->alias_pago,
                    'cbu' => $u->cbu_pago,
                    'qr_url' => $u->qr_pago_path ? asset('storage/' . $u->qr_pago_path) : null,
                ],
            ];
        });
    @endphp

    <div class="gastos-resumen">
        {{-- Tarjeta 1: Total gastado en el grupo --}}
        <div class="gastos-resumen-tarjeta">
            <div class="gastos-resumen-etiqueta">
                Total Gastado en el Plan
            </div>
            <div class="gastos-resumen-valor">
                Bs {{ number_format($saldos['total_gastado'], 2) }}
            </div>
            <div class="gastos-resumen-nota">
                {{ $gastos->count() }} {{ $gastos->count() === 1 ? 'gasto registrado' : 'gastos registrados' }}
            </div>
        </div>

        {{-- Tarjeta 2: Tu posición neta --}}
        <div class="gastos-resumen-tarjeta {{ $tonoBalance }}">
            <div class="gastos-resumen-etiqueta">
                Tu Balance Neto
            </div>
            <div class="gastos-resumen-valor">
                {{ $totalNeto > 0 ? '+Bs ' : ($totalNeto < 0 ? '-Bs ' : 'Bs ') }}{{ number_format(abs($totalNeto), 2) }}
            </div>
            <div class="gastos-resumen-nota">
                @if ($totalNeto > 0)
                    Te deben dinero en el consolidado del plan
                @elseif ($totalNeto < 0)
                    Tienes saldo pendiente por pagar
                @else
                    Estás al día con el grupo
                @endif
            </div>
        </div>

        {{-- Tarjeta 3: Acciones rápidas --}}
        <div class="gastos-resumen-tarjeta gastos-resumen-tarjeta--acciones">
            @can('create', [\App\Models\Gasto::class, $plan])
                <button type="button" class="boton gastos-boton gastos-boton--bloque"
                    onclick="if (window.track) { window.track('gasto_iniciado', { plan_id: {{ $plan->id }} }); } document.getElementById('modal-crear-gasto').showModal()"
                    aria-label="Registrar nuevo gasto">
                    + Registrar Gasto
                </button>
            @endcan

            @can('create', [\App\Models\Pago::class, $plan])
                <button type="button" class="boton boton-claro gastos-boton gastos-boton--bloque"
                    onclick="abrirModalPagoDirecto('', '')" aria-label="Registrar pago a un compañero">
                    <x-icono nombre="pago" class="gastos-icono-base" />
                    Registrar Pago
                </button>
            @endcan

            <button type="button" class="boton boton-claro gastos-boton gastos-boton--bloque"
                onclick="document.getElementById('modal-configurar-cobro').showModal()"
                aria-label="Configurar mis datos de cobro y QR">
                <x-icono nombre="editar" class="gastos-icono-sm-previo" />
                Mis Datos de Cobro
            </button>
        </div>
    </div>

    {{-- Matriz Clara de Deudas (Hick & Fitts): Debes vs Te deben --}}
    <div class="gastos-matriz">
        {{-- Columna 1: Cuentas por Pagar (Debes) --}}
        <div class="gastos-panel">
            <div class="gastos-columna-cabecera">
                <x-icono nombre="pago" class="gastos-icono-peligro" />
                <h3 class="gastos-titulo">
                    Debes a tus compañeros</h3>
            </div>

            @if ($misDeudas->isEmpty())
                <div class="gastos-vacio gastos-vacio--con-icono">
                    <x-icono nombre="check" class="gastos-icono-sm gastos-icono-exito" />
                    <span>No tienes deudas pendientes con ningún participante.</span>
                </div>
            @else
                <div class="gastos-lista">
                    @foreach ($misDeudas as $deuda)
                        <div class="gastos-deuda gastos-tono-peligro">
                            <div>
                                <div class="gastos-deuda-monto">
                                    Bs {{ number_format($deuda['monto'], 2) }}
                                </div>
                                <div class="gastos-deuda-detalle">
                                    Acreedor: <span
                                        class="gastos-enfasis">{{ $deuda['acreedor']->name ?? 'Compañero' }}</span>
                                </div>
                            </div>
                            @can('create', [\App\Models\Pago::class, $plan])
                                <button type="button" class="boton gastos-boton gastos-boton--compacto"
                                    onclick="abrirModalPagoDirecto('{{ $deuda['acreedor_id'] }}', '{{ $deuda['monto'] }}')"
                                    aria-label="Pagar Bs {{ number_format($deuda['monto'], 2) }} a {{ $deuda['acreedor']->name }}">
                                    Pagar deuda
                                </button>
                            @endcan
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Columna 2: Cuentas por Cobrar (Te deben) --}}
        <div class="gastos-panel">
            <div class="gastos-columna-cabecera">
                <x-icono nombre="dinero" class="gastos-icono-exito" />
                <h3 class="gastos-titulo">
                    Te deben tus compañeros</h3>
            </div>

            @if ($misCobros->isEmpty())
                <div class="gastos-vacio">
                    Ningún participante tiene deudas directas contigo actualmente.
                </div>
            @else
                <div class="gastos-lista">
                    @foreach ($misCobros as $cobro)
                        <div class="gastos-deuda gastos-tono-exito">
                            <div>
                                <div class="gastos-deuda-monto">
                                    Bs {{ number_format($cobro['monto'], 2) }}
                                </div>
                                <div class="gastos-deuda-detalle">
                                    Deudor: <span
                                        class="gastos-enfasis">{{ $cobro['deudor']->name ?? 'Compañero' }}</span>
                                </div>
                            </div>
                            <span class="gastos-etiqueta-pendiente">
                                Pendiente de pago
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Sección de Pagos y Liquidaciones Pendientes (CA-03 y CA-04) --}}
    <div class="gastos-panel gastos-panel--separado">
        <div class="gastos-panel-cabecera">
            <div>
                <h3 class="gastos-titulo gastos-titulo--seccion">
                    Pagos y Liquidaciones</h3>
                <p class="gastos-descripcion">
                    Los pagos registrados quedan en estado pendiente hasta la confirmación explícita del receptor.
                </p>
            </div>
        </div>

        @if ($pagos->isEmpty())
            <div class="gastos-vacio gastos-vacio--pagos">
                No se han registrado transferencias ni pagos entre participantes todavía.
            </div>
        @else
            <div class="gastos-lista">
                @foreach ($pagos as $pago)
                    @php
                        $soyReceptor = (int) auth()->id() === (int) $pago->receptor_id;
                        $soyDeudor = (int) auth()->id() === (int) $pago->deudor_id;
                        $tonoPago =
                            $pago->estado === 'confirmado'
                                ? 'gastos-tono-exito'
                                : ($pago->estado === 'rechazado'
                                    ? 'gastos-tono-peligro'
                                    : 'gastos-tono-alerta');
                    @endphp
                    <div class="gastos-pago {{ $tonoPago }}">
                        <div>
                            <div class="gastos-pago-resumen">
                                <span class="gastos-pago-monto">
                                    Bs {{ number_format($pago->monto, 2) }}
                                </span>
                                <span class="badge-estado-pago gastos-pago-estado">
                                    {{ $pago->estado }}
                                </span>
                            </div>
                            <div class="gastos-pago-partes">
                                De <span class="gastos-enfasis">{{ $pago->deudor->name ?? 'Usuario' }}</span>
                                para <span class="gastos-enfasis">{{ $pago->receptor->name ?? 'Usuario' }}</span>
                                · {{ $pago->created_at->format('d/m/Y H:i') }}
                            </div>
                            @if ($pago->tieneComprobante())
                                <div class="gastos-pago-comprobante">
                                    <button type="button" class="boton boton-claro boton-xs gastos-boton--ver"
                                        onclick="document.getElementById('modal-comprobante-{{ $pago->id }}').showModal()"
                                        aria-label="Ver comprobante adjunto del pago {{ $pago->id }}">
                                        <x-icono nombre="pago" class="gastos-icono-mini" />
                                        <span>Ver Comprobante</span>
                                    </button>

                                    @if ($pago->esEscaneoVerificado())
                                        <span class="badge-escaneo verificado gastos-escaneo gastos-tono-exito">
                                            <x-icono nombre="check" class="gastos-icono-micro" />
                                            {{-- prettier-ignore --}}
                                            <span>Monto Verificado: Bs {{ number_format($pago->monto_detectado, 2) }}{{ $pago->banco_detectado ? ' (' . $pago->banco_detectado . ')' : '' }}</span>
                                        </span>
                                    @elseif ($pago->esEscaneoDiscrepante())
                                        <span class="badge-escaneo discrepante gastos-escaneo gastos-tono-peligro">
                                            <x-icono nombre="bloqueado" class="gastos-icono-micro" />
                                            {{-- prettier-ignore --}}
                                            <span>Alerta: Comprobante indica Bs {{ number_format($pago->monto_detectado, 2) }}</span>
                                        </span>
                                    @elseif ($pago->esEscaneoInvalido())
                                        <span
                                            class="badge-escaneo invalido gastos-escaneo gastos-escaneo--tenue gastos-tono-alerta">
                                            Documento no concluyente
                                        </span>
                                    @endif
                                </div>

                                {{-- Modal para ampliar y revisar el comprobante --}}
                                <dialog id="modal-comprobante-{{ $pago->id }}" class="dialogo-md">
                                    <div class="gastos-dialogo-cabecera">
                                        <h3 class="gastos-titulo">
                                            Comprobante de Pago</h3>
                                        <button type="button"
                                            onclick="document.getElementById('modal-comprobante-{{ $pago->id }}').close()"
                                            class="gastos-dialogo-cerrar" aria-label="Cerrar modal">&times;</button>
                                    </div>

                                    @if ($pago->monto_detectado && abs((float) $pago->monto_detectado - (float) $pago->monto) < 0.01)
                                        <div class="badge-ia-verificado gastos-aviso-ia">
                                            <x-icono nombre="check" class="gastos-icono-sm gastos-icono-exito" />
                                            {{-- prettier-ignore --}}
                                            <span>Comprobante verificado por IA: Monto coincide (Bs {{ number_format($pago->monto, 2) }})</span>
                                        </div>
                                    @endif

                                    <div class="gastos-comprobante-vista">
                                        <img src="{{ asset('storage/' . $pago->comprobante_path) }}"
                                            alt="Comprobante de pago" class="gastos-comprobante-img">
                                    </div>
                                    <div class="gastos-comprobante-datos">
                                        <div><strong>Monto registrado:</strong> Bs {{ number_format($pago->monto, 2) }}
                                        </div>
                                        @if ($pago->monto_detectado)
                                            <div><strong>Monto detectado por escáner:</strong> Bs
                                                {{ number_format($pago->monto_detectado, 2) }}</div>
                                        @endif
                                        @if ($pago->banco_detectado)
                                            <div><strong>Banco emisor:</strong> {{ $pago->banco_detectado }}</div>
                                        @endif
                                    </div>
                                    <div class="gastos-acciones gastos-acciones--arriba-4">
                                        <button type="button" class="boton boton-claro gastos-boton"
                                            onclick="document.getElementById('modal-comprobante-{{ $pago->id }}').close()">Cerrar</button>
                                        @if ($pago->estado === 'pendiente')
                                            @can('confirmar', $pago)
                                                <form method="POST" action="{{ route('pagos.confirmar', $pago) }}"
                                                    class="form-reset">
                                                    @csrf
                                                    <button type="submit"
                                                        class="boton gastos-boton gastos-boton--compacto gastos-boton--ancho gastos-boton--exito gastos-boton--con-icono"
                                                        aria-label="Confirmar pago verificado de Bs {{ number_format($pago->monto, 2) }}">
                                                        <x-icono nombre="check" class="gastos-icono-mini" />
                                                        <span>Confirmar pago en 1 clic</span>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                    </div>
                                </dialog>
                            @endif
                            @if ($pago->motivo_rechazo)
                                <div class="gastos-motivo-rechazo">
                                    <span class="gastos-enfasis">Motivo de rechazo:</span>
                                    &laquo;{{ $pago->motivo_rechazo }}&raquo;
                                </div>
                            @endif
                        </div>

                        {{-- Botones de acción del receptor protegidos con Policy (Fitts >= 44px) --}}
                        <div class="gastos-pago-acciones">
                            @if ($pago->estado === 'pendiente')
                                @can('confirmar', $pago)
                                    <form method="POST" action="{{ route('pagos.confirmar', $pago) }}"
                                        class="form-reset">
                                        @csrf
                                        <button type="submit"
                                            class="boton gastos-boton gastos-boton--compacto gastos-boton--exito gastos-boton--con-icono"
                                            aria-label="Confirmar pago de Bs {{ number_format($pago->monto, 2) }}">
                                            <x-icono nombre="check" class="gastos-icono-mini" />
                                            <span>Confirmar pago</span>
                                        </button>
                                    </form>

                                    <button type="button"
                                        class="boton boton-claro gastos-boton gastos-boton--compacto gastos-boton--peligro-suave gastos-boton--con-icono"
                                        onclick="document.getElementById('modal-rechazar-{{ $pago->id }}').showModal()"
                                        aria-label="Rechazar pago de Bs {{ number_format($pago->monto, 2) }}">
                                        <x-icono nombre="bloqueado" class="gastos-icono-mini" />
                                        <span>Rechazar</span>
                                    </button>
                                @else
                                    <span class="gastos-pago-espera">
                                        Esperando confirmación del receptor
                                    </span>
                                @endcan
                            @elseif ($pago->estado === 'confirmado')
                                <span class="gastos-pago-liquidado">
                                    <x-icono nombre="check" class="gastos-icono-mini" />
                                    <span>Liquidado el {{ $pago->fecha_resolucion?->format('d/m/Y') }}</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Modal nativo de rechazo para este pago --}}
                    @can('rechazar', $pago)
                        <dialog id="modal-rechazar-{{ $pago->id }}" class="dialogo-md">
                            <form method="POST" action="{{ route('pagos.rechazar', $pago) }}">
                                @csrf
                                <h3 class="gastos-dialogo-titulo gastos-dialogo-titulo--rechazo">
                                    Rechazar Pago</h3>
                                <p class="gastos-dialogo-texto gastos-dialogo-texto--corto">
                                    Indica al deudor la razón por la cual no aceptas este pago (ej. no recibido en tu banco
                                    o monto incorrecto).
                                </p>
                                <div class="campo">
                                    <label for="motivo-rechazo-{{ $pago->id }}">
                                        Motivo del rechazo (obligatorio)
                                    </label>
                                    <textarea id="motivo-rechazo-{{ $pago->id }}" name="motivo_rechazo" rows="3" required minlength="5"
                                        placeholder="Explica detalladamente por qué rechazas este pago..."></textarea>
                                </div>
                                <div class="gastos-acciones">
                                    <button type="button" class="boton boton-claro gastos-boton"
                                        onclick="document.getElementById('modal-rechazar-{{ $pago->id }}').close()">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="boton gastos-boton gastos-boton--peligro">
                                        Confirmar Rechazo
                                    </button>
                                </div>
                            </form>
                        </dialog>
                    @endcan
                @endforeach
            </div>
        @endif
    </div>

    {{-- Listado de Gastos Registrados --}}
    <div class="gastos-panel">
        <div class="gastos-panel-cabecera">
            <div>
                <h3 class="gastos-titulo gastos-titulo--seccion">
                    Historial de Gastos</h3>
                <p class="gastos-descripcion">Detalle de consumos y
                    cuotas asignadas a cada participante.</p>
            </div>
        </div>

        @if ($gastos->isEmpty())
            <div class="gastos-vacio gastos-vacio--gastos">
                <p class="gastos-vacio-texto">No hay gastos registrados en
                    este plan todavía.</p>
                @can('create', [\App\Models\Gasto::class, $plan])
                    <button type="button" class="boton gastos-boton gastos-boton--ancho"
                        onclick="if (window.track) { window.track('gasto_iniciado', { plan_id: {{ $plan->id }} }); } document.getElementById('modal-crear-gasto').showModal()">
                        + Registrar el primer gasto
                    </button>
                @endcan
            </div>
        @else
            <div class="gastos-historial">
                @foreach ($gastos as $gasto)
                    <div class="gastos-gasto">
                        <div>
                            <div class="gastos-gasto-cabecera">
                                <span class="gastos-gasto-concepto">
                                    {{ $gasto->concepto }}
                                </span>
                                <span class="gastos-gasto-monto">
                                    Bs {{ number_format($gasto->monto_total, 2) }}
                                </span>
                            </div>
                            <div class="gastos-gasto-meta">
                                Pagado por: <span
                                    class="gastos-nombre">{{ $gasto->pagador->name ?? 'Usuario' }}</span>
                                <span class="gastos-gasto-separador">·</span>
                                <span class="gastos-gasto-tipo">{{ $gasto->tipo_division }}</span>
                            </div>

                            <div class="gastos-gasto-participantes">
                                <div class="gastos-gasto-participantes-titulo">
                                    Participantes ({{ $gasto->divisiones->count() }}):</div>
                                <div class="gastos-gasto-cuotas">
                                    @foreach ($gasto->divisiones as $div)
                                        <span class="gastos-gasto-cuota">
                                            {{ $div->user->name ?? 'Usuario' }}: <strong>Bs
                                                {{ number_format($div->monto_asignado, 2) }}</strong>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="gastos-gasto-fecha">
                            {{ $gasto->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

{{-- Modal accesible para Registrar Gasto con Alternancia Dinámica (Hick & Fitts) --}}
@can('create', [\App\Models\Gasto::class, $plan])
    <dialog id="modal-crear-gasto" class="dialogo-lg">
        <form method="POST" action="{{ route('planes.gastos.guardar', $plan) }}">
            @csrf
            <h2 class="gastos-dialogo-titulo">
                Registrar Nuevo Gasto</h2>
            <p class="gastos-dialogo-texto">Anota el
                consumo realizado y cómo se reparte entre los participantes.</p>

            <div class="campo">
                <label for="input-gasto-concepto">
                    Concepto o descripción
                </label>
                <input type="text" id="input-gasto-concepto" name="concepto" required
                    placeholder="Ej. Cena, Supermercado, Alquiler de cabaña">
            </div>

            <div class="campo">
                <label for="input-gasto-monto">
                    Monto total (Bs)
                </label>
                <input type="number" id="input-gasto-monto" name="monto_total" step="0.01" min="0.01" required
                    placeholder="0.00" oninput="recalcularDivision()">
            </div>

            <div class="campo">
                <label for="select-tipo-division">
                    Modo de división
                </label>
                <select id="select-tipo-division" name="tipo_division" required
                    onchange="cambiarModoDivision(this.value)">
                    <option value="equitativa">En partes iguales (Equitativa)</option>
                    <option value="personalizada">Monto personalizado por persona</option>
                </select>
            </div>

            <div class="gastos-grupo">
                <label class="gastos-grupo-etiqueta">
                    Participantes incluidos
                </label>
                <p class="gastos-grupo-ayuda">
                    Selecciona explícitamente a las personas que asumirán este gasto (opt-in consciente).
                </p>
                <div class="gastos-participantes-lista">
                    @foreach ($participantesPlan as $p)
                        <div class="gastos-participante">
                            <label class="gastos-participante-etiqueta">
                                <input type="checkbox" name="participantes[]" value="{{ $p->id }}"
                                    class="check-participante" onchange="recalcularDivision()">
                                <span>{{ $p->name }}</span>
                            </label>
                            <div class="campo contenedor-monto-personalizado gastos-oculto">
                                <input type="number" step="0.01" min="0"
                                    name="divisiones[{{ $p->id }}]" placeholder="0.00" class="input-monto-indiv"
                                    oninput="actualizarSumaPersonalizada()">
                            </div>
                        </div>
                    @endforeach
                </div>
                <div id="indicador-participantes-seleccionados" class="gastos-contador">
                    Participantes seleccionados: <strong id="contador-seleccionados">0</strong> de
                    {{ count($participantesPlan) }}
                </div>
                <div id="aviso-suma-personalizada" class="gastos-oculto gastos-aviso-suma">
                </div>
            </div>

            <div class="gastos-acciones gastos-acciones--arriba-6">
                <button type="button" class="boton boton-claro gastos-boton"
                    onclick="document.getElementById('modal-crear-gasto').close()">
                    Cancelar
                </button>
                <button type="submit" class="boton gastos-boton gastos-boton--exito">
                    Guardar Gasto
                </button>
            </div>
        </form>
    </dialog>
@endcan

{{-- Modal accesible para Registrar Pago --}}
@can('create', [\App\Models\Pago::class, $plan])
    <dialog id="modal-crear-pago" class="dialogo-md">
        <form id="form-crear-pago" method="POST" action="{{ route('planes.pagos.guardar', $plan) }}"
            enctype="multipart/form-data">
            @csrf
            <h2 class="gastos-dialogo-titulo">
                Registrar Pago</h2>
            <p class="gastos-dialogo-texto">
                Anota una transferencia o entrega de dinero. Puedes adjuntar tu comprobante para acelerar la verificación.
            </p>

            <div class="campo">
                <label for="select-pago-receptor">
                    ¿A quién le pagas?
                </label>
                <select id="select-pago-receptor" name="receptor_id" required
                    onchange="actualizarDatosReceptor(this.value)">
                    <option value="">-- Selecciona el destinatario --</option>
                    @foreach ($participantesPlan as $p)
                        @if ($p->id !== auth()->id())
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            {{-- Panel reactivo con datos de cobro del receptor --}}
            <div id="contenedor-datos-receptor" class="gastos-oculto gastos-receptor">
                <div class="gastos-receptor-titulo">
                    Datos para transferir al receptor
                </div>
                <div id="texto-alias-receptor" class="gastos-receptor-dato">
                </div>
                <div id="texto-cbu-receptor" class="gastos-receptor-dato">
                </div>
                <div id="contenedor-qr-receptor" class="gastos-oculto gastos-receptor-qr">
                    <span class="gastos-receptor-qr-etiqueta">Código
                        QR para escanear:</span>
                    <img id="img-qr-receptor" src="" alt="QR del receptor" class="gastos-receptor-qr-img">
                </div>
                <div id="aviso-sin-cobro" class="gastos-oculto gastos-receptor-aviso">
                    El destinatario no ha configurado Alias o QR de cobro.
                </div>
            </div>

            {{-- Selector ergonómico de intención (Ley de Hick & Fitts): Pago Total vs Parcial --}}
            <div id="selector-modalidad-pago" class="gastos-modalidad-grupo">
                <label class="gastos-grupo-etiqueta gastos-grupo-etiqueta--amplia">
                    Modalidad de pago
                </label>
                <div role="radiogroup" aria-label="Seleccionar modalidad de pago" class="gastos-modalidad-opciones">
                    <label id="label-pago-total" class="gastos-modalidad">
                        <input type="radio" name="modalidad_pago" id="radio-pago-total" value="total" required
                            onchange="cambiarModalidadPago('total')">
                        <span id="texto-radio-pago-total">Pagar totalidad (Bs 0.00)</span>
                    </label>

                    <label id="label-pago-parcial" class="gastos-modalidad">
                        <input type="radio" name="modalidad_pago" id="radio-pago-parcial" value="parcial" required
                            onchange="cambiarModalidadPago('parcial')">
                        <span>Pago parcial</span>
                    </label>
                </div>
                <div id="aviso-pago-modalidad" class="gastos-modalidad-aviso">
                    Elige cómo quieres pagar: la totalidad de tu deuda o un monto parcial.
                </div>
            </div>

            <div class="campo">
                <label for="input-pago-monto">
                    Monto a pagar (Bs)
                </label>
                <input type="number" id="input-pago-monto" name="monto" step="0.01" min="0.01" required
                    readonly oninput="verificarMontoParcial(this.value)" placeholder="0.00"
                    class="gastos-input-protegido">
                <div id="aviso-saldo-restante" class="gastos-oculto gastos-aviso-saldo"></div>
            </div>

            <div class="campo">
                <label for="input-pago-comprobante">
                    Comprobante de Transferencia (Opcional - Imagen)
                </label>
                <input type="file" id="input-pago-comprobante" name="comprobante"
                    accept="image/png, image/jpeg, image/jpg, image/webp" onchange="previsualizarComprobante(this)">
                <p class="campo-ayuda">
                    El escaneo inteligente procesará la imagen para extraer el monto y banco emisor.
                </p>
                <div id="preview-comprobante-contenedor" class="gastos-oculto gastos-vista-previa">
                    <img id="img-preview-comprobante" src="" alt="Vista previa" class="gastos-vista-previa-img">
                </div>
            </div>

            {{-- Banner de feedback durante el procesamiento (Nielsen H1) --}}
            <div id="aviso-procesando-pago" class="gastos-oculto gastos-aviso-proceso" role="status"
                aria-live="polite">
                <x-icono nombre="spinner" clases="w-5 h-5 flex-shrink-0" />
                <div>
                    <strong>Procesando tu pago...</strong>
                    <div class="gastos-aviso-proceso-detalle">
                        Estamos registrando la transferencia y analizando el comprobante. Por favor, no cierres esta
                        ventana.
                    </div>
                </div>
            </div>

            <div class="gastos-acciones">
                <button type="button" id="btn-cancelar-pago" class="boton boton-claro gastos-boton"
                    onclick="document.getElementById('modal-crear-pago').close()">
                    Cancelar
                </button>
                <button type="submit" id="btn-submit-pago"
                    class="boton gastos-boton gastos-boton--exito gastos-boton--envio">
                    <span id="spinner-submit-pago" class="gastos-oculto">
                        <x-icono nombre="spinner" clases="w-4 h-4" />
                    </span>
                    <span id="texto-btn-submit-pago">Enviar Pago</span>
                </button>
            </div>
        </form>
    </dialog>
@endcan

{{-- Modal para configurar datos de cobro personales (Alias, CBU, QR) --}}
<dialog id="modal-configurar-cobro" class="dialogo-md">
    <form method="POST" action="{{ route('perfil.cobro.actualizar') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <h2 class="gastos-dialogo-titulo">
            Mis Datos de Cobro</h2>
        <p class="gastos-dialogo-texto">
            Configura tu Alias, CBU o sube tu código QR para que tus compañeros puedan transferirte fácilmente.
        </p>

        <div class="campo">
            <label for="input-perfil-alias">
                Alias de Cobro (ej. banco o billetera móvil)
            </label>
            <input type="text" id="input-perfil-alias" name="alias_pago"
                value="{{ old('alias_pago', $miUsuario->alias_pago) }}" placeholder="mi.alias.pago">
        </div>

        <div class="campo">
            <label for="input-perfil-cbu">
                CBU / N° de Cuenta Bancaria
            </label>
            <input type="text" id="input-perfil-cbu" name="cbu_pago"
                value="{{ old('cbu_pago', $miUsuario->cbu_pago) }}" placeholder="10000000000000">
        </div>

        <div class="campo">
            <label for="input-perfil-qr">
                Imagen de Código QR (Simple / Billetera)
            </label>
            <input type="file" id="input-perfil-qr" name="qr_pago" accept="image/*">
            @if ($miUsuario->qr_pago_path)
                <div class="gastos-qr-actual">
                    <span class="gastos-qr-actual-etiqueta">QR
                        configurado actualmente:</span>
                    <img src="{{ asset('storage/' . $miUsuario->qr_pago_path) }}" alt="QR actual"
                        class="gastos-qr-actual-img">
                </div>
            @endif
        </div>

        <div class="gastos-acciones">
            <button type="button" class="boton boton-claro gastos-boton"
                onclick="document.getElementById('modal-configurar-cobro').close()">
                Cancelar
            </button>
            <button type="submit" class="boton gastos-boton gastos-boton--exito">
                Guardar Datos
            </button>
        </div>
    </form>
</dialog>

<script>
    var datosCobroParticipantes = @json($datosCobroParticipantes);
    var deudasPorAcreedor = @json($deudasPorAcreedor);
    var deudaSeleccionadaActual = 0.0;

    function abrirModalPagoDirecto(receptorId, montoSugerido) {
        var modal = document.getElementById('modal-crear-pago');
        if (!modal) return;

        resetearEstadoModalPago();

        var select = document.getElementById('select-pago-receptor');
        var inputMonto = document.getElementById('input-pago-monto');
        var radioTotal = document.getElementById('radio-pago-total');
        var textoRadioTotal = document.getElementById('texto-radio-pago-total');

        if (select && receptorId) {
            select.value = receptorId;
            actualizarDatosReceptor(receptorId);
        } else if (select) {
            actualizarDatosReceptor(select.value);
        }

        if (montoSugerido && parseFloat(montoSugerido) > 0) {
            deudaSeleccionadaActual = parseFloat(montoSugerido);
        } else if (receptorId && deudasPorAcreedor[receptorId]) {
            deudaSeleccionadaActual = parseFloat(deudasPorAcreedor[receptorId]);
        } else {
            deudaSeleccionadaActual = 0.0;
        }

        if (textoRadioTotal) {
            textoRadioTotal.textContent = 'Pagar totalidad (Bs ' + deudaSeleccionadaActual.toFixed(2) + ')';
        }

        // Sin preselección: la persona elige la modalidad de forma consciente (patrón oscuro 4.1).
        establecerModalidadNeutral();

        modal.showModal();
    }

    /**
     * Estado inicial del modal de pago: ninguna modalidad elegida, monto vacío y bloqueado
     * hasta que la persona decida entre pagar la totalidad o un monto parcial.
     */
    function establecerModalidadNeutral() {
        var radioTotal = document.getElementById('radio-pago-total');
        var radioParcial = document.getElementById('radio-pago-parcial');
        var inputMonto = document.getElementById('input-pago-monto');
        var avisoModalidad = document.getElementById('aviso-pago-modalidad');
        var avisoSaldo = document.getElementById('aviso-saldo-restante');

        if (radioTotal) radioTotal.checked = false;
        if (radioParcial) radioParcial.checked = false;

        ['label-pago-total', 'label-pago-parcial'].forEach(function(id) {
            var etiqueta = document.getElementById(id);
            if (etiqueta) {
                etiqueta.classList.remove('gastos-modalidad--activa');
            }
        });

        if (inputMonto) {
            inputMonto.value = '';
            inputMonto.readOnly = true;
        }
        if (avisoModalidad) {
            avisoModalidad.textContent = 'Elige cómo quieres pagar: la totalidad de tu deuda o un monto parcial.';
            avisoModalidad.classList.remove('gastos-modalidad-aviso--total', 'gastos-modalidad-aviso--parcial');
        }
        if (avisoSaldo) {
            avisoSaldo.style.display = 'none';
        }
    }

    function cambiarModalidadPago(modalidad) {
        var inputMonto = document.getElementById('input-pago-monto');
        var labelTotal = document.getElementById('label-pago-total');
        var labelParcial = document.getElementById('label-pago-parcial');
        var avisoModalidad = document.getElementById('aviso-pago-modalidad');
        var avisoSaldo = document.getElementById('aviso-saldo-restante');

        if (modalidad === 'total') {
            if (labelTotal) {
                labelTotal.classList.add('gastos-modalidad--activa');
            }
            if (labelParcial) {
                labelParcial.classList.remove('gastos-modalidad--activa');
            }
            if (inputMonto) {
                inputMonto.readOnly = true;
                inputMonto.value = deudaSeleccionadaActual > 0 ? deudaSeleccionadaActual.toFixed(2) : '';
            }
            if (avisoModalidad) {
                avisoModalidad.textContent = 'Liquidación completa sin fricción de tipeo (campo numérico protegido).';
                avisoModalidad.classList.add('gastos-modalidad-aviso--total');
                avisoModalidad.classList.remove('gastos-modalidad-aviso--parcial');
            }
            if (avisoSaldo) {
                avisoSaldo.style.display = 'none';
            }
        } else {
            // Parcial
            if (labelTotal) {
                labelTotal.classList.remove('gastos-modalidad--activa');
            }
            if (labelParcial) {
                labelParcial.classList.add('gastos-modalidad--activa');
            }
            if (inputMonto) {
                inputMonto.readOnly = false;
                inputMonto.focus();
            }
            if (avisoModalidad) {
                avisoModalidad.textContent = 'Indica el monto parcial que deseas liquidar hoy.';
                avisoModalidad.classList.add('gastos-modalidad-aviso--parcial');
                avisoModalidad.classList.remove('gastos-modalidad-aviso--total');
            }
            verificarMontoParcial(inputMonto ? inputMonto.value : 0);
        }
    }

    function verificarMontoParcial(valor) {
        var radioParcial = document.getElementById('radio-pago-parcial');
        if (!radioParcial || !radioParcial.checked) return;

        var avisoSaldo = document.getElementById('aviso-saldo-restante');
        if (!avisoSaldo) return;

        var monto = parseFloat(valor || 0);
        if (deudaSeleccionadaActual <= 0) {
            avisoSaldo.style.display = 'none';
            return;
        }

        avisoSaldo.style.display = 'block';

        if (monto > deudaSeleccionadaActual + 0.009) {
            avisoSaldo.textContent = 'Alerta: El monto ingresado (Bs ' + monto.toFixed(2) +
                ') supera la deuda exigible (Bs ' + deudaSeleccionadaActual.toFixed(2) + ').';
            avisoSaldo.classList.add('gastos-aviso-saldo--alerta');
            avisoSaldo.classList.remove('gastos-aviso-saldo--ok');
        } else if (monto > 0) {
            var saldoRestante = (deudaSeleccionadaActual - monto);
            avisoSaldo.textContent = 'Saldo restante estimado tras este pago: Bs ' + saldoRestante.toFixed(2);
            avisoSaldo.classList.add('gastos-aviso-saldo--ok');
            avisoSaldo.classList.remove('gastos-aviso-saldo--alerta');
        } else {
            avisoSaldo.style.display = 'none';
        }
    }

    function actualizarDatosReceptor(receptorId) {
        var contenedor = document.getElementById('contenedor-datos-receptor');
        var textoAlias = document.getElementById('texto-alias-receptor');
        var textoCbu = document.getElementById('texto-cbu-receptor');
        var contenedorQr = document.getElementById('contenedor-qr-receptor');
        var imgQr = document.getElementById('img-qr-receptor');
        var avisoSinCobro = document.getElementById('aviso-sin-cobro');

        if (receptorId && deudasPorAcreedor[receptorId]) {
            deudaSeleccionadaActual = parseFloat(deudasPorAcreedor[receptorId]);
        } else {
            deudaSeleccionadaActual = 0.0;
        }

        var textoRadioTotal = document.getElementById('texto-radio-pago-total');
        if (textoRadioTotal) {
            textoRadioTotal.textContent = 'Pagar totalidad (Bs ' + deudaSeleccionadaActual.toFixed(2) + ')';
        }

        var radioTotal = document.getElementById('radio-pago-total');
        if (radioTotal && radioTotal.checked) {
            var inputMonto = document.getElementById('input-pago-monto');
            if (inputMonto) {
                inputMonto.value = deudaSeleccionadaActual > 0 ? deudaSeleccionadaActual.toFixed(2) : '';
                inputMonto.readOnly = true;
            }
        }

        if (!contenedor) return;

        if (!receptorId || !datosCobroParticipantes[receptorId]) {
            contenedor.style.display = 'none';
            return;
        }

        var datos = datosCobroParticipantes[receptorId];
        contenedor.style.display = 'block';

        var tieneDatos = false;

        if (datos.alias) {
            textoAlias.textContent = 'Alias: ' + datos.alias;
            textoAlias.style.display = 'block';
            tieneDatos = true;
        } else {
            textoAlias.style.display = 'none';
        }

        if (datos.cbu) {
            textoCbu.textContent = 'CBU / Cuenta: ' + datos.cbu;
            textoCbu.style.display = 'block';
            tieneDatos = true;
        } else {
            textoCbu.style.display = 'none';
        }

        if (datos.qr_url) {
            imgQr.src = datos.qr_url;
            contenedorQr.style.display = 'block';
            tieneDatos = true;
        } else {
            contenedorQr.style.display = 'none';
        }

        if (!tieneDatos) {
            avisoSinCobro.style.display = 'block';
        } else {
            avisoSinCobro.style.display = 'none';
        }
    }

    function previsualizarComprobante(input) {
        var previewCont = document.getElementById('preview-comprobante-contenedor');
        var imgPreview = document.getElementById('img-preview-comprobante');

        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                if (imgPreview) imgPreview.src = e.target.result;
                if (previewCont) previewCont.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            if (previewCont) previewCont.style.display = 'none';
        }
    }

    function cambiarModoDivision(modo) {
        var divs = document.querySelectorAll('.contenedor-monto-personalizado');
        var aviso = document.getElementById('aviso-suma-personalizada');

        if (modo === 'personalizada') {
            divs.forEach(function(el) {
                el.style.display = 'block';
            });
            if (aviso) aviso.style.display = 'block';
            actualizarSumaPersonalizada();
        } else {
            divs.forEach(function(el) {
                el.style.display = 'none';
            });
            if (aviso) aviso.style.display = 'none';
        }
    }

    function recalcularDivision() {
        var checks = document.querySelectorAll('.check-participante:checked');
        var contador = document.getElementById('contador-seleccionados');
        if (contador) {
            contador.textContent = checks.length;
        }

        var modo = document.getElementById('select-tipo-division')?.value;
        if (modo === 'personalizada') {
            actualizarSumaPersonalizada();
        }
    }

    function actualizarSumaPersonalizada() {
        var montoTotal = parseFloat(document.getElementById('input-gasto-monto')?.value || 0);
        var inputs = document.querySelectorAll('.input-monto-indiv');
        var suma = 0;

        inputs.forEach(function(inp) {
            var check = inp.closest('div.flex, div')?.parentElement?.querySelector('.check-participante');
            if (!check || check.checked) {
                suma += parseFloat(inp.value || 0);
            }
        });

        var aviso = document.getElementById('aviso-suma-personalizada');
        if (!aviso) return;

        var diff = Math.abs(suma - montoTotal);
        if (diff < 0.01 && montoTotal > 0) {
            aviso.textContent = 'Los montos asignados coinciden exactamente con el total (Bs ' + suma.toFixed(2) + ').';
            aviso.classList.add('gastos-aviso-suma--valido');
            aviso.classList.remove('gastos-aviso-suma--invalido');
        } else {
            aviso.textContent = 'Aviso: Suma actual: Bs ' + suma.toFixed(2) + ' / Total requerido: Bs ' + montoTotal
                .toFixed(2) + ' (Diferencia: Bs ' + (montoTotal - suma).toFixed(2) + ')';
            aviso.classList.add('gastos-aviso-suma--invalido');
            aviso.classList.remove('gastos-aviso-suma--valido');
        }
    }

    function resetearEstadoModalPago() {
        var form = document.getElementById('form-crear-pago');
        var btnSubmit = document.getElementById('btn-submit-pago');
        var btnCancelar = document.getElementById('btn-cancelar-pago');
        var spinner = document.getElementById('spinner-submit-pago');
        var textoBtn = document.getElementById('texto-btn-submit-pago');
        var aviso = document.getElementById('aviso-procesando-pago');

        if (form) {
            delete form.dataset.enviando;
        }
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.classList.remove('boton-procesando');
        }
        if (btnCancelar) {
            btnCancelar.disabled = false;
            btnCancelar.style.pointerEvents = 'auto';
            btnCancelar.style.opacity = '1';
        }
        if (spinner) {
            spinner.style.display = 'none';
        }
        if (textoBtn) {
            textoBtn.textContent = 'Enviar Pago';
        }
        if (aviso) {
            aviso.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var formPago = document.getElementById('form-crear-pago');
        var modalPago = document.getElementById('modal-crear-pago');

        if (formPago) {
            formPago.addEventListener('submit', function(e) {
                if (formPago.dataset.enviando === '1') {
                    e.preventDefault();
                    return;
                }

                if (!formPago.checkValidity()) {
                    return;
                }

                var btnSubmit = document.getElementById('btn-submit-pago');
                var btnCancelar = document.getElementById('btn-cancelar-pago');
                var spinner = document.getElementById('spinner-submit-pago');
                var textoBtn = document.getElementById('texto-btn-submit-pago');
                var aviso = document.getElementById('aviso-procesando-pago');

                if (spinner) spinner.style.display = 'inline-block';
                if (textoBtn) textoBtn.textContent = 'Procesando pago...';
                if (aviso) aviso.style.display = 'flex';

                if (btnCancelar) {
                    btnCancelar.disabled = true;
                    btnCancelar.style.pointerEvents = 'none';
                    btnCancelar.style.opacity = '0.5';
                }

                if (btnSubmit) {
                    btnSubmit.classList.add('boton-procesando');
                    setTimeout(function() {
                        btnSubmit.disabled = true;
                    }, 20);
                }
            });
        }

        if (modalPago) {
            modalPago.addEventListener('cancel', function(e) {
                if (formPago && formPago.dataset.enviando === '1') {
                    e.preventDefault();
                }
            });
        }
    });

    window.addEventListener('pageshow', function(e) {
        if (e.persisted) {
            resetearEstadoModalPago();
        }
    });
</script>

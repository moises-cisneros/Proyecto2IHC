<div class="seccion-gastos-liquidacion" style="margin-top: var(--space-2);">

    {{-- Banner de métricas generales y balance personal --}}
    @php
        $miId = auth()->id();
        $miUsuario = auth()->user();
        $miBalance = $saldos['balances'][$miId] ?? null;
        $misDeudas = collect($saldos['deudas'])->where('deudor_id', $miId)->values();
        $misCobros = collect($saldos['deudas'])->where('acreedor_id', $miId)->values();
        $totalNeto = $miBalance['neto'] ?? 0.0;
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

    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-6);">
        {{-- Tarjeta 1: Total gastado en el grupo --}}
        <div
            style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-4) var(--space-5); box-shadow: var(--shadow-sm);">
            <div
                style="font-size: var(--font-size-sm); color: var(--color-muted); font-weight: var(--font-weight-semibold); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: var(--space-1);">
                Total Gastado en el Plan
            </div>
            <div
                style="font-size: var(--font-size-2xl); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                Bs {{ number_format($saldos['total_gastado'], 2) }}
            </div>
            <div style="font-size: var(--font-size-sm); color: var(--color-muted); margin-top: var(--space-1);">
                {{ $gastos->count() }} {{ $gastos->count() === 1 ? 'gasto registrado' : 'gastos registrados' }}
            </div>
        </div>

        {{-- Tarjeta 2: Tu posición neta --}}
        <div
            style="background: {{ $totalNeto > 0 ? 'var(--color-success-bg)' : ($totalNeto < 0 ? 'var(--color-destructive-bg)' : 'var(--color-surface-soft)') }}; border: 1px solid {{ $totalNeto > 0 ? 'var(--color-success-border)' : ($totalNeto < 0 ? 'var(--color-destructive-border)' : 'var(--color-border)') }}; border-radius: var(--radius-lg); padding: var(--space-4) var(--space-5); box-shadow: var(--shadow-sm);">
            <div
                style="font-size: var(--font-size-sm); color: {{ $totalNeto > 0 ? 'var(--color-success)' : ($totalNeto < 0 ? 'var(--color-destructive)' : 'var(--color-muted)') }}; font-weight: var(--font-weight-semibold); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: var(--space-1);">
                Tu Balance Neto
            </div>
            <div
                style="font-size: var(--font-size-2xl); font-weight: var(--font-weight-bold); color: {{ $totalNeto > 0 ? 'var(--color-success)' : ($totalNeto < 0 ? 'var(--color-destructive)' : 'var(--color-foreground)') }};">
                {{ $totalNeto > 0 ? '+Bs ' : ($totalNeto < 0 ? '-Bs ' : 'Bs ') }}{{ number_format(abs($totalNeto), 2) }}
            </div>
            <div
                style="font-size: var(--font-size-sm); color: {{ $totalNeto > 0 ? 'var(--color-success)' : ($totalNeto < 0 ? 'var(--color-destructive)' : 'var(--color-muted)') }}; margin-top: var(--space-1);">
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
        <div
            style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-4) var(--space-5); display: flex; flex-direction: column; justify-content: center; gap: var(--space-2); box-shadow: var(--shadow-sm);">
            @can('create', [\App\Models\Gasto::class, $plan])
                <button type="button" class="boton"
                    style="min-height: var(--touch-min); width: 100%; border-radius: var(--radius-pill); font-weight: var(--font-weight-semibold); display: inline-flex; align-items: center; justify-content: center; gap: var(--space-2);"
                    onclick="document.getElementById('modal-crear-gasto').showModal()" aria-label="Registrar nuevo gasto">
                    + Registrar Gasto
                </button>
            @endcan

            @can('create', [\App\Models\Pago::class, $plan])
                <button type="button" class="boton boton-claro"
                    style="min-height: var(--touch-min); width: 100%; border-radius: var(--radius-pill); font-weight: var(--font-weight-semibold); display: inline-flex; align-items: center; justify-content: center; gap: var(--space-2);"
                    onclick="abrirModalPagoDirecto('', '')" aria-label="Registrar pago a un compañero">
                    <x-icono nombre="pago"
                        style="width: 18px; height: 18px; display: inline-block; vertical-align: middle; margin-right: var(--space-1);" />
                    Registrar Pago
                </button>
            @endcan

            <button type="button" class="boton boton-claro"
                style="min-height: var(--touch-min); width: 100%; border-radius: var(--radius-pill); font-weight: var(--font-weight-semibold); display: inline-flex; align-items: center; justify-content: center; gap: var(--space-2);"
                onclick="document.getElementById('modal-configurar-cobro').showModal()"
                aria-label="Configurar mis datos de cobro y QR">
                <x-icono nombre="editar"
                    style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: var(--space-1);" />
                Mis Datos de Cobro
            </button>
        </div>
    </div>

    {{-- Matriz Clara de Deudas (Hick & Fitts): Debes vs Te deben --}}
    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: var(--space-5); margin-bottom: var(--space-6);">
        {{-- Columna 1: Cuentas por Pagar (Debes) --}}
        <div
            style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-5);">
            <div style="display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-3);">
                <x-icono nombre="pago" style="width: 20px; height: 20px; color: var(--color-destructive);" />
                <h3
                    style="margin: 0; font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                    Debes a tus compañeros</h3>
            </div>

            @if ($misDeudas->isEmpty())
                <div
                    style="padding: var(--space-4); background: var(--color-surface-soft); border-radius: var(--radius-md); text-align: center; color: var(--color-muted); font-size: var(--font-size-md); display: flex; align-items: center; justify-content: center; gap: var(--space-2);">
                    <x-icono nombre="check" style="width: 16px; height: 16px; color: var(--color-success);" />
                    <span>No tienes deudas pendientes con ningún participante.</span>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                    @foreach ($misDeudas as $deuda)
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-3) var(--space-3); background: var(--color-destructive-bg); border: 1px solid var(--color-destructive-border); border-radius: var(--radius-md);">
                            <div>
                                <div
                                    style="font-weight: var(--font-weight-bold); color: var(--color-destructive); font-size: var(--font-size-base);">
                                    Bs {{ number_format($deuda['monto'], 2) }}
                                </div>
                                <div style="font-size: var(--font-size-sm); color: var(--color-muted);">
                                    Acreedor: <span
                                        style="font-weight: var(--font-weight-semibold);">{{ $deuda['acreedor']->name ?? 'Compañero' }}</span>
                                </div>
                            </div>
                            @can('create', [\App\Models\Pago::class, $plan])
                                <button type="button" class="boton"
                                    style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-4); border-radius: var(--radius-pill); font-size: var(--font-size-sm); font-weight: var(--font-weight-semibold);"
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
        <div
            style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-5);">
            <div style="display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-3);">
                <x-icono nombre="dinero" style="width: 20px; height: 20px; color: var(--color-success);" />
                <h3
                    style="margin: 0; font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                    Te deben tus compañeros</h3>
            </div>

            @if ($misCobros->isEmpty())
                <div
                    style="padding: var(--space-4); background: var(--color-surface-soft); border-radius: var(--radius-md); text-align: center; color: var(--color-muted); font-size: var(--font-size-md);">
                    Ningún participante tiene deudas directas contigo actualmente.
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                    @foreach ($misCobros as $cobro)
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-3) var(--space-3); background: var(--color-success-bg); border: 1px solid var(--color-success-border); border-radius: var(--radius-md);">
                            <div>
                                <div
                                    style="font-weight: var(--font-weight-bold); color: var(--color-success); font-size: var(--font-size-base);">
                                    Bs {{ number_format($cobro['monto'], 2) }}
                                </div>
                                <div style="font-size: var(--font-size-sm); color: var(--color-muted);">
                                    Deudor: <span
                                        style="font-weight: var(--font-weight-semibold);">{{ $cobro['deudor']->name ?? 'Compañero' }}</span>
                                </div>
                            </div>
                            <span
                                style="font-size: var(--font-size-xs); font-weight: var(--font-weight-semibold); color: var(--color-success); background: var(--color-surface); border: 1px solid var(--color-success-border); padding: var(--space-1) var(--space-2); border-radius: var(--radius-pill);">
                                Pendiente de pago
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Sección de Pagos y Liquidaciones Pendientes (CA-03 y CA-04) --}}
    <div
        style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-5); margin-bottom: var(--space-6);">
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); flex-wrap: wrap; gap: var(--space-2);">
            <div>
                <h3
                    style="margin: 0 0 var(--space-1) 0; font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                    Pagos y Liquidaciones</h3>
                <p style="margin: 0; font-size: var(--font-size-sm); color: var(--color-muted);">
                    Los pagos registrados quedan en estado pendiente hasta la confirmación explícita del receptor.
                </p>
            </div>
        </div>

        @if ($pagos->isEmpty())
            <div
                style="padding: var(--space-5); text-align: center; color: var(--color-muted); background: var(--color-surface-soft); border: 1px dashed var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md);">
                No se han registrado transferencias ni pagos entre participantes todavía.
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: var(--space-3);">
                @foreach ($pagos as $pago)
                    @php
                        $soyReceptor = (int) auth()->id() === (int) $pago->receptor_id;
                        $soyDeudor = (int) auth()->id() === (int) $pago->deudor_id;
                    @endphp
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; padding: var(--space-3) var(--space-4); border: 1px solid {{ $pago->estado === 'confirmado' ? 'var(--color-success-border)' : ($pago->estado === 'rechazado' ? 'var(--color-destructive-border)' : 'var(--color-warning-border)') }}; background: {{ $pago->estado === 'confirmado' ? 'var(--color-success-bg)' : ($pago->estado === 'rechazado' ? 'var(--color-destructive-bg)' : 'var(--color-warning-bg)') }}; border-radius: var(--radius-lg); flex-wrap: wrap; gap: var(--space-3);">
                        <div>
                            <div
                                style="display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-1);">
                                <span
                                    style="font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                                    Bs {{ number_format($pago->monto, 2) }}
                                </span>
                                <span class="badge-estado-pago"
                                    style="font-size: var(--font-size-xs); font-weight: var(--font-weight-bold); padding: var(--space-1) var(--space-2); border-radius: var(--radius-pill); text-transform: uppercase;
                                      background: {{ $pago->estado === 'confirmado' ? 'var(--color-success-bg)' : ($pago->estado === 'rechazado' ? 'var(--color-destructive-bg)' : 'var(--color-warning-bg)') }};
                                      color: {{ $pago->estado === 'confirmado' ? 'var(--color-success)' : ($pago->estado === 'rechazado' ? 'var(--color-destructive)' : 'var(--color-warning)') }};">
                                    {{ $pago->estado }}
                                </span>
                            </div>
                            <div style="font-size: var(--font-size-sm); color: var(--color-primary-hover);">
                                De <span
                                    style="font-weight: var(--font-weight-semibold);">{{ $pago->deudor->name ?? 'Usuario' }}</span>
                                para <span
                                    style="font-weight: var(--font-weight-semibold);">{{ $pago->receptor->name ?? 'Usuario' }}</span>
                                · {{ $pago->created_at->format('d/m/Y H:i') }}
                            </div>
                            @if ($pago->tieneComprobante())
                                <div
                                    style="margin-top: var(--space-2); display: flex; align-items: center; gap: var(--space-2); flex-wrap: wrap;">
                                    <button type="button" class="boton boton-claro btn-ver-comprobante"
                                        style="min-height: 32px; padding: var(--space-1) var(--space-2); font-size: var(--font-size-xs); border-radius: var(--radius-sm); display: inline-flex; align-items: center; gap: var(--space-1);"
                                        onclick="document.getElementById('modal-comprobante-{{ $pago->id }}').showModal()"
                                        aria-label="Ver comprobante adjunto del pago {{ $pago->id }}">
                                        <x-icono nombre="pago" style="width: 14px; height: 14px;" />
                                        <span>Ver Comprobante</span>
                                    </button>

                                    @if ($pago->esEscaneoVerificado())
                                        <span class="badge-escaneo verificado"
                                            style="font-size: var(--font-size-xs); font-weight: var(--font-weight-bold); color: var(--color-success); background: var(--color-success-bg); border: 1px solid var(--color-success-border); padding: var(--space-1) var(--space-2); border-radius: var(--radius-pill); display: inline-flex; align-items: center; gap: var(--space-1);">
                                            <x-icono nombre="check" style="width: 12px; height: 12px;" />
                                            {{-- prettier-ignore --}}
                                            <span>Monto Verificado: Bs {{ number_format($pago->monto_detectado, 2) }}{{ $pago->banco_detectado ? ' (' . $pago->banco_detectado . ')' : '' }}</span>
                                        </span>
                                    @elseif ($pago->esEscaneoDiscrepante())
                                        <span class="badge-escaneo discrepante"
                                            style="font-size: var(--font-size-xs); font-weight: var(--font-weight-bold); color: var(--color-destructive); background: var(--color-destructive-bg); border: 1px solid var(--color-destructive-border); padding: var(--space-1) var(--space-2); border-radius: var(--radius-pill); display: inline-flex; align-items: center; gap: var(--space-1);">
                                            <x-icono nombre="bloqueado" style="width: 12px; height: 12px;" />
                                            {{-- prettier-ignore --}}
                                            <span>Alerta: Comprobante indica Bs {{ number_format($pago->monto_detectado, 2) }}</span>
                                        </span>
                                    @elseif ($pago->esEscaneoInvalido())
                                        <span class="badge-escaneo invalido"
                                            style="font-size: var(--font-size-xs); font-weight: var(--font-weight-semibold); color: var(--color-warning); background: var(--color-warning-bg); border: 1px solid var(--color-warning-border); padding: var(--space-1) var(--space-2); border-radius: var(--radius-pill);">
                                            Documento no concluyente
                                        </span>
                                    @endif
                                </div>

                                {{-- Modal para ampliar y revisar el comprobante --}}
                                <dialog id="modal-comprobante-{{ $pago->id }}"
                                    style="border: none; border-radius: var(--radius-xl); padding: var(--space-6); width: 100%; max-width: 500px; box-shadow: var(--shadow-sm);">
                                    <div
                                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-3);">
                                        <h3
                                            style="margin: 0; font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                                            Comprobante de Pago</h3>
                                        <button type="button"
                                            onclick="document.getElementById('modal-comprobante-{{ $pago->id }}').close()"
                                            style="border: none; background: transparent; font-size: var(--font-size-xl); cursor: pointer; color: var(--color-muted);"
                                            aria-label="Cerrar modal">&times;</button>
                                    </div>

                                    @if ($pago->monto_detectado && abs((float) $pago->monto_detectado - (float) $pago->monto) < 0.01)
                                        <div class="badge-ia-verificado"
                                            style="background: var(--color-success-bg); color: var(--color-success); border: 1px solid var(--color-success-border); padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); font-weight: var(--font-weight-semibold); font-size: var(--font-size-sm); display: flex; align-items: center; gap: var(--space-2); margin-bottom: var(--space-3);">
                                            <x-icono nombre="check"
                                                style="width: 16px; height: 16px; color: var(--color-success);" />
                                            {{-- prettier-ignore --}}
                                            <span>Comprobante verificado por IA: Monto coincide (Bs {{ number_format($pago->monto, 2) }})</span>
                                        </div>
                                    @endif

                                    <div style="text-align: center; margin-bottom: var(--space-4);">
                                        <img src="{{ asset('storage/' . $pago->comprobante_path) }}"
                                            alt="Comprobante de pago"
                                            style="max-width: 100%; max-height: 350px; border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                                    </div>
                                    <div
                                        style="background: var(--color-surface-soft); padding: var(--space-3); border-radius: var(--radius-sm); font-size: var(--font-size-sm); color: var(--color-muted);">
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
                                    <div
                                        style="margin-top: var(--space-4); display: flex; justify-content: flex-end; align-items: center; gap: var(--space-3);">
                                        <button type="button" class="boton boton-claro"
                                            style="border-radius: var(--radius-pill); min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-4);"
                                            onclick="document.getElementById('modal-comprobante-{{ $pago->id }}').close()">Cerrar</button>
                                        @if ($pago->estado === 'pendiente')
                                            @can('confirmar', $pago)
                                                <form method="POST" action="{{ route('pagos.confirmar', $pago) }}"
                                                    style="margin: 0;">
                                                    @csrf
                                                    <button type="submit" class="boton"
                                                        style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-5); border-radius: var(--radius-pill); font-size: var(--font-size-sm); font-weight: var(--font-weight-semibold); background: var(--color-success); color: var(--color-surface); display: inline-flex; align-items: center; gap: var(--space-1);"
                                                        aria-label="Confirmar pago verificado de Bs {{ number_format($pago->monto, 2) }}">
                                                        <x-icono nombre="check" style="width: 14px; height: 14px;" />
                                                        <span>Confirmar pago en 1 clic</span>
                                                    </button>
                                                </form>
                                            @endcan
                                        @endif
                                    </div>
                                </dialog>
                            @endif
                            @if ($pago->motivo_rechazo)
                                <div
                                    style="margin-top: var(--space-1); font-size: var(--font-size-sm); color: var(--color-destructive); background: var(--color-surface); padding: var(--space-1) var(--space-3); border-radius: var(--radius-sm); border: 1px solid var(--color-destructive-border);">
                                    <span style="font-weight: var(--font-weight-semibold);">Motivo de rechazo:</span>
                                    &laquo;{{ $pago->motivo_rechazo }}&raquo;
                                </div>
                            @endif
                        </div>

                        {{-- Botones de acción del receptor protegidos con Policy (Fitts >= 44px) --}}
                        <div style="display: flex; align-items: center; gap: var(--space-2);">
                            @if ($pago->estado === 'pendiente')
                                @can('confirmar', $pago)
                                    <form method="POST" action="{{ route('pagos.confirmar', $pago) }}"
                                        style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="boton"
                                            style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-4); border-radius: var(--radius-pill); font-size: var(--font-size-sm); font-weight: var(--font-weight-semibold); background: var(--color-success); color: var(--color-surface); display: inline-flex; align-items: center; gap: var(--space-1);"
                                            aria-label="Confirmar pago de Bs {{ number_format($pago->monto, 2) }}">
                                            <x-icono nombre="check" style="width: 14px; height: 14px;" />
                                            <span>Confirmar pago</span>
                                        </button>
                                    </form>

                                    <button type="button" class="boton boton-claro"
                                        style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-4); border-radius: var(--radius-pill); font-size: var(--font-size-sm); font-weight: var(--font-weight-semibold); color: var(--color-destructive); border-color: var(--color-destructive-border); display: inline-flex; align-items: center; gap: var(--space-1);"
                                        onclick="document.getElementById('modal-rechazar-{{ $pago->id }}').showModal()"
                                        aria-label="Rechazar pago de Bs {{ number_format($pago->monto, 2) }}">
                                        <x-icono nombre="bloqueado" style="width: 14px; height: 14px;" />
                                        <span>Rechazar</span>
                                    </button>
                                @else
                                    <span
                                        style="font-size: var(--font-size-sm); color: var(--color-warning); font-weight: var(--font-weight-medium);">
                                        Esperando confirmación del receptor
                                    </span>
                                @endcan
                            @elseif ($pago->estado === 'confirmado')
                                <span
                                    style="font-size: var(--font-size-sm); color: var(--color-success); font-weight: var(--font-weight-semibold); display: inline-flex; align-items: center; gap: var(--space-1);">
                                    <x-icono nombre="check" style="width: 14px; height: 14px;" />
                                    <span>Liquidado el {{ $pago->fecha_resolucion?->format('d/m/Y') }}</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Modal nativo de rechazo para este pago --}}
                    @can('rechazar', $pago)
                        <dialog id="modal-rechazar-{{ $pago->id }}"
                            style="border: none; border-radius: var(--radius-xl); padding: var(--space-6); width: 100%; max-width: 460px; box-shadow: var(--shadow-sm);">
                            <form method="POST" action="{{ route('pagos.rechazar', $pago) }}">
                                @csrf
                                <h3
                                    style="margin: 0 0 var(--space-2) 0; font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); color: var(--color-destructive);">
                                    Rechazar Pago</h3>
                                <p
                                    style="margin: 0 0 var(--space-4) 0; font-size: var(--font-size-md); color: var(--color-muted);">
                                    Indica al deudor la razón por la cual no aceptas este pago (ej. no recibido en tu banco
                                    o monto incorrecto).
                                </p>
                                <div style="margin-bottom: var(--space-5);">
                                    <label for="motivo-rechazo-{{ $pago->id }}"
                                        style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                                        Motivo del rechazo (obligatorio)
                                    </label>
                                    <textarea id="motivo-rechazo-{{ $pago->id }}" name="motivo_rechazo" rows="3" required minlength="5"
                                        placeholder="Explica detalladamente por qué rechazas este pago..."
                                        style="width: 100%; padding: var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md); box-sizing: border-box;"></textarea>
                                </div>
                                <div style="display: flex; justify-content: flex-end; gap: var(--space-3);">
                                    <button type="button" class="boton boton-claro"
                                        style="min-height: var(--touch-min); min-width: var(--touch-min); border-radius: var(--radius-pill);"
                                        onclick="document.getElementById('modal-rechazar-{{ $pago->id }}').close()">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="boton"
                                        style="min-height: var(--touch-min); min-width: var(--touch-min); border-radius: var(--radius-pill); background: var(--color-destructive); color: var(--color-surface);">
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
    <div
        style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-5);">
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); flex-wrap: wrap; gap: var(--space-2);">
            <div>
                <h3
                    style="margin: 0 0 var(--space-1) 0; font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                    Historial de Gastos</h3>
                <p style="margin: 0; font-size: var(--font-size-sm); color: var(--color-muted);">Detalle de consumos y
                    cuotas asignadas a cada participante.</p>
            </div>
        </div>

        @if ($gastos->isEmpty())
            <div
                style="padding: var(--space-8) var(--space-5); text-align: center; color: var(--color-muted); background: var(--color-surface-soft); border: 1px dashed var(--color-border-input); border-radius: var(--radius-lg);">
                <p style="margin: 0 0 var(--space-3) 0; font-size: var(--font-size-base);">No hay gastos registrados en
                    este plan todavía.</p>
                @can('create', [\App\Models\Gasto::class, $plan])
                    <button type="button" class="boton"
                        style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-5); border-radius: var(--radius-pill);"
                        onclick="document.getElementById('modal-crear-gasto').showModal()">
                        + Registrar el primer gasto
                    </button>
                @endcan
            </div>
        @else
            <div
                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: var(--space-4);">
                @foreach ($gastos as $gasto)
                    <div
                        style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: var(--space-4); display: flex; flex-direction: column; justify-content: space-between; gap: var(--space-3); box-shadow: var(--shadow-sm);">
                        <div>
                            <div
                                style="display: flex; justify-content: space-between; align-items: flex-start; gap: var(--space-2); margin-bottom: var(--space-1);">
                                <span
                                    style="font-size: var(--font-size-lg); font-weight: var(--font-weight-bold); color: var(--color-foreground); word-break: break-word;">
                                    {{ $gasto->concepto }}
                                </span>
                                <span
                                    style="font-weight: var(--font-weight-bold); font-size: var(--font-size-base); color: var(--color-success); white-space: nowrap;">
                                    Bs {{ number_format($gasto->monto_total, 2) }}
                                </span>
                            </div>
                            <div
                                style="font-size: var(--font-size-sm); color: var(--color-muted); margin-bottom: var(--space-2);">
                                Pagado por: <span
                                    style="font-weight: var(--font-weight-semibold); color: var(--color-foreground);">{{ $gasto->pagador->name ?? 'Usuario' }}</span>
                                <span style="margin: 0 var(--space-1);">·</span>
                                <span style="text-transform: capitalize;">{{ $gasto->tipo_division }}</span>
                            </div>

                            <div
                                style="font-size: var(--font-size-xs); color: var(--color-primary-hover); border-top: 1px solid var(--color-neutral-bg); padding-top: var(--space-2);">
                                <div
                                    style="font-weight: var(--font-weight-semibold); margin-bottom: var(--space-1); color: var(--color-muted);">
                                    Participantes ({{ $gasto->divisiones->count() }}):</div>
                                <div style="display: flex; flex-wrap: wrap; gap: var(--space-1);">
                                    @foreach ($gasto->divisiones as $div)
                                        <span
                                            style="background: var(--color-neutral-bg); border: 1px solid var(--color-border); border-radius: var(--radius-sm); padding: var(--space-1) var(--space-2); font-size: var(--font-size-xs);">
                                            {{ $div->user->name ?? 'Usuario' }}: <strong>Bs
                                                {{ number_format($div->monto_asignado, 2) }}</strong>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div style="font-size: var(--font-size-xs); color: var(--color-muted); text-align: right;">
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
    <dialog id="modal-crear-gasto"
        style="border: none; border-radius: var(--radius-xl); padding: var(--space-6); width: 100%; max-width: 520px; box-shadow: var(--shadow-sm);">
        <form method="POST" action="{{ route('planes.gastos.guardar', $plan) }}">
            @csrf
            <h2
                style="margin: 0 0 var(--space-2) 0; font-size: var(--font-size-xl); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                Registrar Nuevo Gasto</h2>
            <p style="margin: 0 0 var(--space-5) 0; font-size: var(--font-size-md); color: var(--color-muted);">Anota el
                consumo realizado y cómo se reparte entre los participantes.</p>

            <div style="margin-bottom: var(--space-4);">
                <label for="input-gasto-concepto"
                    style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                    Concepto o descripción
                </label>
                <input type="text" id="input-gasto-concepto" name="concepto" required
                    placeholder="Ej. Cena, Supermercado, Alquiler de cabaña"
                    style="width: 100%; padding: var(--space-3) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md); box-sizing: border-box; min-height: var(--touch-min);">
            </div>

            <div style="margin-bottom: var(--space-4);">
                <label for="input-gasto-monto"
                    style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                    Monto total (Bs)
                </label>
                <input type="number" id="input-gasto-monto" name="monto_total" step="0.01" min="0.01" required
                    placeholder="0.00" oninput="recalcularDivision()"
                    style="width: 100%; padding: var(--space-3) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md); box-sizing: border-box; min-height: var(--touch-min);">
            </div>

            <div style="margin-bottom: var(--space-4);">
                <label for="select-tipo-division"
                    style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                    Modo de división
                </label>
                <select id="select-tipo-division" name="tipo_division" required
                    onchange="cambiarModoDivision(this.value)"
                    style="width: 100%; padding: var(--space-3) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md); box-sizing: border-box; min-height: var(--touch-min);">
                    <option value="equitativa">En partes iguales (Equitativa)</option>
                    <option value="personalizada">Monto personalizado por persona</option>
                </select>
            </div>

            <div style="margin-bottom: var(--space-5);">
                <label
                    style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                    Participantes incluidos
                </label>
                <p style="margin: 0 0 var(--space-2) 0; font-size: var(--font-size-sm); color: var(--color-muted);">
                    Selecciona explícitamente a las personas que asumirán este gasto (opt-in consciente).
                </p>
                <div
                    style="display: flex; flex-direction: column; gap: var(--space-2); max-height: 180px; overflow-y: auto; padding: var(--space-2); border: 1px solid var(--color-border); border-radius: var(--radius-md); background: var(--color-surface-soft);">
                    @foreach ($participantesPlan as $p)
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; gap: var(--space-2);">
                            <label
                                style="display: flex; align-items: center; gap: var(--space-2); font-size: var(--font-size-md); cursor: pointer;">
                                <input type="checkbox" name="participantes[]" value="{{ $p->id }}"
                                    class="check-participante" onchange="recalcularDivision()"
                                    style="width: 18px; height: 18px;">
                                <span>{{ $p->name }}</span>
                            </label>
                            <div class="contenedor-monto-personalizado" style="display: none;">
                                <input type="number" step="0.01" min="0"
                                    name="divisiones[{{ $p->id }}]" placeholder="0.00" class="input-monto-indiv"
                                    oninput="actualizarSumaPersonalizada()"
                                    style="width: 100px; padding: var(--space-1) var(--space-2); border: 1px solid var(--color-border-input); border-radius: var(--radius-sm); font-size: var(--font-size-sm);">
                            </div>
                        </div>
                    @endforeach
                </div>
                <div id="indicador-participantes-seleccionados"
                    style="margin-top: var(--space-1); font-size: var(--font-size-xs); color: var(--color-muted);">
                    Participantes seleccionados: <strong id="contador-seleccionados">0</strong> de
                    {{ count($participantesPlan) }}
                </div>
                <div id="aviso-suma-personalizada"
                    style="display: none; margin-top: var(--space-2); font-size: var(--font-size-sm); font-weight: var(--font-weight-semibold); padding: var(--space-2) var(--space-3); border-radius: var(--radius-sm);">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: var(--space-3); margin-top: var(--space-6);">
                <button type="button" class="boton boton-claro"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); border-radius: var(--radius-pill);"
                    onclick="document.getElementById('modal-crear-gasto').close()">
                    Cancelar
                </button>
                <button type="submit" class="boton"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); border-radius: var(--radius-pill); background: var(--color-success); color: var(--color-surface);">
                    Guardar Gasto
                </button>
            </div>
        </form>
    </dialog>
@endcan

{{-- Modal accesible para Registrar Pago --}}
@can('create', [\App\Models\Pago::class, $plan])
    <dialog id="modal-crear-pago"
        style="border: none; border-radius: var(--radius-xl); padding: var(--space-6); width: 100%; max-width: 480px; box-shadow: var(--shadow-sm);">
        <form id="form-crear-pago" method="POST" action="{{ route('planes.pagos.guardar', $plan) }}"
            enctype="multipart/form-data">
            @csrf
            <h2
                style="margin: 0 0 var(--space-2) 0; font-size: var(--font-size-xl); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                Registrar Pago</h2>
            <p style="margin: 0 0 var(--space-5) 0; font-size: var(--font-size-md); color: var(--color-muted);">
                Anota una transferencia o entrega de dinero. Puedes adjuntar tu comprobante para acelerar la verificación.
            </p>

            <div style="margin-bottom: var(--space-4);">
                <label for="select-pago-receptor"
                    style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                    ¿A quién le pagas?
                </label>
                <select id="select-pago-receptor" name="receptor_id" required
                    onchange="actualizarDatosReceptor(this.value)"
                    style="width: 100%; padding: var(--space-3) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md); min-height: var(--touch-min); box-sizing: border-box;">
                    <option value="">-- Selecciona el destinatario --</option>
                    @foreach ($participantesPlan as $p)
                        @if ($p->id !== auth()->id())
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            {{-- Panel reactivo con datos de cobro del receptor --}}
            <div id="contenedor-datos-receptor"
                style="display: none; margin-bottom: var(--space-4); padding: var(--space-3) var(--space-4); background: var(--color-success-bg); border: 1px solid var(--color-success-border); border-radius: var(--radius-md);">
                <div
                    style="font-size: var(--font-size-sm); font-weight: var(--font-weight-bold); color: var(--color-success); margin-bottom: var(--space-1);">
                    Datos para transferir al receptor
                </div>
                <div id="texto-alias-receptor"
                    style="font-size: var(--font-size-sm); color: var(--color-foreground); margin-bottom: var(--space-1);">
                </div>
                <div id="texto-cbu-receptor"
                    style="font-size: var(--font-size-sm); color: var(--color-foreground); margin-bottom: var(--space-1);">
                </div>
                <div id="contenedor-qr-receptor" style="display: none; margin-top: var(--space-2);">
                    <span
                        style="font-size: var(--font-size-xs); font-weight: var(--font-weight-semibold); color: var(--color-success); display: block; margin-bottom: var(--space-1);">Código
                        QR para escanear:</span>
                    <img id="img-qr-receptor" src="" alt="QR del receptor"
                        style="max-width: 140px; max-height: 140px; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                </div>
                <div id="aviso-sin-cobro"
                    style="display: none; font-size: var(--font-size-xs); color: var(--color-warning); background: var(--color-warning-bg); padding: var(--space-1) var(--space-2); border-radius: var(--radius-sm); border: 1px solid var(--color-warning-border);">
                    El destinatario no ha configurado Alias o QR de cobro.
                </div>
            </div>

            {{-- Selector ergonómico de intención (Ley de Hick & Fitts): Pago Total vs Parcial --}}
            <div id="selector-modalidad-pago" style="margin-bottom: var(--space-4);">
                <label
                    style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-2);">
                    Modalidad de pago
                </label>
                <div role="radiogroup" aria-label="Seleccionar modalidad de pago"
                    style="display: flex; gap: var(--space-3); flex-wrap: wrap;">
                    <label id="label-pago-total"
                        style="flex: 1; min-width: 170px; min-height: var(--touch-min); display: inline-flex; align-items: center; gap: var(--space-2); padding: var(--space-2) var(--space-3); border: 1px solid var(--color-success-border); background: var(--color-success-bg); border-radius: var(--radius-md); cursor: pointer; font-size: var(--font-size-sm); font-weight: var(--font-weight-semibold); color: var(--color-success);">
                        <input type="radio" name="modalidad_pago" id="radio-pago-total" value="total"
                            onchange="cambiarModalidadPago('total')">
                        <span id="texto-radio-pago-total">Pagar totalidad (Bs 0.00)</span>
                    </label>

                    <label id="label-pago-parcial"
                        style="flex: 1; min-width: 170px; min-height: var(--touch-min); display: inline-flex; align-items: center; gap: var(--space-2); padding: var(--space-2) var(--space-3); border: 1px solid var(--color-border-input); background: var(--color-surface); border-radius: var(--radius-md); cursor: pointer; font-size: var(--font-size-sm); font-weight: var(--font-weight-medium); color: var(--color-primary-hover);">
                        <input type="radio" name="modalidad_pago" id="radio-pago-parcial" value="parcial"
                            onchange="cambiarModalidadPago('parcial')">
                        <span>Pago parcial</span>
                    </label>
                </div>
                <div id="aviso-pago-modalidad"
                    style="font-size: var(--font-size-xs); color: var(--color-success); margin-top: var(--space-1);">
                    Liquidación completa sin fricción de tipeo (campo numérico protegido).
                </div>
            </div>

            <div style="margin-bottom: var(--space-4);">
                <label for="input-pago-monto"
                    style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                    Monto a pagar (Bs)
                </label>
                <input type="number" id="input-pago-monto" name="monto" step="0.01" min="0.01" required
                    readonly oninput="verificarMontoParcial(this.value)" placeholder="0.00"
                    style="width: 100%; padding: var(--space-3) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md); min-height: var(--touch-min); box-sizing: border-box; background: var(--color-surface-soft);">
                <div id="aviso-saldo-restante"
                    style="display: none; font-size: var(--font-size-xs); margin-top: var(--space-1);"></div>
            </div>

            <div style="margin-bottom: var(--space-6);">
                <label for="input-pago-comprobante"
                    style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                    Comprobante de Transferencia (Opcional - Imagen)
                </label>
                <input type="file" id="input-pago-comprobante" name="comprobante"
                    accept="image/png, image/jpeg, image/jpg, image/webp" onchange="previsualizarComprobante(this)"
                    style="width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-sm); box-sizing: border-box;">
                <p
                    style="margin: var(--space-1) 0 var(--space-2) 0; font-size: var(--font-size-xs); color: var(--color-muted);">
                    El escaneo inteligente procesará la imagen para extraer el monto y banco emisor.
                </p>
                <div id="preview-comprobante-contenedor" style="display: none; margin-top: var(--space-2);">
                    <img id="img-preview-comprobante" src="" alt="Vista previa"
                        style="max-width: 100%; max-height: 160px; border-radius: var(--radius-sm); border: 1px solid var(--color-border-input);">
                </div>
            </div>

            {{-- Banner de feedback durante el procesamiento (Nielsen H1) --}}
            <div id="aviso-procesando-pago"
                style="display: none; margin-bottom: var(--space-4); padding: var(--space-3) var(--space-3); background: var(--color-warning-bg); border: 1px solid var(--color-brand); border-radius: var(--radius-md); color: var(--color-warning); font-size: var(--font-size-sm); align-items: center; gap: var(--space-2);"
                role="status" aria-live="polite">
                <x-icono nombre="spinner" clases="w-5 h-5 flex-shrink-0" />
                <div>
                    <strong>Procesando tu pago...</strong>
                    <div style="font-size: var(--font-size-xs); margin-top: var(--space-1); color: var(--color-warning);">
                        Estamos registrando la transferencia y analizando el comprobante. Por favor, no cierres esta
                        ventana.
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: var(--space-3);">
                <button type="button" id="btn-cancelar-pago" class="boton boton-claro"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); border-radius: var(--radius-pill);"
                    onclick="document.getElementById('modal-crear-pago').close()">
                    Cancelar
                </button>
                <button type="submit" id="btn-submit-pago" class="boton"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); border-radius: var(--radius-pill); background: var(--color-success); color: var(--color-surface); display: inline-flex; align-items: center; justify-content: center; gap: var(--space-2);">
                    <span id="spinner-submit-pago" style="display: none;">
                        <x-icono nombre="spinner" clases="w-4 h-4" />
                    </span>
                    <span id="texto-btn-submit-pago">Enviar Pago</span>
                </button>
            </div>
        </form>
    </dialog>
@endcan

{{-- Modal para configurar datos de cobro personales (Alias, CBU, QR) --}}
<dialog id="modal-configurar-cobro"
    style="border: none; border-radius: var(--radius-xl); padding: var(--space-6); width: 100%; max-width: 480px; box-shadow: var(--shadow-sm);">
    <form method="POST" action="{{ route('perfil.cobro.actualizar') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <h2
            style="margin: 0 0 var(--space-2) 0; font-size: var(--font-size-xl); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
            Mis Datos de Cobro</h2>
        <p style="margin: 0 0 var(--space-5) 0; font-size: var(--font-size-md); color: var(--color-muted);">
            Configura tu Alias, CBU o sube tu código QR para que tus compañeros puedan transferirte fácilmente.
        </p>

        <div style="margin-bottom: var(--space-4);">
            <label for="input-perfil-alias"
                style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                Alias de Cobro (ej. banco o billetera móvil)
            </label>
            <input type="text" id="input-perfil-alias" name="alias_pago"
                value="{{ old('alias_pago', $miUsuario->alias_pago) }}" placeholder="mi.alias.pago"
                style="width: 100%; padding: var(--space-3) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md); min-height: var(--touch-min); box-sizing: border-box;">
        </div>

        <div style="margin-bottom: var(--space-4);">
            <label for="input-perfil-cbu"
                style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                CBU / N° de Cuenta Bancaria
            </label>
            <input type="text" id="input-perfil-cbu" name="cbu_pago"
                value="{{ old('cbu_pago', $miUsuario->cbu_pago) }}" placeholder="10000000000000"
                style="width: 100%; padding: var(--space-3) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md); min-height: var(--touch-min); box-sizing: border-box;">
        </div>

        <div style="margin-bottom: var(--space-6);">
            <label for="input-perfil-qr"
                style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                Imagen de Código QR (Simple / Billetera)
            </label>
            <input type="file" id="input-perfil-qr" name="qr_pago" accept="image/*"
                style="width: 100%; padding: var(--space-2) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-sm); box-sizing: border-box;">
            @if ($miUsuario->qr_pago_path)
                <div style="margin-top: var(--space-2);">
                    <span
                        style="font-size: var(--font-size-xs); color: var(--color-muted); display: block; margin-bottom: var(--space-1);">QR
                        configurado actualmente:</span>
                    <img src="{{ asset('storage/' . $miUsuario->qr_pago_path) }}" alt="QR actual"
                        style="max-width: 120px; max-height: 120px; border-radius: var(--radius-sm); border: 1px solid var(--color-border-input);">
                </div>
            @endif
        </div>

        <div style="display: flex; justify-content: flex-end; gap: var(--space-3);">
            <button type="button" class="boton boton-claro"
                style="min-height: var(--touch-min); min-width: var(--touch-min); border-radius: var(--radius-pill);"
                onclick="document.getElementById('modal-configurar-cobro').close()">
                Cancelar
            </button>
            <button type="submit" class="boton"
                style="min-height: var(--touch-min); min-width: var(--touch-min); border-radius: var(--radius-pill); background: var(--color-success); color: var(--color-surface);">
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

        if (radioTotal) {
            radioTotal.checked = true;
        }

        cambiarModalidadPago('total');

        if (inputMonto && deudaSeleccionadaActual > 0) {
            inputMonto.value = deudaSeleccionadaActual.toFixed(2);
        }

        modal.showModal();
    }

    function cambiarModalidadPago(modalidad) {
        var inputMonto = document.getElementById('input-pago-monto');
        var labelTotal = document.getElementById('label-pago-total');
        var labelParcial = document.getElementById('label-pago-parcial');
        var avisoModalidad = document.getElementById('aviso-pago-modalidad');
        var avisoSaldo = document.getElementById('aviso-saldo-restante');

        if (modalidad === 'total') {
            if (labelTotal) {
                labelTotal.style.background = 'var(--color-success-bg)';
                labelTotal.style.borderColor = 'var(--color-success-border)';
                labelTotal.style.color = 'var(--color-success)';
                labelTotal.style.fontWeight = '600';
            }
            if (labelParcial) {
                labelParcial.style.background = 'var(--color-surface)';
                labelParcial.style.borderColor = 'var(--color-border-input)';
                labelParcial.style.color = 'var(--color-primary-hover)';
                labelParcial.style.fontWeight = '500';
            }
            if (inputMonto) {
                inputMonto.readOnly = true;
                inputMonto.style.background = 'var(--color-surface-soft)';
                inputMonto.value = deudaSeleccionadaActual > 0 ? deudaSeleccionadaActual.toFixed(2) : '';
            }
            if (avisoModalidad) {
                avisoModalidad.textContent = 'Liquidación completa sin fricción de tipeo (campo numérico protegido).';
                avisoModalidad.style.color = 'var(--color-success)';
            }
            if (avisoSaldo) {
                avisoSaldo.style.display = 'none';
            }
        } else {
            // Parcial
            if (labelTotal) {
                labelTotal.style.background = 'var(--color-surface)';
                labelTotal.style.borderColor = 'var(--color-border-input)';
                labelTotal.style.color = 'var(--color-primary-hover)';
                labelTotal.style.fontWeight = '500';
            }
            if (labelParcial) {
                labelParcial.style.background = 'var(--color-success-bg)';
                labelParcial.style.borderColor = 'var(--color-success-border)';
                labelParcial.style.color = 'var(--color-success)';
                labelParcial.style.fontWeight = '600';
            }
            if (inputMonto) {
                inputMonto.readOnly = false;
                inputMonto.style.background = 'var(--color-surface)';
                inputMonto.focus();
            }
            if (avisoModalidad) {
                avisoModalidad.textContent = 'Indica el monto parcial que deseas liquidar hoy.';
                avisoModalidad.style.color = 'var(--color-primary-hover)';
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
            avisoSaldo.style.color = 'var(--color-destructive)';
            avisoSaldo.style.fontWeight = '600';
        } else if (monto > 0) {
            var saldoRestante = (deudaSeleccionadaActual - monto);
            avisoSaldo.textContent = 'Saldo restante estimado tras este pago: Bs ' + saldoRestante.toFixed(2);
            avisoSaldo.style.color = 'var(--color-success)';
            avisoSaldo.style.fontWeight = '500';
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
            aviso.style.background = 'var(--color-success-bg)';
            aviso.style.color = 'var(--color-success)';
            aviso.style.border = '1px solid var(--color-success-border)';
        } else {
            aviso.textContent = 'Aviso: Suma actual: Bs ' + suma.toFixed(2) + ' / Total requerido: Bs ' + montoTotal
                .toFixed(2) + ' (Diferencia: Bs ' + (montoTotal - suma).toFixed(2) + ')';
            aviso.style.background = 'var(--color-warning-bg)';
            aviso.style.color = 'var(--color-warning)';
            aviso.style.border = '1px solid var(--color-brand)';
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

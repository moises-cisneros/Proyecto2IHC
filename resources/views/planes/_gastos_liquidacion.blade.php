<div class="seccion-gastos-liquidacion" style="margin-top: 10px;">

    {{-- Banner de métricas generales y balance personal --}}
    @php
        $miId = auth()->id();
        $miUsuario = auth()->user();
        $miBalance = $saldos['balances'][$miId] ?? null;
        $misDeudas = collect($saldos['deudas'])->where('deudor_id', $miId)->values();
        $misCobros = collect($saldos['deudas'])->where('acreedor_id', $miId)->values();
        $totalNeto = $miBalance['neto'] ?? 0.0;
        $participantesPlan = collect([$plan->user])->merge($plan->participantes)->unique('id')->values();
        $deudasPorAcreedor = $misDeudas->mapWithKeys(function ($d) {
            return [$d['acreedor_id'] => (float) $d['monto']];
        });
        $datosCobroParticipantes = $participantesPlan->mapWithKeys(function ($u) {
            return [$u->id => [
                'nombre' => $u->name,
                'alias' => $u->alias_pago,
                'cbu' => $u->cbu_pago,
                'qr_url' => $u->qr_pago_path ? asset('storage/' . $u->qr_pago_path) : null,
            ]];
        });
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Tarjeta 1: Total gastado en el grupo --}}
        <div style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 14px; padding: 18px 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
            <div style="font-size: 13px; color: #6f6f6a; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Total Gastado en el Plan
            </div>
            <div style="font-size: 26px; font-weight: 800; color: #1c1c1a;">
                Bs {{ number_format($saldos['total_gastado'], 2) }}
            </div>
            <div style="font-size: 13px; color: #8e8e88; margin-top: 4px;">
                {{ $gastos->count() }} {{ $gastos->count() === 1 ? 'gasto registrado' : 'gastos registrados' }}
            </div>
        </div>

        {{-- Tarjeta 2: Tu posición neta --}}
        <div style="background: {{ $totalNeto > 0 ? '#edf7ef' : ($totalNeto < 0 ? '#fdf2f2' : '#f9fafb') }}; border: 1px solid {{ $totalNeto > 0 ? '#c2e2c8' : ($totalNeto < 0 ? '#fecaca' : '#e5e7eb') }}; border-radius: 14px; padding: 18px 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
            <div style="font-size: 13px; color: {{ $totalNeto > 0 ? '#25663a' : ($totalNeto < 0 ? '#991b1b' : '#4b5563') }}; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Tu Balance Neto
            </div>
            <div style="font-size: 26px; font-weight: 800; color: {{ $totalNeto > 0 ? '#25663a' : ($totalNeto < 0 ? '#991b1b' : '#1f2937') }};">
                {{ $totalNeto > 0 ? '+Bs ' : ($totalNeto < 0 ? '-Bs ' : 'Bs ') }}{{ number_format(abs($totalNeto), 2) }}
            </div>
            <div style="font-size: 13px; color: {{ $totalNeto > 0 ? '#2e5939' : ($totalNeto < 0 ? '#b91c1c' : '#6b7280') }}; margin-top: 4px;">
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
        <div style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 14px; padding: 18px 20px; display: flex; flex-direction: column; justify-content: center; gap: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
            @can('create', [\App\Models\Gasto::class, $plan])
                <button type="button"
                        class="boton"
                        style="min-height: 44px; width: 100%; border-radius: 50px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px;"
                        onclick="document.getElementById('modal-crear-gasto').showModal()"
                        aria-label="Registrar nuevo gasto">
                    + Registrar Gasto
                </button>
            @endcan

            @can('create', [\App\Models\Pago::class, $plan])
                <button type="button"
                        class="boton boton-claro"
                        style="min-height: 44px; width: 100%; border-radius: 50px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px;"
                        onclick="abrirModalPagoDirecto('', '')"
                        aria-label="Registrar pago a un compañero">
                    <x-icono nombre="pago" style="width: 18px; height: 18px; display: inline-block; vertical-align: middle; margin-right: 6px;" /> Registrar Pago
                </button>
            @endcan

            <button type="button"
                    class="boton boton-claro"
                    style="min-height: 44px; width: 100%; border-radius: 50px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px;"
                    onclick="document.getElementById('modal-configurar-cobro').showModal()"
                    aria-label="Configurar mis datos de cobro y QR">
                <x-icono nombre="editar" style="width: 16px; height: 16px; display: inline-block; vertical-align: middle; margin-right: 6px;" /> Mis Datos de Cobro
            </button>
        </div>
    </div>

    {{-- Matriz Clara de Deudas (Hick & Fitts): Debes vs Te deben --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 28px;">
        {{-- Columna 1: Cuentas por Pagar (Debes) --}}
        <div style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 14px; padding: 20px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 14px;">
                <x-icono nombre="pago" style="width: 20px; height: 20px; color: #9b2c2c;" />
                <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #1c1c1a;">Debes a tus compañeros</h3>
            </div>

            @if ($misDeudas->isEmpty())
                <div style="padding: 16px; background: #f9fafb; border-radius: 10px; text-align: center; color: #6f6f6a; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                    <x-icono nombre="check" style="width: 16px; height: 16px; color: #25663a;" />
                    <span>No tienes deudas pendientes con ningún participante.</span>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach ($misDeudas as $deuda)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 10px;">
                            <div>
                                <div style="font-weight: 700; color: #9b2c2c; font-size: 15px;">
                                    Bs {{ number_format($deuda['monto'], 2) }}
                                </div>
                                <div style="font-size: 13px; color: #4a5568;">
                                    Acreedor: <span style="font-weight: 600;">{{ $deuda['acreedor']->name ?? 'Compañero' }}</span>
                                </div>
                            </div>
                            @can('create', [\App\Models\Pago::class, $plan])
                                <button type="button"
                                        class="boton"
                                        style="min-height: 44px; min-width: 44px; padding: 8px 16px; border-radius: 50px; font-size: 13px; font-weight: 600;"
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
        <div style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 14px; padding: 20px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 14px;">
                <x-icono nombre="dinero" style="width: 20px; height: 20px; color: #22543d;" />
                <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #1c1c1a;">Te deben tus compañeros</h3>
            </div>

            @if ($misCobros->isEmpty())
                <div style="padding: 16px; background: #f9fafb; border-radius: 10px; text-align: center; color: #6f6f6a; font-size: 14px;">
                    Ningún participante tiene deudas directas contigo actualmente.
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach ($misCobros as $cobro)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; background: #f0fff4; border: 1px solid #c6f6d5; border-radius: 10px;">
                            <div>
                                <div style="font-weight: 700; color: #22543d; font-size: 15px;">
                                    Bs {{ number_format($cobro['monto'], 2) }}
                                </div>
                                <div style="font-size: 13px; color: #4a5568;">
                                    Deudor: <span style="font-weight: 600;">{{ $cobro['deudor']->name ?? 'Compañero' }}</span>
                                </div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: #276749; background: #ffffff; border: 1px solid #c6f6d5; padding: 4px 10px; border-radius: 50px;">
                                Pendiente de pago
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Sección de Pagos y Liquidaciones Pendientes (CA-03 y CA-04) --}}
    <div style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 14px; padding: 22px; margin-bottom: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 700; color: #1c1c1a;">Pagos y Liquidaciones</h3>
                <p style="margin: 0; font-size: 13px; color: #6f6f6a;">
                    Los pagos registrados quedan en estado pendiente hasta la confirmación explícita del receptor.
                </p>
            </div>
        </div>

        @if ($pagos->isEmpty())
            <div style="padding: 20px; text-align: center; color: #6f6f6a; background: #fbfbf9; border: 1px dashed #d1d1cd; border-radius: 10px; font-size: 14px;">
                No se han registrado transferencias ni pagos entre participantes todavía.
            </div>
        @else
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach ($pagos as $pago)
                    @php
                        $soyReceptor = (int) auth()->id() === (int) $pago->receptor_id;
                        $soyDeudor = (int) auth()->id() === (int) $pago->deudor_id;
                    @endphp
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; border: 1px solid {{ $pago->estado === 'confirmado' ? '#c2e2c8' : ($pago->estado === 'rechazado' ? '#fecaca' : '#fed7aa') }}; background: {{ $pago->estado === 'confirmado' ? '#f7fdf9' : ($pago->estado === 'rechazado' ? '#fffafa' : '#fffaf0') }}; border-radius: 12px; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                                <span style="font-size: 17px; font-weight: 700; color: #1c1c1a;">
                                    Bs {{ number_format($pago->monto, 2) }}
                                </span>
                                <span class="badge-estado-pago" style="font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 50px; text-transform: uppercase;
                                      background: {{ $pago->estado === 'confirmado' ? '#edf7ef' : ($pago->estado === 'rechazado' ? '#fee2e2' : '#fef3c7') }};
                                      color: {{ $pago->estado === 'confirmado' ? '#25663a' : ($pago->estado === 'rechazado' ? '#991b1b' : '#92400e') }};">
                                    {{ $pago->estado }}
                                </span>
                            </div>
                            <div style="font-size: 13px; color: #4a4a49;">
                                De <span style="font-weight: 600;">{{ $pago->deudor->name ?? 'Usuario' }}</span>
                                para <span style="font-weight: 600;">{{ $pago->receptor->name ?? 'Usuario' }}</span>
                                · {{ $pago->created_at->format('d/m/Y H:i') }}
                            </div>
                            @if ($pago->tieneComprobante())
                                <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <button type="button"
                                            class="boton boton-claro btn-ver-comprobante"
                                            style="min-height: 32px; padding: 4px 10px; font-size: 12px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px;"
                                            onclick="document.getElementById('modal-comprobante-{{ $pago->id }}').showModal()"
                                            aria-label="Ver comprobante adjunto del pago {{ $pago->id }}">
                                        <x-icono nombre="pago" style="width: 14px; height: 14px;" />
                                        <span>Ver Comprobante</span>
                                    </button>

                                    @if ($pago->esEscaneoVerificado())
                                        <span class="badge-escaneo verificado" style="font-size: 11px; font-weight: 700; color: #166534; background: #dcfce7; border: 1px solid #bbf7d0; padding: 2px 8px; border-radius: 50px; display: inline-flex; align-items: center; gap: 4px;">
                                            <x-icono nombre="check" style="width: 12px; height: 12px;" />
                                            <span>Monto Verificado: Bs {{ number_format($pago->monto_detectado, 2) }}{{ $pago->banco_detectado ? ' (' . $pago->banco_detectado . ')' : '' }}</span>
                                        </span>
                                    @elseif ($pago->esEscaneoDiscrepante())
                                        <span class="badge-escaneo discrepante" style="font-size: 11px; font-weight: 700; color: #991b1b; background: #fee2e2; border: 1px solid #fecaca; padding: 2px 8px; border-radius: 50px; display: inline-flex; align-items: center; gap: 4px;">
                                            <x-icono nombre="bloqueado" style="width: 12px; height: 12px;" />
                                            <span>Alerta: Comprobante indica Bs {{ number_format($pago->monto_detectado, 2) }}</span>
                                        </span>
                                    @elseif ($pago->esEscaneoInvalido())
                                        <span class="badge-escaneo invalido" style="font-size: 11px; font-weight: 600; color: #854d0e; background: #fef9c3; border: 1px solid #fef08a; padding: 2px 8px; border-radius: 50px;">
                                            Documento no concluyente
                                        </span>
                                    @endif
                                </div>

                                {{-- Modal para ampliar y revisar el comprobante --}}
                                <dialog id="modal-comprobante-{{ $pago->id }}" style="border: none; border-radius: 16px; padding: 24px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                        <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #1c1c1a;">Comprobante de Pago</h3>
                                        <button type="button" onclick="document.getElementById('modal-comprobante-{{ $pago->id }}').close()" style="border: none; background: transparent; font-size: 20px; cursor: pointer; color: #6f6f6a;" aria-label="Cerrar modal">&times;</button>
                                    </div>

                                    @if ($pago->monto_detectado && abs((float) $pago->monto_detectado - (float) $pago->monto) < 0.01)
                                        <div class="badge-ia-verificado" style="background: #edf7ef; color: #25663a; border: 1px solid #c2e2c8; padding: 10px 14px; border-radius: 10px; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px; margin-bottom: 14px;">
                                            <x-icono nombre="check" style="width: 16px; height: 16px; color: #25663a;" />
                                            <span>Comprobante verificado por IA: Monto coincide (Bs {{ number_format($pago->monto, 2) }})</span>
                                        </div>
                                    @endif

                                    <div style="text-align: center; margin-bottom: 16px;">
                                        <img src="{{ asset('storage/' . $pago->comprobante_path) }}" alt="Comprobante de pago" style="max-width: 100%; max-height: 350px; border-radius: 10px; border: 1px solid #e5e7eb;">
                                    </div>
                                    <div style="background: #f9fafb; padding: 12px; border-radius: 8px; font-size: 13px; color: #374151;">
                                        <div><strong>Monto registrado:</strong> Bs {{ number_format($pago->monto, 2) }}</div>
                                        @if ($pago->monto_detectado)
                                            <div><strong>Monto detectado por escáner:</strong> Bs {{ number_format($pago->monto_detectado, 2) }}</div>
                                        @endif
                                        @if ($pago->banco_detectado)
                                            <div><strong>Banco emisor:</strong> {{ $pago->banco_detectado }}</div>
                                        @endif
                                    </div>
                                    <div style="margin-top: 16px; display: flex; justify-content: flex-end; align-items: center; gap: 12px;">
                                        <button type="button" class="boton boton-claro" style="border-radius: 50px; min-height: 44px; min-width: 44px; padding: 8px 18px;" onclick="document.getElementById('modal-comprobante-{{ $pago->id }}').close()">Cerrar</button>
                                        @if ($pago->estado === 'pendiente')
                                            @can('confirmar', $pago)
                                                <form method="POST" action="{{ route('pagos.confirmar', $pago) }}" style="margin: 0;">
                                                    @csrf
                                                    <button type="submit"
                                                            class="boton"
                                                            style="min-height: 44px; min-width: 44px; padding: 8px 20px; border-radius: 50px; font-size: 13px; font-weight: 600; background: #25663a; color: #ffffff; display: inline-flex; align-items: center; gap: 6px;"
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
                                <div style="margin-top: 6px; font-size: 13px; color: #991b1b; background: #ffffff; padding: 6px 12px; border-radius: 6px; border: 1px solid #fecaca;">
                                    <span style="font-weight: 600;">Motivo de rechazo:</span> &laquo;{{ $pago->motivo_rechazo }}&raquo;
                                </div>
                            @endif
                        </div>

                        {{-- Botones de acción del receptor protegidos con Policy (Fitts >= 44px) --}}
                        <div style="display: flex; align-items: center; gap: 10px;">
                            @if ($pago->estado === 'pendiente')
                                @can('confirmar', $pago)
                                    <form method="POST" action="{{ route('pagos.confirmar', $pago) }}" style="margin: 0;">
                                        @csrf
                                        <button type="submit"
                                                class="boton"
                                                style="min-height: 44px; min-width: 44px; padding: 8px 18px; border-radius: 50px; font-size: 13px; font-weight: 600; background: #25663a; color: #ffffff; display: inline-flex; align-items: center; gap: 6px;"
                                                aria-label="Confirmar pago de Bs {{ number_format($pago->monto, 2) }}">
                                            <x-icono nombre="check" style="width: 14px; height: 14px;" />
                                            <span>Confirmar pago</span>
                                        </button>
                                    </form>

                                    <button type="button"
                                            class="boton boton-claro"
                                            style="min-height: 44px; min-width: 44px; padding: 8px 18px; border-radius: 50px; font-size: 13px; font-weight: 600; color: #991b1b; border-color: #fca5a5; display: inline-flex; align-items: center; gap: 6px;"
                                            onclick="document.getElementById('modal-rechazar-{{ $pago->id }}').showModal()"
                                            aria-label="Rechazar pago de Bs {{ number_format($pago->monto, 2) }}">
                                        <x-icono nombre="bloqueado" style="width: 14px; height: 14px;" />
                                        <span>Rechazar</span>
                                    </button>
                                @else
                                    <span style="font-size: 13px; color: #8a6100; font-weight: 500;">
                                        Esperando confirmación del receptor
                                    </span>
                                @endcan
                            @elseif ($pago->estado === 'confirmado')
                                <span style="font-size: 13px; color: #25663a; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                                    <x-icono nombre="check" style="width: 14px; height: 14px;" />
                                    <span>Liquidado el {{ $pago->fecha_resolucion?->format('d/m/Y') }}</span>
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Modal nativo de rechazo para este pago --}}
                    @can('rechazar', $pago)
                        <dialog id="modal-rechazar-{{ $pago->id }}" style="border: none; border-radius: 16px; padding: 24px; width: 100%; max-width: 460px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
                            <form method="POST" action="{{ route('pagos.rechazar', $pago) }}">
                                @csrf
                                <h3 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 700; color: #991b1b;">Rechazar Pago</h3>
                                <p style="margin: 0 0 16px 0; font-size: 14px; color: #6f6f6a;">
                                    Indica al deudor la razón por la cual no aceptas este pago (ej. no recibido en tu banco o monto incorrecto).
                                </p>
                                <div style="margin-bottom: 20px;">
                                    <label for="motivo-rechazo-{{ $pago->id }}" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                                        Motivo del rechazo (obligatorio)
                                    </label>
                                    <textarea id="motivo-rechazo-{{ $pago->id }}"
                                              name="motivo_rechazo"
                                              rows="3"
                                              required
                                              minlength="5"
                                              placeholder="Explica detalladamente por qué rechazas este pago..."
                                              style="width: 100%; padding: 12px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; box-sizing: border-box;"></textarea>
                                </div>
                                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                                    <button type="button"
                                            class="boton boton-claro"
                                            style="min-height: 44px; min-width: 44px; border-radius: 50px;"
                                            onclick="document.getElementById('modal-rechazar-{{ $pago->id }}').close()">
                                        Cancelar
                                    </button>
                                    <button type="submit"
                                            class="boton"
                                            style="min-height: 44px; min-width: 44px; border-radius: 50px; background: #991b1b; color: #ffffff;">
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
    <div style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 14px; padding: 22px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="margin: 0 0 4px 0; font-size: 18px; font-weight: 700; color: #1c1c1a;">Historial de Gastos</h3>
                <p style="margin: 0; font-size: 13px; color: #6f6f6a;">Detalle de consumos y cuotas asignadas a cada participante.</p>
            </div>
        </div>

        @if ($gastos->isEmpty())
            <div style="padding: 32px 20px; text-align: center; color: #6f6f6a; background: #fbfbf9; border: 1px dashed #d1d1cd; border-radius: 12px;">
                <p style="margin: 0 0 12px 0; font-size: 15px;">No hay gastos registrados en este plan todavía.</p>
                @can('create', [\App\Models\Gasto::class, $plan])
                    <button type="button"
                            class="boton"
                            style="min-height: 44px; min-width: 44px; padding: 10px 20px; border-radius: 50px;"
                            onclick="document.getElementById('modal-crear-gasto').showModal()">
                        + Registrar el primer gasto
                    </button>
                @endcan
            </div>
        @else
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
                @foreach ($gastos as $gasto)
                    <div style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 12px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; gap: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 6px;">
                                <span style="font-size: 17px; font-weight: 700; color: #1c1c1a; word-break: break-word;">
                                    {{ $gasto->concepto }}
                                </span>
                                <span style="font-weight: 800; font-size: 16px; color: #25663a; white-space: nowrap;">
                                    Bs {{ number_format($gasto->monto_total, 2) }}
                                </span>
                            </div>
                            <div style="font-size: 13px; color: #6f6f6a; margin-bottom: 10px;">
                                Pagado por: <span style="font-weight: 600; color: #1c1c1a;">{{ $gasto->pagador->name ?? 'Usuario' }}</span>
                                <span style="margin: 0 4px;">·</span>
                                <span style="text-transform: capitalize;">{{ $gasto->tipo_division }}</span>
                            </div>

                            <div style="font-size: 12px; color: #4a4a49; border-top: 1px solid #f0f0ed; padding-top: 8px;">
                                <div style="font-weight: 600; margin-bottom: 4px; color: #6f6f6a;">Participantes ({{ $gasto->divisiones->count() }}):</div>
                                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                    @foreach ($gasto->divisiones as $div)
                                        <span style="background: #f4f4f2; border: 1px solid #e3e3df; border-radius: 6px; padding: 2px 8px; font-size: 12px;">
                                            {{ $div->user->name ?? 'Usuario' }}: <strong>Bs {{ number_format($div->monto_asignado, 2) }}</strong>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div style="font-size: 11px; color: #8e8e88; text-align: right;">
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
<dialog id="modal-crear-gasto" style="border: none; border-radius: 16px; padding: 28px; width: 100%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
    <form method="POST" action="{{ route('planes.gastos.guardar', $plan) }}">
        @csrf
        <h2 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #1c1c1a;">Registrar Nuevo Gasto</h2>
        <p style="margin: 0 0 20px 0; font-size: 14px; color: #6f6f6a;">Anota el consumo realizado y cómo se reparte entre los participantes.</p>

        <div style="margin-bottom: 16px;">
            <label for="input-gasto-concepto" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Concepto o descripción
            </label>
            <input type="text"
                   id="input-gasto-concepto"
                   name="concepto"
                   required
                   placeholder="Ej. Cena, Supermercado, Alquiler de cabaña"
                   style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; box-sizing: border-box; min-height: 44px;">
        </div>

        <div style="margin-bottom: 16px;">
            <label for="input-gasto-monto" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Monto total (Bs)
            </label>
            <input type="number"
                   id="input-gasto-monto"
                   name="monto_total"
                   step="0.01"
                   min="0.01"
                   required
                   placeholder="0.00"
                   oninput="recalcularDivision()"
                   style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; box-sizing: border-box; min-height: 44px;">
        </div>

        <div style="margin-bottom: 16px;">
            <label for="select-tipo-division" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Modo de división
            </label>
            <select id="select-tipo-division"
                    name="tipo_division"
                    required
                    onchange="cambiarModoDivision(this.value)"
                    style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; box-sizing: border-box; min-height: 44px;">
                <option value="equitativa">En partes iguales (Equitativa)</option>
                <option value="personalizada">Monto personalizado por persona</option>
            </select>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 8px;">
                Participantes incluidos
            </label>
            <div style="display: flex; flex-direction: column; gap: 8px; max-height: 180px; overflow-y: auto; padding: 10px; border: 1px solid #e3e3df; border-radius: 10px; background: #fafaf8;">
                @foreach ($participantesPlan as $p)
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer;">
                            <input type="checkbox"
                                   name="participantes[]"
                                   value="{{ $p->id }}"
                                   checked
                                   class="check-participante"
                                   onchange="recalcularDivision()"
                                   style="width: 18px; height: 18px;">
                            <span>{{ $p->name }}</span>
                        </label>
                        <div class="contenedor-monto-personalizado" style="display: none;">
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="divisiones[{{ $p->id }}]"
                                   placeholder="0.00"
                                   class="input-monto-indiv"
                                   oninput="actualizarSumaPersonalizada()"
                                   style="width: 100px; padding: 6px 10px; border: 1px solid #d1d1cd; border-radius: 8px; font-size: 13px;">
                        </div>
                    </div>
                @endforeach
            </div>
            <div id="aviso-suma-personalizada" style="display: none; margin-top: 8px; font-size: 13px; font-weight: 600; padding: 8px 12px; border-radius: 8px;"></div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
            <button type="button"
                    class="boton boton-claro"
                    style="min-height: 44px; min-width: 44px; border-radius: 50px;"
                    onclick="document.getElementById('modal-crear-gasto').close()">
                Cancelar
            </button>
            <button type="submit"
                    class="boton"
                    style="min-height: 44px; min-width: 44px; border-radius: 50px; background: #25663a; color: #ffffff;">
                Guardar Gasto
            </button>
        </div>
    </form>
</dialog>
@endcan

{{-- Modal accesible para Registrar Pago --}}
@can('create', [\App\Models\Pago::class, $plan])
<dialog id="modal-crear-pago" style="border: none; border-radius: 16px; padding: 28px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
    <form id="form-crear-pago" method="POST" action="{{ route('planes.pagos.guardar', $plan) }}" enctype="multipart/form-data">
        @csrf
        <h2 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #1c1c1a;">Registrar Pago</h2>
        <p style="margin: 0 0 20px 0; font-size: 14px; color: #6f6f6a;">
            Anota una transferencia o entrega de dinero. Puedes adjuntar tu comprobante para acelerar la verificación.
        </p>

        <div style="margin-bottom: 16px;">
            <label for="select-pago-receptor" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                ¿A quién le pagas?
            </label>
            <select id="select-pago-receptor"
                    name="receptor_id"
                    required
                    onchange="actualizarDatosReceptor(this.value)"
                    style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; min-height: 44px; box-sizing: border-box;">
                <option value="">-- Selecciona el destinatario --</option>
                @foreach ($participantesPlan as $p)
                    @if ($p->id !== auth()->id())
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endif
                @endforeach
            </select>
        </div>

        {{-- Panel reactivo con datos de cobro del receptor --}}
        <div id="contenedor-datos-receptor" style="display: none; margin-bottom: 18px; padding: 14px 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px;">
            <div style="font-size: 13px; font-weight: 700; color: #166534; margin-bottom: 6px;">
                Datos para transferir al receptor
            </div>
            <div id="texto-alias-receptor" style="font-size: 13px; color: #1f2937; margin-bottom: 4px;"></div>
            <div id="texto-cbu-receptor" style="font-size: 13px; color: #1f2937; margin-bottom: 4px;"></div>
            <div id="contenedor-qr-receptor" style="display: none; margin-top: 8px;">
                <span style="font-size: 12px; font-weight: 600; color: #166534; display: block; margin-bottom: 4px;">Código QR para escanear:</span>
                <img id="img-qr-receptor" src="" alt="QR del receptor" style="max-width: 140px; max-height: 140px; border-radius: 8px; border: 1px solid #d1d5db;">
            </div>
            <div id="aviso-sin-cobro" style="display: none; font-size: 12px; color: #854d0e; background: #fefce8; padding: 6px 10px; border-radius: 6px; border: 1px solid #fef08a;">
                El destinatario no ha configurado Alias o QR de cobro.
            </div>
        </div>

        {{-- Selector ergonómico de intención (Ley de Hick & Fitts): Pago Total vs Parcial --}}
        <div id="selector-modalidad-pago" style="margin-bottom: 16px;">
            <label style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 8px;">
                Modalidad de pago
            </label>
            <div role="radiogroup" aria-label="Seleccionar modalidad de pago" style="display: flex; gap: 12px; flex-wrap: wrap;">
                <label id="label-pago-total"
                       style="flex: 1; min-width: 170px; min-height: 44px; display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border: 1px solid #c2e2c8; background: #edf7ef; border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 600; color: #25663a;">
                    <input type="radio"
                           name="modalidad_pago"
                           id="radio-pago-total"
                           value="total"
                           checked
                           onchange="cambiarModalidadPago('total')">
                    <span id="texto-radio-pago-total">Pagar totalidad (Bs 0.00)</span>
                </label>

                <label id="label-pago-parcial"
                       style="flex: 1; min-width: 170px; min-height: 44px; display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border: 1px solid #d1d1cd; background: #ffffff; border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 500; color: #4a4a49;">
                    <input type="radio"
                           name="modalidad_pago"
                           id="radio-pago-parcial"
                           value="parcial"
                           onchange="cambiarModalidadPago('parcial')">
                    <span>Pago parcial</span>
                </label>
            </div>
            <div id="aviso-pago-modalidad" style="font-size: 12px; color: #25663a; margin-top: 6px;">
                Liquidación completa sin fricción de tipeo (campo numérico protegido).
            </div>
        </div>

        <div style="margin-bottom: 18px;">
            <label for="input-pago-monto" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Monto a pagar (Bs)
            </label>
            <input type="number"
                   id="input-pago-monto"
                   name="monto"
                   step="0.01"
                   min="0.01"
                   required
                   readonly
                   oninput="verificarMontoParcial(this.value)"
                   placeholder="0.00"
                   style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; min-height: 44px; box-sizing: border-box; background: #f9fafb;">
            <div id="aviso-saldo-restante" style="display: none; font-size: 12px; margin-top: 4px;"></div>
        </div>

        <div style="margin-bottom: 24px;">
            <label for="input-pago-comprobante" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Comprobante de Transferencia (Opcional - Imagen)
            </label>
            <input type="file"
                   id="input-pago-comprobante"
                   name="comprobante"
                   accept="image/png, image/jpeg, image/jpg, image/webp"
                   onchange="previsualizarComprobante(this)"
                   style="width: 100%; padding: 10px 12px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 13px; box-sizing: border-box;">
            <p style="margin: 4px 0 8px 0; font-size: 12px; color: #6f6f6a;">
                El escaneo inteligente procesará la imagen para extraer el monto y banco emisor.
            </p>
            <div id="preview-comprobante-contenedor" style="display: none; margin-top: 8px;">
                <img id="img-preview-comprobante" src="" alt="Vista previa" style="max-width: 100%; max-height: 160px; border-radius: 8px; border: 1px solid #d1d1cd;">
            </div>
        </div>

        {{-- Banner de feedback durante el procesamiento (Nielsen H1) --}}
        <div id="aviso-procesando-pago"
             style="display: none; margin-bottom: 18px; padding: 12px 14px; background: #fff4d6; border: 1px solid #ffe683; border-radius: 10px; color: #8a6100; font-size: 13px; align-items: center; gap: 10px;"
             role="status"
             aria-live="polite">
            <x-icono nombre="spinner" clases="w-5 h-5 flex-shrink-0" />
            <div>
                <strong>Procesando tu pago...</strong>
                <div style="font-size: 12px; margin-top: 2px; color: #78350f;">
                    Estamos registrando la transferencia y analizando el comprobante. Por favor, no cierres esta ventana.
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <button type="button"
                    id="btn-cancelar-pago"
                    class="boton boton-claro"
                    style="min-height: 44px; min-width: 44px; border-radius: 50px;"
                    onclick="document.getElementById('modal-crear-pago').close()">
                Cancelar
            </button>
            <button type="submit"
                    id="btn-submit-pago"
                    class="boton"
                    style="min-height: 44px; min-width: 44px; border-radius: 50px; background: #25663a; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
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
<dialog id="modal-configurar-cobro" style="border: none; border-radius: 16px; padding: 28px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
    <form method="POST" action="{{ route('perfil.cobro.actualizar') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <h2 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #1c1c1a;">Mis Datos de Cobro</h2>
        <p style="margin: 0 0 20px 0; font-size: 14px; color: #6f6f6a;">
            Configura tu Alias, CBU o sube tu código QR para que tus compañeros puedan transferirte fácilmente.
        </p>

        <div style="margin-bottom: 16px;">
            <label for="input-perfil-alias" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Alias de Cobro (ej. banco o billetera móvil)
            </label>
            <input type="text"
                   id="input-perfil-alias"
                   name="alias_pago"
                   value="{{ old('alias_pago', $miUsuario->alias_pago) }}"
                   placeholder="mi.alias.pago"
                   style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; min-height: 44px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 16px;">
            <label for="input-perfil-cbu" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                CBU / N° de Cuenta Bancaria
            </label>
            <input type="text"
                   id="input-perfil-cbu"
                   name="cbu_pago"
                   value="{{ old('cbu_pago', $miUsuario->cbu_pago) }}"
                   placeholder="10000000000000"
                   style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; min-height: 44px; box-sizing: border-box;">
        </div>

        <div style="margin-bottom: 24px;">
            <label for="input-perfil-qr" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Imagen de Código QR (Simple / Billetera)
            </label>
            <input type="file"
                   id="input-perfil-qr"
                   name="qr_pago"
                   accept="image/*"
                   style="width: 100%; padding: 10px 12px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 13px; box-sizing: border-box;">
            @if ($miUsuario->qr_pago_path)
                <div style="margin-top: 10px;">
                    <span style="font-size: 12px; color: #6f6f6a; display: block; margin-bottom: 4px;">QR configurado actualmente:</span>
                    <img src="{{ asset('storage/' . $miUsuario->qr_pago_path) }}" alt="QR actual" style="max-width: 120px; max-height: 120px; border-radius: 8px; border: 1px solid #d1d1cd;">
                </div>
            @endif
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <button type="button"
                    class="boton boton-claro"
                    style="min-height: 44px; min-width: 44px; border-radius: 50px;"
                    onclick="document.getElementById('modal-configurar-cobro').close()">
                Cancelar
            </button>
            <button type="submit"
                    class="boton"
                    style="min-height: 44px; min-width: 44px; border-radius: 50px; background: #25663a; color: #ffffff;">
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
                labelTotal.style.background = '#edf7ef';
                labelTotal.style.borderColor = '#c2e2c8';
                labelTotal.style.color = '#25663a';
                labelTotal.style.fontWeight = '600';
            }
            if (labelParcial) {
                labelParcial.style.background = '#ffffff';
                labelParcial.style.borderColor = '#d1d1cd';
                labelParcial.style.color = '#4a4a49';
                labelParcial.style.fontWeight = '500';
            }
            if (inputMonto) {
                inputMonto.readOnly = true;
                inputMonto.style.background = '#f9fafb';
                inputMonto.value = deudaSeleccionadaActual > 0 ? deudaSeleccionadaActual.toFixed(2) : '';
            }
            if (avisoModalidad) {
                avisoModalidad.textContent = 'Liquidación completa sin fricción de tipeo (campo numérico protegido).';
                avisoModalidad.style.color = '#25663a';
            }
            if (avisoSaldo) {
                avisoSaldo.style.display = 'none';
            }
        } else {
            // Parcial
            if (labelTotal) {
                labelTotal.style.background = '#ffffff';
                labelTotal.style.borderColor = '#d1d1cd';
                labelTotal.style.color = '#4a4a49';
                labelTotal.style.fontWeight = '500';
            }
            if (labelParcial) {
                labelParcial.style.background = '#edf7ef';
                labelParcial.style.borderColor = '#c2e2c8';
                labelParcial.style.color = '#25663a';
                labelParcial.style.fontWeight = '600';
            }
            if (inputMonto) {
                inputMonto.readOnly = false;
                inputMonto.style.background = '#ffffff';
                inputMonto.focus();
            }
            if (avisoModalidad) {
                avisoModalidad.textContent = 'Indica el monto parcial que deseas liquidar hoy.';
                avisoModalidad.style.color = '#4a4a49';
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
            avisoSaldo.textContent = 'Alerta: El monto ingresado (Bs ' + monto.toFixed(2) + ') supera la deuda exigible (Bs ' + deudaSeleccionadaActual.toFixed(2) + ').';
            avisoSaldo.style.color = '#991b1b';
            avisoSaldo.style.fontWeight = '600';
        } else if (monto > 0) {
            var saldoRestante = (deudaSeleccionadaActual - monto);
            avisoSaldo.textContent = 'Saldo restante estimado tras este pago: Bs ' + saldoRestante.toFixed(2);
            avisoSaldo.style.color = '#25663a';
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
            divs.forEach(function(el) { el.style.display = 'block'; });
            if (aviso) aviso.style.display = 'block';
            actualizarSumaPersonalizada();
        } else {
            divs.forEach(function(el) { el.style.display = 'none'; });
            if (aviso) aviso.style.display = 'none';
        }
    }

    function recalcularDivision() {
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
            aviso.style.background = '#edf7ef';
            aviso.style.color = '#25663a';
            aviso.style.border = '1px solid #c2e2c8';
        } else {
            aviso.textContent = 'Aviso: Suma actual: Bs ' + suma.toFixed(2) + ' / Total requerido: Bs ' + montoTotal.toFixed(2) + ' (Diferencia: Bs ' + (montoTotal - suma).toFixed(2) + ')';
            aviso.style.background = '#fff4d6';
            aviso.style.color = '#8a6100';
            aviso.style.border = '1px solid #ffe683';
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

    document.addEventListener('DOMContentLoaded', function () {
        var formPago = document.getElementById('form-crear-pago');
        var modalPago = document.getElementById('modal-crear-pago');

        if (formPago) {
            formPago.addEventListener('submit', function (e) {
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
                    setTimeout(function () {
                        btnSubmit.disabled = true;
                    }, 20);
                }
            });
        }

        if (modalPago) {
            modalPago.addEventListener('cancel', function (e) {
                if (formPago && formPago.dataset.enviando === '1') {
                    e.preventDefault();
                }
            });
        }
    });

    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            resetearEstadoModalPago();
        }
    });
</script>

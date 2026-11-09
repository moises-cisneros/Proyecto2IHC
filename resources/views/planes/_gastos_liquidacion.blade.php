<div class="seccion-gastos-liquidacion" style="margin-top: 10px;">

    {{-- Banner de métricas generales y balance personal --}}
    @php
        $miId = auth()->id();
        $miBalance = $saldos['balances'][$miId] ?? null;
        $misDeudas = collect($saldos['deudas'])->where('deudor_id', $miId)->values();
        $misCobros = collect($saldos['deudas'])->where('acreedor_id', $miId)->values();
        $totalNeto = $miBalance['neto'] ?? 0.0;
        $participantesPlan = collect([$plan->user])->merge($plan->participantes)->unique('id')->values();
    @endphp

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
        {{-- Tarjeta 1: Total gastado en el grupo --}}
        <div style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 14px; padding: 18px 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.03);">
            <div style="font-size: 13px; color: #6f6f6a; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Total Gastado en el Plan
            </div>
            <div style="font-size: 26px; font-weight: 800; color: #1c1c1a;">
                ${{ number_format($saldos['total_gastado'], 2) }}
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
                {{ $totalNeto > 0 ? '+' : '' }}${{ number_format($totalNeto, 2) }}
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
                    💸 Registrar Pago
                </button>
            @endcan
        </div>
    </div>

    {{-- Matriz Clara de Deudas (Hick & Fitts): Debes vs Te deben --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 28px;">
        {{-- Columna 1: Cuentas por Pagar (Debes) --}}
        <div style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 14px; padding: 20px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 14px;">
                <span style="font-size: 18px;" aria-hidden="true">📤</span>
                <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #1c1c1a;">Debes a tus compañeros</h3>
            </div>

            @if ($misDeudas->isEmpty())
                <div style="padding: 16px; background: #f9fafb; border-radius: 10px; text-align: center; color: #6f6f6a; font-size: 14px;">
                    ✓ No tienes deudas pendientes con ningún participante.
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach ($misDeudas as $deuda)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 10px;">
                            <div>
                                <div style="font-weight: 700; color: #9b2c2c; font-size: 15px;">
                                    ${{ number_format($deuda['monto'], 2) }}
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
                                        aria-label="Pagar ${{ number_format($deuda['monto'], 2) }} a {{ $deuda['acreedor']->name }}">
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
                <span style="font-size: 18px;" aria-hidden="true">📥</span>
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
                                    ${{ number_format($cobro['monto'], 2) }}
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
                                    ${{ number_format($pago->monto, 2) }}
                                </span>
                                <span style="font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 50px; text-transform: uppercase;
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
                                                style="min-height: 44px; min-width: 44px; padding: 8px 18px; border-radius: 50px; font-size: 13px; font-weight: 600; background: #25663a; color: #ffffff;"
                                                aria-label="Confirmar pago de ${{ number_format($pago->monto, 2) }}">
                                            ✓ Confirmar pago
                                        </button>
                                    </form>

                                    <button type="button"
                                            class="boton boton-claro"
                                            style="min-height: 44px; min-width: 44px; padding: 8px 18px; border-radius: 50px; font-size: 13px; font-weight: 600; color: #991b1b; border-color: #fca5a5;"
                                            onclick="document.getElementById('modal-rechazar-{{ $pago->id }}').showModal()"
                                            aria-label="Rechazar pago de ${{ number_format($pago->monto, 2) }}">
                                        ✕ Rechazar
                                    </button>
                                @else
                                    <span style="font-size: 13px; color: #8a6100; font-weight: 500;">
                                        Esperando confirmación del receptor
                                    </span>
                                @endcan
                            @elseif ($pago->estado === 'confirmado')
                                <span style="font-size: 13px; color: #25663a; font-weight: 600;">
                                    ✓ Liquidado el {{ $pago->fecha_resolucion?->format('d/m/Y') }}
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
                                    ${{ number_format($gasto->monto_total, 2) }}
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
                                            {{ $div->user->name ?? 'Usuario' }}: <strong>${{ number_format($div->monto_asignado, 2) }}</strong>
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
                Monto total ($)
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
    <form method="POST" action="{{ route('planes.pagos.guardar', $plan) }}">
        @csrf
        <h2 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #1c1c1a;">Registrar Pago</h2>
        <p style="margin: 0 0 20px 0; font-size: 14px; color: #6f6f6a;">
            Anota una transferencia o entrega de dinero. El pago quedará pendiente hasta que el receptor lo confirme.
        </p>

        <div style="margin-bottom: 16px;">
            <label for="select-pago-receptor" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                ¿A quién le pagas?
            </label>
            <select id="select-pago-receptor"
                    name="receptor_id"
                    required
                    style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; min-height: 44px; box-sizing: border-box;">
                <option value="">-- Selecciona el destinatario --</option>
                @foreach ($participantesPlan as $p)
                    @if ($p->id !== auth()->id())
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endif
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 24px;">
            <label for="input-pago-monto" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Monto pagado ($)
            </label>
            <input type="number"
                   id="input-pago-monto"
                   name="monto"
                   step="0.01"
                   min="0.01"
                   required
                   placeholder="0.00"
                   style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; min-height: 44px; box-sizing: border-box;">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <button type="button"
                    class="boton boton-claro"
                    style="min-height: 44px; min-width: 44px; border-radius: 50px;"
                    onclick="document.getElementById('modal-crear-pago').close()">
                Cancelar
            </button>
            <button type="submit"
                    class="boton"
                    style="min-height: 44px; min-width: 44px; border-radius: 50px; background: #25663a; color: #ffffff;">
                Enviar Pago
            </button>
        </div>
    </form>
</dialog>
@endcan

<script>
    function abrirModalPagoDirecto(receptorId, montoSugerido) {
        var modal = document.getElementById('modal-crear-pago');
        if (!modal) return;

        var select = document.getElementById('select-pago-receptor');
        var inputMonto = document.getElementById('input-pago-monto');

        if (select && receptorId) {
            select.value = receptorId;
        }
        if (inputMonto && montoSugerido) {
            inputMonto.value = parseFloat(montoSugerido).toFixed(2);
        }
        modal.showModal();
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
            aviso.textContent = '✓ Los montos asignados coinciden exactamente con el total ($' + suma.toFixed(2) + ').';
            aviso.style.background = '#edf7ef';
            aviso.style.color = '#25663a';
            aviso.style.border = '1px solid #c2e2c8';
        } else {
            aviso.textContent = '⚠️ Suma actual: $' + suma.toFixed(2) + ' / Total requerido: $' + montoTotal.toFixed(2) + ' (Diferencia: $' + (montoTotal - suma).toFixed(2) + ')';
            aviso.style.background = '#fff4d6';
            aviso.style.color = '#8a6100';
            aviso.style.border = '1px solid #ffe683';
        }
    }
</script>

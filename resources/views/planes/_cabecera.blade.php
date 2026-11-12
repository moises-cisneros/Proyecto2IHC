<div class="cabecera-plan" style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 16px; padding: 24px; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div style="flex: 1; min-width: 280px;">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 8px;">
                <h1 style="margin: 0; font-size: 28px; font-weight: 700; color: #1c1c1a;">{{ $plan->nombre }}</h1>
                <span class="estado estado-{{ $plan->estado }}" style="padding: 4px 12px; border-radius: 9999px; font-size: 13px; font-weight: 600;">
                    @if ($plan->estado === 'pendiente') Pendiente
                    @elseif ($plan->estado === 'confirmado') Confirmado
                    @else Cancelado
                    @endif
                </span>
            </div>
            
            <div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap; color: #6f6f6a; font-size: 14px; margin-top: 8px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #1c1c1a; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0;" aria-hidden="true">
                        {{ strtoupper(substr($plan->organizador, 0, 1)) }}
                    </div>
                    <div>
                        <span style="font-weight: 600; color: #1c1c1a;">Organizador:</span>
                        <span>{{ $plan->organizador }}</span>
                    </div>
                </div>
                <div>
                    <span style="font-weight: 600; color: #1c1c1a;">Fecha límite de votación:</span>
                    <span>{{ $plan->fecha_limite->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="{{ route('planes.listar') }}"
               class="boton boton-claro"
               style="min-height: 44px; min-width: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 10px 18px; text-decoration: none;"
               aria-label="Volver a Mis Planes">
                &larr; Volver a Mis Planes
            </a>
            @can('update', $plan)
                <a href="{{ route('planes.editar', $plan) }}"
                   class="boton boton-claro"
                   style="min-height: 44px; min-width: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 10px 18px; text-decoration: none;"
                   aria-label="Editar configuración del plan">
                    Editar plan
                </a>
            @endcan
            @can('retirarse', $plan)
                <button type="button"
                        class="boton boton-claro boton-retirarse"
                        style="min-height: 44px; min-width: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 10px 18px; color: #c0392b; border-color: #fca5a5;"
                        onclick="if (window.track) { window.track('retiro_iniciado', { plan_id: {{ $plan->id }} }); } document.getElementById('modal-retirarse').showModal();"
                        aria-label="Retirarme del plan">
                    Retirarme del plan
                </button>
            @endcan
        </div>
    </div>

    @if ($plan->estado === 'cancelado')
        <div style="margin-top: 18px; padding: 14px 18px; background: #f4f4f5; border: 1px solid #e4e4e7; border-radius: 12px; display: flex; align-items: center; gap: 12px; color: #52525b;">
            <x-icono nombre="bloqueado" style="width: 24px; height: 24px; color: #52525b; flex-shrink: 0;" />
            <div>
                <div style="font-weight: 700; color: #27272a; font-size: 14px;">Plan cancelado</div>
                <div style="font-size: 13px;">Este plan ha sido cancelado. La sala se encuentra en modo de solo lectura (no se permiten ediciones, invitaciones, nuevas propuestas ni votos).</div>
            </div>
        </div>
    @endif

    @can('aceptar', $plan)
        <div style="margin-top: 18px; padding: 16px 20px; background: #fefce8; border: 1px solid #fef08a; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-weight: 700; color: #854d0e; font-size: 15px;">¡Fuiste invitado a colaborar en este plan!</div>
                <div style="color: #a16207; font-size: 13px;">Acepta la invitación para proponer fechas, lugares y votar en las opciones del grupo.</div>
            </div>
            <form method="POST" action="{{ route('planes.aceptar', $plan) }}" style="margin: 0;">
                @csrf
                <button type="submit"
                        class="boton"
                        style="min-height: 44px; min-width: 44px; padding: 10px 20px; font-weight: 600;"
                        aria-label="Aceptar invitación al plan">
                    Aceptar invitación
                </button>
            </form>
        </div>
    @endcan
</div>

{{-- Diálogo modal accesible para confirmar retiro de plan (Nielsen H3, H5, H6, Hick y Fitts) --}}
@can('retirarse', $plan)
    @php
        $usuarioTieneDeuda = $plan->usuarioTieneDeuda(auth()->id());
        $montoDeuda = $usuarioTieneDeuda ? $plan->obtenerMontoDeudaUsuario(auth()->id()) : 0.0;
    @endphp
    <dialog id="modal-retirarse" style="border: none; border-radius: 16px; padding: 28px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
        <div style="margin-bottom: 16px;">
            <h2 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #1c1c1a; display: flex; align-items: center; gap: 8px;">
                <x-icono nombre="bloqueado" style="width: 22px; height: 22px; color: #c0392b;" />
                ¿Retirarse de este plan?
            </h2>
            <p style="margin: 0; font-size: 14px; color: #6f6f6a; line-height: 1.5;">
                Estás por retirarte como colaborador de <strong>&laquo;{{ $plan->nombre }}&raquo;</strong>.
            </p>
        </div>

        @if ($usuarioTieneDeuda)
            {{-- Banner informativo de advertencia y bloqueo por deuda pendiente (Nielsen H5 y H6) --}}
            <div style="margin-bottom: 20px; padding: 16px; background-color: #fbeceb; border: 1px solid #f5c6cb; border-radius: 12px; color: #c0392b;">
                <div style="font-weight: 700; font-size: 14px; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                    <x-icono nombre="tarjeta" style="width: 18px; height: 18px; color: #c0392b;" />
                    No puedes retirarte: tienes deudas pendientes
                </div>
                <p style="margin: 0 0 12px 0; font-size: 13px; line-height: 1.4; color: #7f1d1d;">
                    Tienes un saldo deudor acumulado de <strong>Bs {{ number_format($montoDeuda, 2) }}</strong> en los gastos compartidos de este plan. Para retirarte, primero debes liquidar tus cuentas pendientes con los demás miembros.
                </p>
                <a href="{{ route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']) }}"
                   class="boton boton-claro"
                   style="min-height: 44px; min-width: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 8px 16px; font-size: 13px; font-weight: 600; text-decoration: none; border-color: #f5c6cb; color: #c0392b;"
                   aria-label="Ir a Gastos y Liquidación para saldar deuda">
                    <x-icono nombre="dinero" style="width: 16px; height: 16px; margin-right: 6px;" /> Ir a Gastos y Liquidación
                </a>
            </div>

            {{-- Botón de acción para cerrar en caso de bloqueo con botón destructivo inhabilitado (Fitts >= 12px) --}}
            <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px;">
                <button type="button"
                        class="boton boton-claro"
                        style="min-height: 44px; min-width: 44px; padding: 10px 20px; border-radius: 50px;"
                        onclick="document.getElementById('modal-retirarse').close()"
                        aria-label="Mantener plan">
                    Mantener plan
                </button>
                <button type="button"
                        class="boton boton-rojo"
                        disabled
                        style="min-height: 44px; min-width: 44px; padding: 10px 22px; border-radius: 50px; background-color: #c0392b; color: #ffffff; opacity: 0.5; cursor: not-allowed;"
                        title="Bloqueado por deudas pendientes"
                        aria-disabled="true">
                    Sí, retirarme del plan
                </button>
            </div>
        @else
            {{-- Flujo normal sin deuda: advertencia sobre anulación de votos (Nielsen H5) --}}
            <div style="margin-bottom: 20px; padding: 14px 16px; background-color: #fff4d6; border: 1px solid #ffe683; border-radius: 12px; color: #8a6100; font-size: 13px; line-height: 1.4;">
                <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                    <x-icono nombre="calendario" style="width: 16px; height: 16px; color: #8a6100;" />
                    Anulación automática de votos
                </div>
                Al confirmar tu retiro, todos los votos que hayas emitido en las propuestas de fechas y lugares serán eliminados inmediatamente, recalculando el conteo de la sala en tiempo real. Esta acción es definitiva.
            </div>

            <form method="POST" action="{{ route('planes.retirarse', $plan) }}">
                @csrf
                {{-- Separación antagónica de >= 12px (gap-3) según Ley de Fitts y desambiguación léxica H3/Hick --}}
                <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
                    <button type="button"
                            class="boton boton-claro"
                            style="min-height: 44px; min-width: 44px; padding: 10px 20px; border-radius: 50px;"
                            onclick="document.getElementById('modal-retirarse').close()"
                            aria-label="Mantener plan">
                        Mantener plan
                    </button>
                    <button type="submit"
                            class="boton boton-rojo"
                            style="min-height: 44px; min-width: 44px; padding: 10px 22px; border-radius: 50px; background-color: #c0392b; color: #ffffff;"
                            aria-label="Sí, retirarme del plan">
                        Sí, retirarme del plan
                    </button>
                </div>
            </form>
        @endif
    </dialog>
@endcan

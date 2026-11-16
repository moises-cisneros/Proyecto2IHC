<div class="cabecera-plan">
    <div
        style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: var(--space-4);">
        <div style="flex: 1; min-width: 280px;">
            <div
                style="display: flex; align-items: center; gap: var(--space-3); flex-wrap: wrap; margin-bottom: var(--space-2);">
                <h1
                    style="margin: 0; font-size: var(--font-size-2xl); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                    {{ $plan->nombre }}</h1>
                <span class="estado estado-{{ $plan->estado }}"
                    style="padding: var(--space-1) var(--space-3); border-radius: var(--radius-pill); font-size: var(--font-size-sm); font-weight: var(--font-weight-semibold);">
                    @if ($plan->estado === 'pendiente')
                        Pendiente
                    @elseif ($plan->estado === 'confirmado')
                        Confirmado
                    @else
                        Cancelado
                    @endif
                </span>
            </div>

            <div
                style="display: flex; gap: var(--space-5); align-items: center; flex-wrap: wrap; color: var(--color-muted); font-size: var(--font-size-md); margin-top: var(--space-2);">
                <div style="display: flex; align-items: center; gap: var(--space-2);">
                    <div class="avatar-circulo" aria-hidden="true">
                        {{ strtoupper(substr($plan->organizador, 0, 1)) }}
                    </div>
                    <div>
                        <span
                            style="font-weight: var(--font-weight-semibold); color: var(--color-foreground);">Organizador:</span>
                        <span>{{ $plan->organizador }}</span>
                    </div>
                </div>
                <div>
                    <span style="font-weight: var(--font-weight-semibold); color: var(--color-foreground);">Fecha límite
                        de votación:</span>
                    <span>{{ $plan->fecha_limite->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: var(--space-3); align-items: center;">
            <a href="{{ route('planes.listar') }}" class="boton boton-claro"
                style="min-height: var(--touch-min); min-width: var(--touch-min); display: inline-flex; align-items: center; justify-content: center; padding: var(--space-2) var(--space-4); text-decoration: none;"
                aria-label="Volver a Mis Planes">
                &larr; Volver a Mis Planes
            </a>
            @can('update', $plan)
                <a href="{{ route('planes.editar', $plan) }}" class="boton boton-claro"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); display: inline-flex; align-items: center; justify-content: center; padding: var(--space-2) var(--space-4); text-decoration: none;"
                    aria-label="Editar configuración del plan">
                    Editar plan
                </a>
            @endcan
            @can('retirarse', $plan)
                <button type="button" class="boton boton-claro boton-retirarse"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); display: inline-flex; align-items: center; justify-content: center; padding: var(--space-2) var(--space-4); color: var(--color-destructive); border-color: var(--color-destructive-border);"
                    onclick="if (window.track) { window.track('retiro_iniciado', { plan_id: {{ $plan->id }} }); } document.getElementById('modal-retirarse').showModal();"
                    aria-label="Retirarme del plan">
                    Retirarme del plan
                </button>
            @endcan
        </div>
    </div>

    @if ($plan->estado === 'cancelado')
        <div class="banner-sala banner-cancelado">
            <x-icono nombre="bloqueado" style="width: 24px; height: 24px; color: var(--color-muted); flex-shrink: 0;" />
            <div>
                <div
                    style="font-weight: var(--font-weight-bold); color: var(--color-foreground); font-size: var(--font-size-md);">
                    Plan cancelado</div>
                <div style="font-size: var(--font-size-sm);">Este plan ha sido cancelado. La sala se encuentra en modo de solo lectura (no se permiten ediciones, invitaciones, nuevas propuestas ni votos).</div>
            </div>
        </div>
    @endif

    @can('aceptar', $plan)
        <div class="banner-sala banner-invitado">
            <div>
                <div
                    style="font-weight: var(--font-weight-bold); color: var(--color-warning); font-size: var(--font-size-base);">
                    ¡Fuiste invitado a colaborar en este plan!</div>
                <div style="color: var(--color-warning); font-size: var(--font-size-sm);">Acepta la invitación para proponer
                    fechas, lugares y votar en las opciones del grupo.</div>
            </div>
            <form method="POST" action="{{ route('planes.aceptar', $plan) }}" style="margin: 0;">
                @csrf
                <button type="submit" class="boton"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-5); font-weight: var(--font-weight-semibold);"
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
    <dialog id="modal-retirarse"
        style="border: none; border-radius: var(--radius-xl); padding: var(--space-6); width: 100%; max-width: 500px; box-shadow: var(--shadow-md);">
        <div style="margin-bottom: var(--space-4);">
            <h2
                style="margin: 0 0 var(--space-2) 0; font-size: var(--font-size-xl); font-weight: var(--font-weight-bold); color: var(--color-foreground); display: flex; align-items: center; gap: var(--space-2);">
                <x-icono nombre="bloqueado" style="width: 22px; height: 22px; color: var(--color-destructive);" />
                ¿Retirarse de este plan?
            </h2>
            <p style="margin: 0; font-size: var(--font-size-md); color: var(--color-muted); line-height: 1.5;">
                Estás por retirarte como colaborador de <strong>&laquo;{{ $plan->nombre }}&raquo;</strong>.
            </p>
        </div>

        @if ($usuarioTieneDeuda)
            {{-- Banner informativo de advertencia y bloqueo por deuda pendiente (Nielsen H5 y H6) --}}
            <div
                style="margin-bottom: var(--space-5); padding: var(--space-4); background-color: var(--color-destructive-bg); border: 1px solid var(--color-destructive-border); border-radius: var(--radius-lg); color: var(--color-destructive);">
                <div
                    style="font-weight: var(--font-weight-bold); font-size: var(--font-size-md); margin-bottom: var(--space-1); display: flex; align-items: center; gap: var(--space-1);">
                    <x-icono nombre="tarjeta" style="width: 18px; height: 18px; color: var(--color-destructive);" />
                    No puedes retirarte: tienes deudas pendientes
                </div>
                <p
                    style="margin: 0 0 var(--space-3) 0; font-size: var(--font-size-sm); line-height: 1.4; color: var(--color-destructive);">
                    Tienes un saldo deudor acumulado de <strong>Bs {{ number_format($montoDeuda, 2) }}</strong> en los
                    gastos compartidos de este plan. Para retirarte, primero debes liquidar tus cuentas pendientes con los
                    demás miembros.
                </p>
                <a href="{{ route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']) }}" class="boton boton-claro"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); display: inline-flex; align-items: center; justify-content: center; padding: var(--space-2) var(--space-4); font-size: var(--font-size-sm); font-weight: var(--font-weight-semibold); text-decoration: none; border-color: var(--color-destructive-border); color: var(--color-destructive);"
                    aria-label="Ir a Gastos y Liquidación para saldar deuda">
                    <x-icono nombre="dinero" style="width: 16px; height: 16px; margin-right: var(--space-1);" /> Ir a Gastos
                    y Liquidación
                </a>
            </div>

            {{-- Botón de acción para cerrar en caso de bloqueo con botón destructivo inhabilitado (Fitts >= 12px) --}}
            <div style="display: flex; justify-content: flex-end; gap: var(--space-3); margin-top: var(--space-5);">
                <button type="button" class="boton boton-claro"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-5); border-radius: var(--radius-pill);"
                    onclick="document.getElementById('modal-retirarse').close()" aria-label="Mantener plan">
                    Mantener plan
                </button>
                <button type="button" class="boton boton-rojo" disabled
                    style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-5); border-radius: var(--radius-pill); background-color: var(--color-destructive); color: var(--color-surface); opacity: 0.5; cursor: not-allowed;"
                    title="Bloqueado por deudas pendientes" aria-disabled="true">
                    Sí, retirarme del plan
                </button>
            </div>
        @else
            {{-- Flujo normal sin deuda: advertencia sobre anulación de votos (Nielsen H5) --}}
            <div
                style="margin-bottom: var(--space-5); padding: var(--space-3) var(--space-4); background-color: var(--color-warning-bg); border: 1px solid var(--color-warning-border); border-radius: var(--radius-lg); color: var(--color-warning); font-size: var(--font-size-sm); line-height: 1.4;">
                <div
                    style="font-weight: var(--font-weight-bold); margin-bottom: var(--space-1); display: flex; align-items: center; gap: var(--space-1);">
                    <x-icono nombre="calendario" style="width: 16px; height: 16px; color: var(--color-warning);" />
                    Anulación automática de votos
                </div>
                Al confirmar tu retiro, todos los votos que hayas emitido en las propuestas de fechas y lugares serán
                eliminados inmediatamente, recalculando el conteo de la sala en tiempo real. Esta acción es definitiva.
            </div>

            <form method="POST" action="{{ route('planes.retirarse', $plan) }}">
                @csrf
                {{-- Separación antagónica de >= 12px (gap-3) según Ley de Fitts y desambiguación léxica H3/Hick --}}
                <div style="display: flex; justify-content: flex-end; gap: var(--space-3); margin-top: var(--space-6);">
                    <button type="button" class="boton boton-claro"
                        style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-5); border-radius: var(--radius-pill);"
                        onclick="document.getElementById('modal-retirarse').close()" aria-label="Mantener plan">
                        Mantener plan
                    </button>
                    <button type="submit" class="boton boton-rojo"
                        style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-5); border-radius: var(--radius-pill); background-color: var(--color-destructive); color: var(--color-surface);"
                        aria-label="Sí, retirarme del plan">
                        Sí, retirarme del plan
                    </button>
                </div>
            </form>
        @endif
    </dialog>
@endcan

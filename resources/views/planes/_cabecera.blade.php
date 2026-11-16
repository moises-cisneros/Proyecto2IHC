<div class="cabecera-plan">
    <div class="cabecera-encabezado">
        <div class="cabecera-info">
            <div class="cabecera-titulo-fila">
                <h1 class="cabecera-titulo">
                    {{ $plan->nombre }}</h1>
                <span class="estado estado-{{ $plan->estado }} cabecera-estado">
                    @if ($plan->estado === 'pendiente')
                        Pendiente
                    @elseif ($plan->estado === 'confirmado')
                        Confirmado
                    @else
                        Cancelado
                    @endif
                </span>
            </div>

            <div class="cabecera-meta">
                <div class="cabecera-organizador">
                    <div class="avatar" aria-hidden="true">
                        {{ strtoupper(substr($plan->organizador, 0, 1)) }}
                    </div>
                    <div>
                        <span class="cabecera-meta-etiqueta">Organizador:</span>
                        <span>{{ $plan->organizador }}</span>
                    </div>
                </div>
                <div>
                    <span class="cabecera-meta-etiqueta">Fecha límite
                        de votación:</span>
                    <span>{{ $plan->fecha_limite->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <div class="cabecera-acciones">
            <a href="{{ route('planes.listar') }}" class="boton boton-claro" aria-label="Volver a Mis Planes">
                &larr; Volver a Mis Planes
            </a>
            @can('update', $plan)
                <a href="{{ route('planes.editar', $plan) }}" class="boton boton-claro"
                    aria-label="Editar configuración del plan">
                    Editar plan
                </a>
            @endcan
            @can('retirarse', $plan)
                <button type="button" class="boton boton-claro boton-retirarse cabecera-boton-retirarse"
                    onclick="if (window.track) { window.track('retiro_iniciado', { plan_id: {{ $plan->id }} }); } document.getElementById('modal-retirarse').showModal();"
                    aria-label="Retirarme del plan">
                    Retirarme del plan
                </button>
            @endcan
        </div>
    </div>

    @if ($plan->estado === 'cancelado')
        <div class="banner banner-neutro banner-fila">
            <x-icono nombre="bloqueado" tamano="lg" class="banner-icono" />
            <div>
                <div class="banner-titulo">
                    Plan cancelado</div>
                <div class="banner-texto">Este plan ha sido cancelado. La sala se encuentra en
                    <span>modo de solo lectura</span> (no se permiten ediciones, invitaciones, nuevas propuestas ni
                    votos).
                </div>
            </div>
        </div>
    @endif

    @can('aceptar', $plan)
        <div class="banner banner-advertencia banner-fila banner-espaciado">
            <div>
                <div class="banner-titulo">
                    ¡Fuiste invitado a colaborar en este plan!</div>
                <div class="banner-texto">Acepta la invitación para proponer
                    fechas, lugares y votar en las opciones del grupo.</div>
            </div>
            <div class="banner-acciones">
                <form class="form-reset" method="POST" action="{{ route('planes.aceptar', $plan) }}">
                    @csrf
                    <button type="submit" class="boton cabecera-boton-invitacion" aria-label="Aceptar invitación al plan">
                        Aceptar invitación
                    </button>
                </form>
                <form class="form-reset" method="POST" action="{{ route('planes.rechazar-invitacion', $plan) }}">
                    @csrf
                    <button type="submit" class="boton boton-claro cabecera-boton-invitacion"
                        aria-label="Rechazar invitación al plan">
                        Rechazar invitación
                    </button>
                </form>
            </div>
        </div>
    @endcan
</div>

{{-- Diálogo modal accesible para confirmar retiro de plan (Nielsen H3, H5, H6, Hick y Fitts) --}}
@can('retirarse', $plan)
    @php
        $usuarioTieneDeuda = $plan->usuarioTieneDeuda(auth()->id());
        $montoDeuda = $usuarioTieneDeuda ? $plan->obtenerMontoDeudaUsuario(auth()->id()) : 0.0;
    @endphp
    <dialog class="dialogo-md" id="modal-retirarse">
        <div class="cabecera-dialogo-encabezado">
            <h2 class="dialogo-titulo cabecera-dialogo-titulo">
                <x-icono nombre="bloqueado" tamano="md" class="cabecera-icono-destructive" />
                ¿Retirarse de este plan?
            </h2>
            <p class="cabecera-dialogo-mensaje">
                Estás por retirarte como colaborador de <strong>&laquo;{{ $plan->nombre }}&raquo;</strong>.
            </p>
        </div>

        @if ($usuarioTieneDeuda)
            {{-- Banner informativo de advertencia y bloqueo por deuda pendiente (Nielsen H5 y H6) --}}
            <div class="banner banner-peligro">
                <div class="banner-titulo">
                    <x-icono nombre="tarjeta" tamano="sm" class="cabecera-icono-destructive" />
                    No puedes retirarte: tienes deudas pendientes
                </div>
                <p class="banner-texto">
                    Tienes un saldo deudor acumulado de <strong>Bs {{ number_format($montoDeuda, 2) }}</strong> en los
                    gastos compartidos de este plan. Para retirarte, primero debes liquidar tus cuentas pendientes con los
                    demás miembros.
                </p>
                <a href="{{ route('planes.detalle', ['plan' => $plan, 'tab' => 'gastos']) }}"
                    class="boton boton-claro cabecera-boton-deuda" aria-label="Ir a Gastos y Liquidación para saldar deuda">
                    <x-icono nombre="dinero" tamano="sm" class="cabecera-icono-inline" /> Ir a Gastos
                    y Liquidación
                </a>
            </div>

            {{-- Botón de acción para cerrar en caso de bloqueo con botón destructivo inhabilitado (Fitts >= 12px) --}}
            <div class="dialogo-botones-derecha">
                <button type="button" class="boton boton-claro cabecera-boton-dialogo"
                    onclick="document.getElementById('modal-retirarse').close()" aria-label="Mantener plan">
                    Mantener plan
                </button>
                <button type="button" class="boton boton-rojo cabecera-boton-dialogo" disabled
                    title="Bloqueado por deudas pendientes" aria-disabled="true">
                    Sí, retirarme del plan
                </button>
            </div>
        @else
            {{-- Flujo normal sin deuda: advertencia sobre anulación de votos (Nielsen H5) --}}
            <div class="banner banner-advertencia">
                <div class="banner-titulo">
                    <x-icono nombre="calendario" tamano="sm" class="cabecera-icono-warning" />
                    Anulación automática de votos
                </div>
                Al confirmar tu retiro, todos los votos que hayas emitido en las propuestas de fechas y lugares serán
                eliminados inmediatamente, recalculando el conteo de la sala en tiempo real. Esta acción es definitiva.
            </div>

            <form method="POST" action="{{ route('planes.retirarse', $plan) }}">
                @csrf
                {{-- Separación antagónica de >= 12px (gap-3) según Ley de Fitts y desambiguación léxica H3/Hick --}}
                <div class="dialogo-botones-derecha cabecera-dialogo-botones-amplio">
                    <button type="button" class="boton boton-claro cabecera-boton-dialogo"
                        onclick="document.getElementById('modal-retirarse').close()" aria-label="Mantener plan">
                        Mantener plan
                    </button>
                    <button type="submit" class="boton boton-rojo cabecera-boton-dialogo"
                        aria-label="Sí, retirarme del plan">
                        Sí, retirarme del plan
                    </button>
                </div>
            </form>
        @endif
    </dialog>
@endcan

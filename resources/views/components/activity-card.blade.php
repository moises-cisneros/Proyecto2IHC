@props(['actividad'])

@php
    $accionClase = match ($actividad->accion) {
        'creacion' => 'badge-accion-creacion',
        'cambio_estado' => 'badge-accion-estado',
        'actualizacion' => 'badge-accion-actualizacion',
        'voto', 'participacion', 'invitacion' => 'badge-accion-participacion',
        'gasto', 'pago' => 'badge-accion-pago',
        'pago_confirmado' => 'badge-accion-pago-confirmado',
        'pago_rechazado' => 'badge-accion-pago-rechazado',
        default => 'badge-accion-default',
    };

    $estadoClase = match ($actividad->estado_resultante) {
        'confirmado' => 'badge-estado-confirmado',
        'cancelado' => 'badge-estado-cancelado',
        'pendiente' => 'badge-estado-pendiente',
        default => 'badge-estado-default',
    };

    $etiquetaAccion = match ($actividad->accion) {
        'creacion' => 'Creación de plan',
        'cambio_estado' => 'Cambio de estado',
        'actualizacion' => 'Actualización de datos',
        'voto' => 'Votación registrada',
        'invitacion' => 'Invitación enviada',
        'gasto' => 'Gasto registrado',
        'pago' => 'Pago registrado',
        'pago_confirmado' => 'Pago confirmado',
        'pago_rechazado' => 'Pago rechazado',
        default => ucfirst(str_replace('_', ' ', $actividad->accion)),
    };
@endphp

<article class="activity-card" aria-label="Tarjeta de actividad: {{ $etiquetaAccion }}">

    {{-- Cabecera de la tarjeta: Qué ocurrió y Cuándo --}}
    <div
        style="display: flex; justify-content: space-between; align-items: flex-start; gap: var(--space-3); margin-bottom: var(--space-2);">
        {{-- Dimensión 1: Qué ocurrió (Acción) --}}
        <div style="display: flex; align-items: center; gap: var(--space-2); flex-wrap: wrap;">
            <span class="badge-accion {{ $accionClase }}">
                {{ $etiquetaAccion }}
            </span>

            {{-- Dimensión 3: Cuál fue el resultado (Estado resultante) --}}
            @if ($actividad->estado_resultante)
                <span class="badge-estado {{ $estadoClase }}">
                    Resultado: <strong>{{ ucfirst($actividad->estado_resultante) }}</strong>
                </span>
            @endif
        </div>

        {{-- Dimensión 2: Cuándo ocurrió (Temporalidad) --}}
        <time class="meta-tiempo" datetime="{{ $actividad->created_at?->toIso8601String() }}"
            title="{{ $actividad->created_at?->format('d/m/Y H:i:s') }}"
            style="font-size: var(--font-size-sm); color: var(--color-muted); white-space: nowrap;">
            {{ $actividad->created_at?->diffForHumans() ?? 'Reciente' }}
        </time>
    </div>

    {{-- Detalle descriptivo del evento --}}
    <p class="descripcion-actividad"
        style="margin: 0 0 var(--space-2) 0; font-size: var(--font-size-base); color: var(--color-foreground); line-height: 1.4;">
        {{ $actividad->descripcion }}
    </p>

    {{-- Dimensión 4: Qué elemento involucró (Plan y Actor) --}}
    <div class="meta-elemento"
        style="display: flex; align-items: center; gap: var(--space-3); font-size: var(--font-size-sm); color: var(--color-muted); border-top: 1px solid var(--color-neutral-bg); padding-top: var(--space-2); margin-top: var(--space-1);">
        @if ($actividad->plan)
            <span class="elemento-plan" style="display: inline-flex; align-items: center; gap: var(--space-1);">
                Plan involucrado:
                <strong style="color: var(--color-foreground);">{{ $actividad->plan->nombre }}</strong>
            </span>
        @endif

        @if ($actividad->user)
            <span class="elemento-usuario" style="display: inline-flex; align-items: center; gap: var(--space-1);">
                Autor:
                <strong style="color: var(--color-foreground);">{{ $actividad->user->name }}</strong>
            </span>
        @endif

        @if ($actividad->destinatario)
            <span class="elemento-destinatario" style="display: inline-flex; align-items: center; gap: var(--space-1);">
                Destinatario:
                <strong style="color: var(--color-foreground);">{{ $actividad->destinatario->name }}</strong>
            </span>
        @endif
    </div>

</article>

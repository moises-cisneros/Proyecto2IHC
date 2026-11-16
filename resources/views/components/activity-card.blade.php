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
    <div class="activity-card-cabecera">
        {{-- Dimensión 1: Qué ocurrió (Acción) --}}
        <div class="activity-card-etiquetas">
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
        <time class="activity-card-tiempo" datetime="{{ $actividad->created_at?->toIso8601String() }}"
            title="{{ $actividad->created_at?->format('d/m/Y H:i:s') }}">
            {{ $actividad->created_at?->diffForHumans() ?? 'Reciente' }}
        </time>
    </div>

    {{-- Detalle descriptivo del evento --}}
    <p class="activity-card-descripcion">
        {{ $actividad->descripcion }}
    </p>

    {{-- Dimensión 4: Qué elemento involucró (Plan y Actor) --}}
    <div class="activity-card-meta">
        @if ($actividad->plan)
            <span class="activity-card-elemento">
                Plan involucrado:
                <strong>{{ $actividad->plan->nombre }}</strong>
            </span>
        @endif

        @if ($actividad->user)
            <span class="activity-card-elemento">
                Autor:
                <strong>{{ $actividad->user->name }}</strong>
            </span>
        @endif

        @if ($actividad->destinatario)
            <span class="activity-card-elemento">
                Destinatario:
                <strong>{{ $actividad->destinatario->name }}</strong>
            </span>
        @endif
    </div>

</article>

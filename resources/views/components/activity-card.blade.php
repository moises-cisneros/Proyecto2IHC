@props(['actividad'])

@php
    $accionClases = match ($actividad->accion) {
        'creacion' => 'background: #edf7ef; color: #25663a; border: 1px solid #c2e2c8;',
        'cambio_estado' => 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;',
        'actualizacion' => 'background: #fefce8; color: #854d0e; border: 1px solid #fef08a;',
        'voto', 'participacion', 'invitacion' => 'background: #faf5ff; color: #6b21a8; border: 1px solid #e9d5ff;',
        default => 'background: #f4f4f5; color: #3f3f46; border: 1px solid #e4e4e7;',
    };

    $estadoClases = match ($actividad->estado_resultante) {
        'confirmado' => 'background: #edf7ef; color: #25663a; border: 1px solid #c2e2c8;',
        'cancelado' => 'background: #fbeceb; color: #c0392b; border: 1px solid #f5c6cb;',
        'pendiente' => 'background: #fffbeb; color: #b45309; border: 1px solid #fde68a;',
        default => 'background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb;',
    };

    $etiquetaAccion = match ($actividad->accion) {
        'creacion' => 'Creación de plan',
        'cambio_estado' => 'Cambio de estado',
        'actualizacion' => 'Actualización de datos',
        'voto' => 'Votación registrada',
        'invitacion' => 'Invitación enviada',
        default => ucfirst(str_replace('_', ' ', $actividad->accion)),
    };
@endphp

<article class="activity-card"
         style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 16px; padding: 18px 20px; margin-bottom: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.03);"
         aria-label="Tarjeta de actividad: {{ $etiquetaAccion }}">

    {{-- Cabecera de la tarjeta: Qué ocurrió y Cuándo --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 10px;">
        {{-- Dimensión 1: Qué ocurrió (Acción) --}}
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span class="badge-accion"
                  style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; padding: 4px 10px; border-radius: 9999px; {{ $accionClases }}">
                {{ $etiquetaAccion }}
            </span>

            {{-- Dimensión 3: Cuál fue el resultado (Estado resultante) --}}
            @if ($actividad->estado_resultante)
                <span class="badge-resultado"
                      style="font-size: 12px; font-weight: 500; padding: 4px 10px; border-radius: 9999px; {{ $estadoClases }}">
                    Resultado: <strong>{{ ucfirst($actividad->estado_resultante) }}</strong>
                </span>
            @endif
        </div>

        {{-- Dimensión 2: Cuándo ocurrió (Temporalidad) --}}
        <time class="meta-tiempo"
              datetime="{{ $actividad->created_at?->toIso8601String() }}"
              title="{{ $actividad->created_at?->format('d/m/Y H:i:s') }}"
              style="font-size: 13px; color: #6f6f6a; white-space: nowrap;">
            {{ $actividad->created_at?->diffForHumans() ?? 'Reciente' }}
        </time>
    </div>

    {{-- Detalle descriptivo del evento --}}
    <p class="descripcion-actividad" style="margin: 0 0 10px 0; font-size: 15px; color: #1c1c1a; line-height: 1.4;">
        {{ $actividad->descripcion }}
    </p>

    {{-- Dimensión 4: Qué elemento involucró (Plan y Actor) --}}
    <div class="meta-elemento" style="display: flex; align-items: center; gap: 14px; font-size: 13px; color: #6f6f6a; border-top: 1px solid #f0f0ed; padding-top: 8px; margin-top: 4px;">
        @if ($actividad->plan)
            <span class="elemento-plan" style="display: inline-flex; align-items: center; gap: 4px;">
                Plan involucrado:
                <strong style="color: #1c1c1a;">{{ $actividad->plan->nombre }}</strong>
            </span>
        @endif

        @if ($actividad->user)
            <span class="elemento-usuario" style="display: inline-flex; align-items: center; gap: 4px;">
                Autor:
                <strong style="color: #1c1c1a;">{{ $actividad->user->name }}</strong>
            </span>
        @endif
    </div>

</article>

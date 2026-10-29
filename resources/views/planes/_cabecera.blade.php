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
            
            <div style="display: flex; gap: 20px; flex-wrap: wrap; color: #6f6f6a; font-size: 14px; margin-top: 8px;">
                <div>
                    <span style="font-weight: 600; color: #1c1c1a;">Organizador:</span>
                    <span>{{ $plan->organizador }}</span>
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
        </div>
    </div>
</div>

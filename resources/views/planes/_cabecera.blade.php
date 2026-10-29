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
            @can('abandonar', $plan)
                <button type="button"
                        class="boton boton-claro"
                        style="min-height: 44px; min-width: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 10px 18px; color: #b90e13; border-color: #fca5a5;"
                        onclick="document.getElementById('modal-abandonar').showModal()"
                        aria-label="Abandonar este plan">
                    Abandonar plan
                </button>
            @endcan
        </div>
    </div>
</div>

{{-- Diálogo modal accesible para confirmar abandono de plan (Nielsen H3) --}}
@can('abandonar', $plan)
<dialog id="modal-abandonar" style="border: none; border-radius: 16px; padding: 28px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
    <form method="POST" action="{{ route('planes.abandonar', $plan) }}">
        @csrf
        <h2 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #1c1c1a;">¿Abandonar este plan?</h2>
        <p style="margin: 0 0 20px 0; font-size: 14px; color: #6f6f6a; line-height: 1.5;">
            Dejarás de figurar como colaborador de <strong>&laquo;{{ $plan->nombre }}&raquo;</strong> y todos los votos que hayas emitido en las propuestas serán retirados. Esta acción no se puede deshacer.
        </p>

        {{-- Separación antagónica de 12px (gap-3) según Ley de Fitts y desambiguación léxica H3 --}}
        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
            <button type="button"
                    class="boton boton-claro"
                    style="min-height: 44px; min-width: 44px; padding: 10px 18px; border-radius: 50px;"
                    onclick="document.getElementById('modal-abandonar').close()">
                Permanecer en el plan
            </button>
            <button type="submit"
                    class="boton"
                    style="min-height: 44px; min-width: 44px; padding: 10px 22px; border-radius: 50px; background-color: rgb(185, 14, 19); color: #ffffff;">
                Sí, abandonar plan
            </button>
        </div>
    </form>
</dialog>
@endcan

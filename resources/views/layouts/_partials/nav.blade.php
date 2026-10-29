{{-- Barra de navegación --}}
<nav class="barra">
    <div style="display: flex; align-items: center; gap: 24px;">
        <a href="{{ route('planes.listar') }}" class="marca">Planazo</a>
        <div style="display: flex; align-items: center; gap: 8px;">
            <a href="{{ route('planes.listar') }}"
               class="boton {{ request()->routeIs('planes.*') && !request()->routeIs('historial.*') ? '' : 'boton-claro' }}"
               style="min-height: 44px; min-width: 44px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;"
               aria-label="Ir a Mis Planes">
                Mis Planes
            </a>
            <a href="{{ route('historial.index') }}"
               class="boton {{ request()->routeIs('historial.*') ? '' : 'boton-claro' }}"
               style="min-height: 44px; min-width: 44px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;"
               aria-label="Ir al Historial de Actividad">
                Historial
            </a>
        </div>
    </div>

    <div class="barra-derecha">
        <span>{{ auth()->user()->name }}</span>

        {{-- Botón para cerrar sesión --}}
        <form method="POST" action="{{ route('salir') }}">
            @csrf
            <button type="submit"
                    class="boton boton-claro"
                    style="min-height: 44px; min-width: 44px; display: inline-flex; align-items: center; justify-content: center;"
                    aria-label="Cerrar sesión">
                Salir
            </button>
        </form>
    </div>
</nav>

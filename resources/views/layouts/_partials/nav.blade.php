{{-- Barra de navegación --}}
<nav class="barra" aria-label="Navegación principal">
    <div style="display: flex; align-items: center; gap: 24px;">
        <a href="{{ route('planes.listar') }}" class="marca" aria-label="Planazo - Inicio">Planazo</a>

        <div class="nav-menu" role="menubar" aria-label="Secciones de la aplicación">
            <x-nav-link :href="route('planes.listar')" :activo="request()->routeIs('planes.*') && !request()->routeIs('historial.*')" icono="calendario" aria-label="Ir a Mis Planes">
                Mis Planes
            </x-nav-link>

            <x-nav-link :href="route('historial.index')" :activo="request()->routeIs('historial.*')" icono="historial" aria-label="Ir al Historial de Actividad">
                Historial
            </x-nav-link>

            @if (app()->environment('local', 'testing'))
                <x-nav-link :href="route('analitica.index')" :activo="request()->routeIs('analitica.*')" icono="analitica" aria-label="Ir a Analítica de Interacción">
                    Analítica
                </x-nav-link>
            @endif
        </div>
    </div>

    <div class="barra-derecha">
        <div class="nav-usuario" title="Usuario autenticado: {{ auth()->user()->name }}">
            <span class="nav-usuario-avatar" aria-hidden="true">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
            <span class="nav-usuario-nombre">{{ auth()->user()->name }}</span>
        </div>

        {{-- Botón accesible para cerrar sesión --}}
        <form method="POST" action="{{ route('salir') }}" style="margin: 0;">
            @csrf
            <button type="submit" class="boton-salir" aria-label="Cerrar sesión">
                <x-icono nombre="salir" />
                <span>Salir</span>
            </button>
        </form>
    </div>
</nav>

{{-- Barra de navegación --}}
<nav class="barra" aria-label="Navegación principal">
    <div style="display: flex; align-items: center; gap: var(--space-6);">
        <a href="{{ route('planes.listar') }}" class="marca" aria-label="Planazo - Inicio">Planazo</a>

        <div class="nav-menu" role="menubar" aria-label="Secciones de la aplicación">
            <x-nav-link :href="route('planes.listar')" :activo="request()->routeIs('planes.*') &&
                !request()->routeIs('historial.*') &&
                !request()->routeIs('perfil.*')" icono="calendario" aria-label="Ir a Mis Planes">
                Mis Planes
            </x-nav-link>

            <x-nav-link :href="route('historial.index')" :activo="request()->routeIs('historial.*')" icono="historial" aria-label="Ir al Historial de Actividad">
                Historial
            </x-nav-link>

            <x-nav-link :href="route('perfil.datos')" :activo="request()->routeIs('perfil.*')" icono="usuario" aria-label="Ir a Mi Cuenta y Privacidad">
                Mi Cuenta
            </x-nav-link>
        </div>
    </div>

    <div class="barra-derecha">
        <a href="{{ route('perfil.datos') }}" class="nav-usuario" style="text-decoration: none;"
            title="Mi Cuenta y Privacidad ({{ auth()->user()->name }})" aria-label="Mi Cuenta y Privacidad">
            <span class="nav-usuario-avatar" aria-hidden="true">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
            <span class="nav-usuario-nombre">{{ auth()->user()->name }}</span>
        </a>

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

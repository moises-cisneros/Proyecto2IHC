{{-- Barra de navegación --}}
<nav class="barra" aria-label="Navegación principal">
    <div class="barra-izquierda">
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
        <button type="button" id="boton-tema" class="boton-tema" aria-label="Cambiar tema de color"
            title="Alternar modo claro / oscuro">
            <svg class="icono-svg icono-tema-luna" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" aria-hidden="true">
                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
            </svg>
            <svg class="icono-svg icono-tema-sol" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="4"></circle>
                <path d="M12 2v2"></path>
                <path d="M12 20v2"></path>
                <path d="m4.93 4.93 1.41 1.41"></path>
                <path d="m17.66 17.66 1.41 1.41"></path>
                <path d="M2 12h2"></path>
                <path d="M20 12h2"></path>
                <path d="m6.34 17.66-1.41 1.41"></path>
                <path d="m19.07 4.93-1.41 1.41"></path>
            </svg>
        </button>
        <div class="nav-usuario" title={{ auth()->user()->email }}>
            <span class="avatar" aria-hidden="true">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
            <span class="nav-usuario-nombre">{{ auth()->user()->name }}</span>
        </div>

        {{-- Botón accesible para cerrar sesión --}}
        <form method="POST" action="{{ route('salir') }}" class="form-reset">
            @csrf
            <button type="submit" class="boton-salir" aria-label="Cerrar sesión">
                <x-icono nombre="salir" />
                <span>Salir</span>
            </button>
        </form>
    </div>
</nav>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo') · Planazo</title>
    @vite('resources/css/app.css')

    {{-- Script anti-flicker de tema --}}
    <script>
        (function() {
            var tema = localStorage.getItem('planazo_tema') ||
                (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'oscuro' : 'claro');
            if (tema === 'oscuro') {
                document.documentElement.setAttribute('data-tema', 'oscuro');
            }
        })();
    </script>
</head>

<body class="acceso-body">

    <main class="pagina-acceso">
        {{-- Barra superior de acceso alineada con la tarjeta --}}
        <header class="acceso-barra-flotante">
            <a href="{{ route('landing') }}" class="marca" aria-label="Volver al inicio">Planazo</a>
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
        </header>

        {{-- Tarjeta blanca/oscura con el formulario --}}
        <div class="tarjeta-acceso">
            {{-- Mensajes flash flotantes (éxito o error) sin layout shift --}}
            @include('layouts._partials.mensajes')

            {{-- Contenido de la vista (ingresar, registro, recuperar, restablecer) --}}
            @yield('contenido')
        </div>
    </main>

    {{-- Script global: ui.js para notificaciones toast y switcher de tema --}}
    <script src="{{ asset('js/ui.js') }}"></script>

</body>

</html>

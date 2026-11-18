<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo') · Planazo</title>

    {{-- Estilos globales de la app --}}
    @vite('resources/css/app.css')

    {{-- Script anti-flicker de tema (Modo Oscuro) --}}
    <script>
        (function() {
            var tema = localStorage.getItem('planazo_tema') ||
                (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'oscuro' : 'claro');
            if (tema === 'oscuro') {
                document.documentElement.setAttribute('data-tema', 'oscuro');
            }
        })();
    </script>

    {{-- Cada vista puede agregar su propio CSS extra aquí --}}
    @yield('estilos')
</head>

<body>

    {{-- Barra de navegación --}}
    @include('layouts._partials.nav')

    {{-- Contenido principal de la página --}}
    <main class="contenedor">

        {{-- Mensajes flash flotantes (éxito o error) sin layout shift --}}
        @include('layouts._partials.mensajes')

        {{-- Aquí va el contenido de cada vista --}}
        @yield('contenido')

    </main>

    {{-- Barra de herramientas de desarrollo flotante (solo en local y testing) --}}
    @include('layouts._partials.dev_toolbar')

    {{-- Modal nativo accesible para confirmaciones (<dialog>) --}}
    <x-dialog-confirm />

    {{-- Script global: interacción inmediata + confirmaciones modales nativas --}}
    <script src="{{ asset('js/ui.js') }}"></script>
    {{-- Analítica de interacción (telemetría de intenciones) --}}
    <script src="{{ asset('js/analitica.js') }}"></script>

    {{-- Cada vista puede agregar scripts extra --}}
    @yield('scripts')

</body>

</html>

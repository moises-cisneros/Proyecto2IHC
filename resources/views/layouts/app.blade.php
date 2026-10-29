<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo') · Planazo</title>

    {{-- Estilos globales de la app --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

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

    {{-- Modal nativo accesible para confirmaciones (<dialog>) --}}
    <x-dialog-confirm />

    {{-- Script global: interacción inmediata + confirmaciones modales nativas --}}
    <script src="{{ asset('js/ui.js') }}"></script>

    {{-- Cada vista puede agregar scripts extra --}}
    @yield('scripts')

</body>
</html>

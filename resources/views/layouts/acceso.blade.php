<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titulo') · Planazo</title>
    <link rel="stylesheet" href="{{ asset('css/acceso.css') }}">
</head>
<body>

    {{-- Página centrada verticalmente --}}
    <main class="pagina-acceso">

        {{-- Tarjeta blanca con el formulario --}}
        <div class="tarjeta-acceso">

            {{-- Mensajes flash flotantes (éxito o error) sin layout shift --}}
            @include('layouts._partials.mensajes')

            {{-- Contenido de la vista (ingresar, registro, recuperar, restablecer) --}}
            @yield('contenido')

        </div>
    </main>

    {{-- Script global: ui.js para notificaciones toast --}}
    <script src="{{ asset('js/ui.js') }}"></script>

</body>
</html>

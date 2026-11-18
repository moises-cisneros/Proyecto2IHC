@extends('layouts.app')

@section('titulo', 'Historial de actividad')

@section('contenido')

    <div class="encabezado-planes historial-cabecera">
        <div>
            <h1 class="historial-titulo">
                Historial de actividad</h1>
            <p class="historial-subtitulo">Registro cronológico e inmutable
                de eventos sobre tus planes.</p>
        </div>
        <a href="{{ route('planes.listar') }}" class="boton boton-claro" aria-label="Volver a Mis Planes">
            &larr; Volver a Mis Planes
        </a>
    </div>

    {{-- Selector temático de n=4 chips (Ley de Hick: T = b log2(n + 1)) --}}
    <div class="historial-filtros" role="group" aria-label="Filtrar historial por categoría temática">

        <a href="{{ route('historial.index', ['categoria' => 'todos']) }}"
            class="boton historial-chip {{ $categoria === 'todos' ? '' : 'boton-claro' }}"
            @if ($categoria === 'todos') aria-current="page" @endif>
            Todos
        </a>

        <a href="{{ route('historial.index', ['categoria' => 'estados']) }}"
            class="boton historial-chip {{ $categoria === 'estados' ? '' : 'boton-claro' }}"
            @if ($categoria === 'estados') aria-current="page" @endif>
            Estados
        </a>

        <a href="{{ route('historial.index', ['categoria' => 'creaciones']) }}"
            class="boton historial-chip {{ $categoria === 'creaciones' ? '' : 'boton-claro' }}"
            @if ($categoria === 'creaciones') aria-current="page" @endif>
            Creaciones
        </a>

        <a href="{{ route('historial.index', ['categoria' => 'participacion']) }}"
            class="boton historial-chip {{ $categoria === 'participacion' ? '' : 'boton-claro' }}"
            @if ($categoria === 'participacion') aria-current="page" @endif>
            Participación
        </a>

        <a href="{{ route('historial.index', ['categoria' => 'gastos']) }}"
            class="boton historial-chip {{ $categoria === 'gastos' ? '' : 'boton-claro' }}"
            @if ($categoria === 'gastos') aria-current="page" @endif>
            Gastos
        </a>

    </div>

    {{-- Listado de eventos o estado vacío ilustrado --}}
    @if ($actividades->isEmpty())
        <div class="historial-vacio" role="status">

            {{-- Ilustración SVG temática --}}
            <div class="historial-vacio-ilustracion">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>

            <h2 class="historial-vacio-titulo">
                Sin actividades registradas</h2>
            <p class="historial-vacio-texto">
                No se encontraron eventos en la categoría seleccionada. Las acciones de creación, actualización y cambio de
                estado sobre tus planes quedarán documentadas aquí automáticamente.
            </p>

            <a href="{{ route('planes.listar') }}" class="boton historial-vacio-accion"
                aria-label="Ir a planes para crear o gestionar">
                Ir a mis planes
            </a>
        </div>
    @else
        <section aria-label="Lista de actividades registradas">
            @foreach ($actividades as $actividad)
                <x-activity-card :actividad="$actividad" />
            @endforeach
        </section>
    @endif

@endsection

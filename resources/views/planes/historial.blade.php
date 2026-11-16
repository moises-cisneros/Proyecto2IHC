@extends('layouts.app')

@section('titulo', 'Historial de actividad')

@section('contenido')

    <div class="encabezado-planes" style="margin-bottom: var(--space-6);">
        <div>
            <h1
                style="margin: 0 0 var(--space-1) 0; font-size: var(--font-size-2xl); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                Historial de actividad</h1>
            <p style="margin: 0; color: var(--color-muted); font-size: var(--font-size-md);">Registro cronológico e inmutable
                de eventos sobre tus planes.</p>
        </div>
        <a href="{{ route('planes.listar') }}" class="boton boton-claro"
            style="min-height: var(--touch-min); display: inline-flex; align-items: center; justify-content: center;"
            aria-label="Volver a Mis Planes">
            &larr; Volver a Mis Planes
        </a>
    </div>

    {{-- Selector temático de n=4 chips (Ley de Hick: T = b log2(n + 1)) --}}
    <div class="selector-chips" role="group" aria-label="Filtrar historial por categoría temática"
        style="display: flex; gap: var(--space-2); flex-wrap: wrap; margin-bottom: var(--space-6);">

        <a href="{{ route('historial.index', ['categoria' => 'todos']) }}"
            class="boton {{ $categoria === 'todos' ? '' : 'boton-claro' }}"
            style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-4); display: inline-flex; align-items: center; justify-content: center; font-size: var(--font-size-md); font-weight: var(--font-weight-medium); text-decoration: none;"
            aria-pressed="{{ $categoria === 'todos' ? 'true' : 'false' }}">
            Todos
        </a>

        <a href="{{ route('historial.index', ['categoria' => 'estados']) }}"
            class="boton {{ $categoria === 'estados' ? '' : 'boton-claro' }}"
            style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-4); display: inline-flex; align-items: center; justify-content: center; font-size: var(--font-size-md); font-weight: var(--font-weight-medium); text-decoration: none;"
            aria-pressed="{{ $categoria === 'estados' ? 'true' : 'false' }}">
            Estados
        </a>

        <a href="{{ route('historial.index', ['categoria' => 'creaciones']) }}"
            class="boton {{ $categoria === 'creaciones' ? '' : 'boton-claro' }}"
            style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-4); display: inline-flex; align-items: center; justify-content: center; font-size: var(--font-size-md); font-weight: var(--font-weight-medium); text-decoration: none;"
            aria-pressed="{{ $categoria === 'creaciones' ? 'true' : 'false' }}">
            Creaciones
        </a>

        <a href="{{ route('historial.index', ['categoria' => 'participacion']) }}"
            class="boton {{ $categoria === 'participacion' ? '' : 'boton-claro' }}"
            style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-4); display: inline-flex; align-items: center; justify-content: center; font-size: var(--font-size-md); font-weight: var(--font-weight-medium); text-decoration: none;"
            aria-pressed="{{ $categoria === 'participacion' ? 'true' : 'false' }}">
            Participación
        </a>

        <a href="{{ route('historial.index', ['categoria' => 'gastos']) }}"
            class="boton {{ $categoria === 'gastos' ? '' : 'boton-claro' }}"
            style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-4); display: inline-flex; align-items: center; justify-content: center; font-size: var(--font-size-md); font-weight: var(--font-weight-medium); text-decoration: none;"
            aria-pressed="{{ $categoria === 'gastos' ? 'true' : 'false' }}">
            Gastos
        </a>

    </div>

    {{-- Listado de eventos o estado vacío ilustrado --}}
    @if ($actividades->isEmpty())
        <div class="estado-vacio-ilustrado"
            style="background: var(--color-surface); border: 1px dashed var(--color-border-input); border-radius: var(--radius-xl); padding: var(--space-10) var(--space-6); text-align: center; margin-top: var(--space-4);"
            role="status">

            {{-- Ilustración SVG temática --}}
            <div
                style="margin-bottom: var(--space-4); display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: var(--radius-circle); background: var(--color-warning-bg); color: var(--color-warning);">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>

            <h2
                style="font-size: var(--font-size-xl); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin: 0 0 var(--space-2) 0;">
                Sin actividades registradas</h2>
            <p
                style="color: var(--color-muted); font-size: var(--font-size-base); max-width: 420px; margin: 0 auto var(--space-5) auto; line-height: 1.5;">
                No se encontraron eventos en la categoría seleccionada. Las acciones de creación, actualización y cambio de
                estado sobre tus planes quedarán documentadas aquí automáticamente.
            </p>

            <a href="{{ route('planes.listar') }}" class="boton"
                style="min-height: var(--touch-min); display: inline-flex; align-items: center; justify-content: center; padding: var(--space-2) var(--space-6); text-decoration: none;"
                aria-label="Ir a planes para crear o gestionar">
                Ir a mis planes
            </a>
        </div>
    @else
        <section class="lista-actividades" aria-label="Lista de actividades registradas">
            @foreach ($actividades as $actividad)
                <x-activity-card :actividad="$actividad" />
            @endforeach
        </section>
    @endif

@endsection

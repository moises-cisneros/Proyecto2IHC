@extends('layouts.app')

@section('titulo', 'Historial de actividad')

@section('contenido')

<div class="encabezado-planes" style="margin-bottom: 24px;">
    <div>
        <h1 style="margin: 0 0 6px 0; font-size: 28px; font-weight: 700; color: #1c1c1a;">Historial de actividad</h1>
        <p style="margin: 0; color: #6f6f6a; font-size: 14px;">Registro cronológico e inmutable de eventos sobre tus planes.</p>
    </div>
    <a href="{{ route('planes.listar') }}"
       class="boton boton-claro"
       style="min-height: 44px; display: inline-flex; align-items: center; justify-content: center;"
       aria-label="Volver a Mis Planes">
        &larr; Volver a Mis Planes
    </a>
</div>

{{-- Selector temático de n=4 chips (Ley de Hick: T = b log2(n + 1)) --}}
<div class="selector-chips"
     role="group"
     aria-label="Filtrar historial por categoría temática"
     style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px;">

    <a href="{{ route('historial.index', ['categoria' => 'todos']) }}"
       class="boton {{ $categoria === 'todos' ? '' : 'boton-claro' }}"
       style="min-height: 44px; min-width: 44px; padding: 10px 18px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 500; text-decoration: none;"
       aria-pressed="{{ $categoria === 'todos' ? 'true' : 'false' }}">
        Todos
    </a>

    <a href="{{ route('historial.index', ['categoria' => 'estados']) }}"
       class="boton {{ $categoria === 'estados' ? '' : 'boton-claro' }}"
       style="min-height: 44px; min-width: 44px; padding: 10px 18px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 500; text-decoration: none;"
       aria-pressed="{{ $categoria === 'estados' ? 'true' : 'false' }}">
        Estados
    </a>

    <a href="{{ route('historial.index', ['categoria' => 'creaciones']) }}"
       class="boton {{ $categoria === 'creaciones' ? '' : 'boton-claro' }}"
       style="min-height: 44px; min-width: 44px; padding: 10px 18px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 500; text-decoration: none;"
       aria-pressed="{{ $categoria === 'creaciones' ? 'true' : 'false' }}">
        Creaciones
    </a>

    <a href="{{ route('historial.index', ['categoria' => 'participacion']) }}"
       class="boton {{ $categoria === 'participacion' ? '' : 'boton-claro' }}"
       style="min-height: 44px; min-width: 44px; padding: 10px 18px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 500; text-decoration: none;"
       aria-pressed="{{ $categoria === 'participacion' ? 'true' : 'false' }}">
        Participación
    </a>

</div>

{{-- Listado de eventos o estado vacío ilustrado --}}
@if ($actividades->isEmpty())
    <div class="estado-vacio-ilustrado"
         style="background: #ffffff; border: 1px dashed #d1d1cd; border-radius: 16px; padding: 48px 24px; text-align: center; margin-top: 16px;"
         role="status">

        {{-- Ilustración SVG temática --}}
        <div style="margin-bottom: 16px; display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: 50%; background: #fdf6d8; color: #854d0e;">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>

        <h2 style="font-size: 20px; font-weight: 600; color: #1c1c1a; margin: 0 0 8px 0;">Sin actividades registradas</h2>
        <p style="color: #6f6f6a; font-size: 15px; max-width: 420px; margin: 0 auto 20px auto; line-height: 1.5;">
            No se encontraron eventos en la categoría seleccionada. Las acciones de creación, actualización y cambio de estado sobre tus planes quedarán documentadas aquí automáticamente.
        </p>

        <a href="{{ route('planes.listar') }}"
           class="boton"
           style="min-height: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 10px 24px; text-decoration: none;"
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

@extends('layouts.app')

@section('titulo', 'Historial de actividad')

@section('contenido')
<div class="encabezado-planes">
    <h1>Historial de actividad</h1>
    <a href="{{ route('planes.listar') }}" class="boton boton-claro">Volver a planes</a>
</div>

{{-- Filtros tematicos (Ley de Hick: n = 4) --}}
<div class="filtros mb-6 flex flex-wrap gap-2">
    <a href="{{ route('historial.index', ['categoria' => 'todos']) }}"
       class="boton {{ $categoria === 'todos' ? '' : 'boton-claro' }}">
        Todos
    </a>
    <a href="{{ route('historial.index', ['categoria' => 'estados']) }}"
       class="boton {{ $categoria === 'estados' ? '' : 'boton-claro' }}">
        Estados
    </a>
    <a href="{{ route('historial.index', ['categoria' => 'creaciones']) }}"
       class="boton {{ $categoria === 'creaciones' ? '' : 'boton-claro' }}">
        Creaciones
    </a>
    <a href="{{ route('historial.index', ['categoria' => 'participacion']) }}"
       class="boton {{ $categoria === 'participacion' ? '' : 'boton-claro' }}">
        Participación
    </a>
</div>

@if ($actividades->isEmpty())
    <div class="tarjeta text-center py-12">
        <p class="text-neutral-500 text-lg">No hay actividades registradas en esta categoría.</p>
    </div>
@else
    <div class="grid gap-4">
        @foreach ($actividades as $actividad)
            <div class="tarjeta flex flex-col md:flex-row md:items-center justify-between gap-3 p-4 border rounded-xl bg-white shadow-sm">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-semibold uppercase px-2 py-0.5 rounded bg-neutral-100 text-neutral-700">
                            {{ $actividad->accion }}
                        </span>
                        @if ($actividad->estado_resultante)
                            <span class="text-xs px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-medium">
                                {{ $actividad->estado_resultante }}
                            </span>
                        @endif
                    </div>
                    <p class="font-medium text-neutral-900">{{ $actividad->descripcion }}</p>
                    @if ($actividad->plan)
                        <p class="text-xs text-neutral-500">Plan: {{ $actividad->plan->nombre }}</p>
                    @endif
                </div>
                <div class="text-xs text-neutral-400 whitespace-nowrap">
                    {{ $actividad->created_at?->diffForHumans() ?? 'Reciente' }}
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection

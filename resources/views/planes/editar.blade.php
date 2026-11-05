@extends('layouts.app')

@section('titulo', 'Editar plan')

@section('contenido')

{{-- Tarjeta centrada con el formulario de edición --}}
<div class="tarjeta-editar">
    <h1>Editar plan</h1>

    {{-- PUT porque estamos actualizando un recurso existente --}}
    <form method="POST" action="{{ route('planes.actualizar', $plan) }}">
        @csrf
        @method('PUT')

        <div class="campo">
            <label for="nombre">Nombre del plan</label>
            {{-- old() recupera el valor si hay errores de validación --}}
            <input type="text" id="nombre" name="nombre"
                   value="{{ old('nombre', $plan->nombre) }}"
                   placeholder="Nombre del plan">
            @error('nombre')
                <span class="campo-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="campo">
            <label>Organizador</label>
            <div class="inline-flex items-center gap-2 px-3 py-2 bg-stone-100 border border-stone-200 rounded-lg text-sm text-stone-700">
                <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="font-medium">{{ $plan->organizador }}</span>
            </div>
        </div>

        <div class="campo">
            <label for="fecha_limite">Fecha límite</label>
            {{-- format('Y-m-d') porque el input type=date espera ese formato --}}
            <input type="date" id="fecha_limite" name="fecha_limite"
                   value="{{ old('fecha_limite', $plan->fecha_limite->format('Y-m-d')) }}"
                   min="{{ now()->format('Y-m-d') }}">
            @error('fecha_limite')
                <span class="campo-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="botones-formulario">
            <a href="{{ route('planes.listar') }}" class="boton boton-claro">Cancelar</a>
            <button type="submit" class="boton">Actualizar</button>
        </div>
    </form>
</div>

@endsection

@extends('layouts.app')

@section('titulo', 'Sala colaborativa · ' . $plan->nombre)

@section('contenido')

<div class="sala-colaborativa" style="max-width: 1000px; margin: 0 auto; padding-bottom: 40px;">
    {{-- Parcial 1: Cabecera con título, badge de estado, fecha límite y organizador --}}
    @include('planes._cabecera')

    {{-- Parcial 2: Participantes con roles y botón accesible "Invitar" --}}
    @include('planes._participantes')

    {{-- Parcial 3: Sala de votación con pestañas semánticas de Hick y tarjetas de opciones --}}
    @include('planes._sala_votacion')
</div>

@endsection

@extends('layouts.acceso')

@section('titulo', 'Ingresar')

@section('contenido')

    {{-- Encabezado: logo + título + descripción --}}
    <header class="acceso-encabezado">
        <span class="acceso-logo">✓</span>
        <h1>Ingresar</h1>
        <p>Entra para ver tus planes.</p>
    </header>

    {{-- Formulario de login --}}
    <form method="POST" action="{{ route('ingresar') }}" class="acceso-form" novalidate>
        @csrf

        {{-- Campo correo --}}
        <div class="campo">
            <label for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" value="{{ old('correo') }}" placeholder="tucorreo@ejemplo.com"
                class="{{ $errors->has('correo') ? 'invalido' : '' }}">
            @error('correo')
                <span class="campo-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Campo contraseña con enlace "¿La olvidaste?" en la misma fila --}}
        <div class="campo">
            <div class="campo-fila">
                <label for="contrasena">Contraseña</label>
                <a href="{{ route('recuperar') }}" class="enlace-pequeno">¿La olvidaste?</a>
            </div>
            <input type="password" id="contrasena" name="contrasena" placeholder="••••••••"
                class="{{ $errors->has('contrasena') ? 'invalido' : '' }}">
            @error('contrasena')
                <span class="campo-error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="boton-acceso">Entrar</button>
    </form>

    {{-- Pie: enlace para ir a registro --}}
    <footer class="acceso-pie">
        ¿No tienes cuenta? <a href="{{ route('registro') }}">Regístrate</a>
    </footer>

@endsection

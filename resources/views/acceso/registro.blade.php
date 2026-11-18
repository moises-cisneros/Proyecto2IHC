@extends('layouts.acceso')

@section('titulo', 'Crear cuenta')

@section('contenido')

    <header class="acceso-encabezado">
        <span class="acceso-logo"><x-icono nombre="check" tamano="md" /></span>
        <h1>Crear cuenta</h1>
        <p>Empieza a crear tus planes.</p>
    </header>

    <form method="POST" action="{{ route('registrar') }}" class="formulario" novalidate>
        @csrf

        <div class="campo">
            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Tu nombre"
                class="{{ $errors->has('nombre') ? 'invalido' : '' }}">
            @error('nombre')
                <span class="campo-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="campo">
            <label for="correo">Correo electrónico</label>
            <input type="email" id="correo" name="correo" value="{{ old('correo') }}"
                placeholder="tucorreo@ejemplo.com" class="{{ $errors->has('correo') ? 'invalido' : '' }}">
            @error('correo')
                <span class="campo-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="campo">
            <label for="contrasena">Contraseña</label>
            <input type="password" id="contrasena" name="contrasena" placeholder="Mínimo 8 caracteres"
                class="{{ $errors->has('contrasena') ? 'invalido' : '' }}">
            @error('contrasena')
                <span class="campo-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Laravel compara 'contrasena' con 'contrasena_confirmation' automáticamente
         cuando usas la regla 'confirmed' en el controlador --}}
        <div class="campo">
            <label for="contrasena_confirmation">Confirmar contraseña</label>
            <input type="password" id="contrasena_confirmation" name="contrasena_confirmation"
                placeholder="Repite la contraseña">
        </div>

        <button type="submit" class="boton-acceso">Registrarme</button>
    </form>

    <footer class="acceso-pie">
        ¿Ya tienes cuenta? <a href="{{ route('login') }}">Ingresa</a>
    </footer>

@endsection

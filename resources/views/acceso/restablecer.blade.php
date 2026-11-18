@extends('layouts.acceso')

@section('titulo', 'Nueva contraseña')

@section('contenido')

    <header class="acceso-encabezado">
        <span class="acceso-logo"><x-icono nombre="check" tamano="md" /></span>
        <h1>Nueva contraseña</h1>
        <p>Escribe tu nueva contraseña.</p>
    </header>

    <form method="POST" action="{{ route('restablecer') }}" class="formulario" novalidate>
        @csrf

        {{-- El token viene en la URL del correo y se manda oculto para validar que el enlace es legítimo --}}
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="campo">
            <label for="correo">Correo electrónico</label>
            {{-- old('correo', $correo): si hubo error usa el old, si no usa el $correo de la URL --}}
            <input type="email" id="correo" name="correo" value="{{ old('correo', $correo) }}"
                class="{{ $errors->has('correo') ? 'invalido' : '' }}">
            @error('correo')
                <span class="campo-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="campo">
            <label for="contrasena">Nueva contraseña</label>
            <input type="password" id="contrasena" name="contrasena" placeholder="Mínimo 8 caracteres"
                class="{{ $errors->has('contrasena') ? 'invalido' : '' }}">
            @error('contrasena')
                <span class="campo-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="campo">
            <label for="contrasena_confirmation">Confirmar contraseña</label>
            <input type="password" id="contrasena_confirmation" name="contrasena_confirmation"
                placeholder="Repite la contraseña">
        </div>

        <button type="submit" class="boton-acceso">Cambiar contraseña</button>
    </form>

@endsection

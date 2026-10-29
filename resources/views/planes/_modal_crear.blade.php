<dialog id="modal-crear" {{ $errors->any() ? 'open' : '' }}>
    <h2>Nuevo plan</h2>

    <form method="POST" action="{{ route('planes.guardar') }}">
        @csrf

        <div class="campo">
            <label for="nombre">Nombre del plan</label>
            <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Nombre del plan">
            @error('nombre')
                <span class="campo-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Badge informativo de solo lectura: Nielsen H6 y H8 --}}
        <div class="campo-estatico" title="Organizador asignado automáticamente por sesión">
            <svg class="icono-organizador" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>Organizador: <strong>{{ auth()->user()->name }}</strong></span>
        </div>

        <div class="campo">
            <label for="fecha_limite">Fecha límite</label>
            <input type="date" id="fecha_limite" name="fecha_limite" value="{{ old('fecha_limite') }}">
            @error('fecha_limite')
                <span class="campo-error">{{ $message }}</span>
            @enderror
        </div>

        {{-- Botones del modal --}}
        <div class="dialogo-botones">
            <button type="button" class="boton boton-claro"
                    onclick="document.getElementById('modal-crear').close()">Cancelar</button>
            <button type="submit" class="boton">Guardar</button>
        </div>
    </form>
</dialog>


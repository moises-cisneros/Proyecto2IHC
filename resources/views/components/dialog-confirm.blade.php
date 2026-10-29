@props([
    'id' => 'dialog-confirm',
    'title' => 'Confirmar acción',
    'message' => '¿Estás seguro de realizar esta acción?',
    'confirmText' => 'Confirmar',
    'cancelText' => 'Cancelar',
])

<dialog id="{{ $id }}" class="dialogo-modal" aria-labelledby="{{ $id }}-titulo" aria-describedby="{{ $id }}-mensaje">
    <div class="dialogo-caja">
        <h2 id="{{ $id }}-titulo" class="dialogo-titulo">{{ $title }}</h2>
        <p id="{{ $id }}-mensaje" class="dialogo-mensaje">{{ $message }}</p>

        <div class="dialogo-botones">
            <button type="button" id="{{ $id }}-cancelar" class="boton boton-claro" data-dialog-cancel>
                {{ $cancelText }}
            </button>
            <button type="button" id="{{ $id }}-aceptar" class="boton" data-dialog-ok>
                {{ $confirmText }}
            </button>
        </div>
    </div>
</dialog>


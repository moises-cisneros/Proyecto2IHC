@props([
    'tipo' => 'exito',
    'mensaje' => null,
])

@php
    $tipoFinal = in_array($tipo, ['exito', 'error']) ? $tipo : 'exito';
    $esError = $tipoFinal === 'error';
    $role = $esError ? 'alert' : 'status';
@endphp

@if ($mensaje)
    <div id="toast-contenedor" class="toast-contenedor" aria-live="polite">
        <div id="toast-alerta" role="{{ $role }}" class="toast-alerta toast-{{ $tipoFinal }}"
            data-auto-dismiss="3000">
            <div class="toast-contenido">
                @if ($esError)
                    <svg class="toast-icono" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                @else
                    <svg class="toast-icono" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                @endif
                <span class="toast-mensaje">{{ $mensaje }}</span>
            </div>

            <button type="button" id="toast-btn-cerrar" class="toast-cerrar" aria-label="Cerrar notificación">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

    <script>
        (function() {
            var toast = document.getElementById('toast-alerta');
            var btn = document.getElementById('toast-btn-cerrar');
            if (!toast) return;

            var descartado = false;

            function descartar() {
                if (descartado) return;
                descartado = true;
                toast.classList.add('toast-ocultando');
                setTimeout(function() {
                    if (toast && toast.parentNode) {
                        toast.remove();
                    }
                }, 300);
            }

            var timer = setTimeout(descartar, 3000);

            if (btn) {
                btn.addEventListener('click', function() {
                    clearTimeout(timer);
                    descartar();
                });
            }
        })();
    </script>
@endif

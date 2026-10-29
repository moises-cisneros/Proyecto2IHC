@if (session('exito'))
    <x-toast-alerta tipo="exito" :mensaje="session('exito')" />
@endif

@if (session('error'))
    <x-toast-alerta tipo="error" :mensaje="session('error')" />
@endif

@props([
    'nombre' => '',
    'clases' => '',
    'tamano' => 'md',
])

@php
    $normalizado = strtolower(trim((string) $nombre));
    $mapaTamanos = [
        'sm' => ['clase' => 'icono-sm', 'dim' => 16],
        'md' => ['clase' => 'icono-md', 'dim' => 20],
        'lg' => ['clase' => 'icono-lg', 'dim' => 24],
    ];
    $tamanoConfig = $mapaTamanos[$tamano] ?? $mapaTamanos['md'];
    $dimension = $tamanoConfig['dim'];
    $claseTamano = $tamanoConfig['clase'];

    $clasesCss = trim('icono-svg ' . $claseTamano . ' ' . $clases . ' ' . $attributes->get('class', ''));
    $atributosExtra = $attributes->except('class');
@endphp

@switch($normalizado)
    @case('calendario')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
            class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <path d="M8 2v4M16 2v4M3 10h18" />
            <rect width="18" height="18" x="3" y="4" rx="2" />
        </svg>
    @break

    @case('ubicacion')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
            <circle cx="12" cy="10" r="3" />
        </svg>
    @break

    @case('bloqueado')
    @case('cancelado')

    @case('prohibido')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <circle cx="12" cy="12" r="10" />
            <path d="m4.9 4.9 14.2 14.2" />
        </svg>
    @break

    @case('check')
    @case('confirmado')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <path d="M20 6 9 17l-5-5" />
        </svg>
    @break

    @case('editar')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
            <path d="m15 5 4 4" />
        </svg>
    @break

    @case('eliminar')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <path d="M3 6h18" />
            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
            <line x1="10" x2="10" y1="11" y2="17" />
            <line x1="14" x2="14" y1="11" y2="17" />
        </svg>
    @break

    @case('dinero')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <circle cx="12" cy="12" r="10" />
            <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" />
            <path d="M12 18V6" />
        </svg>
    @break

    @case('pago')
    @case('tarjeta')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <rect width="20" height="14" x="2" y="5" rx="2" />
            <line x1="2" x2="22" y1="10" y2="10" />
        </svg>
    @break

    @case('balanza')
    @case('desempate')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z" />
            <path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z" />
            <path d="M7 21h10" />
            <path d="M12 3v18" />
            <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2" />
        </svg>
    @break

    @case('historial')
    @case('reloj')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <circle cx="12" cy="12" r="10" />
            <polyline points="12 6 12 12 16 14" />
        </svg>
    @break

    @case('analitica')
    @case('estadistica')

    @case('grafico')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <line x1="18" x2="18" y1="20" y2="10" />
            <line x1="12" x2="12" y1="20" y2="4" />
            <line x1="6" x2="6" y1="20" y2="14" />
        </svg>
    @break

    @case('salir')
    @case('logout')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" x2="9" y1="12" y2="12" />
        </svg>
    @break

    @case('spinner')
    @case('cargando')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
            stroke-linejoin="round" aria-hidden="true" class="icono-spinner {{ $clasesCss }}" {{ $atributosExtra }}>
            <path d="M21 12a9 9 0 1 1-6.219-8.56" />
        </svg>
    @break

    @case('descarga')
    @case('download')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
            <polyline points="7 10 12 15 17 10" />
            <line x1="12" x2="12" y1="15" y2="3" />
        </svg>
    @break

    @case('usuario')
    @case('cuenta')

    @case('perfil')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
            <circle cx="12" cy="7" r="4" />
        </svg>
    @break

    @case('alerta')
    @case('advertencia')

    @case('peligro')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
            <line x1="12" x2="12" y1="9" y2="13" />
            <line x1="12" x2="12.01" y1="17" y2="17" />
        </svg>
    @break

    @case('candado')
    @case('seguridad')

    @case('password')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
        </svg>
    @break

    @case('qr')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <rect width="5" height="5" x="3" y="3" rx="1" />
            <rect width="5" height="5" x="16" y="3" rx="1" />
            <rect width="5" height="5" x="3" y="16" rx="1" />
            <path d="M21 16h-3a2 2 0 0 0-2 2v3" />
            <path d="M21 21v.01" />
            <path d="M12 7v3a2 2 0 0 1-2 2H7" />
            <path d="M3 12h.01" />
            <path d="M12 3h.01" />
            <path d="M12 16v.01" />
            <path d="M16 12h1" />
            <path d="M21 12v.01" />
            <path d="M12 21v-1" />
        </svg>
    @break

    @case('x')
    @case('cerrar')
        <svg xmlns="http://www.w3.org/2000/svg" width="{{ $dimension }}" height="{{ $dimension }}"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round" aria-hidden="true" class="{{ $clasesCss }}" {{ $atributosExtra }}>
            <path d="M18 6 6 18M6 6l12 12" />
        </svg>
    @break

    @default
@endswitch

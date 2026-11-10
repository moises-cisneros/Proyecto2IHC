@props([
    'href' => '#',
    'activo' => false,
    'icono' => null,
])

<a href="{{ $href }}"
    {{ $attributes->merge([
        'class' => 'nav-item ' . ($activo ? 'nav-item-activo' : 'nav-item-inactivo'),
        'role' => 'menuitem',
        'aria-current' => $activo ? 'page' : null,
    ]) }}>
    @if ($icono)
        <x-icono :nombre="$icono" class="nav-item-icono" />
    @endif
    <span>{{ $slot }}</span>
</a>

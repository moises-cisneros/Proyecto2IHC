<div class="tabla-contenedor">
    <table class="tabla">
        <thead>
            <tr>
                <th style="width: 30%;">Plan</th>
                <th style="width: 16%;">Organizador</th>
                <th style="width: 13%;">Fecha límite</th>
                <th style="width: 12%;">Estado</th>
                <th style="width: 29%; text-align: right;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $plan)
                <tr>
                    {{-- Nombre y organizador con touch target Fitts >=44px y elipsis estricta --}}
                    <td style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <a href="{{ route('planes.detalle', $plan) }}" class="enlace-plan"
                            style="min-height: 44px; display: inline-flex; align-items: center; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                            title="{{ $plan->nombre }}">
                            {{ $plan->nombre }}
                        </a>
                    </td>
                    <td style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ $plan->organizador }}
                    </td>

                    {{-- Fecha formateada como dd/mm/yyyy --}}
                    <td style="white-space: nowrap;">
                        {{ $plan->fecha_limite ? $plan->fecha_limite->format('d/m/Y') : '-' }}
                    </td>

                    {{-- Chip de color según el estado --}}
                    <td style="white-space: nowrap;">
                        <span class="estado estado-{{ $plan->estado }}">
                            @if ($plan->estado === 'pendiente')
                                Pendiente
                            @elseif ($plan->estado === 'confirmado')
                                Confirmado
                            @else
                                Cancelado
                            @endif
                        </span>
                    </td>

                    {{-- Columna de acciones ergonómica (Fitts >=44px, gap: 8px, fila horizontal estricta) --}}
                    <td class="acciones">
                        <div class="acciones-grupo">
                            {{-- 0. Botón ergonómico de acceso a la sala (Fitts >= 44x44px sin desbordamiento) --}}
                            @can('cambiarEstado', $plan)
                                <a href="{{ route('planes.detalle', $plan) }}" class="boton boton-claro"
                                    style="min-height: 44px; min-width: 44px; width: 44px; height: 44px; padding: 0; border-radius: 50px; display: inline-flex; align-items: center; justify-content: center;"
                                    title="Entrar a la sala de votación"
                                    aria-label="Entrar a la sala de votación del plan {{ $plan->nombre }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"
                                        style="display: block;">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                    <span class="sr-only"
                                        style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;">Entrar
                                        a la sala</span>
                                </a>
                            @endcan

                            {{-- 1. Botón de cambio de estado (Confirmar / Cancelar con ancho homogéneo de 104px para evitar desalineación) --}}
                            @can('cambiarEstado', $plan)
                                @if ($plan->estado == 'pendiente')
                                    <form method="POST" action="{{ route('planes.estado', $plan) }}" style="margin: 0;">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="estado" value="confirmado">
                                        <button type="submit" class="boton boton-claro boton-accion-md"
                                            data-confirm="¿Confirmar el plan &laquo;{{ $plan->nombre }}&raquo;?"
                                            data-title="Confirmar plan" data-btn-ok="Sí, confirmar"
                                            data-btn-cancel="Mantener pendiente">Confirmar</button>
                                    </form>
                                @elseif ($plan->estado == 'confirmado')
                                    <form method="POST" action="{{ route('planes.estado', $plan) }}" style="margin: 0;">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="estado" value="cancelado">
                                        <button type="submit" class="boton boton-claro boton-accion-md"
                                            data-confirm="¿Cancelar el plan &laquo;{{ $plan->nombre }}&raquo;? No podrá volver a confirmarse."
                                            data-title="Cancelar plan" data-btn-ok="Sí, cancelar"
                                            data-btn-cancel="Mantener plan">Cancelar</button>
                                    </form>
                                @else
                                    {{-- Plan cancelado: no puede volver a confirmarse --}}
                                    <button class="boton boton-claro boton-accion-md" disabled
                                        title="Un plan cancelado no puede volver a confirmarse">Confirmar</button>
                                @endif
                            @endcan

                            {{-- 2. Botón EDITAR (SVG nítido de alta definición) --}}
                            @can('update', $plan)
                                <a href="{{ route('planes.editar', $plan) }}" class="boton boton-editar boton-accion-sm"
                                    title="Editar plan" aria-label="Editar plan {{ $plan->nombre }}">
                                    <x-icono nombre="editar" style="width: 18px; height: 18px;" />
                                    <span class="sr-only">Editar plan</span>
                                </a>
                            @else
                                @can('cambiarEstado', $plan)
                                    {{-- Creador pero plan cancelado: deshabilitado (Nielsen H5) --}}
                                    @if ($plan->estado === 'cancelado')
                                        <button class="boton boton-editar boton-accion-sm" disabled
                                            style="opacity: 0.4; cursor: not-allowed;"
                                            title="Un plan cancelado no puede editarse"
                                            aria-label="Editar no disponible en plan cancelado">
                                            <x-icono nombre="editar" style="width: 18px; height: 18px;" />
                                            <span class="sr-only">Editar plan</span>
                                        </button>
                                    @endif
                                @endcan
                            @endcan

                            {{-- 3. Botón ELIMINAR (SVG nítido de alta definición) --}}
                            @can('delete', $plan)
                                @if ($plan->estado == 'confirmado')
                                    <button class="boton boton-rojo boton-accion-sm" disabled
                                        style="opacity: 0.4; cursor: not-allowed;"
                                        title="Cancela el plan antes de eliminarlo"
                                        aria-label="Eliminar no disponible en plan confirmado">
                                        <x-icono nombre="eliminar" style="width: 18px; height: 18px;" />
                                        <span class="sr-only">Eliminar plan</span>
                                    </button>
                                @else
                                    <form method="POST" action="{{ route('planes.eliminar', $plan) }}" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="boton boton-rojo boton-accion-sm"
                                            data-confirm="¿Eliminar &laquo;{{ $plan->nombre }}&raquo;? Esta acción no se puede deshacer."
                                            data-title="Eliminar plan" data-btn-ok="Sí, eliminar" data-btn-cancel="Volver"
                                            data-type="danger" title="Eliminar plan"
                                            aria-label="Eliminar plan {{ $plan->nombre }}">
                                            <x-icono nombre="eliminar" style="width: 18px; height: 18px;" />
                                            <span class="sr-only">Eliminar plan</span>
                                        </button>
                                    </form>
                                @endif
                            @endcan

                            {{-- 4. Acciones para participantes o invitados --}}
                            @cannot('cambiarEstado', $plan)
                                @can('aceptar', $plan)
                                    <form method="POST" action="{{ route('planes.aceptar', $plan) }}"
                                        style="margin: 0; display: inline-block;">
                                        @csrf
                                        <button type="submit" class="boton"
                                            style="min-height: 44px; padding: 8px 18px; font-size: 13px; border-radius: 50px; white-space: nowrap;">
                                            Aceptar invitación
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('planes.detalle', $plan) }}" class="boton boton-claro"
                                        style="min-height: 44px; display: inline-flex; align-items: center; padding: 8px 18px; font-size: 13px; border-radius: 50px; white-space: nowrap;">
                                        Ver sala
                                    </a>
                                @endcan
                            @endcannot
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

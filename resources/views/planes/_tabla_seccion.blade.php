<div class="tabla-contenedor">
    <table class="tabla">
        <thead>
            <tr>
                <th class="tabla-seccion-col-plan">Plan</th>
                <th class="tabla-seccion-col-organizador">Organizador</th>
                <th class="tabla-seccion-col-fecha">Fecha límite</th>
                <th class="tabla-seccion-col-estado">Estado</th>
                <th class="tabla-seccion-col-acciones">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $plan)
                <tr>
                    {{-- Nombre y organizador con touch target Fitts >=44px y elipsis estricta --}}
                    <td class="tabla-seccion-celda-truncada">
                        <a href="{{ route('planes.detalle', $plan) }}" class="enlace-plan tabla-seccion-enlace"
                            title="{{ $plan->nombre }}">
                            {{ $plan->nombre }}
                        </a>
                    </td>
                    <td class="tabla-seccion-celda-truncada">
                        {{ $plan->organizador }}
                    </td>

                    {{-- Fecha formateada como dd/mm/yyyy --}}
                    <td class="tabla-seccion-celda-nowrap">
                        {{ $plan->fecha_limite ? $plan->fecha_limite->format('d/m/Y') : '-' }}
                    </td>

                    {{-- Chip de color según el estado --}}
                    <td class="tabla-seccion-celda-nowrap">
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

                    {{-- Columna de acciones ergonómica (Fitts >=44px, gap: var(--space-2), fila horizontal estricta) --}}
                    <td class="acciones">
                        <div class="acciones-grupo">
                            {{-- 0. Botón ergonómico de acceso a la sala (Fitts >= 44x44px sin desbordamiento) --}}
                            @can('cambiarEstado', $plan)
                                <a href="{{ route('planes.detalle', $plan) }}" class="boton boton-claro boton-accion-sm"
                                    title="Entrar a la sala de votación"
                                    aria-label="Entrar a la sala de votación del plan {{ $plan->nombre }}">
                                    <svg class="icono-svg" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                    <span class="sr-only">Entrar
                                        a la sala</span>
                                </a>
                            @endcan

                            {{-- 1. Botón de cambio de estado (Confirmar / Cancelar con ancho homogéneo de 104px para evitar desalineación) --}}
                            @can('cambiarEstado', $plan)
                                @if ($plan->estado == 'pendiente')
                                    <form class="form-reset" method="POST" action="{{ route('planes.estado', $plan) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="estado" value="confirmado">
                                        <button type="submit" class="boton boton-claro boton-accion-md"
                                            data-confirm="¿Confirmar el plan &laquo;{{ $plan->nombre }}&raquo;?"
                                            data-title="Confirmar plan" data-btn-ok="Sí, confirmar"
                                            data-btn-cancel="Mantener pendiente">Confirmar</button>
                                    </form>
                                @elseif ($plan->estado == 'confirmado')
                                    <form class="form-reset" method="POST" action="{{ route('planes.estado', $plan) }}">
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
                                    <x-icono nombre="editar" />
                                    <span class="sr-only">Editar plan</span>
                                </a>
                            @else
                                @can('cambiarEstado', $plan)
                                    {{-- Creador pero plan cancelado: deshabilitado (Nielsen H5) --}}
                                    @if ($plan->estado === 'cancelado')
                                        <button class="boton boton-editar boton-accion-sm" disabled
                                            title="Un plan cancelado no puede editarse"
                                            aria-label="Editar no disponible en plan cancelado">
                                            <x-icono nombre="editar" />
                                            <span class="sr-only">Editar plan</span>
                                        </button>
                                    @endif
                                @endcan
                            @endcan

                            @can('delete', $plan)
                                <button type="button" class="boton boton-rojo boton-accion-sm"
                                    onclick="document.getElementById('modal-eliminar-plan-{{ $plan->id }}').showModal()"
                                    title="Eliminar plan" aria-label="Eliminar plan {{ $plan->nombre }}">
                                    <x-icono nombre="eliminar" />
                                    <span class="sr-only">Eliminar plan</span>
                                </button>

                                <dialog id="modal-eliminar-plan-{{ $plan->id }}" class="dialogo-md">
                                    @php
                                        $puedeEliminar = $plan->puedeEliminarse();
                                        $bloqueosPlan = $puedeEliminar ? [] : $plan->validarBloqueosEliminacion();
                                    @endphp

                                    @if (!$puedeEliminar)
                                        <h2
                                            class="dialogo-titulo tabla-seccion-titulo-destructive tabla-seccion-titulo-con-icono">
                                            <x-icono nombre="alerta" />
                                            <span>No es posible eliminar este plan</span>
                                        </h2>

                                        <div class="caja-salida-honesta">
                                            <div class="caja-salida-honesta-seccion">
                                                <span class="caja-salida-honesta-label">1. ¿Por qué está bloqueada la
                                                    eliminación?</span>
                                                <ul class="tabla-seccion-lista-bloqueos">
                                                    @if (isset($bloqueosPlan['plan_confirmado']))
                                                        <li>
                                                            <strong>Plan confirmado:</strong> Este plan ya fue confirmado
                                                            formalmente y tiene propuestas ganadoras definidas. Para
                                                            eliminarlo, primero debes cancelarlo notificando al grupo.
                                                        </li>
                                                    @endif
                                                    @if (isset($bloqueosPlan['deudas_pendientes_gastos']))
                                                        <li>
                                                            <strong>Gastos con deudas pendientes:</strong> Existen saldos
                                                            impagos entre los participantes. Deben saldarse antes de
                                                            destruir el registro del plan.
                                                        </li>
                                                    @endif
                                                </ul>
                                            </div>

                                            <div class="caja-salida-honesta-seccion">
                                                <span class="caja-salida-honesta-label">2. ¿Qué debes hacer para
                                                    resolverlo?</span>
                                                <div class="caja-salida-honesta-acciones">
                                                    @if (isset($bloqueosPlan['plan_confirmado']))
                                                        <form class="form-reset" method="POST"
                                                            action="{{ route('planes.estado', $plan) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="estado" value="cancelado">
                                                            <button type="submit"
                                                                class="boton boton-claro tabla-seccion-boton-resolver"
                                                                aria-label="Cancelar plan formalmente">
                                                                Cancelar plan formalmente
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if (isset($bloqueosPlan['deudas_pendientes_gastos']))
                                                        <a href="{{ route('planes.detalle', ['plan' => $plan->id, 'pestana' => 'gastos']) }}"
                                                            class="boton boton-claro tabla-seccion-boton-resolver"
                                                            aria-label="Ver gastos y liquidación">
                                                            Ir a saldar gastos
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="dialogo-botones">
                                            <button type="button" class="boton boton-claro boton-simetrico"
                                                onclick="document.getElementById('modal-eliminar-plan-{{ $plan->id }}').close()"
                                                aria-label="Cerrar modal de advertencia">
                                                Entendido, volver
                                            </button>
                                        </div>
                                    @else
                                        <form class="form-reset" method="POST"
                                            action="{{ route('planes.eliminar', $plan) }}">
                                            @csrf
                                            @method('DELETE')
                                            <h2 class="dialogo-titulo tabla-seccion-titulo-destructive">
                                                Eliminar plan
                                            </h2>
                                            <p class="dialogo-mensaje tabla-seccion-dialogo-mensaje">
                                                ¿Estás seguro de que deseas eliminar «{{ $plan->nombre }}»? Esta acción
                                                borrará el plan y todas sus propuestas. No se puede deshacer.
                                            </p>
                                            <div class="dialogo-botones">
                                                <button type="button" class="boton boton-claro boton-simetrico"
                                                    onclick="document.getElementById('modal-eliminar-plan-{{ $plan->id }}').close()"
                                                    aria-label="Conservar plan">
                                                    Conservar plan
                                                </button>
                                                <button type="submit" class="boton boton-rojo boton-simetrico"
                                                    aria-label="Confirmar eliminación de plan">
                                                    Sí, eliminar plan
                                                </button>
                                            </div>
                                        </form>
                                    @endif
                                </dialog>
                            @endcan

                            {{-- 4. Acciones para participantes o invitados --}}
                            @cannot('cambiarEstado', $plan)
                                @can('aceptar', $plan)
                                    <form class="form-reset-inline" method="POST"
                                        action="{{ route('planes.aceptar', $plan) }}">
                                        @csrf
                                        <button type="submit" class="boton tabla-seccion-boton-compacto">
                                            Aceptar invitación
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('planes.detalle', $plan) }}"
                                        class="boton boton-claro tabla-seccion-boton-compacto">
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

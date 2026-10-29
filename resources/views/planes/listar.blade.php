@extends('layouts.app')

@section('titulo', 'Mis planes')

@section('contenido')


<div class="encabezado-planes">
    <h1>Mis planes</h1>
    <button class="boton" onclick="document.getElementById('modal-crear').showModal()">
        + Crear plan
    </button>
</div>

<form method="GET" action="{{ route('planes.listar') }}" class="filtros">

    <input type="text"
           name="buscar"
           placeholder="Buscar por nombre"
           value="{{ $buscar }}">

    <select name="estado">
        <option value="">Todos los estados</option>
        <option value="pendiente"  {{ $estado === 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
        <option value="confirmado" {{ $estado === 'confirmado' ? 'selected' : '' }}>Confirmado</option>
        <option value="cancelado"  {{ $estado === 'cancelado'  ? 'selected' : '' }}>Cancelado</option>
    </select>

    <select name="fecha">
        <option value="">Cualquier fecha</option>
        <option value="7dias"    {{ $fecha === '7dias'    ? 'selected' : '' }}>Próximos 7 días</option>
        <option value="vencidos" {{ $fecha === 'vencidos' ? 'selected' : '' }}>Ya vencidos</option>
    </select>

    <button type="submit" class="boton">Filtrar</button>

    {{-- Solo muestra "Limpiar" si hay algún filtro activo --}}
    @if ($buscar || $estado || $fecha)
        <a href="{{ route('planes.listar') }}" class="boton boton-claro">Limpiar</a>
    @endif

</form>

@if ($buscar || $estado || $fecha)
<div class="filtros-activos">
    <span>Filtros activos:</span>

    {{-- Chip de búsqueda: al clicar, se quita solo el buscar --}}
    @if ($buscar)
        <a href="{{ route('planes.listar', array_filter(['estado' => $estado, 'fecha' => $fecha])) }}"
           class="chip">"{{ $buscar }}" ×</a>
    @endif

    {{-- Chip de estado --}}
    @if ($estado)
        <a href="{{ route('planes.listar', array_filter(['buscar' => $buscar, 'fecha' => $fecha])) }}"
           class="chip">{{ ucfirst($estado) }} ×</a>
    @endif

    {{-- Chip de fecha --}}
    @if ($fecha)
        <a href="{{ route('planes.listar', array_filter(['buscar' => $buscar, 'estado' => $estado])) }}"
           class="chip">{{ $fecha === '7dias' ? 'Próximos 7 días' : 'Ya vencidos' }} ×</a>
    @endif
</div>
@endif

<table class="tabla">
    <thead>
        <tr>
            <th>Plan</th>
            <th>Organizador</th>
            <th>Fecha límite</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>

        {{-- @forelse itera los planes; si no hay ninguno, cae en @empty --}}
        @forelse ($planes as $plan)
        <tr>

            {{-- Nombre y organizador --}}
            <td>{{ $plan->nombre }}</td>
            <td>{{ $plan->organizador }}</td>

            {{-- Fecha formateada como dd/mm/yyyy --}}
            <td>{{ $plan->fecha_limite->format('d/m/Y') }}</td>

            {{-- Chip de color según el estado --}}
            <td>
                <span class="estado estado-{{ $plan->estado }}">
                    @if ($plan->estado === 'pendiente')  Pendiente
                    @elseif ($plan->estado === 'confirmado') Confirmado
                    @else Cancelado
                    @endif
                </span>
            </td>

            {{-- Columna de acciones --}}
            <td class="acciones">

                {{-- BOTÓN DE ESTADO
                     pendiente  → muestra "Confirmar"
                     confirmado → muestra "Cancelar" (con aviso de que no podrá reactivarse)
                     cancelado  → muestra "Confirmar" deshabilitado (regla de negocio) --}}

                @if ($plan->estado == 'pendiente')
                    <form method="POST" action="{{ route('planes.estado', $plan) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="estado" value="confirmado">
                        <button type="submit" class="boton boton-claro"
                            data-confirm="¿Confirmar el plan &laquo;{{ $plan->nombre }}&raquo;?"
                            data-title="Confirmar plan"
                            data-btn-ok="Sí, confirmar"
                            data-btn-cancel="Mantener pendiente">Confirmar</button>
                    </form>

                @elseif ($plan->estado == 'confirmado')
                    <form method="POST" action="{{ route('planes.estado', $plan) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="estado" value="cancelado">
                        <button type="submit" class="boton boton-claro"
                            data-confirm="¿Cancelar el plan &laquo;{{ $plan->nombre }}&raquo;? No podrá volver a confirmarse."
                            data-title="Cancelar plan"
                            data-btn-ok="Sí, cancelar"
                            data-btn-cancel="Mantener plan">Cancelar</button>
                    </form>

                @else
                    {{-- Plan cancelado: no puede volver a confirmarse --}}
                    <button class="boton boton-claro" disabled
                            title="Un plan cancelado no puede volver a confirmarse">Confirmar</button>
                @endif
                <div class="botones-columna">
                    {{-- BOTÓN EDITAR --}}
                    <a href="{{ route('planes.editar', $plan) }}" class="boton boton-editar"> ✏ </a>

                    {{-- BOTÓN ELIMINAR
                        Si está confirmado: deshabilitado (primero hay que cancelarlo)
                        Si no: formulario DELETE con confirmación modal <dialog> nativo --}}
                    @if ($plan->estado == 'confirmado')
                        <button class="boton boton-rojo" disabled
                                title="Cancela el plan antes de eliminarlo"> 🗑 </button>
                    @else
                        <form method="POST" action="{{ route('planes.eliminar', $plan) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="boton boton-rojo"
                                data-confirm="¿Eliminar &laquo;{{ $plan->nombre }}&raquo;? Esta acción no se puede deshacer."
                                data-title="Eliminar plan"
                                data-btn-ok="Sí, eliminar"
                                data-btn-cancel="Volver"
                                data-type="danger"> 🗑 </button>
                        </form>
                    @endif
                </div>
            </td>
        </tr>
        @empty
            {{-- Cuando no hay planes (o no coinciden los filtros) --}}
            <tr>
                <td colspan="5" class="vacio">
                    @if ($buscar || $estado || $fecha)
                        Ningún plan coincide con los filtros aplicados.
                    @else
                        Todavía no tienes planes. ¡Crea el primero!
                    @endif
                </td>
            </tr>
        @endforelse

    </tbody>
</table>

{{-- Modal accesible para crear nuevo plan con organizador automático --}}
@include('planes._modal_crear')

@endsection

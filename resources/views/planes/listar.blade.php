@extends('layouts.app')

@section('titulo', 'Mis planes')

@section('contenido')

    <div class="encabezado-planes">
        <h1>Mis planes</h1>
        <button class="boton" onclick="document.getElementById('modal-crear').showModal()">
            + Crear plan
        </button>
    </div>

    {{-- Pestañas de Navegación por Propósito y Rol (Arquitectura de Información - Nivel 1) --}}
    <div class="pestanas-roles" role="tablist" aria-label="Secciones de planes por rol"
        style="display: flex; gap: 12px; margin-bottom: 20px;">
        <a href="{{ route('planes.listar', array_merge(request()->except('seccion'), ['seccion' => 'creados'])) }}"
            role="tab" aria-selected="{{ $seccion === 'creados' ? 'true' : 'false' }}"
            class="boton {{ $seccion === 'creados' ? '' : 'boton-claro' }}"
            style="min-height: 44px; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; text-decoration: none; padding: 10px 20px; border-radius: 50px;"
            aria-label="Ver mis planes creados como organizador">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M19 8v6" />
                <path d="M22 11h-6" />
            </svg>
            <span>Mis planes creados</span>
        </a>

        <a href="{{ route('planes.listar', array_merge(request()->except('seccion'), ['seccion' => 'invitaciones'])) }}"
            role="tab" aria-selected="{{ $seccion === 'invitaciones' ? 'true' : 'false' }}"
            class="boton {{ $seccion === 'invitaciones' ? '' : 'boton-claro' }}"
            style="min-height: 44px; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; text-decoration: none; padding: 10px 20px; border-radius: 50px;"
            aria-label="Ver planes donde participo como invitado">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                <circle cx="9" cy="7" r="4" />
                <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
            <span>Planes donde participo</span>
        </a>
    </div>

    <form method="GET" action="{{ route('planes.listar') }}" class="filtros">
        <input type="hidden" name="seccion" value="{{ $seccion }}">

        <input type="text" name="buscar" placeholder="Buscar por nombre" value="{{ $buscar }}">

        <select name="estado">
            <option value="">Todos los estados</option>
            <option value="pendiente" {{ $estado === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
            <option value="confirmado" {{ $estado === 'confirmado' ? 'selected' : '' }}>Confirmado</option>
            <option value="cancelado" {{ $estado === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
        </select>

        <select name="fecha">
            <option value="">Cualquier fecha</option>
            <option value="7dias" {{ $fecha === '7dias' ? 'selected' : '' }}>Próximos 7 días</option>
            <option value="vencidos" {{ $fecha === 'vencidos' ? 'selected' : '' }}>Ya vencidos</option>
        </select>

        <button type="submit" class="boton">Filtrar</button>

        {{-- Solo muestra "Limpiar" si hay algún filtro activo --}}
        @if ($buscar || $estado || $fecha)
            <a href="{{ route('planes.listar', ['seccion' => $seccion]) }}" class="boton boton-claro">Limpiar</a>
        @endif
    </form>

    @if ($buscar || $estado || $fecha)
        <div class="filtros-activos">
            <span>Filtros activos:</span>

            {{-- Chip de búsqueda: al clicar, se quita solo el buscar --}}
            @if ($buscar)
                <a href="{{ route('planes.listar', array_filter(['seccion' => $seccion, 'estado' => $estado, 'fecha' => $fecha])) }}"
                    class="chip">"{{ $buscar }}" ×</a>
            @endif

            {{-- Chip de estado --}}
            @if ($estado)
                <a href="{{ route('planes.listar', array_filter(['seccion' => $seccion, 'buscar' => $buscar, 'fecha' => $fecha])) }}"
                    class="chip">{{ ucfirst($estado) }} ×</a>
            @endif

            {{-- Chip de fecha --}}
            @if ($fecha)
                <a href="{{ route('planes.listar', array_filter(['seccion' => $seccion, 'buscar' => $buscar, 'estado' => $estado])) }}"
                    class="chip">{{ $fecha === '7dias' ? 'Próximos 7 días' : 'Ya vencidos' }} ×</a>
            @endif
        </div>
    @endif

    @if ($planes->isEmpty())
        {{-- Estado vacío general de la sección --}}
        <div
            style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 12px; padding: 40px 20px; text-align: center; margin-top: 24px;">
            <p style="font-size: 16px; color: #6f6f6a; margin: 0 0 16px 0;">
                @if ($buscar || $estado || $fecha)
                    Ningún plan coincide con los filtros aplicados en esta sección.
                @elseif ($seccion === 'invitaciones')
                    Todavía no tienes invitaciones a planes. Cuando otros organizadores te inviten, los verás aquí.
                @else
                    Todavía no tienes planes creados. ¡Crea el primero para empezar a coordinar!
                @endif
            </p>
            @if ($seccion === 'creados' && !$buscar && !$estado && !$fecha)
                <button class="boton" onclick="document.getElementById('modal-crear').showModal()">
                    + Crear mi primer plan
                </button>
            @endif
        </div>
    @else
        {{-- Bloques Cronológicos por Urgencia (Arquitectura de Información - Nivel 2) --}}

        {{-- 1. HOY --}}
        <section class="seccion-cronologica" aria-labelledby="bloque-hoy" style="margin-top: 24px;">
            <div
                style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 2px solid #e3e3df;">
                <h2 id="bloque-hoy"
                    style="font-size: 16px; font-weight: 700; margin: 0; display: inline-flex; align-items: center; gap: 8px;">
                    <span>Hoy</span>
                    <span class="badge"
                        style="background: #ffe683; color: #1c1c1a; padding: 2px 10px; border-radius: 50px; font-size: 12px; font-weight: 700;">
                        {{ $totalHoy }}
                    </span>
                </h2>
                <span style="font-size: 13px; color: #6f6f6a;">Planes con fecha límite programada para hoy</span>
            </div>

            @if ($hoy->isEmpty())
                <div
                    style="background: #ffffff; border: 1px dashed #d1d1cd; border-radius: 8px; padding: 12px 16px; text-align: center; color: #6f6f6a; font-size: 14px; margin-bottom: 20px;">
                    No tienes planes para hoy en esta sección.
                </div>
            @else
                @include('planes._tabla_seccion', ['items' => $hoy])
            @endif
        </section>

        {{-- 2. PRÓXIMOS --}}
        <section class="seccion-cronologica" aria-labelledby="bloque-proximos" style="margin-top: 24px;">
            <div
                style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 2px solid #e3e3df;">
                <h2 id="bloque-proximos"
                    style="font-size: 16px; font-weight: 700; margin: 0; display: inline-flex; align-items: center; gap: 8px;">
                    <span>Próximos</span>
                    <span class="badge"
                        style="background: #e3e3df; color: #1c1c1a; padding: 2px 10px; border-radius: 50px; font-size: 12px; font-weight: 700;">
                        {{ $totalProximos }}
                    </span>
                </h2>
                <span style="font-size: 13px; color: #6f6f6a;">Planes futuros en curso</span>
            </div>

            @if ($proximos->isEmpty())
                <div
                    style="background: #ffffff; border: 1px dashed #d1d1cd; border-radius: 8px; padding: 12px 16px; text-align: center; color: #6f6f6a; font-size: 14px; margin-bottom: 20px;">
                    No tienes planes futuros programados en esta sección.
                </div>
            @else
                @include('planes._tabla_seccion', ['items' => $proximos])
            @endif
        </section>

        {{-- 3. VENCIDOS --}}
        <section class="seccion-cronologica" aria-labelledby="bloque-vencidos" style="margin-top: 24px;">
            <div
                style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 2px solid #e3e3df;">
                <h2 id="bloque-vencidos"
                    style="font-size: 16px; font-weight: 700; margin: 0; display: inline-flex; align-items: center; gap: 8px;">
                    <span>Vencidos</span>
                    <span class="badge"
                        style="background: #ffd2d2; color: #9c0000; padding: 2px 10px; border-radius: 50px; font-size: 12px; font-weight: 700;">
                        {{ $totalVencidos }}
                    </span>
                </h2>
                <span style="font-size: 13px; color: #6f6f6a;">Planes cuya fecha límite ya expiró</span>
            </div>

            @if ($vencidos->isEmpty())
                <div
                    style="background: #ffffff; border: 1px dashed #d1d1cd; border-radius: 8px; padding: 12px 16px; text-align: center; color: #6f6f6a; font-size: 14px; margin-bottom: 20px;">
                    No tienes planes vencidos en esta sección.
                </div>
            @else
                @include('planes._tabla_seccion', ['items' => $vencidos])
            @endif
        </section>

    @endif

    {{-- Modal accesible para crear nuevo plan con organizador automático --}}
    @include('planes._modal_crear')

@endsection

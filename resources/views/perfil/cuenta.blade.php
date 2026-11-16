@extends('layouts.app')

@section('titulo', 'Mi Cuenta y Privacidad - Planazo')

@section('contenido')
    <div class="contenedor-principal" style="max-width: 840px; margin: 0 auto; padding: var(--space-6) var(--space-4);">
        <header style="margin-bottom: var(--space-6);">
            <h1
                style="font-size: var(--font-size-2xl); font-weight: var(--font-weight-bold); color: var(--color-foreground); margin: 0 0 var(--space-2);">
                Mi Cuenta y Privacidad
            </h1>
            <p style="font-size: var(--font-size-md); color: var(--color-muted); margin: 0; line-height: 1.5;">
                Visualiza tus datos personales y colaborativos, descarga una copia estructurada de tu información y gestiona
                la permanencia o baja definitiva de tu cuenta de forma transparente.
            </p>
        </header>

        {{-- 1. Resumen de Perfil y Actividad --}}
        <section class="tarjeta-exportacion-datos" aria-labelledby="seccion-datos-personales">
            <h2 id="seccion-datos-personales" class="tarjeta-exportacion-datos-titulo">
                Datos personales y de actividad
            </h2>
            <p class="tarjeta-exportacion-datos-desc">
                Información de identificación y métodos de cobro vinculados a tu cuenta en Planazo.
            </p>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: var(--space-4); margin-bottom: var(--space-4);">
                <div
                    style="padding: var(--space-3) var(--space-4); background: var(--color-surface-muted); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                    <span
                        style="font-size: var(--font-size-xs); font-weight: var(--font-weight-semibold); color: var(--color-muted); text-transform: uppercase;">Nombre
                        completo</span>
                    <div
                        style="font-size: var(--font-size-base); font-weight: var(--font-weight-medium); color: var(--color-foreground); margin-top: var(--space-1);">
                        {{ $usuario->name }}
                    </div>
                </div>

                <div
                    style="padding: var(--space-3) var(--space-4); background: var(--color-surface-muted); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                    <span
                        style="font-size: var(--font-size-xs); font-weight: var(--font-weight-semibold); color: var(--color-muted); text-transform: uppercase;">Correo
                        electrónico</span>
                    <div
                        style="font-size: var(--font-size-base); font-weight: var(--font-weight-medium); color: var(--color-foreground); margin-top: var(--space-1);">
                        {{ $usuario->email }}
                    </div>
                </div>

                <div
                    style="padding: var(--space-3) var(--space-4); background: var(--color-surface-muted); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                    <span
                        style="font-size: var(--font-size-xs); font-weight: var(--font-weight-semibold); color: var(--color-muted); text-transform: uppercase;">Alias
                        o CBU de cobro</span>
                    <div
                        style="font-size: var(--font-size-base); font-weight: var(--font-weight-medium); color: var(--color-foreground); margin-top: var(--space-1);">
                        {{ $usuario->alias_pago ?? ($usuario->cbu_pago ?? 'Sin datos de cobro configurados') }}
                    </div>
                </div>

                <div
                    style="padding: var(--space-3) var(--space-4); background: var(--color-surface-muted); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
                    <span
                        style="font-size: var(--font-size-xs); font-weight: var(--font-weight-semibold); color: var(--color-muted); text-transform: uppercase;">Planes
                        vinculados</span>
                    <div
                        style="font-size: var(--font-size-base); font-weight: var(--font-weight-medium); color: var(--color-foreground); margin-top: var(--space-1);">
                        {{ $planesOrganizados->count() }} organizados · {{ $planesParticipados->count() }} colaboraciones
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. Portabilidad de Datos (DESIGN.md §7.C) --}}
        <section class="tarjeta-exportacion-datos" aria-labelledby="seccion-portabilidad">
            <h2 id="seccion-portabilidad" class="tarjeta-exportacion-datos-titulo">
                Portabilidad y descarga de datos
            </h2>
            <p class="tarjeta-exportacion-datos-desc">
                Descarga una copia completa de tus datos personales, planes organizados, participaciones, votos emitidos y
                balances históricos de gastos en formatos estándar (JSON o CSV).
            </p>

            <div class="tarjeta-exportacion-datos-botones">
                <a href="{{ route('perfil.datos.exportar', ['formato' => 'json']) }}"
                    class="boton boton-claro boton-simetrico" download="mis-datos-planazo.json"
                    aria-label="Descargar mis datos en formato JSON">
                    <x-icono nombre="descarga" />
                    <span>Descargar datos (JSON)</span>
                </a>

                <a href="{{ route('perfil.datos.exportar', ['formato' => 'csv']) }}"
                    class="boton boton-claro boton-simetrico" download="mis-datos-planazo.csv"
                    aria-label="Descargar mis datos en formato CSV tabular">
                    <x-icono nombre="descarga" />
                    <span>Descargar datos (CSV)</span>
                </a>
            </div>
        </section>

        {{-- 3. Zona de Permanencia y Salida Honesta (DESIGN.md §7.A y §7.B) --}}
        <section class="tarjeta-exportacion-datos" style="border-color: var(--color-destructive-border);"
            aria-labelledby="seccion-salida-cuenta">
            <h2 id="seccion-salida-cuenta" class="tarjeta-exportacion-datos-titulo"
                style="color: var(--color-destructive);">
                Salida honesta y eliminación de cuenta
            </h2>
            <p class="tarjeta-exportacion-datos-desc">
                En Planazo salir debe ser tan transparente y sencillo como registrarte. No aplicamos trabas artificiales ni
                chantaje emocional, pero validamos que no dejes deudas sin liquidar ni planes abiertos que afecten a otros
                participantes.
            </p>

            @if (!empty($bloqueosCuenta))
                {{-- Caja de Salida Honesta: Por qué y Qué hacer (DESIGN.md §7.A) --}}
                <div class="caja-salida-honesta" role="alert" aria-live="polite">
                    <div class="caja-salida-honesta-titulo">
                        <x-icono nombre="alerta" />
                        <span>Eliminación de cuenta temporalmente restringida</span>
                    </div>

                    <div class="caja-salida-honesta-seccion">
                        <span class="caja-salida-honesta-label">1. ¿Por qué no puedes dar de baja tu cuenta en este
                            momento?</span>
                        <ul style="margin: var(--space-1) 0 var(--space-3) var(--space-4); padding: 0;">
                            @if (isset($bloqueosCuenta['planes_abiertos_organizados']))
                                <li style="margin-bottom: var(--space-1);">
                                    <strong>Planes abiertos organizados:</strong> Organizas planes que aún están activos.
                                    Para no dejar al grupo sin organizador, debes cancelarlos o transferir la coordinación
                                    antes de retirarte.
                                </li>
                            @endif

                            @if (isset($bloqueosCuenta['deudas_pendientes_gastos']))
                                <li style="margin-bottom: var(--space-1);">
                                    <strong>Deudas pendientes en gastos:</strong> Mantienes saldos deudores pendientes de
                                    liquidación en planes activos. Debes saldar tus cuentas antes de eliminar tu perfil.
                                </li>
                            @endif
                        </ul>
                    </div>

                    <div class="caja-salida-honesta-seccion">
                        <span class="caja-salida-honesta-label">2. ¿Qué debes hacer para resolverlo?</span>
                        <div
                            style="display: flex; flex-direction: column; gap: var(--space-2); margin-top: var(--space-2);">
                            @if (isset($bloqueosCuenta['planes_abiertos_organizados']))
                                @foreach ($bloqueosCuenta['planes_abiertos_organizados']['planes'] as $planBloqueado)
                                    <div
                                        style="display: flex; align-items: center; justify-content: space-between; background: var(--color-surface); padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); border: 1px solid var(--color-destructive-border);">
                                        <span
                                            style="font-weight: var(--font-weight-medium); color: var(--color-foreground);">
                                            Plan: {{ $planBloqueado['nombre'] }} ({{ ucfirst($planBloqueado['estado']) }})
                                        </span>
                                        <a href="{{ route('planes.detalle', $planBloqueado['id']) }}"
                                            class="boton boton-claro"
                                            style="min-height: var(--touch-min); padding: var(--space-1) var(--space-3); font-size: var(--font-size-xs);"
                                            aria-label="Gestionar plan {{ $planBloqueado['nombre'] }}">
                                            Gestionar o cancelar plan
                                        </a>
                                    </div>
                                @endforeach
                            @endif

                            @if (isset($bloqueosCuenta['deudas_pendientes_gastos']))
                                @foreach ($bloqueosCuenta['deudas_pendientes_gastos']['planes'] as $planConDeuda)
                                    <div
                                        style="display: flex; align-items: center; justify-content: space-between; background: var(--color-surface); padding: var(--space-2) var(--space-3); border-radius: var(--radius-md); border: 1px solid var(--color-destructive-border);">
                                        <span
                                            style="font-weight: var(--font-weight-medium); color: var(--color-foreground);">
                                            Deuda en «{{ $planConDeuda['nombre'] }}»:
                                            <strong>{{ number_format($planConDeuda['monto'], 2) }} Bs</strong>
                                        </span>
                                        <a href="{{ route('planes.detalle', ['plan' => $planConDeuda['id'], 'pestana' => 'gastos']) }}"
                                            class="boton boton-claro"
                                            style="min-height: var(--touch-min); padding: var(--space-1) var(--space-3); font-size: var(--font-size-xs);"
                                            aria-label="Ir a liquidar deuda en plan {{ $planConDeuda['nombre'] }}">
                                            Ir a liquidar gastos
                                        </a>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div
                    style="background: var(--color-surface-muted); padding: var(--space-4); border-radius: var(--radius-lg); margin-bottom: var(--space-4); font-size: var(--font-size-sm); color: var(--color-foreground); line-height: 1.5;">
                    <p style="margin: 0 0 var(--space-2);">
                        <strong>Tu cuenta no posee planes abiertos ni deudas pendientes.</strong> Cumples con todas las
                        condiciones para solicitar la baja definitiva en cualquier momento.
                    </p>
                    <p style="margin: 0; color: var(--color-muted);">
                        Al confirmar la baja, se inhabilitarán tus credenciales de forma inmediata y no podrás volver a
                        iniciar sesión. Los registros históricos de planes cerrados en los que participaste se conservarán
                        anonimizados para la trazabilidad contable del grupo.
                    </p>
                </div>

                <button type="button" class="boton boton-rojo boton-simetrico"
                    onclick="document.getElementById('modal-eliminar-cuenta').showModal()"
                    aria-label="Abrir diálogo para eliminar mi cuenta">
                    <x-icono nombre="eliminar" />
                    <span>Eliminar mi cuenta</span>
                </button>
            @endif
        </section>
    </div>

    {{-- Diálogo Modal Accesible de Salida Simétrica (DESIGN.md §7.B) --}}
    <dialog id="modal-eliminar-cuenta" class="dialogo-salida-simetrica" aria-labelledby="titulo-modal-eliminar-cuenta">
        <form method="POST" action="{{ route('perfil.eliminar-cuenta') }}">
            @csrf
            <h2 id="titulo-modal-eliminar-cuenta" class="dialogo-titulo" style="color: var(--color-destructive);">
                Confirmar eliminación de cuenta
            </h2>

            <p class="dialogo-mensaje" style="line-height: 1.5;">
                ¿Deseas eliminar definitivamente tu cuenta en Planazo? Esta acción inhabilitará tu acceso y cerrará tu
                sesión activa de inmediato. No podrás volver a ingresar con este correo electrónico.
            </p>

            <div style="margin-bottom: var(--space-5);">
                <label for="input-current-password"
                    style="display: block; font-size: var(--font-size-sm); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-2);">
                    Ingresa tu contraseña actual para confirmar:
                </label>
                <input type="password" id="input-current-password" name="current_password" required
                    autocomplete="current-password" placeholder="Contraseña actual"
                    style="width: 100%; min-height: var(--touch-min); padding: var(--space-2) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-base); box-sizing: border-box;">
                @error('current_password')
                    <div style="color: var(--color-destructive); font-size: var(--font-size-xs); margin-top: var(--space-1);">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Botonera con Simetría Estricta de Fitts (DESIGN.md §7.B) --}}
            <div class="dialogo-botones"
                style="display: flex; gap: var(--space-3); justify-content: flex-end; align-items: center; margin-top: var(--space-5);">
                <button type="button" class="boton boton-claro boton-simetrico"
                    onclick="document.getElementById('modal-eliminar-cuenta').close()"
                    aria-label="Cancelar y mantener mi cuenta">
                    Conservar cuenta
                </button>

                <button type="submit" class="boton boton-rojo boton-simetrico"
                    aria-label="Confirmar y eliminar mi cuenta">
                    Sí, eliminar mi cuenta
                </button>
            </div>
        </form>
    </dialog>
@endsection

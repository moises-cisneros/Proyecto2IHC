@extends('layouts.app')

@section('titulo', 'Mi Cuenta y Privacidad - Planazo')

@section('contenido')
    <div class="cuenta-contenedor">
        <header class="cuenta-encabezado">
            <h1 class="cuenta-encabezado-titulo">
                Mi Cuenta y Privacidad
            </h1>
            <p class="cuenta-encabezado-desc">
                Administra tus datos personales, credenciales de acceso, métodos de cobro y opciones de privacidad.
            </p>
        </header>

        <section class="tarjeta cuenta-perfil" aria-label="Resumen de tu perfil">
            <span class="avatar avatar-lg" aria-hidden="true">{{ strtoupper(mb_substr($usuario->name, 0, 1)) }}</span>
            <div class="cuenta-perfil-datos">
                <p class="cuenta-perfil-nombre">{{ $usuario->name }}</p>
                <p class="cuenta-perfil-correo">{{ $usuario->email }}</p>
            </div>
            @if ($usuario->created_at)
                <span class="cuenta-perfil-meta">Miembro desde {{ $usuario->created_at->format('d/m/Y') }}</span>
            @endif
        </section>

        <div class="cuenta-columnas">
            <div class="cuenta-columna">
                <section class="tarjeta" aria-labelledby="seccion-datos-personales">
                    <div>
                        <div class="tarjeta-header">
                            <div class="tarjeta-titulo-grupo">
                                <x-icono nombre="perfil" />
                                <h2 id="seccion-datos-personales" class="tarjeta-titulo">
                                    Datos personales
                                </h2>
                            </div>
                        </div>

                        <p class="tarjeta-desc">
                            Visible para quienes comparten planes contigo.
                        </p>

                        <form method="POST" action="{{ route('perfil.actualizar') }}" class="formulario">
                            @csrf
                            @method('PUT')

                            <div class="campo">
                                <label for="input-perfil-name">
                                    Nombre completo
                                </label>
                                <input type="text" id="input-perfil-name" name="name"
                                    value="{{ old('name', $usuario->name) }}" required
                                    class="@error('name') invalido @enderror" placeholder="Tu nombre y apellido">
                                @error('name')
                                    <span class="campo-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="campo">
                                <label for="input-perfil-email">
                                    Correo electrónico
                                </label>
                                <input type="email" id="input-perfil-email" name="email"
                                    value="{{ old('email', $usuario->email) }}" required
                                    class="@error('email') invalido @enderror" placeholder="correo@ejemplo.com">
                                @error('email')
                                    <span class="campo-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="tarjeta-acciones tarjeta-acciones-fin">
                                <button type="submit" class="boton boton-simetrico"
                                    aria-label="Guardar cambios de datos personales">
                                    Guardar datos
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="tarjeta" aria-labelledby="seccion-seguridad">
                    <div>
                        <div class="tarjeta-header">
                            <div class="tarjeta-titulo-grupo">
                                <x-icono nombre="candado" />
                                <h2 id="seccion-seguridad" class="tarjeta-titulo">
                                    Seguridad y contraseña
                                </h2>
                            </div>
                        </div>

                        <p class="tarjeta-desc">
                            Usa al menos 8 caracteres.
                        </p>

                        <form method="POST" action="{{ route('perfil.contrasena.actualizar') }}" class="formulario">
                            @csrf
                            @method('PUT')

                            <div class="campo">
                                <label for="input-seguridad-current-password">
                                    Contraseña actual
                                </label>
                                <input type="password" id="input-seguridad-current-password" name="current_password"
                                    required autocomplete="current-password"
                                    class="@if ($errors->cambioContrasena->has('current_password')) invalido @endif"
                                    placeholder="Tu contraseña actual">
                                @if ($errors->cambioContrasena->has('current_password'))
                                    <span
                                        class="campo-error">{{ $errors->cambioContrasena->first('current_password') }}</span>
                                @endif
                            </div>

                            <div class="campo">
                                <label for="input-seguridad-new-password">
                                    Nueva contraseña
                                </label>
                                <input type="password" id="input-seguridad-new-password" name="password" required
                                    autocomplete="new-password" minlength="8"
                                    class="@if ($errors->cambioContrasena->has('password')) invalido @endif"
                                    placeholder="Mínimo 8 caracteres">
                                @if ($errors->cambioContrasena->has('password'))
                                    <span class="campo-error">{{ $errors->cambioContrasena->first('password') }}</span>
                                @endif
                            </div>

                            <div class="campo">
                                <label for="input-seguridad-password-confirmation">
                                    Confirmar nueva contraseña
                                </label>
                                <input type="password" id="input-seguridad-password-confirmation"
                                    name="password_confirmation" required autocomplete="new-password" minlength="8"
                                    placeholder="Repite la nueva contraseña">
                            </div>

                            <div class="tarjeta-acciones tarjeta-acciones-fin">
                                <button type="submit" class="boton boton-simetrico"
                                    aria-label="Actualizar contraseña de acceso">
                                    Actualizar contraseña
                                </button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>

            <div class="cuenta-columna">
                <section class="tarjeta" aria-labelledby="seccion-datos-cobro">
                    <div>
                        <div class="tarjeta-header">
                            <div class="tarjeta-titulo-grupo">
                                <x-icono nombre="pago" />
                                <h2 id="seccion-datos-cobro" class="tarjeta-titulo">
                                    Métodos de cobro
                                </h2>
                            </div>
                            @if ($usuario->tieneDatosCobro())
                                <span class="badge-estado badge-estado-confirmado">
                                    <x-icono nombre="check" tamano="sm" />
                                    <span>Configurado</span>
                                </span>
                            @else
                                <span class="badge-estado badge-estado-pendiente">
                                    <span>Sin configurar</span>
                                </span>
                            @endif
                        </div>

                        <p class="tarjeta-desc">
                            Para que tus amigos te liquiden los gastos.
                        </p>

                        <form method="POST" action="{{ route('perfil.cobro.actualizar') }}"
                            enctype="multipart/form-data" class="formulario">
                            @csrf
                            @method('PUT')

                            <div class="campo">
                                <label for="input-cobro-alias">
                                    Alias de cobro
                                </label>
                                <input type="text" id="input-cobro-alias" name="alias_pago"
                                    value="{{ old('alias_pago', $usuario->alias_pago) }}"
                                    class="@error('alias_pago') invalido @enderror"
                                    placeholder="ej. usuario.mp o banco.alias">
                                @error('alias_pago')
                                    <span class="campo-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="campo">
                                <label for="input-cobro-cbu">
                                    CBU o N° de cuenta
                                </label>
                                <input type="text" id="input-cobro-cbu" name="cbu_pago"
                                    value="{{ old('cbu_pago', $usuario->cbu_pago) }}"
                                    class="@error('cbu_pago') invalido @enderror"
                                    placeholder="ej. 0000003100010000000000">
                                @error('cbu_pago')
                                    <span class="campo-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="campo">
                                <label for="input-cobro-qr">
                                    Código QR (Imagen JPEG, PNG o WebP)
                                </label>
                                <input type="file" id="input-cobro-qr" name="qr_pago"
                                    accept="image/jpeg,image/png,image/webp" class="@error('qr_pago') invalido @enderror">
                                @error('qr_pago')
                                    <span class="campo-error">{{ $message }}</span>
                                @enderror

                                @if ($usuario->qr_pago_path)
                                    <div class="cuenta-qr-preview-box">
                                        <img src="{{ asset('storage/' . $usuario->qr_pago_path) }}"
                                            alt="Código QR configurado" class="cuenta-qr-img">
                                        <div class="cuenta-qr-info">
                                            <span>QR actual configurado</span>
                                            <label class="cuenta-qr-remover">
                                                <input type="checkbox" name="eliminar_qr" value="1">
                                                <span>Eliminar este código QR</span>
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="tarjeta-acciones tarjeta-acciones-fin">
                                <button type="submit" class="boton boton-simetrico"
                                    aria-label="Guardar métodos de cobro">
                                    Guardar cobro
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="tarjeta" aria-labelledby="seccion-actividad">
                    <div>
                        <div class="tarjeta-header">
                            <div class="tarjeta-titulo-grupo">
                                <x-icono nombre="analitica" />
                                <h2 id="seccion-actividad" class="tarjeta-titulo">
                                    Actividad y participación
                                </h2>
                            </div>
                        </div>

                        <p class="tarjeta-desc">
                            Tu actividad en Planazo de un vistazo.
                        </p>

                        <div class="metricas-grid">
                            <a href="{{ route('planes.listar', ['seccion' => 'creados']) }}"
                                class="metrica metrica-enlace">
                                <span class="metrica-valor">{{ $resumenActividad['activos'] }}</span>
                                <span class="metrica-label">Planes activos</span>
                            </a>

                            <a href="{{ route('planes.listar', ['seccion' => 'creados', 'estado' => 'confirmado']) }}"
                                class="metrica metrica-enlace">
                                <span class="metrica-valor">{{ $resumenActividad['completados'] }}</span>
                                <span class="metrica-label">Completados</span>
                            </a>

                            <a href="{{ route('planes.listar', ['seccion' => 'invitaciones']) }}"
                                class="metrica metrica-enlace">
                                <span class="metrica-valor">{{ $resumenActividad['participaciones'] }}</span>
                                <span class="metrica-label">Participaciones</span>
                            </a>
                        </div>
                    </div>

                </section>
            </div>
        </div>

        <section class="tarjeta cuenta-seccion-completa" aria-labelledby="seccion-portabilidad">
            <div class="tarjeta-header">
                <h2 id="seccion-portabilidad" class="tarjeta-titulo">
                    Descarga de datos
                </h2>
            </div>
            <p class="tarjeta-desc">
                Descarga una copia completa de tus datos personales, planes, votos y liquidaciones en formatos estándar.
            </p>

            <div class="tarjeta-acciones">
                <a href="{{ route('perfil.datos.exportar', ['formato' => 'json']) }}"
                    class="boton boton-claro boton-simetrico" download="mis-datos-planazo.json"
                    aria-label="Descargar mis datos en formato JSON">
                    <x-icono nombre="descarga" />
                    <span>Descargar mis datos (JSON)</span>
                </a>

                <a href="{{ route('perfil.datos.exportar', ['formato' => 'csv']) }}"
                    class="boton boton-claro boton-simetrico" download="mis-datos-planazo.csv"
                    aria-label="Descargar mis datos en formato CSV">
                    <x-icono nombre="descarga" />
                    <span>Descargar mis datos (CSV)</span>
                </a>
            </div>
        </section>

        <section class="tarjeta tarjeta-peligro cuenta-seccion-completa" aria-labelledby="seccion-salida-cuenta">
            <div class="tarjeta-header">
                <div class="tarjeta-titulo-grupo">
                    <x-icono nombre="peligro" />
                    <h2 id="seccion-salida-cuenta" class="tarjeta-titulo">
                        Eliminación de cuenta
                    </h2>
                </div>
            </div>

            <div class="cuenta-danger-resumen">
                <div class="cuenta-danger-texto">
                    Esta acción es definitiva y cierra tu acceso de inmediato.
                </div>

                @if (!empty($bloqueosCuenta))
                    <button type="button" class="boton boton-rojo boton-simetrico"
                        onclick="document.getElementById('modal-bloqueos-cuenta').showModal()"
                        aria-label="Revisar requisitos para eliminar mi cuenta">
                        <x-icono nombre="eliminar" />
                        <span>Eliminar mi cuenta</span>
                    </button>
                @else
                    <button type="button" class="boton boton-rojo boton-simetrico"
                        onclick="document.getElementById('modal-eliminar-cuenta').showModal()"
                        aria-label="Abrir diálogo para eliminar mi cuenta">
                        <x-icono nombre="eliminar" />
                        <span>Eliminar mi cuenta</span>
                    </button>
                @endif
            </div>
        </section>
    </div>

    {{-- Diálogos Modales Accesibles --}}

    @if (!empty($bloqueosCuenta))
        <dialog id="modal-bloqueos-cuenta" class="dialogo-md" aria-labelledby="titulo-modal-bloqueos">
            <h2 id="titulo-modal-bloqueos" class="dialogo-titulo-destructive">
                No puedes dar de baja tu cuenta todavía
            </h2>

            <p class="dialogo-mensaje-muted">
                Tienes acciones pendientes que requieren tu atención:
            </p>

            <div class="caja-salida-honesta-planes">
                @if (isset($bloqueosCuenta['planes_abiertos_organizados']))
                    @foreach ($bloqueosCuenta['planes_abiertos_organizados']['planes'] as $planBloqueado)
                        <div class="caja-salida-honesta-fila">
                            <span class="caja-salida-honesta-info">
                                <span class="caja-salida-honesta-nombre">{{ $planBloqueado['nombre'] }}</span>
                                <span
                                    class="caja-salida-honesta-meta">{{ 'Plan abierto · ' . ucfirst($planBloqueado['estado']) }}</span>
                            </span>
                            <a href="{{ route('planes.detalle', $planBloqueado['id']) }}"
                                class="boton boton-claro boton-xs"
                                aria-label="Gestionar plan {{ $planBloqueado['nombre'] }}">
                                Gestionar
                            </a>
                        </div>
                    @endforeach
                @endif

                @if (isset($bloqueosCuenta['deudas_pendientes_gastos']))
                    @foreach ($bloqueosCuenta['deudas_pendientes_gastos']['planes'] as $planConDeuda)
                        <div class="caja-salida-honesta-fila">
                            <span class="caja-salida-honesta-info">
                                <span class="caja-salida-honesta-nombre">{{ $planConDeuda['nombre'] }}</span>
                                <span class="caja-salida-honesta-meta">Deuda pendiente ·
                                    <strong>{{ number_format($planConDeuda['monto'], 2) }} Bs</strong></span>
                            </span>
                            <a href="{{ route('planes.detalle', ['plan' => $planConDeuda['id'], 'pestana' => 'gastos']) }}"
                                class="boton boton-claro boton-xs"
                                aria-label="Ir a liquidar deuda en plan {{ $planConDeuda['nombre'] }}">
                                Liquidar
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="dialogo-botones-derecha">
                <button type="button" class="boton boton-claro boton-simetrico"
                    onclick="document.getElementById('modal-bloqueos-cuenta').close()"
                    aria-label="Cerrar diálogo y resolver pendientes">
                    Entendido
                </button>
            </div>
        </dialog>
    @else
        <dialog id="modal-eliminar-cuenta" class="dialogo-md" aria-labelledby="titulo-modal-eliminar-cuenta">
            <form method="POST" action="{{ route('perfil.eliminar-cuenta') }}">
                @csrf
                <h2 id="titulo-modal-eliminar-cuenta" class="dialogo-titulo-destructive">
                    Confirmar eliminación de cuenta
                </h2>

                <p class="dialogo-mensaje-muted">
                    Perderás el acceso con este correo y se cerrará tu sesión de inmediato.
                </p>

                <div class="campo">
                    <label for="input-current-password">
                        Ingresa tu contraseña actual para confirmar:
                    </label>
                    <input type="password" id="input-current-password" name="current_password" required
                        autocomplete="current-password" placeholder="Contraseña actual">
                    @error('current_password')
                        <span class="campo-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="dialogo-botones-derecha">
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
    @endif

    @if (empty($bloqueosCuenta) && $errors->has('current_password'))
        <script>
            document.getElementById('modal-eliminar-cuenta')?.showModal();
        </script>
    @endif
@endsection

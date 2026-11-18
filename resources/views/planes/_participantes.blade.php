<div class="seccion-participantes tarjeta participantes-seccion">
    <div class="participantes-encabezado">
        <div>
            <h2 class="participantes-titulo">
                Participantes y Colaboradores</h2>
            <p class="participantes-subtitulo">Miembros convocados para
                coordinar y votar en este plan.</p>
        </div>

        @can('invitar', $plan)
            <button type="button" class="boton participantes-boton-invitar"
                onclick="if (window.track) { window.track('invitacion_iniciada', { plan_id: {{ $plan->id }} }); } document.getElementById('modal-invitar').showModal()"
                aria-label="+ Invitar colaborador">
                + Invitar colaborador
            </button>
        @endcan
    </div>

    {{-- Lista de colaboradores con roles --}}
    <div class="participantes-grid">
        {{-- Creador / Organizador --}}
        <div class="participantes-item">
            <div class="avatar participantes-avatar">
                {{ strtoupper(substr($plan->organizador, 0, 1)) }}
            </div>
            <div class="participantes-datos">
                <div class="participantes-nombre">
                    {{ $plan->organizador }}
                </div>
                <span class="participantes-rol participantes-rol-organizador">
                    Organizador
                </span>
            </div>
        </div>

        {{-- Participantes con invitación aceptada --}}
        @foreach ($plan->participantes as $participante)
            <div class="participantes-item">
                <div class="avatar participantes-avatar participantes-avatar-participante">
                    {{ strtoupper(substr($participante->name ?? $participante->email, 0, 1)) }}
                </div>
                <div class="participantes-datos">
                    <div class="participantes-nombre">
                        {{ $participante->name ?? $participante->email }}
                    </div>
                    <span class="participantes-rol participantes-rol-participante">
                        Participante
                    </span>
                </div>
            </div>
        @endforeach

        {{-- Invitaciones pendientes (visibles para el creador y para el usuario invitado) --}}
        @foreach ($plan->invitaciones->where('estado', 'pendiente') as $invitacion)
            @php
                $esInvitadoActual =
                    auth()->check() &&
                    ((int) auth()->id() === (int) $invitacion->user_id ||
                        strtolower(auth()->user()->email) === strtolower($invitacion->email));
                $esCreador = auth()->check() && (int) auth()->id() === (int) $plan->user_id;
                $usuarioInvitado = $invitacion->user ?? \App\Models\User::where('email', $invitacion->email)->first();
                $nombreAMostrar = $usuarioInvitado?->name ?? $invitacion->email;
                $inicial = strtoupper(substr($nombreAMostrar, 0, 1));
            @endphp

            @if ($esCreador || $esInvitadoActual)
                <div class="participantes-item participantes-item-pendiente">
                    <div class="participantes-pendiente-info">
                        <div class="avatar participantes-avatar participantes-avatar-pendiente">
                            {{ $inicial }}
                        </div>
                        <div class="participantes-datos">
                            <div class="participantes-nombre">
                                {{ $nombreAMostrar }}
                            </div>
                            @if ($usuarioInvitado && $usuarioInvitado->name !== $invitacion->email)
                                <div class="participantes-correo">
                                    {{ $invitacion->email }}
                                </div>
                            @endif
                            <span class="participantes-rol participantes-rol-pendiente">
                                {{ $esInvitadoActual ? 'Tu invitación pendiente' : 'Invitación pendiente' }}
                            </span>
                        </div>
                    </div>

                    @if ($esInvitadoActual)
                        <div class="participantes-invitacion-acciones">
                            <form class="form-reset" method="POST" action="{{ route('planes.aceptar', $plan) }}">
                                @csrf
                                <button type="submit" class="boton participantes-boton-invitacion"
                                    aria-label="Aceptar invitación">
                                    Aceptar invitación
                                </button>
                            </form>
                            <form class="form-reset" method="POST"
                                action="{{ route('planes.rechazar-invitacion', $plan) }}">
                                @csrf
                                <button type="submit" class="boton boton-claro participantes-boton-invitacion"
                                    aria-label="Rechazar invitación">
                                    Rechazar invitación
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
        @endforeach
    </div>
</div>

{{-- Modal accesible para invitar colaborador --}}
@can('invitar', $plan)
    <dialog class="dialogo-md" id="modal-invitar">
        <form method="POST" action="{{ route('planes.invitar', $plan) }}">
            @csrf
            <h2 class="dialogo-titulo participantes-dialogo-titulo">
                Invitar colaborador</h2>
            <p class="dialogo-mensaje participantes-dialogo-mensaje">Envía una
                invitación por correo electrónico para que se una a este plan y participe en las votaciones.</p>

            <div class="campo">
                <label for="input-email-invitar">
                    Correo electrónico del invitado
                </label>
                <input type="email" id="input-email-invitar" name="email" required placeholder="ejemplo@correo.com">
            </div>

            <div class="dialogo-botones-derecha participantes-dialogo-botones">
                <button type="button" class="boton boton-claro" onclick="document.getElementById('modal-invitar').close()">
                    Cancelar
                </button>
                <button type="submit" class="boton participantes-boton-enviar">
                    Enviar invitación
                </button>
            </div>
        </form>
    </dialog>
@endcan

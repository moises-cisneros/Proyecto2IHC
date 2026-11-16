<div class="seccion-participantes"
    style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-xl); padding: var(--space-6); margin-bottom: var(--space-6);">
    <div
        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-4); flex-wrap: wrap; gap: var(--space-3);">
        <div>
            <h2
                style="margin: 0 0 var(--space-1) 0; font-size: var(--font-size-xl); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                Participantes y Colaboradores</h2>
            <p style="margin: 0; color: var(--color-muted); font-size: var(--font-size-md);">Miembros convocados para
                coordinar y votar en este plan.</p>
        </div>

        @can('invitar', $plan)
            <button type="button" class="boton"
                style="min-height: var(--touch-min); min-width: var(--touch-min); display: inline-flex; align-items: center; justify-content: center; padding: var(--space-2) var(--space-4); font-weight: var(--font-weight-semibold);"
                onclick="document.getElementById('modal-invitar').showModal()" aria-label="+ Invitar colaborador">
                + Invitar colaborador
            </button>
        @endcan
    </div>

    {{-- Lista de colaboradores con roles --}}
    <div class="grid-participantes"
        style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: var(--space-3);">
        {{-- Creador / Organizador --}}
        <div
            style="display: flex; align-items: center; gap: var(--space-3); padding: var(--space-3) var(--space-4); background: var(--color-surface-soft); border: 1px solid var(--color-border); border-radius: var(--radius-lg);">
            <div
                style="width: 36px; height: 36px; border-radius: var(--radius-circle); background: var(--color-foreground); color: var(--color-surface); display: flex; align-items: center; justify-content: center; font-weight: var(--font-weight-bold); font-size: var(--font-size-md);">
                {{ strtoupper(substr($plan->organizador, 0, 1)) }}
            </div>
            <div style="flex: 1; min-width: 0;">
                <div
                    style="font-weight: var(--font-weight-semibold); color: var(--color-foreground); font-size: var(--font-size-md); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $plan->organizador }}
                </div>
                <span
                    style="display: inline-block; padding: var(--space-1) var(--space-2); border-radius: var(--radius-sm); background: var(--color-success-bg); color: var(--color-success); font-size: var(--font-size-xs); font-weight: var(--font-weight-semibold);">
                    Organizador
                </span>
            </div>
        </div>

        {{-- Participantes con invitación aceptada --}}
        @foreach ($plan->participantes as $participante)
            <div
                style="display: flex; align-items: center; gap: var(--space-3); padding: var(--space-3) var(--space-4); background: var(--color-surface-soft); border: 1px solid var(--color-border); border-radius: var(--radius-lg);">
                <div
                    style="width: 36px; height: 36px; border-radius: var(--radius-circle); background: var(--color-muted); color: var(--color-surface); display: flex; align-items: center; justify-content: center; font-weight: var(--font-weight-bold); font-size: var(--font-size-md);">
                    {{ strtoupper(substr($participante->name ?? $participante->email, 0, 1)) }}
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div
                        style="font-weight: var(--font-weight-semibold); color: var(--color-foreground); font-size: var(--font-size-md); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $participante->name ?? $participante->email }}
                    </div>
                    <span
                        style="display: inline-block; padding: var(--space-1) var(--space-2); border-radius: var(--radius-sm); background: var(--color-neutral-bg); color: var(--color-foreground); font-size: var(--font-size-xs); font-weight: var(--font-weight-semibold);">
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
                <div
                    style="display: flex; align-items: center; justify-content: space-between; gap: var(--space-3); padding: var(--space-3) var(--space-4); background: var(--color-surface-soft); border: 1px dashed var(--color-border-input); border-radius: var(--radius-lg); flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: var(--space-3); min-width: 0; flex: 1;">
                        <div
                            style="width: 36px; height: 36px; border-radius: var(--radius-circle); background: var(--color-border-input); color: var(--color-foreground); display: flex; align-items: center; justify-content: center; font-weight: var(--font-weight-bold); font-size: var(--font-size-md); flex-shrink: 0;">
                            {{ $inicial }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div
                                style="font-weight: var(--font-weight-semibold); color: var(--color-foreground); font-size: var(--font-size-md); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $nombreAMostrar }}
                            </div>
                            @if ($usuarioInvitado && $usuarioInvitado->name !== $invitacion->email)
                                <div
                                    style="font-size: var(--font-size-xs); color: var(--color-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $invitacion->email }}
                                </div>
                            @endif
                            <span
                                style="display: inline-block; padding: var(--space-1) var(--space-2); border-radius: var(--radius-sm); background: var(--color-warning-bg); color: var(--color-warning); font-size: var(--font-size-xs); font-weight: var(--font-weight-semibold); margin-top: var(--space-1);">
                                {{ $esInvitadoActual ? 'Tu invitación pendiente' : 'Invitación pendiente' }}
                            </span>
                        </div>
                    </div>

                    @if ($esInvitadoActual)
                        <div style="display: flex; align-items: center; gap: var(--space-2); flex-wrap: wrap;">
                            <form method="POST" action="{{ route('planes.aceptar', $plan) }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="boton"
                                    style="min-height: var(--touch-min); min-width: var(--touch-min); display: inline-flex; align-items: center; justify-content: center; padding: var(--space-2) var(--space-4); font-size: var(--font-size-sm); font-weight: var(--font-weight-semibold); border-radius: var(--radius-sm);"
                                    aria-label="Aceptar invitación">
                                    Aceptar invitación
                                </button>
                            </form>
                            <form method="POST" action="{{ route('planes.rechazar-invitacion', $plan) }}"
                                style="margin: 0;">
                                @csrf
                                <button type="submit" class="boton boton-claro"
                                    style="min-height: var(--touch-min); min-width: var(--touch-min); display: inline-flex; align-items: center; justify-content: center; padding: var(--space-2) var(--space-4); font-size: var(--font-size-sm); font-weight: var(--font-weight-semibold); border-radius: var(--radius-sm);"
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
    <dialog id="modal-invitar"
        style="border: none; border-radius: var(--radius-xl); padding: var(--space-6); width: 100%; max-width: 480px; box-shadow: var(--shadow-md);">
        <form method="POST" action="{{ route('planes.invitar', $plan) }}">
            @csrf
            <h2
                style="margin: 0 0 var(--space-2) 0; font-size: var(--font-size-xl); font-weight: var(--font-weight-bold); color: var(--color-foreground);">
                Invitar colaborador</h2>
            <p style="margin: 0 0 var(--space-5) 0; font-size: var(--font-size-md); color: var(--color-muted);">Envía una
                invitación por correo electrónico para que se una a este plan y participe en las votaciones.</p>

            <div style="margin-bottom: var(--space-5);">
                <label for="input-email-invitar"
                    style="display: block; font-size: var(--font-size-md); font-weight: var(--font-weight-semibold); color: var(--color-foreground); margin-bottom: var(--space-1);">
                    Correo electrónico del invitado
                </label>
                <input type="email" id="input-email-invitar" name="email" required placeholder="ejemplo@correo.com"
                    style="width: 100%; padding: var(--space-3) var(--space-3); border: 1px solid var(--color-border-input); border-radius: var(--radius-md); font-size: var(--font-size-md); box-sizing: border-box; min-height: var(--touch-min);">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: var(--space-3); margin-top: var(--space-6);">
                <button type="button" class="boton boton-claro"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-4); border-radius: var(--radius-pill);"
                    onclick="document.getElementById('modal-invitar').close()">
                    Cancelar
                </button>
                <button type="submit" class="boton"
                    style="min-height: var(--touch-min); min-width: var(--touch-min); padding: var(--space-2) var(--space-5); border-radius: var(--radius-pill);">
                    Enviar invitación
                </button>
            </div>
        </form>
    </dialog>
@endcan

<div class="seccion-participantes" style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 16px; padding: 24px; margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="margin: 0 0 4px 0; font-size: 20px; font-weight: 700; color: #1c1c1a;">Participantes y Colaboradores</h2>
            <p style="margin: 0; color: #6f6f6a; font-size: 14px;">Miembros convocados para coordinar y votar en este plan.</p>
        </div>

        @can('invitar', $plan)
            <button type="button"
                    class="boton"
                    style="min-height: 44px; min-width: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 10px 18px; font-weight: 600;"
                    onclick="document.getElementById('modal-invitar').showModal()"
                    aria-label="Invitar a un nuevo colaborador">
                + Invitar colaborador
            </button>
        @endcan
    </div>

    {{-- Lista de colaboradores con roles --}}
    <div class="grid-participantes" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px;">
        {{-- Creador / Organizador --}}
        <div style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: #fbfbf9; border: 1px solid #e3e3df; border-radius: 12px;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: #1c1c1a; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px;">
                {{ strtoupper(substr($plan->organizador, 0, 1)) }}
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 600; color: #1c1c1a; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $plan->organizador }}
                </div>
                <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; background: #e8f5e9; color: #2e7d32; font-size: 11px; font-weight: 600;">
                    Organizador
                </span>
            </div>
        </div>

        {{-- Participantes con invitación aceptada --}}
        @foreach ($plan->participantes as $participante)
            <div style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; background: #fbfbf9; border: 1px solid #e3e3df; border-radius: 12px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #4a5568; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px;">
                    {{ strtoupper(substr($participante->name ?? $participante->email, 0, 1)) }}
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 600; color: #1c1c1a; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $participante->name ?? $participante->email }}
                    </div>
                    <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; background: #e0f2fe; color: #0369a1; font-size: 11px; font-weight: 600;">
                        Participante
                    </span>
                </div>
            </div>
        @endforeach

        {{-- Invitaciones pendientes (visibles para el creador y para el usuario invitado) --}}
        @foreach ($plan->invitaciones->where('estado', 'pendiente') as $invitacion)
            @php
                $esInvitadoActual = auth()->check() && (
                    (int) auth()->id() === (int) $invitacion->user_id ||
                    strtolower(auth()->user()->email) === strtolower($invitacion->email)
                );
                $esCreador = auth()->check() && (int) auth()->id() === (int) $plan->user_id;
                $usuarioInvitado = $invitacion->user ?? \App\Models\User::where('email', $invitacion->email)->first();
                $nombreAMostrar = $usuarioInvitado?->name ?? $invitacion->email;
                $inicial = strtoupper(substr($nombreAMostrar, 0, 1));
            @endphp

            @if ($esCreador || $esInvitadoActual)
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 16px; background: #fafaf9; border: 1px dashed #d1d1cd; border-radius: 12px; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1;">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: #d1d1cd; color: #1c1c1a; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; flex-shrink: 0;">
                            {{ $inicial }}
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; color: #1c1c1a; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $nombreAMostrar }}
                            </div>
                            @if ($usuarioInvitado && $usuarioInvitado->name !== $invitacion->email)
                                <div style="font-size: 12px; color: #6f6f6a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $invitacion->email }}
                                </div>
                            @endif
                            <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; background: #fef3c7; color: #b45309; font-size: 11px; font-weight: 600; margin-top: 2px;">
                                {{ $esInvitadoActual ? 'Tu invitación pendiente' : 'Invitación pendiente' }}
                            </span>
                        </div>
                    </div>

                    @if ($esInvitadoActual)
                        <form method="POST" action="{{ route('planes.aceptar', $plan) }}" style="margin: 0;">
                            @csrf
                            <button type="submit"
                                    class="boton"
                                    style="min-height: 44px; min-width: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: 8px;"
                                    aria-label="Aceptar invitación">
                                Aceptar invitación
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        @endforeach
    </div>
</div>

{{-- Modal accesible para invitar colaborador --}}
@can('invitar', $plan)
<dialog id="modal-invitar" style="border: none; border-radius: 16px; padding: 28px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
    <form method="POST" action="{{ route('planes.invitar', $plan) }}">
        @csrf
        <h2 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #1c1c1a;">Invitar colaborador</h2>
        <p style="margin: 0 0 20px 0; font-size: 14px; color: #6f6f6a;">Envía una invitación por correo electrónico para que se una a este plan y participe en las votaciones.</p>

        <div style="margin-bottom: 20px;">
            <label for="input-email-invitar" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Correo electrónico del invitado
            </label>
            <input type="email"
                   id="input-email-invitar"
                   name="email"
                   required
                   placeholder="ejemplo@correo.com"
                   style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; box-sizing: border-box; min-height: 44px;">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
            <button type="button"
                    class="boton boton-claro"
                    style="min-height: 44px; min-width: 44px; padding: 10px 18px; border-radius: 50px;"
                    onclick="document.getElementById('modal-invitar').close()">
                Cancelar
            </button>
            <button type="submit"
                    class="boton"
                    style="min-height: 44px; min-width: 44px; padding: 10px 22px; border-radius: 50px;">
                Enviar invitación
            </button>
        </div>
    </form>
</dialog>
@endcan

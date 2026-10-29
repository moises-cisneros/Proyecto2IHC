<div class="seccion-votacion" style="background: #ffffff; border: 1px solid #e3e3df; border-radius: 16px; padding: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h2 style="margin: 0 0 4px 0; font-size: 20px; font-weight: 700; color: #1c1c1a;">Sala de Votación y Propuestas</h2>
            <p style="margin: 0; color: #6f6f6a; font-size: 14px;">Elige entre fechas y lugares sugeridos o propón una nueva alternativa.</p>
        </div>

        @can('proponer', $plan)
            <button type="button"
                    class="boton"
                    style="min-height: 44px; min-width: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 10px 18px; font-weight: 600;"
                    onclick="document.getElementById('modal-proponer-opcion').showModal()"
                    aria-label="Proponer nueva fecha o lugar">
                + Proponer opción
            </button>
        @endcan
    </div>

    {{-- Pestañas semánticas de Hick: n=2 alternativas equiprobables (Fechas vs Lugares) --}}
    <div class="pestanas-hick"
         role="tablist"
         aria-label="Categorías de decisión colaborativa"
         style="display: flex; gap: 12px; border-bottom: 1px solid #e3e3df; padding-bottom: 12px; margin-bottom: 24px;">
        
        <a href="{{ route('planes.detalle', ['plan' => $plan, 'tab' => 'fechas']) }}"
           role="tab"
           aria-selected="{{ $pestanaActiva === 'fechas' ? 'true' : 'false' }}"
           class="boton {{ $pestanaActiva === 'fechas' ? '' : 'boton-claro' }}"
           style="min-height: 44px; min-width: 44px; padding: 10px 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 10px;">
            📅 Fechas propuestas ({{ $fechas->count() }})
        </a>

        <a href="{{ route('planes.detalle', ['plan' => $plan, 'tab' => 'lugares']) }}"
           role="tab"
           aria-selected="{{ $pestanaActiva === 'lugares' ? 'true' : 'false' }}"
           class="boton {{ $pestanaActiva === 'lugares' ? '' : 'boton-claro' }}"
           style="min-height: 44px; min-width: 44px; padding: 10px 20px; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; text-decoration: none; border-radius: 10px;">
            📍 Lugares propuestos ({{ $lugares->count() }})
        </a>
    </div>

    {{-- Contenido de la pestaña activa --}}
    @php
        $opcionesMostradas = ($pestanaActiva === 'fechas') ? $fechas : $lugares;
    @endphp

    @if ($opcionesMostradas->isEmpty())
        <div style="background: #fbfbf9; border: 1px dashed #d1d1cd; border-radius: 12px; padding: 36px 20px; text-align: center;">
            <p style="margin: 0 0 12px 0; color: #6f6f6a; font-size: 15px;">
                No hay {{ $pestanaActiva === 'fechas' ? 'fechas propuestas' : 'lugares propuestos' }} todavía.
            </p>
            @can('proponer', $plan)
                <button type="button"
                        class="boton"
                        style="min-height: 44px; min-width: 44px; padding: 10px 18px;"
                        onclick="abrirModalConTipo('{{ $pestanaActiva === 'fechas' ? 'fecha' : 'lugar' }}')">
                    Sé el primero en proponer una {{ $pestanaActiva === 'fechas' ? 'fecha' : 'un lugar' }}
                </button>
            @endcan
        </div>
    @else
        <div class="grid-opciones" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
            @foreach ($opcionesMostradas as $opcion)
                @php
                    $haVotado = $opcion->votos->contains('user_id', auth()->id());
                    $totalVotos = $opcion->votos->count();
                @endphp
                <div class="tarjeta-opcion" style="background: #ffffff; border: 1px solid {{ $haVotado ? '#1c1c1a' : '#e3e3df' }}; border-radius: 12px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; gap: 14px; box-shadow: {{ $haVotado ? '0 4px 6px -1px rgba(0, 0, 0, 0.05)' : 'none' }};">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 6px;">
                            <span style="font-size: 17px; font-weight: 700; color: #1c1c1a; word-break: break-word;">
                                {{ $opcion->valor }}
                            </span>
                            <span class="badge-votos" style="background: {{ $haVotado ? '#e8f5e9' : '#f3f4f6' }}; color: {{ $haVotado ? '#2e7d32' : '#374151' }}; font-weight: 700; font-size: 13px; padding: 4px 10px; border-radius: 9999px; white-space: nowrap;">
                                {{ $totalVotos }} {{ $totalVotos === 1 ? 'voto' : 'votos' }}
                            </span>
                        </div>

                        @if ($opcion->descripcion)
                            <p style="margin: 0 0 10px 0; font-size: 13px; color: #6f6f6a; line-height: 1.4;">
                                {{ $opcion->descripcion }}
                            </p>
                        @endif

                        <div style="font-size: 12px; color: #8e8e88;">
                            Propuesto por: <span style="font-weight: 600; color: #575752;">{{ $opcion->user->name ?? $opcion->user->email ?? 'Colaborador' }}</span>
                        </div>
                    </div>

                    {{-- Botón de votación ergonómico (Fitts min-h-[44px]) --}}
                    @can('votar', $plan)
                        <form method="POST" action="{{ route('opciones.votar', $opcion) }}" style="margin: 0;">
                            @csrf
                            <button type="submit"
                                    class="boton {{ $haVotado ? 'boton-claro' : '' }}"
                                    style="width: 100%; min-height: 44px; display: inline-flex; align-items: center; justify-content: center; font-weight: 600; gap: 8px;"
                                    aria-label="{{ $haVotado ? 'Retirar voto de ' . $opcion->valor : 'Votar por ' . $opcion->valor }}">
                                @if ($haVotado)
                                    ✓ Votado (clic para retirar)
                                @else
                                    Votar por esta opción
                                @endif
                            </button>
                        </form>
                    @endcan
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Modal accesible para proponer opción --}}
@can('proponer', $plan)
<dialog id="modal-proponer-opcion" style="border: none; border-radius: 16px; padding: 28px; width: 100%; max-width: 480px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
    <form method="POST" action="{{ route('planes.opciones.guardar', $plan) }}">
        @csrf
        <h2 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #1c1c1a;">Proponer nueva opción</h2>
        <p style="margin: 0 0 20px 0; font-size: 14px; color: #6f6f6a;">Sugiere una fecha o un lugar para que el grupo lo vote.</p>

        <div style="margin-bottom: 16px;">
            <label for="select-tipo-opcion" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Tipo de sugerencia
            </label>
            <select id="select-tipo-opcion"
                    name="tipo"
                    required
                    style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; box-sizing: border-box; min-height: 44px;">
                <option value="fecha" {{ $pestanaActiva === 'fechas' ? 'selected' : '' }}>Fecha y hora</option>
                <option value="lugar" {{ $pestanaActiva === 'lugares' ? 'selected' : '' }}>Lugar o destino</option>
            </select>
        </div>

        <div style="margin-bottom: 16px;">
            <label for="input-valor-opcion" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Propuesta (ej. Sábado 20:00 o Restaurante Central)
            </label>
            <input type="text"
                   id="input-valor-opcion"
                   name="valor"
                   required
                   placeholder="Escribe tu propuesta"
                   style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; box-sizing: border-box; min-height: 44px;">
        </div>

        <div style="margin-bottom: 20px;">
            <label for="input-descripcion-opcion" style="display: block; font-size: 14px; font-weight: 600; color: #1c1c1a; margin-bottom: 6px;">
                Detalles adicionales (opcional)
            </label>
            <textarea id="input-descripcion-opcion"
                      name="descripcion"
                      rows="3"
                      placeholder="Información extra, enlaces o notas"
                      style="width: 100%; padding: 12px 14px; border: 1px solid #d1d1cd; border-radius: 10px; font-size: 14px; box-sizing: border-box;"></textarea>
        </div>

        {{-- Separación antagónica de al menos 12px (gap-3) según Ley de Fitts --}}
        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
            <button type="button"
                    class="boton boton-claro"
                    style="min-height: 44px; min-width: 44px; padding: 10px 18px; border-radius: 50px;"
                    onclick="document.getElementById('modal-proponer-opcion').close()">
                Cancelar
            </button>
            <button type="submit"
                    class="boton"
                    style="min-height: 44px; min-width: 44px; padding: 10px 22px; border-radius: 50px;">
                Guardar propuesta
            </button>
        </div>
    </form>
</dialog>

<script>
    function abrirModalConTipo(tipo) {
        var select = document.getElementById('select-tipo-opcion');
        if (select) {
            select.value = tipo;
        }
        var modal = document.getElementById('modal-proponer-opcion');
        if (modal) {
            modal.showModal();
        }
    }
</script>
@endcan

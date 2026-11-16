@if (app()->environment('local', 'testing'))
    <aside class="dev-toolbar-flotante" aria-label="Herramientas de desarrollo">
        <a href="{{ route('analitica.index') }}" class="dev-toolbar-pildora"
            title="Panel interno de analítica de interacción" aria-label="Abrir panel de telemetría y analítica">
            <x-icono nombre="analitica" class="dev-toolbar-icono" />
            <span>Telemetría Dev · /analitica</span>
        </a>
    </aside>
@endif

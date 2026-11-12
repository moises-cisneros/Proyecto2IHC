@if (app()->environment('local', 'testing'))
    <aside class="dev-toolbar-flotante" aria-label="Herramientas de desarrollo">
        <a href="{{ route('analitica.index') }}"
           class="dev-toolbar-pildora"
           title="Panel interno de analítica de interacción"
           aria-label="Abrir panel de telemetría y analítica">
            <x-icono nombre="analitica" class="dev-toolbar-icono" />
            <span class="dev-toolbar-texto">Telemetría Dev · /analitica</span>
        </a>
    </aside>

    <style>
        .dev-toolbar-flotante {
            position: fixed;
            bottom: 16px;
            right: 16px;
            z-index: 50;
        }
        .dev-toolbar-pildora {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 44px;
            min-width: 44px;
            padding: 8px 16px;
            background: #1c1c1a;
            color: #ffe683;
            border: 1px solid #4a4a49;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: background 0.2s ease, transform 0.1s ease;
        }
        .dev-toolbar-pildora:hover {
            background: #2a2a28;
            transform: translateY(-1px);
            color: #ffd93d;
        }
        .dev-toolbar-pildora:focus-visible {
            outline: 2px solid #ffe683;
            outline-offset: 2px;
        }
        .dev-toolbar-icono {
            width: 16px;
            height: 16px;
        }
    </style>
@endif

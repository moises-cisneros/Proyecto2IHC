<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planazo · Coordinación Grupal y Gastos Compartidos</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2325663a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect width='18' height='18' x='3' y='4' rx='2'/%3E%3Cpath d='M8 2v4M16 2v4M3 10h18'/%3E%3Cpath d='m9 16 2 2 4-4'/%3E%3C/svg%3E">
    @vite('resources/css/app.css')

    {{-- Script anti-flicker de tema --}}
    <script>
        (function() {
            var tema = localStorage.getItem('planazo_tema') ||
                (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'oscuro' : 'claro');
            if (tema === 'oscuro') {
                document.documentElement.setAttribute('data-tema', 'oscuro');
            }
        })();
    </script>
</head>

<body class="landing-body">

    {{-- Barra superior pública --}}
    <header class="barra landing-barra" role="banner">
        <div class="barra-izquierda">
            <a href="{{ route('landing') }}" class="marca" aria-label="Planazo - Inicio">
                <x-icono nombre="planazo" tamano="md" class="marca-icono" />
                <span>Planazo</span>
            </a>
        </div>

        <div class="barra-derecha">
            <button type="button" id="boton-tema" class="boton-tema" aria-label="Cambiar tema de color"
                title="Alternar modo claro / oscuro">
                <svg class="icono-svg icono-tema-luna" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                </svg>
                <svg class="icono-svg icono-tema-sol" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2"></path>
                    <path d="M12 20v2"></path>
                    <path d="m4.93 4.93 1.41 1.41"></path>
                    <path d="m17.66 17.66 1.41 1.41"></path>
                    <path d="M2 12h2"></path>
                    <path d="M20 12h2"></path>
                    <path d="m6.34 17.66-1.41 1.41"></path>
                    <path d="m19.07 4.93-1.41 1.41"></path>
                </svg>
            </button>

            <a href="{{ route('login') }}" class="boton boton-claro" aria-label="Iniciar sesión">
                Iniciar Sesión
            </a>
            <a href="{{ route('registro') }}" class="boton" aria-label="Crear una cuenta nueva">
                Registrarme
            </a>
        </div>
    </header>

    <main class="landing-contenedor">

        {{-- Hero Section limpio y enfocado --}}
        <section class="landing-hero" aria-labelledby="hero-titulo">
            <span class="chip landing-badge-hero">Coordinación de Planes y Gastos</span>
            <h1 id="hero-titulo" class="landing-hero-titulo">
                Planes con amigos, sin vueltas ni cuentas confusas.
            </h1>
            <p class="landing-hero-subtitulo">
                Voten fechas y lugares por consenso, dividan gastos con comprobantes y disfruten de la salida sin
                discusiones.
            </p>

            <div class="landing-hero-acciones">
                <a href="{{ route('registro') }}" class="boton landing-btn-cta" aria-label="Crear cuenta gratuita">
                    Empezar Gratis
                </a>
                <a href="{{ route('login') }}" class="boton boton-claro landing-btn-cta" aria-label="Iniciar sesión">
                    Iniciar Sesión
                </a>
            </div>
        </section>

        {{-- 3 Características Clave --}}
        <section class="landing-pilares" aria-labelledby="pilares-titulo">
            <h2 id="pilares-titulo" class="landing-seccion-titulo">Todo resuelto en un solo lugar</h2>

            <div class="landing-pilares-grid">
                <article class="tarjeta landing-pilar-card">
                    <div class="landing-pilar-icono" aria-hidden="true">
                        <x-icono nombre="calendario" tamano="lg" />
                    </div>
                    <h3 class="landing-pilar-titulo">Votación por Aprobación</h3>
                    <p class="landing-pilar-texto">
                        Proponé fechas y lugares. Cada uno vota sus opciones viables y el consenso grupal se define
                        automáticamente.
                    </p>
                </article>

                <article class="tarjeta landing-pilar-card">
                    <div class="landing-pilar-icono" aria-hidden="true">
                        <x-icono nombre="pago" tamano="lg" />
                    </div>
                    <h3 class="landing-pilar-titulo">Gastos Claros</h3>
                    <p class="landing-pilar-texto">
                        Dividí cuentas equitativas o montos específicos. Subí comprobantes y confirmá pagos entre amigos
                        con total tranquilidad.
                    </p>
                </article>

                <article class="tarjeta landing-pilar-card">
                    <div class="landing-pilar-icono" aria-hidden="true">
                        <x-icono nombre="escudo" tamano="lg" />
                    </div>
                    <h3 class="landing-pilar-titulo">Transparencia y Respeto</h3>
                    <p class="landing-pilar-texto">
                        Trazabilidad total de cada acción, libertad para retirarte de planes si estás al día y control
                        absoluto de tu cuenta.
                    </p>
                </article>
            </div>
        </section>

        {{-- Llamado a la acción final --}}
        <section class="landing-cta-banner" aria-labelledby="cta-titulo">
            <h2 id="cta-titulo" class="landing-cta-titulo">¿Listo para armar tu próxima salida?</h2>
            <p class="landing-cta-subtitulo">Creá tu cuenta en segundos y empezá a organizar tus encuentros fácilmente.
            </p>
            <a href="{{ route('registro') }}" class="boton landing-btn-cta" aria-label="Crear cuenta en Planazo">
                Crear Mi Cuenta Gratis
            </a>
        </section>

    </main>

    {{-- Pie de página --}}
    <footer class="landing-pie" role="contentinfo">
        <div class="landing-pie-contenedor">
            <p><strong>Planazo</strong> · Coordinación grupal y gastos compartidos.</p>
            <p class="landing-pie-stack">&copy; {{ date('Y') }} Planazo. Todos los derechos reservados.</p>
        </div>
    </footer>

    {{-- Script global: ui.js para alternar tema --}}
    <script src="{{ asset('js/ui.js') }}"></script>
</body>

</html>

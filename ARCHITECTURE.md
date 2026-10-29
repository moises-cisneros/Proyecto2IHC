# Arquitectura del Sistema - Planazo (Laravel)

Este documento describe la arquitectura técnica, la organización de capas y las responsabilidades en el proyecto **Planazo**. Toda refactorización o nueva característica debe respetar estas directrices.

---

## 1. Visión General y Stack Tecnológico

Planazo es una aplicación web monolítica basada en el framework **Laravel 12** con persistencia en **MySQL 8** y una interfaz de usuario renderizada mediante plantillas **Blade** estilizadas con **Tailwind CSS v4** y empaquetadas con **Vite**.

```
[ Cliente Web / Navegador ]
            │ HTTP (Blade HTML + Vite CSS/JS)
            ▼
┌─────────────────────────────────────────────────────────────┐
│                   Laravel 12 Application                    │
│                                                             │
│  [ Rutas: routes/web.php ] ──▶ [ Middleware: auth ]         │
│            │                                                │
│  [ Controladores: app/Http/Controllers/ ]                   │
│       │                      │                              │
│       ▼                      ▼                              │
│  [ Políticas: Policies/ ] [ Vistas Modulares: Blade ]       │
│  (PlanPolicy)             resources/views/                  │
│       │                   (layouts, components, planes)     │
│       ▼                      │                              │
│  [ Modelos Eloquent ] ◀──────┘                              │
│  app/Models/ (Plan, User, Invitacion, Opcion, Voto)         │
└───────┬─────────────────────────────────────────────────────┘
        │ SQL (PDO MySQL)
        ▼
[ Base de Datos MySQL 8.0 ]
```

---

## 2. Organización del Repositorio

```text
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AutentificacionController.php   # Registro, login, logout, recuperación
│   │   │   ├── PlanesController.php           # CRUD de planes y transiciones de estado
│   │   │   ├── HistorialController.php        # Línea de tiempo y registro de eventos
│   │   │   ├── InvitacionesController.php     # Gestión de invitaciones y tokens
│   │   │   ├── OpcionesController.php         # Propuestas de fechas y lugares
│   │   │   ├── VotosController.php            # Emisión y retiro de votos
│   │   │   └── Controller.php                 # Base controller
│   │   └── Requests/                          # Form Requests para validación desacoplada
│   │       ├── GuardarPlanRequest.php         # Validación de creación/edición de plan
│   │       └── ProponerOpcionRequest.php      # Validación de opciones propuestas
│   ├── Models/
│   │   ├── User.php                          # Modelo de usuario autenticable
│   │   ├── Plan.php                          # Modelo central de plan y relaciones
│   │   ├── PlanActivity.php                  # Modelo de eventos y auditoría de cambios
│   │   ├── Invitacion.php                    # Membresías y estado de invitados
│   │   ├── Opcion.php                        # Fechas o lugares propuestos
│   │   └── Voto.php                          # Votos relacionales únicos
│   ├── Policies/
│   │   └── PlanPolicy.php                    # Autorización: Creador vs Participante vs Invitado
│   └── Providers/                            # Service Providers (AppServiceProvider)
├── bootstrap/                                # Inicialización del framework (app.php)
├── config/                                   # Configuraciones de Laravel (database, auth, etc.)
├── database/
│   ├── factories/                            # Factories para testing y seeders
│   ├── migrations/                           # Esquema de base de datos relacional
│   └── seeders/                              # Población de datos iniciales
├── docker/                                   # Dockerfiles y scripts de arranque
├── docs/                                     # Documentación técnica, evidencias e informes
├── openspec/                                 # Especificaciones SDD (propuestas, cambios, tareas)
├── public/                                   # Raíz pública web (index.php, css/app.css, js/ui.js)
├── resources/
│   ├── css/                                  # Estilos base Tailwind v4
│   ├── js/                                   # Entry points de JavaScript
│   └── views/                                # Plantillas Blade modulares
│       ├── acceso/                           # Vistas de autenticación (ingresar, registro, etc.)
│       ├── components/                       # Componentes reutilizables (<dialog> modal nativo, toast-alerta flotante)
│       ├── layouts/                          # Layouts base (app, acceso, parciales de nav/mensajes)
│       └── planes/                           # Vistas de gestión, auditoría y sala (listar, historial, detalle)
├── routes/
│   ├── web.php                               # Definición de rutas web y middleware auth
│   └── console.php                           # Comandos de consola de Artisan
├── tests/
│   ├── Feature/                              # Pruebas HTTP, endpoints, auth y flujos completos
│   └── Unit/                                 # Pruebas de modelos, scopes y reglas de persistencia
├── docker-compose.yml                        # Orquestación de servicios (app, mysql, vite)
├── AGENTS.md                                 # Directivas operativas para agentes IA
├── CONTRIBUTING.md                           # Guía para desarrolladores
└── DESIGN.md                                 # Guía oficial del sistema de diseño y tokens
```

---

## 3. Capas y Responsabilidades

### A. Capa de Ruteo (`routes/web.php`)

- Mapea URLs a métodos específicos de controladores.
- Aplica middlewares de seguridad (`Route::middleware('auth')` para proteger rutas privadas).
- **Prohibido:** Colocar lógica de negocio o queries complejas directamente en closures de rutas.

### B. Capa de Controladores (`app/Http/Controllers/`)

- Orquesta las peticiones HTTP: recibe parámetros, delega validación en Form Requests, autoriza mediante Policies y coordina con los modelos Eloquent.
- **Controladores delgados (Slim Controllers):** Cada controlador gestiona exclusivamente su ámbito de dominio.

### C. Capa de Autorización (`app/Policies/`)

- Centraliza la seguridad y los permisos de usuario (`PlanPolicy.php`):
  - Solo el **Creador** puede editar, cancelar o eliminar el plan.
  - Los **Participantes aceptados** pueden proponer opciones, votar y abandonar el plan (H3).
  - Los **Invitados pendientes** tienen acceso de solo lectura hasta aceptar o rechazar.
- Invocada mediante `$this->authorize()` en controladores o directivas `@can` en Blade.

### D. Capa de Persistencia y Modelos (`app/Models/`)

- Mapea las tablas relacionales mediante **Eloquent ORM**.
- Define relaciones formales:
  - `Plan`: `organizador()`, `actividades()`, `invitaciones()`, `participantes()`, `opciones()`.
  - `User`: `planes()`, `actividades()`, `votos()`.
  - `PlanActivity`: `plan()`, `usuario()`.
  - `Invitacion`: `plan()`, `usuario()`.
  - `Opcion`: `plan()`, `proponente()`, `votos()`.
  - `Voto`: `opcion()`, `usuario()`.
- Garantiza integridad mediante constraints únicos (`unique(['opcion_id', 'user_id'])`).

### E. Capa de Presentación (`resources/views/`)

- Plantillas Blade modulares desacopladas en componentes y vistas de dominio:
  - **`layouts/app.blade.php`**: Shell maestro libre de CDNs externos.
  - **`components/dialog-confirm.blade.php`**: Diálogo modal nativo de HTML5 (`<dialog>`) para confirmaciones ergonómicas de Fitts y Hick.
  - **`components/toast-alerta.blade.php`**: Notificación flotante nativa (`fixed top-5 right-5 z-50`) con auto-cierre a los 3 segundos para mensajes de sesión sin alterar el layout.
  - **`planes/listar.blade.php`**: Orquestador del listado que incluye parciales (`_filtros.blade.php`, `_tabla.blade.php`, `_modal_crear.blade.php`).
  - **`planes/historial.blade.php`**: Vista de auditoría cronológica con tarjetas autónomas de 4 dimensiones y filtros temáticos de Hick ($n=4$).
  - **`planes/detalle.blade.php`**: Sala colaborativa que incluye parciales (`_cabecera.blade.php`, `_participantes.blade.php`, `_sala_votacion.blade.php`).
- **Prohibido:** Ejecutar consultas directas a la base de datos dentro de los archivos Blade.

---

## 4. Flujo de Datos y Transición de Estados

### Entidad `Plan`

- **Estados posibles:** `pendiente` ➔ `confirmado` ➔ `cancelado`.
- Transición válida: `pendiente` puede pasar a `confirmado` o `cancelado`. Un plan `cancelado` no puede volver a `confirmado`.
- Para eliminar un plan, primero debe cancelarse (no se eliminan planes confirmados activos).

### Flujo Colaborativo

1. El organizador crea el plan (con organizador asignado automáticamente).
2. El organizador invita miembros mediante email o token.
3. Los participantes aceptados proponen opciones de fechas o lugares.
4. Los participantes votan (un voto por opción por usuario garantizado por base de datos).
5. Los participantes cuentan con salida de emergencia (H3) para abandonar el plan sin romper la sala.

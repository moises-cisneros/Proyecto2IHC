# Planazo — Proyecto 2 IHC

> Aplicación web desarrollada con **Laravel 12**, plantillas **Blade**, **Tailwind CSS 4** y **MySQL 8.0**. Diseñada para ejecutarse por defecto mediante **Docker**, con soporte completo para **ejecución manual local**.

---

## 🚀 Requisitos Previos

Elige según tu modalidad preferida:

- **Modo por defecto (Docker):**
    - [Docker Desktop](https://www.docker.com/products/docker-desktop) (con Docker Compose).
    - Git.
- **Modo manual (Host local):**
    - PHP 8.2 o superior (con extensiones `pdo_mysql`, `mbstring`, `openssl`).
    - Composer 2.x.
    - Servidor MySQL 8.0 en ejecución.
    - Node.js 18+ y npm.
    - Git.

---

## ⚡ Guía de Inicio Rápido

### Perfil 1: Docente / Evaluador

Si tu objetivo es probar y calificar la aplicación inmediatamente:

1. Clona el repositorio:

    ```bash
    git clone https://github.com/moises-cisneros/Proyecto2IHC.git
    cd Proyecto2IHC
    ```

2. Inicia el entorno con Docker (comando estándar para cualquier sistema operativo):

    ```bash
    docker compose up --build
    ```

    _(Para comprobar que todas las herramientas base estén instaladas antes de iniciar, podés ejecutar `node scripts/setup.mjs`, `pnpm run setup` o `powershell ./scripts/setup.ps1`)._

3. Abre tu navegador en [http://localhost:9300](http://localhost:9300).
4. Inicia sesión con cualquiera de los usuarios de prueba precargados (por ejemplo: `test@example.com` / `password`).

_(El script crea el `.env`, levanta MySQL, migra la base de datos, siembra los usuarios de prueba y arranca Laravel y Vite automáticamente)._

---

### 💳 Conciliación Inteligente de Comprobantes (Modo Offline vs Modo IA)

El módulo de liquidación de gastos incluye un servicio de escaneo y validación de comprobantes bancarios diseñado mediante el patrón **Driver**:

- **Modo Offline / Evaluación por Defecto (`fake`):**
  No requiere **ninguna API key ni conexión a internet**. Viene configurado por defecto (`COMPROBANTE_SCANNER_DRIVER=fake` en `.env.example`). Interpreta comprobantes sintéticos de manera determinista, permitiendo probar la aplicación y ejecutar los tests E2E y unitarios de forma 100% aislada y sin costo.
- **Modo IA con Visión Multimodal (`gemini` - Opcional):**
  Si deseas habilitar la extracción real en vivo con IA para cualquier foto o comprobante bancario, basta con configurar tu clave en el archivo `.env`:

    ```env
    COMPROBANTE_SCANNER_DRIVER=gemini
    GEMINI_API_KEY=tu_api_key_de_google_ai_studio
    GEMINI_MODEL=gemini-2.5-flash
    ```

    _(Se puede generar una clave gratuita en [Google AI Studio](https://aistudio.google.com/))._

- **Generador de Comprobantes Sintéticos Limpios:**
  Para pruebas reproducibles sin exponer datos sensibles ni cuentas bancarias reales, el repositorio incluye un generador que sintetiza comprobantes móviles con estilo bancario boliviano (BNB, Banco Unión, Yape, Yastá):

    ```bash
    pnpm run fixtures:comprobantes
    ```

---

### Perfil 2: Desarrollador / Colaborador

Si vas a programar, refactorizar o añadir funcionalidades:

#### Opción A: Desarrollo con Docker (Por defecto)

- Iniciar entorno:

    ```bash
    docker compose up --build
    ```

- Correr migraciones adicionales:

    ```bash
    docker compose exec planazo_app php artisan migrate
    ```

- Ejecutar pruebas:

    ```bash
    docker compose exec planazo_app php artisan test
    ```

- Detener entorno:

    ```bash
    docker compose down
    ```

#### Opción B: Desarrollo Manual en Host Local

```bash
# 1. Instalar dependencias
composer install
pnpm install

# 2. Configurar variables de entorno y clave de cifrado
cp .env.example .env
php artisan key:generate

# 3. Configurar tus credenciales de base de datos en .env y migrar
php artisan migrate

# 4. Iniciar servidores (en terminales separadas)
php artisan serve    # Servidor HTTP en http://localhost:8000
pnpm dev             # Servidor Vite HMR en http://localhost:9373
```

#### Sincronización de Habilidades de IA (OpenSpec / Asistentes)

El repositorio incluye habilidades versionadas para evaluación heurística y diseño de interacción en `.agents/skills/`. Si utilizas editores con agentes de IA (Cursor, OpenCode, Claude Code, etc.), puedes sincronizarlas sin ensuciar el control de versiones ejecutando:

```bash
npx @fission-ai/openspec init
```

Las configuraciones locales de cada herramienta (`.agent/`, `.claude/`, `.cursor/`, etc.) están convenientemente excluidas en `.gitignore`.

---

## 🌐 Accesos y Servicios

| Servicio                  | URL / Host                                     | Puerto Host | Descripción                                                |
| :------------------------ | :--------------------------------------------- | :---------- | :--------------------------------------------------------- |
| **Aplicación Web**        | [http://localhost:9300](http://localhost:9300) | `9300`      | Laravel 12 (PHP 8.2) + Servidor HTTP                       |
| **Vite Dev Server (HMR)** | [http://localhost:9373](http://localhost:9373) | `9373`      | Compilación de assets y recarga en caliente                |
| **Base de Datos MySQL**   | `127.0.0.1`                                    | `9306`      | MySQL 8.0 (BD: `proyecto2ihc`, user: `root`, pass: `root`) |

---

## 👤 Usuarios de Prueba Precargados (Seeders)

La base de datos se siembra automáticamente al iniciar Docker con tres cuentas listas para usar sin necesidad de registrarse:

| Usuario         | Correo Electrónico     | Contraseña    | Datos de inicio    |
| :-------------- | :--------------------- | :------------ | :----------------- |
| **Test User**   | `test@example.com`     | `password`    | Cuenta 1 de prueba |
| **Test User 2** | `test2@example.com`    | `password`    | Cuenta 2 de prueba |
| **Usuario 1**   | `usuario1@example.com` | `password123` | Cuenta 3 de prueba |

Para volver a sembrar los datos manualmente:

```bash
docker compose exec planazo_app php artisan db:seed
```

---

## 🗺️ App Map — Mapa de Flujos y Arquitectura de la Aplicación

```mermaid
flowchart TD
    subgraph G_Acceso["1. Acceso y Control de Sesión"]
        Login["/ingresar | /registro | /recuperar<br/>Rate-limiting y cuentas de prueba"]
    end

    subgraph G_Dashboard["2. Dashboard de Gestión (/mis-planes)"]
        Dash["Pestañas por Rol (Creados vs Participo)<br/>Bloques LATCH + Filtros Hick"]
        ModalCrear["Modal Crear Plan<br/>(Organizador automático de sesión)"]
        Dash -->|"Organizador crea plan"| ModalCrear
    end

    subgraph G_Sala["3. Sala Colaborativa (/planes/{id}) — Máquina de Estados"]
        direction TB
        EstadoPendiente{"Estado: PENDIENTE"}

        subgraph S_Participante["Perspectiva: Invitado / Participante"]
            AceptarInv["Aceptar Invitación"] --> ProponerVotar["Proponer Fechas/Lugares<br/>Votación Aprobación (Toggle Reactivo)"]
            ProponerVotar --> Abandono["Salida H3: Abandonar Plan"]
        end

        subgraph S_Organizador["Perspectiva: Organizador"]
            Invitar["Invitar Miembros por Correo"]
            Cierre{"¿Cierre de Votación / Fecha Límite?"}
            Empate{"¿Existe Empate en 1er Lugar?"}
            DesempateModal["Modal Desempate Obligatorio<br/>(Motivo justificado >= 5 caracteres)"]
            ConfirmarAccion["Confirmar Plan Oficial<br/>(Congela sala y bloquea votos)"]

            Invitar --> Cierre
            Cierre --> Empate
            Empate -->|"Sí"| DesempateModal
            DesempateModal --> ConfirmarAccion
            Empate -->|"No (Ganador directo)"| ConfirmarAccion
        end

        EstadoPendiente --> S_Participante
        EstadoPendiente --> S_Organizador
    end

    subgraph G_Post["4. Post-Evento & Liquidación (/planes/{id})"]
        EstadoConfirmado["Estado: CONFIRMADO (Sala Congelada)"]
        Gastos["Módulo de Gastos y Saldos Netos<br/>(División 1/N o personalizada)"]
        Pagos["Liquidación de Deudas<br/>(Pendiente -> Confirmado/Rechazado)"]
        EstadoConfirmado --> Gastos --> Pagos
    end

    subgraph G_Auditoria["5. Trazabilidad Transversal (/historial)"]
        Bitacora["Bitácora Inmutable de 4 Dimensiones<br/>(Qué, Quién, Cuándo, Resultado) + Chips"]
    end

    Login --> Dash
    ModalCrear -->|"Redirección tras creación"| EstadoPendiente
    Dash -->|"Acceso a detalle del plan"| EstadoPendiente
    ConfirmarAccion --> EstadoConfirmado

    ModalCrear & ProponerVotar & DesempateModal & ConfirmarAccion & Pagos -.-> Bitacora
```

### Detalle de los Flujos de la Aplicación

1. **Flujo 1 (Acceso y Control de Sesión):** Puerta de entrada (`/ingresar`, `/registro`, `/recuperar`). Rate-limiting (`throttle:5,1`), protección contra enumeración de credenciales, autenticación con cuentas de prueba sembradas y regeneración de token CSRF al cerrar sesión.
2. **Flujo 2 (Dashboard y Organización de Planes - `/mis-planes`):** Centro de navegación posautenticación. Aplica Arquitectura de la Información con dos pestañas semánticas por propósito/rol (_«Mis planes creados»_ vs _«Planes donde participo»_), clasificación cronológica LATCH (_Hoy_, _Próximos_, _Vencidos_), y filtros rápidos acotados ($\le 4$ opciones) conforme a la Ley de Hick.
3. **Flujo 3 (Creación y Ciclo de Vida del Plan):** Diálogo modal nativo `<dialog>` de creación ágil con captura automática del organizador desde la sesión (`auth()->user()->name`), validación preventiva de fechas pasadas (`after_or_equal:today`) y transición segura de la máquina de estados (`pendiente` &rarr; `confirmado` &rarr; `cancelado` inmutable).
4. **Flujo 4 (Sala Colaborativa, Deliberación y Cierre - `/planes/{plan}`):** Espacio interactivo unificado que bifurca la experiencia según el rol gobernado por políticas (`PlanPolicy`):
    - **Carril Participante:** Aceptación de invitación, propuesta de opciones (fechas/lugares), votación reactiva por aprobación (Approval Voting) con alternancia de votos y salida de emergencia voluntaria (Nielsen H3).
    - **Carril Organizador:** Envío de invitaciones por correo, supervisión de quórum, resolución obligatoria de empates con motivo justificado ($\ge 5$ caracteres) y confirmación oficial que congela la sala (`403` a votos y propuestas posteriores).
5. **Flujo 5 (Gastos, Liquidación y Conciliación Inteligente):** Proceso post-evento activado exclusivamente en planes confirmados. Registro de gastos grupales (partes iguales $Monto/N$ o cuotas personalizadas), cálculo automatizado de la matriz de saldos netos cruzados, configuración de datos de cobro por participante (Alias, CBU, QR) y ciclo de liquidación de deudas con comprobante bancario adjunto, validación inteligente (IA Gemini Vision o Fake) y estados controlados (`pendiente` &rarr; `verificado` / `discrepante` &rarr; `confirmado` o `rechazado` con motivo obligatorio).
6. **Servicio Transversal (Auditoría y Trazabilidad - `/historial`):** Bitácora inmutable accesible desde la navegación global. Registra en 4 dimensiones (Qué, Quién, Cuándo, Resultado) todas las transiciones de estado, propuestas, votos, desempates y liquidaciones, con selector temático de 4 chips.

---

## 🧠 Principios y Avances de Interacción Humano-Computador (IHC)

La aplicación sigue una disciplina estricta de usabilidad, ergonomía y accesibilidad:

- **Ley de Fitts:** $T = a + b \log_{2}(2D/W)$ — Todo botón o control interactivo (votar, proponer, invitar, confirmar) garantiza un área táctil mínima de **44×44px** (`min-height: 44px`). Los controles antagónicos mantienen una separación preventiva de **12px (`gap-3`)**.
- **Ley de Hick:** $T = b \log_{2}(n + 1)$ — Clasificación en $n = 2$ pestañas en la sala de votación (_Fechas propuestas_ vs _Lugares propuestos_) y $n = 4$ chips temáticos en el historial (_Todos, Estados, Creaciones, Participación_).
- **Carga Cognitiva (7 ± 2 chunks):** Tarjetas sintéticas con los 4 metadatos esenciales para evitar sobrecarga mental.
- **10 Heurísticas de Nielsen:**
    - _H1 (Visibilidad del estado):_ Contadores de votos en vivo y badges semánticos con texto e ícono.
    - _H3 (Control y libertad):_ Diálogo modal nativo `<dialog>` accesible para abandono de planes o retiro de votos.
    - _H4 (Consistencia y estándares):_ Cero dependencias externas (sin SweetAlert2 ni NProgress), usando `<dialog>` y `<x-toast-alerta />` flotante nativo de 3 segundos.
    - _H5 (Prevención de errores):_ Inhabilitación de controles y mensajes de sala cerrada para planes confirmados o vencidos.
    - _H6 (Reconocimiento antes que recuerdo):_ Organizador cargado automáticamente desde la sesión activa (`auth()->user()->name`).

---

## 🎭 Pruebas E2E con Playwright (Modo Visible en Escritorio)

Siguiendo el estándar pedagógico de que **«una prueba es una tarea humana»**, cada test E2E narra el recorrido real de un usuario («Anita...», «Carlos...») utilizando localizadores semánticos accesibles (`getByRole`, `getByLabel`, `getByText`) sin clases CSS técnicas.

### Instalación y Configuración Inicial de Playwright

Para que Playwright pueda controlar y abrir la ventana del navegador en tu sistema operativo, es necesario descargar los binarios de Chromium (se ejecuta una única vez tras clonar el repositorio):

```bash
pnpm dlx playwright install chromium
```

### Ejecución Rápida

```bash
# Abre la ventana de Chromium en tu pantalla para ver la interacción en vivo:
pnpm run test:e2e:headed

# Abre el UI Runner interactivo paso a paso de Playwright:
pnpm run test:e2e:ui
```

_(Para ejecución de pruebas puntuales, filtrado por nombres y el protocolo detallado para crear nuevas pruebas E2E, consultá [AGENTS.md](AGENTS.md) y [CONTRIBUTING.md](CONTRIBUTING.md))._

---

## 🛑 Detener y Reiniciar el Entorno

- **Detener la aplicación:**

    ```bash
    docker compose down
    ```

- **Reiniciar desde cero (Limpiar contenedores y base de datos):**

    ```bash
    docker compose down -v
    docker compose up --build
    ```

---

## 🧭 Rutas y Funcionalidades de la Aplicación

- **Acceso y Autenticación:**
    - `/` ➔ Redirección automática a `/ingresar`.
    - `/ingresar` ➔ Formulario de inicio de sesión (`login`).
    - `/registro` ➔ Creación de nueva cuenta de usuario.
    - `/recuperar` y `/restablecer/{token}` ➔ Recuperación de contraseña por correo.
- **Gestión de Planes y Colaboración (Zona Autenticada):**
    - `/mis-planes` ➔ Listado interactivo de planes personales con filtros por estado y fecha.
    - `/planes` (POST) ➔ Creación de un nuevo plan (descripción, fecha límite y organizador automático).
    - `/planes/{plan}` ➔ Sala colaborativa del plan: invitación a miembros, propuestas de fechas/lugares y votación grupal.
    - `/planes/{plan}/editar` y `PUT /planes/{plan}` ➔ Edición de plan existente (organizador inmutable).
    - `DELETE /planes/{plan}` ➔ Eliminación de plan con diálogo modal accesible.
    - `PUT /planes/{plan}/estado` ➔ Transición de estados del plan (`pendiente` ➔ `confirmado` ➔ `cancelado`).
    - `/historial` ➔ Bitácora cronológica e inmutable de eventos con selector de 4 categorías (Hick).

---

## 🧪 Pruebas Automatizadas

La suite de pruebas unitarias y de integración de Laravel se ejecuta mediante **PHPUnit**:

- **Dentro del contenedor Docker (Recomendado):**

    ```bash
    docker compose exec planazo_app php artisan test
    ```

- **Desde el host local (si se cuenta con PHP y Composer instalados):**

    ```bash
    php artisan test
    ```

---

## 📚 Documentación Técnica y Académica

- [AGENTS.md](AGENTS.md): Reglas operativas estrictas para agentes de IA, prohibición de atribución de IA, principios IHC (Fitts, Hick, Carga Cognitiva) y flujo de sub-issues.
- [ARCHITECTURE.md](ARCHITECTURE.md): Arquitectura de capas, modelos Eloquent, controladores y vistas Blade.
- [resources/DESIGN.md](resources/DESIGN.md): Guía oficial del sistema de diseño, tokens cromáticos y directrices visuales y ergonómicas (IHC).
- [CONTRIBUTING.md](CONTRIBUTING.md): Guía para colaboradores, ramas y convención de Conventional Commits.
- [docs/](docs/): Evidencias académicas, historial de tareas de clase y capturas del proyecto recibido.

---

## 📄 Licencia

Este proyecto está licenciado bajo la licencia MIT. Consulta el archivo [LICENSE](LICENSE) para más información.

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

   *(En Windows también podés usar el script `dev.bat`).*
3. Abre tu navegador en [http://localhost:9300](http://localhost:9300).
4. Inicia sesión con cualquiera de los usuarios de prueba precargados (por ejemplo: `test@example.com` / `password`).

*(El script crea el `.env`, levanta MySQL, migra la base de datos, siembra los usuarios de prueba y arranca Laravel y Vite automáticamente).*

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
npm install

# 2. Configurar variables de entorno y clave de cifrado
cp .env.example .env
php artisan key:generate

# 3. Configurar tus credenciales de base de datos en .env y migrar
php artisan migrate

# 4. Iniciar servidores (en terminales separadas)
php artisan serve    # Servidor HTTP en http://localhost:8000
npm run dev          # Servidor Vite HMR en http://localhost:5173
```

#### Sincronización de Habilidades de IA (OpenSpec / Asistentes)

El repositorio incluye habilidades versionadas para evaluación heurística y diseño de interacción en `.agents/skills/`. Si utilizas editores con agentes de IA (Cursor, OpenCode, Claude Code, etc.), puedes sincronizarlas sin ensuciar el control de versiones ejecutando:

```bash
npx @fission-ai/openspec init
```

Las configuraciones locales de cada herramienta (`.agent/`, `.claude/`, `.cursor/`, etc.) están convenientemente excluidas en `.gitignore`.

---

## 🌐 Accesos y Servicios

| Servicio | URL / Host | Puerto Host | Descripción |
| :--- | :--- | :--- | :--- |
| **Aplicación Web** | [http://localhost:9300](http://localhost:9300) | `9300` | Laravel 12 (PHP 8.2) + Servidor HTTP |
| **Vite Dev Server (HMR)** | [http://localhost:9373](http://localhost:9373) | `9373` | Compilación de assets y recarga en caliente |
| **Base de Datos MySQL** | `127.0.0.1` | `9306` | MySQL 8.0 (BD: `proyecto2ihc`, user: `root`, pass: `root`) |

---

## 👤 Usuarios de Prueba Precargados (Seeders)

La base de datos se siembra automáticamente al iniciar Docker con dos cuentas listas para usar sin necesidad de registrarse:

| Usuario | Correo Electrónico | Contraseña | Datos de inicio |
| :--- | :--- | :--- | :--- |
| **Usuario 1** | `test@example.com` | `password` | Cuenta 1 de prueba |
| **Usuario 2** | `test2@example.com` | `password` | Cuenta 2 de prueba |

Para volver a sembrar los datos manualmente:

```bash
docker compose exec planazo_app php artisan db:seed
```

---

## 🛑 Detener y Reiniciar el Entorno

- **Detener la aplicación:**

  ```bash
  docker compose down
  ```

  *(En Windows también podés ejecutar `down.bat`).*
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
- **Gestión de Planes (Zona Autenticada):**
  - `/mis-planes` ➔ Listado interactivo de planes personales con filtros por estado y fecha.
  - `/planes` (POST) ➔ Creación de un nuevo plan (descripción, fecha límite).
  - `/planes/{plan}/editar` y `PUT /planes/{plan}` ➔ Edición de plan existente.
  - `DELETE /planes/{plan}` ➔ Eliminación de plan con diálogo de confirmación.
  - `PUT /planes/{plan}/estado` ➔ Transición de estados del plan:
    - `pendiente` ➔ `confirmado` ➔ `cancelado`.

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
- [CONTRIBUTING.md](CONTRIBUTING.md): Guía para colaboradores, ramas y convención de Conventional Commits.
- [docs/](docs/): Evidencias académicas, historial de tareas de clase y capturas del proyecto recibido.

---

## 📄 Licencia

Este proyecto está licenciado bajo la licencia MIT. Consulta el archivo [LICENSE](LICENSE) para más información.

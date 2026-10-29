# automatizacion-organizador-plan Specification

## Purpose
Automatizar la asignación del organizador de planes a partir de la sesión activa de Laravel y eliminar el campo editable en la interfaz mediante un badge informativo de solo lectura.

## Requirements

### Requirement: Asignación automática del organizador en backend
El controlador de planes debe asignar de forma automática el nombre del usuario autenticado al crear un nuevo plan, sin requerir dicho campo en la petición HTTP.

#### Scenario: Creación exitosa de plan sin campo organizador en payload
- **Given** un usuario autenticado en la sesión con nombre "Moisés Cisneros"
- **When** envía una petición `POST /planes` con `nombre` y `fecha_limite` válidos (sin enviar `organizador`)
- **Then** el plan se persiste exitosamente en base de datos
- **And** el registro resultante posee `organizador = "Moisés Cisneros"` y `user_id = auth()->id()`
- **And** el usuario es redirigido a la ruta `planes.listar` con mensaje de éxito

#### Scenario: Fallo de validación por ausencia de campos obligatorios
- **Given** un usuario autenticado
- **When** envía una petición `POST /planes` sin `nombre` o sin `fecha_limite`
- **Then** el sistema responde con errores de validación para los campos faltantes
- **And** no se solicita ni reporta error sobre el campo `organizador`

### Requirement: Badge informativo de solo lectura en la interfaz
La interfaz de creación de planes no debe ofrecer un campo editable para el organizador; debe mostrar un badge de solo lectura con el nombre del usuario logueado.

#### Scenario: Renderizado del modal de creación
- **Given** un usuario autenticado que accede a `GET /mis-planes`
- **When** se inspecciona el contenido del modal de creación (`#modal-crear`)
- **Then** no existe ningún elemento `<input name="organizador">`
- **And** se renderiza un badge informativo visible con el texto `Organizador: {{ auth()->user()->name }}` estilizado con color atenuado `#6f6f6a` e ícono de usuario

### Requirement: Protección de acceso y persistencia
La creación de planes debe exigir autenticación obligatoria y preservar la estructura de datos existente.

#### Scenario: Acceso no autenticado a endpoint de creación
- **Given** un usuario invitado no autenticado
- **When** intenta enviar una petición `POST /planes`
- **Then** la petición es interceptada por el middleware `auth` y redirigida a `/ingresar`


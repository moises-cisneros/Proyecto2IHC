# suite-pruebas-autenticacion Specification

## Purpose
Proporcionar una suite automatizada de pruebas de integración HTTP (Feature) en Laravel PHPUnit para validar el flujo completo de autenticación, control de acceso por middleware y registro de usuarios.

## Requirements

### Requirement: Protección de rutas y redirección de invitados
Las rutas administrativas y de gestión de planes protegidas con el middleware `auth` deben rechazar el acceso a peticiones no autenticadas y redirigir al formulario de ingreso.

#### Scenario: Acceso no autenticado a /mis-planes
- **Given** una petición HTTP entrante sin sesión de usuario activa
- **When** se solicita la ruta `GET /mis-planes`
- **Then** el middleware `auth` intercepta la petición y responde con un código de redirección HTTP 302 hacia `/ingresar`

### Requirement: Autenticación de usuarios mediante credenciales
El endpoint de ingreso debe autenticar usuarios existentes cuando las credenciales coincidan, regenerar la sesión y rechazar intentos inválidos con un mensaje genérico.

#### Scenario: Inicio de sesión exitoso con credenciales correctas
- **Given** un usuario existente en la base de datos con contraseña hasheada
- **When** envía una petición `POST /ingresar` con su correo y contraseña válidos
- **Then** el sistema autentica al usuario en la sesión
- **And** regenera el ID de sesión para prevenir fijación de sesión
- **And** redirige al usuario a la ruta `planes.listar` (`/mis-planes`)

#### Scenario: Intento de inicio de sesión con credenciales erróneas
- **Given** un usuario registrado en el sistema
- **When** envía una petición `POST /ingresar` con una contraseña errónea o correo no coincidente
- **Then** la autenticación es rechazada y el usuario permanece como invitado (`assertGuest`)
- **And** se almacena en la sesión un mensaje flash genérico: "El correo o la contraseña no son correctos."

### Requirement: Registro de usuarios y validaciones de cuenta
El endpoint de registro debe validar la unicidad del correo y la solidez de la contraseña, persistiendo al nuevo usuario y autenticándolo inmediatamente si la entrada es válida.

#### Scenario: Validación de correo duplicado y contraseña corta
- **Given** un correo electrónico previamente registrado en la tabla `users`
- **When** se envía una petición `POST /registro` con dicho correo y una contraseña de longitud menor a 8 caracteres
- **Then** la petición es rechazada con errores de validación para los campos `correo` y `contrasena`
- **And** no se crea ningún registro adicional en base de datos

#### Scenario: Registro exitoso con logueo directo
- **Given** datos válidos de un nuevo usuario (nombre, correo único y contraseña de al menos 8 caracteres confirmada)
- **When** se envía la petición `POST /registro`
- **Then** se crea el nuevo registro en la tabla `users` con la contraseña debidamente hasheada
- **And** el usuario queda autenticado en la sesión inmediatamente
- **And** se redirige a la ruta `planes.listar`

### Requirement: Cierre de sesión de usuarios autenticados
El endpoint de salida debe invalidar la sesión actual del usuario y redirigirlo a la pantalla de ingreso.

#### Scenario: Salida exitosa de usuario logueado
- **Given** un usuario autenticado en el sistema
- **When** envía una petición `POST /salir`
- **Then** se destruye la sesión y el usuario queda desautenticado (`assertGuest`)
- **And** se redirige al usuario a la ruta `login` (`/ingresar`)

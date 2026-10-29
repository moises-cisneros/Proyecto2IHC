# endpoint-consulta-historial-filtros Specification

## Purpose
Exponer y gestionar la consulta cronológica del historial de actividades personales de los planes con aislamiento por usuario autenticado y filtros temáticos basados en la Ley de Hick.

## Requirements

### Requirement: Autenticación obligatoria para acceso al historial
El acceso a la consulta del historial debe exigir sesión activa mediante el middleware `auth`.

#### Scenario: Acceso de usuario no autenticado
- **Given** un visitante sin sesión iniciada
- **When** realiza una petición `GET /historial`
- **Then** el sistema intercepta la petición y redirige a la ruta de inicio de sesión `/ingresar` con código HTTP 302

### Requirement: Aislamiento estricto por usuario autenticado
La consulta de actividades debe filtrar exclusivamente los registros correspondientes al usuario en sesión (`where('user_id', auth()->id())`).

#### Scenario: Visualización exclusiva de registros propios
- **Given** un usuario A autenticado con actividades registradas
- **And** un usuario B con actividades independientes
- **When** el usuario A consulta `GET /historial`
- **Then** la respuesta contiene únicamente las actividades del usuario A
- **And** ninguna actividad del usuario B es visible o accesible

### Requirement: Filtros temáticos por categoría (Ley de Hick: n = 4)
El endpoint debe soportar el filtrado de eventos según 4 categorías principales: `todos`, `estados`, `creaciones` y `participacion`.

#### Scenario: Filtrado por categoría de estados
- **Given** un usuario autenticado con eventos de creación y cambio de estado
- **When** envía una petición `GET /historial?categoria=estados`
- **Then** los resultados devueltos corresponden únicamente a acciones con `accion = 'cambio_estado'`

#### Scenario: Filtrado por categoría de creaciones
- **Given** un usuario autenticado
- **When** envía una petición `GET /historial?categoria=creaciones`
- **Then** los resultados devueltos corresponden únicamente a acciones con `accion = 'creacion'`

### Requirement: Orden cronológico descendente
Los registros de actividad deben devolverse ordenados desde el más reciente al más antiguo.

#### Scenario: Ordenación temporal de eventos
- **Given** múltiples eventos registrados en diferentes instantes de tiempo
- **When** el usuario consulta el historial
- **Then** la lista de actividades se retorna ordenada de forma descendente por marca de tiempo (`latest()`)

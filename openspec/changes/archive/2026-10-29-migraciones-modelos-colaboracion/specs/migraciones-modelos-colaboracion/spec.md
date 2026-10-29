# migraciones-modelos-colaboracion Specification

## Purpose
Proveer el esquema de base de datos relacional y los modelos de dominio Eloquent para soportar la colaboración grupal en planes mediante invitaciones, propuestas de fechas o lugares y votaciones únicas.

## Requirements

### Requirement: Esquema y modelo de invitaciones
El sistema debe persistir invitaciones a planes con un identificador único de token y estados de respuesta.

#### Scenario: Creación de invitación
- **Given** un plan existente y un correo electrónico
- **When** se crea una invitación con un token único y estado `pendiente`
- **Then** el registro se almacena en la tabla `invitaciones` manteniendo integridad referencial con el plan

#### Scenario: Aceptación de invitación y asociación de usuario
- **Given** una invitación existente
- **When** es aceptada por un usuario registrado
- **Then** su estado transiciona a `aceptada`
- **And** el campo `user_id` referencia al usuario aceptante
- **And** el usuario pasa a formar parte de `$plan->participantes`

### Requirement: Esquema y modelo de opciones propuestas
El sistema debe permitir asociar opciones de fecha o lugar a un plan, identificando al usuario autor.

#### Scenario: Propuesta de fecha o lugar
- **Given** un plan y un usuario participante o creador
- **When** se registra una opción con tipo `fecha` o `lugar` y su valor respectivo
- **Then** la opción se almacena en la tabla `opciones`
- **And** es accesible a través de `$plan->opciones`

### Requirement: Esquema y restricción única de votos
El sistema debe persistir los votos emitidos por los usuarios para cada opción, impidiendo a nivel de base de datos votos duplicados.

#### Scenario: Emisión de voto válido
- **Given** una opción registrada y un usuario
- **When** se crea un registro en la tabla `votos` con `opcion_id` y `user_id`
- **Then** el voto queda persistido y asociado tanto a la opción como al usuario

#### Scenario: Prevención de voto duplicado
- **Given** un voto ya registrado para un par (`opcion_id`, `user_id`)
- **When** se intenta insertar un segundo registro con los mismos identificadores
- **Then** la base de datos rechaza la operación disparando una excepción de violación de unicidad (`QueryException`)

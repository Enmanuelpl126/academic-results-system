# Funcionalidades del sistema

Este documento describe qué puede hacer el usuario en el sistema de Resultados Académicos, organizado por módulos. No cubre detalles técnicos ni de arquitectura; para eso consulte `docs/caracteristicas_y_funcionalidades.md`.

## Público objetivo y alcance

- Dirigido a usuarios finales, responsables funcionales y administradores.
- Cubre flujos de uso, acciones disponibles, restricciones por permisos y reglas de negocio visibles para el usuario.

---

## 1) Autenticación y acceso

- Iniciar sesión con correo y contraseña.
- Verificación de correo electrónico previa al uso de módulos protegidos.
- Cierre de sesión desde cualquier pantalla autenticada.
- Seguridad:
  - Si la cuenta está deshabilitada (is_enabled = false), no se permite iniciar sesión.
  - Límite de intentos en el inicio de sesión.

## 2) Inicio y navegación

- Al acceder autenticado, se redirige automáticamente al listado de Publicaciones.
- Barra y layouts consistentes para acceder a módulos: Publicaciones, Premios, Reconocimientos, Eventos, Ajustes y Administración (si tiene permisos).

## 3) Publicaciones

- Listar publicaciones en formato paginado (10 por página).
- Ver detalles principales: nombre, fecha, tipo y autores.
- Crear una publicación con soporte para tres tipos:
  - Revista (número, volumen, DOI).
  - Libro (editorial, ciudad).
  - Capítulo de libro (nombre del libro, autor, editorial, ciudad).
- Editar una publicación existente y sus detalles según el tipo.
- Eliminar una publicación.
- Autores:
  - Asignar múltiples autores mediante autocompletar.
  - Si elimina con permiso de "propios" y hay más autores, se retira su autoría sin eliminar la publicación.

## 4) Premios

- Listar premios paginados, mostrando tipo, fecha y autores.
- Crear, editar y eliminar premios.
- Asignar múltiples autores mediante autocompletar.
- Regla al eliminar con permiso "propios": si existen coautores, sólo se retira su autoría.

## 5) Reconocimientos

- Listar reconocimientos paginados con nombre, tipo, fecha, descripción y autores.
- Crear, editar y eliminar reconocimientos.
- Asignar múltiples autores mediante autocompletar.
- Regla al eliminar con permiso "propios": si existen coautores, sólo se retira su autoría.

## 6) Eventos

- Listar eventos paginados con nombre, categoría, fecha, descripción y autores.
- Crear, editar y eliminar eventos.
- Asignar múltiples autores mediante autocompletar.
- Regla al eliminar con permiso "propios": si existen coautores, sólo se retira su autoría.

## 7) Administración (para usuarios con permiso de acceso al panel)

El panel de administración incluye tres secciones: Usuarios, Departamentos y Roles.

### 7.1 Usuarios

- Buscar y filtrar usuarios por nombre, rol y departamento.
- Crear usuario: nombre, email, CI, departamento, rol, categorías y nivel profesional; contraseña con complejidad.
- Editar usuario: mismos campos; la contraseña es opcional.
- Habilitar/Deshabilitar usuarios mediante un interruptor:
  - No se permite deshabilitar su propia cuenta.
  - No se permite deshabilitar al último administrador habilitado.
- Estado visual (habilitado/deshabilitado) en la tabla.

### 7.2 Departamentos

- Listar departamentos con conteo de usuarios.
- Crear y editar departamentos: nombre, descripción y Jefe de Departamento (opcional).
- Establecer/actualizar Jefe de Departamento respetando reglas de negocio.
- Eliminar departamento:
  - Los usuarios del departamento quedan sin departamento.
  - Si había un Jefe de Departamento, su rol se reasigna a "profesor".

### 7.3 Roles y permisos

- Listar roles con conteo de usuarios y vista rápida de permisos asignados.
- Crear rol nuevo (slug) y asignar permisos.
- Editar rol: actualizar permisos asignados.
- Eliminar rol (excepto el rol administrador):
  - Los usuarios con ese rol se reasignan al rol "profesor".

## 8) Perfil y ajustes del usuario

- Ver página de perfil público (del propio usuario autenticado) sin barra de navegación.
- Ver contadores: publicaciones, premios, reconocimientos y eventos asociados.
- Editar datos de perfil (nombre, email, departamento, etc.). Si cambia el email, se requiere nueva verificación.
- Cambiar contraseña desde Ajustes > Contraseña.

## 9) Búsqueda y autocompletar de usuarios

- Al crear/editar publicaciones, premios, reconocimientos y eventos, se puede buscar y seleccionar autores con un campo de autocompletar.
- Sólo se listan usuarios habilitados.

## 10) Apariencia (tema)

- Tema claro (light) aplicado de forma consistente en toda la aplicación.
- La preferencia se guarda para cargas futuras.

## 11) Reglas de visibilidad y edición/eliminación

Las acciones disponibles dependen de los permisos asignados al usuario. A modo de resumen:

- Ver resultados:
  - Ver todos los resultados.
  - Ver resultados del propio departamento.
  - Ver sólo los resultados donde el usuario es autor.
- Crear resultados:
  - Crear publicaciones, premios, reconocimientos y eventos.
- Editar resultados:
  - Editar cualquier resultado.
  - Editar resultados del departamento (al menos un autor pertenece al mismo departamento).
  - Editar sólo resultados donde el usuario es autor.
- Eliminar resultados:
  - Eliminar cualquier resultado.
  - Eliminar resultados del departamento.
  - Eliminar sólo resultados propios: si hay coautores, se retira la autoría del usuario; si es único autor, se elimina el registro.
- Administración:
  - Gestionar usuarios (crear/editar/habilitar-deshabilitar).
  - Gestionar departamentos (crear/editar/eliminar y jefe).
  - Gestionar roles y permisos.
  - Acceso al panel de administración.

## 12) Paginación y mensajes

- Listados paginados (10 ítems por página) con navegación.
- Mensajes de éxito y error tras operaciones de crear/editar/eliminar y cambios de estado.

---

## Glosario breve

- Autoría: relación entre un usuario y un resultado (publicación, premio, reconocimiento o evento).
- Jefe de Departamento: usuario designado como responsable de un departamento.
- Habilitar/Deshabilitar: control de acceso a la plataforma sin eliminar la cuenta.

# Documento de características y funcionalidades del sistema

Este documento describe en detalle el sistema de “Resultados Académicos”, incluyendo su arquitectura, módulos, rutas, permisos, modelo de datos, reglas de negocio y consideraciones de seguridad/UX.

## 1) Visión general

- Sistema web para gestión y visualización de resultados académico-científicos:
  - Publicaciones (Revistas, Libros, Capítulos de Libro)
  - Premios
  - Reconocimientos
  - Eventos
  - Departamentos y usuarios
  - Roles y permisos
- Acceso autenticado y verificado. Vistas y acciones reguladas por permisos finos (Spatie Permissions).
- Interfaz moderna con Vue 3, Inertia y Tailwind.

## 2) Arquitectura y stack

- Backend:
  - Laravel 12 (PHP ^8.2) — `composer.json`
  - Spatie Laravel-Permission ^6.21 para roles/permisos
  - Inertia Laravel ^2.0 y Ziggy para rutas en frontend
- Frontend:
  - Vue 3.5 + TypeScript — `package.json`
  - Inertia.js `@inertiajs/vue3` ^2.1
  - Vite 7, Tailwind CSS 4
  - UI: Reka UI, `lucide-vue-next`
- Patrón:
  - Inertia: renderizado de páginas Vue desde controladores Laravel (sin API REST separada)
  - Vista base: `resources/views/app.blade.php` con `@inertia`
  - Páginas Vue: `resources/js/pages/`
  - Layouts: `resources/js/layouts/`

## 3) Módulos y funcionalidades

### 3.1 Autenticación y verificación
- Rutas: `routes/auth.php`
  - `GET/POST login` con `guest`
  - Verificación de email (`verify-email`, `verification.verify`, `verification.send`)
  - `POST logout`
- Inicio: `GET /` redirige a `publications` si autenticado; de lo contrario a `login` — `routes/web.php`
- Sólo login habilitado (no hay ruta de registro en `auth.php`).
- Rate limit y bloqueo tras intentos fallidos — `app/Http/Requests/Auth/LoginRequest.php`
- Política de acceso:
  - Usuario con `is_enabled=false` no puede iniciar sesión; se finaliza sesión y se informa — `LoginRequest::authenticate()`

### 3.2 Administración
- Rutas bajo `/admin` protegidas por `permission:admin_system` — `routes/web.php`
- Vista: `resources/js/pages/Admin.vue`
- Controladores:
  - `app/Http/Controllers/AdminController.php`
  - `app/Http/Controllers/DepartmentController.php`
- Funcionalidades:
  - Gestión de usuarios:
    - Crear, editar, habilitar/deshabilitar (no se eliminan físicamente)
    - Validaciones de contraseña con complejidad
    - Asignación de rol vía Spatie
    - Evita auto-deshabilitarse
    - Evita deshabilitar al último admin habilitado
  - Gestión de departamentos:
    - Crear, editar, eliminar
    - Asignar “Jefe de Departamento” con reglas coherentes — `DepartmentController`
  - Gestión de roles y permisos:
    - Crear, actualizar permisos, eliminar (no permite eliminar `admin`)
    - Limpieza de caché de permisos tras cambios

- UI:
  - Listado/filtros de usuarios, badges por rol, estado habilitado/deshabilitado
  - Modal de edición y de estado (toggle habilitar/deshabilitar) con bloqueo para el propio usuario — `resources/js/components/Admin.vue`
  - Sección de Roles con listado de permisos asignados y formularios

### 3.3 Publicaciones
- Controlador: `app/Http/Controllers/PublicationController.php`
- Ruta principal: `GET /publications` (paginado, 10 por página) — `routes/web.php`
- Tipos:
  - Revista (`Revista`)
  - Libro (`Libro`)
  - Capítulo de libro (`Capitulo de Libro`)
- Crear/Editar/Eliminar con validaciones específicas por tipo. Asociación con autores (usuarios).
- Reglas de visibilidad y edición/eliminación basadas en permisos:
  - Ver/editar/eliminar todos, por departamento o propios
  - Si `delete_own_result` y hay múltiples autores, se desasocia al usuario; si es único autor, se elimina toda la publicación

### 3.4 Premios (Awards)
- Controlador: `app/Http/Controllers/AwardController.php`
- Rutas: `GET/POST/PUT/DELETE /awards` — `routes/web.php`
- Mismas reglas de alcance (todos/departamento/propios) para listar, editar y eliminar.
- Autores: relación muchos-a-muchos; al crear/editar se asegura incluir al usuario autenticado.

### 3.5 Reconocimientos (Recognitions)
- Controlador: `app/Http/Controllers/RecognitionController.php`
- Rutas: `GET/POST/PUT/DELETE /recognitions` — `routes/web.php`
- Reglas y flujo similares a Premios/Eventos.

### 3.6 Eventos
- Controlador: `app/Http/Controllers/EventController.php`
- Rutas: `GET/POST/PUT/DELETE /events` — `routes/web.php`
- Reglas de visibilidad, edición y eliminación análogas.
- Campos: `name`, `category`, `date`, `description`, autores.

### 3.7 Ajustes de usuario
- Rutas: `routes/settings.php`
- Perfil:
  - `GET settings/profile` muestra contadores y permite editar datos con validaciones — `Settings/ProfileController@edit`
  - `PATCH settings/profile` actualiza; si cambia email, resetea verificación — `ProfileController@update`
  - `GET profile` página de perfil público del usuario autenticado — `resources/js/pages/ProfileView.vue`
- Contraseña:
  - `GET/PUT settings/password` con reglas de seguridad — `Settings/PasswordController`
- Apariencia:
  - `GET settings/appearance` renderiza `settings/Appearance`
  - Tema forzado a claro (light) a nivel de aplicación

### 3.8 Búsqueda de usuarios
- Endpoint: `GET /users/search` — `UserController@search`
- Autenticado y verificado
- Devuelve JSON paginado con `id` y `name`
- Filtra por `is_enabled` si la columna existe

## 4) Modelo de datos (resumen)

Entidades y relaciones principales — `database/migrations/`:

- Usuarios (`users`) — `0001_01_01_000000_create_users_table.php`
  - Campos adicionales: `ci`, `teaching_category`, `scientific_category`, `professional_level`, `department_id`, `is_enabled` — `2025_09_06_000001_add_is_enabled_to_users_table.php`
  - Relación: `belongsTo Department`
  - Relaciones N:N con `awards`, `recognitions`, `events`, `publications`
- Departamentos (`departments`)
  - `name`, `description`, `head_of_department_id` — `2025_08_22_150703_create_departments_table.php`
  - Reglas de jefe en `DepartmentController`
- Publicaciones (`publications`)
  - Detalle por tipo en tablas 1-1: `magazines`, `books`, `chapters` (PK compartida `id`)
  - Pivotes N:N con usuarios: `publication_user`
- Premios (`awards`) + pivote `award_user`
- Reconocimientos (`recognitions`) + pivote `recognition_user`
- Eventos (`events`) + pivote `event_user`
- Tablas de permisos/roles de Spatie — `2025_08_22_190401_create_permission_tables.php`

Modelos — `app/Models/`:
- `User` con `HasRoles`, casts para `is_enabled`, relaciones N:N y `department()`

## 5) Roles y permisos

- Gestión con Spatie — `HasRoles` en `User`
- Roles típicos (ejemplo UI): `admin`, `directive`, `head_dp`, `profesor` — `AdminController@index`
- Permisos más relevantes — `AdminController`:
  - Ver resultados: `view_all_results`, `view_department_results`, `view_own_results`
  - Crear/editar/eliminar: `create_result`, `edit_any_result`, `edit_department_result`, `edit_own_result`, `delete_any_result`, `delete_department_result`, `delete_own_result`
  - Administración: `manage_users`, `assign_roles`, `manage_roles_permissions`, `view_all_users`, `view_all_departments`, `create_department`, `edit_department`, `delete_department`
  - Acceso al área admin: se usa `permission:admin_system` en rutas — `routes/web.php`

Notas:
- El área `/admin` requiere el permiso `admin_system`. Asegurar que exista en base de datos y se asigne a los roles que deban acceder.

## 6) Rutas principales

- `routes/web.php`, `routes/auth.php`, `routes/settings.php`
- Redirecciones: `/` y `/dashboard` redirigen a `publications` si autenticado
- Módulos con middleware: `auth`, `verified` y permisos fine-grained (Spatie)

## 7) UI/UX y tema

- Layout base Inertia: `resources/views/app.blade.php`
  - Carga CSS/JS via Vite
  - Aplica tema “light” de forma temprana y persistente (localStorage + cookie)
- Forzado de tema claro:
  - `resources/js/composables/useAppearance.ts`: elimina `dark` y persiste `appearance=light`
  - `resources/views/app.blade.php`: script temprano que elimina `dark` y persiste preferencia
- Layouts Vue:
  - `AppLayout`, `AppHeaderLayout`, `AuthenticatedLayout`, `AuthLayout`, `AuthSplitLayout`, `AdminLayout`
- Componentes:
  - Listados con paginación
  - Formularios con validaciones y modales de confirmación
  - Uso de Ziggy para rutas (`@routes` y `ZiggyVue`)

## 8) Reglas de negocio y validaciones

- Usuarios:
  - No se eliminan; se habilitan/deshabilitan vía `is_enabled` — `AdminController@destroyUser`, `AdminController@updateUserStatus`
  - No se permite auto-deshabilitarse
  - No se puede deshabilitar al último administrador habilitado
  - Complejidad de contraseña en creación/edición de usuario Administrativo
- Departamentos:
  - `head_user_id` debe ser usuario habilitado
  - En creación, el jefe debe estar sin departamento (se asigna); en edición se permiten miembros del departamento o, si no hay jefe, usuarios sin departamento
  - Si se elimina un departamento, los usuarios pasan a `department_id = null`; el jefe pierde rol y pasa a `profesor`
- Publicaciones/Premios/Reconocimientos/Eventos:
  - Visibilidad y edición/eliminación condicionadas por permisos:
    - “Todos”, “Departamento” (coincidencia de `department_id` de alguno de los autores) o “Propios”
  - En eliminación con permiso “propio” y múltiples autores, se desasocia al usuario en lugar de eliminar el registro

## 9) Seguridad

- Autenticación obligatoria y verificación de email para acceso a módulos
- Middleware de permisos (Spatie) en rutas
- Rate limiting de login y verificación — `LoginRequest`
- Usuarios deshabilitados no pueden iniciar sesión (enfoque “soft-disable”)
- Cookies de apariencia con `SameSite=Lax` y `Secure` bajo HTTPS — `useAppearance.ts`

## 10) No funcionales

- Paginación (10 elementos por página) y eager loading para eficiencia
- Scripts de desarrollo:
  - PHP server + queue + vite (concurrently) — `composer.json` script `dev`
- Estilo y lint:
  - ESLint + Prettier + Tailwind plugin — `package.json`
- Pruebas:
  - PHPUnit + Pest configurados

## 11) Observaciones y oportunidades de mejora

- Consistencia de permisos:
  - En `app/Http/Controllers/EventController.php` se usa `edit_department_results` (plural) en algunos puntos, pero en el resto del sistema se utiliza `edit_department_result` (singular) y en `routes/web.php` el middleware también usa el singular. Unificar el nombre para evitar brechas de autorización.
- Política de no eliminación de usuarios:
  - Existe la ruta `DELETE settings/profile` que actualmente elimina el usuario (`Settings/ProfileController@destroy`). Si la política institucional es no eliminar usuarios y sólo deshabilitarlos, se recomienda:
    - Deshabilitar/ocultar esta ruta en UI.
    - Cambiar la implementación para marcar `is_enabled=false` en lugar de `delete()`.
- Permiso `admin_system`:
  - Asegurar que se cree y asigne a los roles adecuados, ya que es requerido por el grupo de rutas `/admin`.
- Accesibilidad y tema:
  - El tema se fuerza a claro. Si en el futuro se requiere modo oscuro, habría que revertir el hard-lock en `useAppearance.ts` y en el script temprano de `app.blade.php`.

## 12) Endpoints (resumen)

- Autenticación — `routes/auth.php`
  - `GET login`, `POST login`, `POST logout`, verificación y notificaciones de verificación.
- Módulos — `routes/web.php`
  - `GET /publications` (+ `POST`, `PUT`, `DELETE /publications/{id}`) — permisos: ver/crear/editar/eliminar (all/department/own)
  - `GET /awards` (+ `POST`, `PUT`, `DELETE /awards/{id}`) — mismas reglas
  - `GET /recognitions` (+ `POST`, `PUT`, `DELETE /recognitions/{id}`) — mismas reglas
  - `GET /events` (+ `POST`, `PUT`, `DELETE /events/{event}`) — mismas reglas
  - `GET /users/search` — autenticado; JSON paginado
  - `GET /dashboard` — redirige a `publications`
  - Administración `/admin` (requiere `permission:admin_system`):
    - `GET /admin` (panel)
    - `POST/PUT/PUT(status)/DELETE /admin/users/...`
    - `POST/PUT/DELETE /admin/departments/...`
    - `GET/POST/PUT/DELETE /admin/roles/...`
- Ajustes — `routes/settings.php`
  - `GET/PATCH/DELETE settings/profile`
  - `GET profile` (pública del propio usuario autenticado, sin navbar)
  - `GET/PUT settings/password`
  - `GET settings/appearance`

## 13) Créditos

- Autoría del proyecto: añadir el autor en el README y documentación (p. ej., “Autor: [Tu Nombre]”).

## 14) Siguientes pasos recomendados

- Unificar el nombre de permiso en `EventController` a `edit_department_result` para mantener coherencia con el resto del sistema.
- Alinear la ruta `DELETE settings/profile` con la política de “no eliminar usuarios”; migrar a deshabilitado (`is_enabled=false`) o retirar la opción de UI.
- Verificar en la base de datos la existencia del permiso `admin_system` y asignarlo al rol correspondiente.
- Añadir documentación de “cómo desplegar” y “datos de ejemplo” si se requiere onboarding más rápido.
- Opcional: enlazar este documento desde el `README.md`.

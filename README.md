# Resultados Académicos

**Autor:** Enmanuel Piñero Linares 
**Contacto:** pinerolinaresenmanuel@gmail.com
**Año:** 2025

Aplicación Laravel 11 con Inertia.js (Vue 3) para gestionar resultados académicos : publicaciones, eventos, premios y reconocimientos de el claustro de una institución educativa. Incluye control de acceso basado en roles y permisos con Spatie Permission, paginación , y UI moderna.

## Tecnologías
- Laravel 11
- Inertia.js + Vue 3
- Spatie Laravel Permission
- TailwindCSS (estilos utilitarios en componentes)
- PHP >= 8.2

## Características clave
- Roles y permisos con Spatie:
- Alcance de datos por permisos (backend):
- UI condicionada por permisos (frontend):


## Requisitos previos
- PHP 8.2+
- Composer
- Node.js 18+
- Base de datos MySQL/MariaDB (o la que configure `.env`)

## Configuración del proyecto

1) Clonar e instalar dependencias
```
composer install
npm install
```

2) Copiar variables de entorno y generar clave
```
cp .env.example .env
php artisan key:generate
```
Configura la conexión a BD en `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

3) Migraciones y seeders
```
php artisan migrate:fresh --seed
```
Este comando ejecuta:
- `RolesAndPermissionsSeeder` para crear permisos y roles.
- `UserSeeder` para crear usuarios de ejemplo y asignarles roles.

4) Limpiar cachés (recomendado tras cambios de permisos/rutas)
```
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

5) Compilar assets
```
npm run dev
# o para build de producción
npm run build
```

6) Levantar el servidor de desarrollo
```
php artisan serve
```
La app quedará disponible en `http://127.0.0.1:8000`.

## Estructura relevante
- `app/Http/Controllers/*Controller.php`: controladores con reglas de alcance por permisos para listar y editar.
- `app/Http/Middleware/HandleInertiaRequests.php`: comparte `auth.roles` y `auth.permissions` con Vue.
- `database/seeders/RolesAndPermissionsSeeder.php`: define roles y permisos.
- `database/seeders/UserSeeder.php`: crea usuarios y asigna roles existentes.
- `resources/js/components/*`: componentes Vue (Awards, Events, Publications, Recognitions) con UI basada en permisos.
- `resources/js/components/Pagination.vue`: componente compartido de paginación en español.
- `routes/web.php`: rutas protegidas, incluidas DELETE con `permission:delete_any_result`.

## Roles y permisos (resumen)
- `admin`:
  - Puede ver/editar todos los resultados y eliminar (`delete_any_result`).
- `directive` / `head_dp`:
  - Ver/editar resultados del departamento (`view_department_results`, `edit_department_results`).
- `profesor`:
  - Ver/editar solo los propios (`edit_own_result`).

Ajusta estos permisos/roles según tus necesidades en `RolesAndPermissionsSeeder`.

## Política de usuarios
- Los usuarios no se eliminan; se marcan con `is_enabled`.
- Usuarios deshabilitados no pueden iniciar sesión.
- En la UI, los administradores no pueden deshabilitar su propia cuenta (para evitar lockout).

## Paginación
- El backend usa `paginate(10)`.
- El frontend muestra controles en español usando `Pagination.vue`.
- Se alinea a la derecha bajo tablas/listados.

## Desarrollo y mantenimiento
- Actualiza permisos/roles modificando `RolesAndPermissionsSeeder` y ejecuta:
```
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan cache:clear && php artisan route:clear && php artisan config:clear
```
- Si cambias middleware de Spatie en Laravel 11, recuerda registrar los alias en `bootstrap/app.php`.

## Troubleshooting
- "Missing middleware alias ...": verifica `bootstrap/app.php` y limpia cachés.
- Cambios en permisos no se reflejan en UI: asegúrate de que `HandleInertiaRequests` comparte `auth.permissions` y limpia cachés.
- Problemas con fechas: los componentes formatean sin zona horaria; valida formato `YYYY-MM-DD` en el backend.

## Scripts útiles
```
# Reset total de BD
php artisan migrate:fresh --seed

# Limpiar cachés
php artisan cache:clear && php artisan route:clear && php artisan config:clear

# Desarrollo frontend
npm run dev

# Producción frontend
npm run build
```

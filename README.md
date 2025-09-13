# Resultados Académicos


Aplicación Laravel 11 con Inertia.js (Vue 3) para gestionar resultados académicos : publicaciones, eventos, premios y reconocimientos del claustro de una institución educativa. Incluye control de acceso basado en roles y permisos con Spatie Permission, paginación , y UI moderna.

## Tecnologías
- Laravel 11
- Inertia.js + Vue 3
- Spatie Laravel Permission
- TailwindCSS (estilos utilitarios en componentes)
- PHP >= 8.2

## Características clave
- Gestión de roles y permisos (Spatie):
  - Definición centralizada de permisos y roles en `database/seeders/RolesAndPermissionsSeeder.php`.
  - Alias de middleware registrados en `resultAcademic/bootstrap/app.php` (`role`, `permission`, `role_or_permission`).
  - Rutas protegidas usando `permission:*` según el caso (ver `resultAcademic/routes/web.php`).

- Alcance de datos por permisos (backend):
  - Listados y operaciones CRUD aplican filtros por rol/permisos: ver todos, por departamento o solo propios.
  - Controladores validan el permiso correspondiente antes de permitir edición/eliminación.

- UI condicionada por permisos (frontend):
  - Componentes Vue muestran/ocultan acciones según `auth.roles` y `auth.permissions` compartidos por Inertia.
  - Botones de crear/editar/eliminar y accesos de administración se renderizan de forma condicional.

- Módulos de resultados académicos:
  - Publicaciones, Eventos, Premios y Reconocimientos con CRUD completo y validación.
  - Búsqueda y filtros básicos (por usuario y/o departamento cuando aplique).

- Paginación y UX coherente:
  - Componente `resources/js/components/Pagination.vue` en español, reutilizable en listados.
  - Diseño responsivo con TailwindCSS.

- Administración del sistema:
  - Área bajo `/admin` con permiso agregado `admin_system` que encapsula la gestión de usuarios, roles/permisos y departamentos.
  - Política de usuarios: habilitar/deshabilitar vía `is_enabled` (sin borrado definitivo) y prevención de auto-deshabilitado para administradores.

- Arquitectura Laravel + Inertia (SPA ligera):
  - Renderizado del lado del servidor con Inertia y vistas en Vue 3.
  - Estados de autenticación compartidos y navegación sin recarga completa.

- Semillas de datos para inicio rápido:
  - Seeders crean roles, permisos, departamentos, usuarios de ejemplo y datos de demostración.
  - Contraseña por defecto para usuarios de ejemplo: `password`.

- Preparado para despliegue:
  - Scripts y pasos de cacheo (`config:cache`, `route:cache`, `view:cache`).
  - Build de frontend con `npm run build`.


## Requisitos previos
- PHP 8.2+
- Composer
- Node.js 18+
- Base de datos MySQL/MariaDB (o la que configure `.env`)

## Configuración del proyecto (paso a paso)

Estas instrucciones cubren Windows con XAMPP.

1) Clonar el repositorio e instalar dependencias
```
composer install
npm install
```

2) Copiar variables de entorno y generar clave de la app
- En Windows (PowerShell o CMD):
```
copy .env.example .env
```

Luego:
```
php artisan key:generate
```

3) Configurar la base de datos
- Si usas XAMPP, inicia `Apache` y `MySQL` y entra a `http://localhost/phpmyadmin`.
- Crea una base de datos (por ejemplo `result_academic`).
- En el archivo `.env`, ajusta:
```
APP_NAME="Resultados Académicos"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=result_academic
DB_USERNAME=root
DB_PASSWORD=
```
Si usas otra BD/usuario/puerto, actualiza los valores correspondientes.

4) (Opcional) Configurar correo para verificación y notificaciones
Configura las variables `MAIL_` en `.env` si vas a usar verificación de email o envío de correos. Para desarrollo puedes usar Mailtrap.

5) Crear enlace de almacenamiento (si la app lo requiere)
```
php artisan storage:link
```

6) Ejecutar migraciones y seeders
```
php artisan migrate:fresh --seed
```
Este comando ejecuta los seeders definidos en `resultAcademic/database/seeders/DatabaseSeeder.php`, incluyendo:
- `RolesAndPermissionsSeeder` (crea permisos y roles)
- `DepartmentSeeder` (departamentos de ejemplo)
- `UserSeeder` (usuarios de ejemplo y asignación de roles)
- `PublicationSeeder`, `AwardSeeder`, `RecognitionSeeder`, `EventSeeder` (datos de demostración)

Usuarios sembrados (contraseña por defecto: `password`):
- admin: `admin@example.com`
- directivo: `directivo@example.com`
- jefe de departamento 1: `jefeD1@example.com`
- jefe de departamento 2: `jefeD2@example.com`
- profesor 1: `prof1@example.com`
- profesor 2: `prof2@example.com`

7) Limpiar cachés de la app (recomendado tras cambios de permisos/rutas)
```
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

8) Compilar assets de frontend
```
npm run dev
# Para build de producción
npm run build
```

9) Levantar el servidor de desarrollo
```
php artisan serve
```
La aplicación quedará disponible en `http://127.0.0.1:8000`.

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



## Desarrollo y mantenimiento
- Actualiza permisos/roles modificando `RolesAndPermissionsSeeder` y ejecuta:
```
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan cache:clear && php artisan route:clear && php artisan config:clear
```
- Si cambias middleware de Spatie en Laravel 11, recuerda registrar los alias en `bootstrap/app.php`.



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

## Política de usuarios (importante)
- Los usuarios no se eliminan, se habilitan/deshabilitan mediante el campo `is_enabled`.
- Un usuario deshabilitado no puede iniciar sesión.
- La UI debe mostrar un toggle de habilitar/deshabilitar.
- Para evitar bloqueos del sistema, un administrador no puede auto-deshabilitarse.

Esta política está soportada por las rutas y controladores bajo el prefijo `admin` (ver `resultAcademic/routes/web.php`) y las reglas de negocio correspondientes.

## Despliegue (producción)
1) Variables de entorno
- Establece `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` con tu dominio.
- Configura `DB_*` y `MAIL_*` reales.

2) Dependencias y build
```
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

3) Migraciones y caches
```
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4) Permisos de carpetas (Linux)
```
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
```

## Solución de problemas
- 403 Forbidden tras login o al acceder a módulos
  - Ejecuta `php artisan cache:clear` y verifica que los alias de middleware de Spatie estén registrados en `resultAcademic/bootstrap/app.php`:
    - `role`, `permission`, `role_or_permission` apuntando a los middlewares de `Spatie\Permission`.
- Error de conexión a BD
  - Revisa credenciales `DB_*` en `.env` y que la BD exista. En XAMPP suele ser usuario `root` sin contraseña.
- Puerto en uso al hacer `php artisan serve`
  - Ejecuta `php artisan serve --host=127.0.0.1 --port=8001` o cierra el proceso que ocupa el puerto 8000.
- `npm` no reconocido
  - Instala Node.js 18+ y reabre la terminal. En Windows, verifica que Node esté en el PATH.

## Autor y créditos
- Autor: Enmanuel (autor del proyecto). Gracias por su trabajo y liderazgo del código base.
- Frameworks y paquetes: Laravel 11, Inertia.js, Vue 3, TailwindCSS, Spatie Laravel Permission.

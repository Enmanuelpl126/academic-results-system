# Sistema de Gestión de Resultados Académicos

Sistema web para gestionar resultados académicos de profesores en instituciones educativas: publicaciones, eventos, premios y reconocimientos. Desarrollado con Laravel 11 e Inertia.js (Vue 3), con control de acceso basado en roles y permisos.

## 📋 Características Principales

- **Gestión de Resultados Académicos**: CRUD completo para publicaciones, eventos, premios y reconocimientos
- **Control de Acceso Robusto**: Sistema de roles y permisos mediante Spatie Laravel Permission
- **Alcance de Datos por Permisos**: Los usuarios ven y gestionan datos según su rol (todos, departamento o propios)
- **Interfaz Moderna y Responsiva**: Construida con Vue 3 y TailwindCSS
- **Administración del Sistema**: Gestión de usuarios, roles, permisos y departamentos
- **Búsqueda y Filtros**: Por usuario y departamento cuando aplique
- **Paginación en Español**: Componente reutilizable para todos los listados
- **SPA Ligera**: Navegación fluida sin recargas completas gracias a Inertia.js

## 🛠️ Tecnologías

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: Vue 3 + Inertia.js
- **Estilos**: TailwindCSS
- **Autenticación y Permisos**: Spatie Laravel Permission
- **Base de Datos**: MySQL/MariaDB

## 📦 Requisitos Previos

- PHP >= 8.2
- Composer
- Node.js >= 18
- MySQL/MariaDB (o la base de datos configurada en `.env`)
- XAMPP, WAMP, Laragon o servidor web similar (para desarrollo local)

## 🚀 Instalación

### 1. Clonar el Repositorio

```bash
git clone https://github.com/Enmanuelpl126/academic-results-system.git
cd academic-results-system
```

### 2. Instalar Dependencias

```bash
composer install
npm install
```

### 3. Configurar Variables de Entorno

**En Windows (PowerShell o CMD):**
```bash
copy .env.example .env
```

**En Linux/Mac:**
```bash
cp .env.example .env
```

Generar la clave de la aplicación:
```bash
php artisan key:generate
```

### 4. Configurar Base de Datos

Si usas XAMPP:
1. Inicia Apache y MySQL
2. Accede a [phpMyAdmin](http://localhost/phpmyadmin)
3. Crea una base de datos (ejemplo: `result_academic`)

Edita el archivo `.env` con tus credenciales:

```env
APP_NAME="Resultados Académicos"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=result_academic
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Configurar Almacenamiento (Opcional)

```bash
php artisan storage:link
```

### 6. Ejecutar Migraciones y Seeders

```bash
php artisan migrate:fresh --seed
```

Este comando creará:
- Roles y permisos del sistema
- Departamentos de ejemplo
- Usuarios de prueba con diferentes roles
- Datos de demostración (publicaciones, eventos, premios, reconocimientos)

### 7. Limpiar Cachés

```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### 8. Compilar Assets Frontend

**Para desarrollo:**
```bash
npm run dev
```

**Para producción:**
```bash
npm run build
```

### 9. Iniciar Servidor de Desarrollo

```bash
php artisan serve
```

La aplicación estará disponible en: **http://127.0.0.1:8000**

## 👥 Usuarios de Prueba

Todos los usuarios tienen la contraseña: `password`

| Rol | Email | Permisos |
|-----|-------|----------|
| Administrador | admin@example.com | Acceso completo, eliminar resultados |
| Directivo | directivo@example.com | Ver/editar resultados del departamento |
| Jefe Departamento 1 | jefeD1@example.com | Ver/editar resultados del departamento |
| Jefe Departamento 2 | jefeD2@example.com | Ver/editar resultados del departamento |
| Profesor 1 | prof1@example.com | Ver/editar solo resultados propios |
| Profesor 2 | prof2@example.com | Ver/editar solo resultados propios |

## 🔐 Sistema de Roles y Permisos

### Roles Principales

- **admin**: Acceso completo al sistema, puede eliminar cualquier resultado
- **directive**: Visualiza y edita resultados de su departamento
- **head_dp**: Jefe de departamento, gestiona resultados departamentales
- **profesor**: Solo puede ver y editar sus propios resultados

### Permisos Clave

- `admin_system`: Acceso al panel de administración
- `delete_any_result`: Eliminar cualquier resultado académico
- `view_department_results`: Ver resultados del departamento
- `edit_department_results`: Editar resultados del departamento
- `edit_own_result`: Editar solo resultados propios

### Personalizar Roles y Permisos

Edita el archivo `database/seeders/RolesAndPermissionsSeeder.php` y ejecuta:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan cache:clear && php artisan route:clear && php artisan config:clear
```

## 📁 Estructura del Proyecto

```
academic-results-system/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # Controladores con lógica de permisos
│   │   └── Middleware/
│   │       └── HandleInertiaRequests.php  # Comparte roles/permisos con Vue
├── database/
│   └── seeders/
│       ├── RolesAndPermissionsSeeder.php  # Define roles y permisos
│       ├── UserSeeder.php                 # Usuarios de ejemplo
│       └── DatabaseSeeder.php             # Orquesta todos los seeders
├── resources/
│   └── js/
│       └── components/         # Componentes Vue con UI basada en permisos
│           ├── Awards/
│           ├── Events/
│           ├── Publications/
│           ├── Recognitions/
│           └── Pagination.vue  # Componente de paginación reutilizable
├── routes/
│   └── web.php                # Rutas protegidas por middleware de permisos
└── bootstrap/
    └── app.php                # Registro de alias de middleware
```

## 🔧 Comandos Útiles

### Reset Completo de Base de Datos
```bash
php artisan migrate:fresh --seed
```

### Limpiar Todas las Cachés
```bash
php artisan cache:clear && php artisan route:clear && php artisan config:clear
```

### Desarrollo Frontend
```bash
npm run dev
```

### Build de Producción
```bash
npm run build
```



## 🚢 Despliegue a Producción

1. Configura las variables de entorno en `.env` para producción
2. Ejecuta las migraciones:
   ```bash
   php artisan migrate --force
   ```
3. Cachea configuraciones:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. Compila assets:
   ```bash
   npm run build
   ```
5. Configura el servidor web (Apache/Nginx) para apuntar a la carpeta `public/`



⭐ Si este proyecto te fue útil, considera darle una estrella en GitHub


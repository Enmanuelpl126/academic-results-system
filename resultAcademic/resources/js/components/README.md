# Componentes Vue.js - Navbar

Este directorio contiene los componentes Vue.js convertidos desde React para la navegación de la aplicación.

## Estructura de Archivos

```
resources/js/
├── components/
│   ├── NavItem.vue      # Componente reutilizable para elementos de navegación
│   ├── Navbar.vue       # Componente principal de navegación
│   └── README.md        # Esta documentación
└── composables/
    └── useUser.js       # Composable para gestión de usuario
```

## Componentes Incluidos

### 1. NavItem.vue
Componente reutilizable para elementos de navegación individual.

**Props:**
- `icon`: Componente de icono (String o Object)
- `label`: Texto del elemento (String)
- `active`: Estado activo (Boolean)
- `className`: Clases CSS adicionales (String)
- `variant`: Variante de estilo ('default' | 'admin')

**Eventos:**
- `@click`: Emitido cuando se hace clic en el elemento

### 2. Navbar.vue
Componente principal de navegación con menú responsive.

**Props:**
- `activeSection`: Sección actualmente activa (String)

**Eventos:**
- `@section-change`: Emitido cuando cambia la sección

## Composables

### useUser.js
Composable para manejar el estado del usuario y autenticación.

**Funciones exportadas:**
- `useUser()`: Hook principal para usar el contexto de usuario
- `provideUserContext()`: Provee el contexto en el componente padre
- `injectUserContext()`: Inyecta el contexto en componentes hijos

## Dependencias Requeridas

Para usar estos componentes necesitas instalar:

```bash
npm install lucide-vue-next
# o
yarn add lucide-vue-next
```

## Uso

### 1. Configurar el contexto de usuario en tu aplicación principal:

```vue
<template>
  <div id="app">
    <Navbar 
      :active-section="currentSection" 
      @section-change="handleSectionChange" 
    />
    <!-- resto de tu aplicación -->
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { provideUserContext } from '@/composables/useUser.js'
import Navbar from '@/components/Navbar.vue'

// Proveer el contexto de usuario
const { setCurrentUser } = provideUserContext()

const currentSection = ref('dashboard')

const handleSectionChange = (section) => {
  currentSection.value = section
}

// Ejemplo: establecer usuario admin
setCurrentUser({ role: 'admin', name: 'Usuario Admin' })
</script>
```

### 2. Usar el composable en otros componentes:

```vue
<script setup>
import { useUser } from '@/composables/useUser.js'

const { currentUser, isAdmin, isAuthenticated } = useUser()
</script>
```

## Integración con Laravel

Estos componentes están ubicados en la estructura estándar de Laravel:
- `resources/js/components/` - Componentes Vue.js
- `resources/js/composables/` - Composables reutilizables

Para usar con Inertia.js o en tu aplicación Laravel:

```javascript
// En tu app.js principal
import { createApp } from 'vue'
import Navbar from './components/Navbar.vue'

const app = createApp({})
app.component('Navbar', Navbar)
```

## Clases CSS

Los componentes usan Tailwind CSS. Asegúrate de tener Tailwind configurado en tu proyecto Laravel.

## Diferencias con la versión React

1. **Gestión de estado**: Se usa `ref()` en lugar de `useState()`
2. **Eventos**: Se usan `@click` y `emit()` en lugar de props de callback
3. **Iconos**: Se usa `lucide-vue-next` en lugar de `lucide-react`
4. **Contexto**: Se usa `provide/inject` y composables en lugar de React Context
5. **Clases condicionales**: Se usan arrays y objetos de Vue en lugar de template literals

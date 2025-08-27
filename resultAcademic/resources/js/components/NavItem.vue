<template>
  <!-- Botón de elemento de navegación. Combina estilos calculados y emite un evento al hacer clic -->
  <button
    @click="onClick"
    :class="buttonClasses"
  >
    <!-- Icono dinámico. Se espera un componente de icono de lucide-vue-next u otro -->
    <component :is="icon" :size="20" />
    <!-- Texto del ítem -->
    <span class="font-medium">{{ label }}</span>
  </button>
</template>

<script setup>
import { computed } from 'vue'

// Props del componente
// - icon: Componente de icono a renderizar
// - label: Texto a mostrar
// - active: Si el ítem está activo (aplica estilos)
// - className: Clases adicionales externas
// - variant: Cambia la paleta (default | admin)
const props = defineProps({
  icon: {
    type: [String, Object],
    required: true
  },
  label: {
    type: String,
    required: true
  },
  active: {
    type: Boolean,
    default: false
  },
  className: {
    type: String,
    default: ''
  },
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'admin'].includes(value)
  }
})

// Declaración de eventos emitidos por el componente
const emit = defineEmits(['click'])

// Handler de clic: notifica al padre sin lógica adicional
const onClick = () => {
  emit('click')
}

// Computa las clases de estilo según el estado y la variante
const buttonClasses = computed(() => {
  const baseClasses = 'flex items-center gap-2 px-4 py-2 rounded-lg transition-colors'
  
  let variantClasses = ''
  if (props.variant === 'admin') {
    variantClasses = props.active
      ? 'bg-purple-100 text-purple-700'
      : 'hover:bg-purple-50 text-purple-600'
  } else {
    variantClasses = props.active
      ? 'bg-blue-100 text-blue-700'
      : 'hover:bg-gray-100 text-gray-700'
  }
  
  return `${baseClasses} ${variantClasses} ${props.className}`
})
</script>

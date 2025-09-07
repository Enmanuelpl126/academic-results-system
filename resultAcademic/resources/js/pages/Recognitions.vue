<template>
  <!-- Página principal de Recognitions - el layout se aplica automáticamente -->
  <Recognitions :recognitions="recognitions" :users="users" />
</template>

<script setup>
// Importaciones necesarias
import AppLayout from '@/layouts/AppLayout.vue'
import Recognitions from '@/components/Recognitions.vue'

// Configuración de la página para Inertia.js
const props = defineProps({
  recognitions: {
    type: Object,
    default: () => ({ data: [], links: [] })
  },
  users: {
    type: Array,
    default: () => []
  }
})

defineOptions({
  layout: AppLayout
})
</script>

<template>
  <!-- Página principal de Recognitions - el layout se aplica automáticamente -->
  <Recognitions :recognitions="recognitions" :users="users" />
  
  <!-- Paginación (opcional) -->
  <nav v-if="recognitions && recognitions.links" class="flex flex-wrap gap-2 items-center px-6 py-3">
    <Link
      v-for="link in recognitions.links"
      :key="(link.url || '') + (link.label || '')"
      :href="link.url || '#'"
      :class="[
        'px-3 py-1.5 rounded border',
        link.active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50',
        !link.url ? 'opacity-50 cursor-not-allowed' : ''
      ]"
      v-html="link.label"
      :disabled="!link.url"
    />
  </nav>
</template>

<script setup>
// Importaciones necesarias
import AppLayout from '@/layouts/AppLayout.vue'
import Recognitions from '@/components/Recognitions.vue'
import { Link } from '@inertiajs/vue3'

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

<template>
  <!-- Página principal de Awards - el layout se aplica automáticamente -->
  <div class="space-y-6">
    <Awards :awards="awards?.data ?? []" :users="users ?? []" />

    <!-- Paginación (sin contenedor extra) -->
    <nav v-if="awards && awards.links" class="flex flex-wrap gap-2 items-center px-6 py-3">
      <Link
        v-for="link in awards.links"
        :key="link.url + (link.label || '')"
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
  </div>
</template>

<script setup>
// Importaciones necesarias
import AppLayout from '@/layouts/AppLayout.vue'
import Awards from '@/components/Awards.vue'
import { Link } from '@inertiajs/vue3'

// Configuración de la página para Inertia.js
defineOptions({
  layout: AppLayout
})

// Props que llegan desde el servidor (Inertia)
const props = defineProps({
  awards: {
    type: Object,
    default: () => ({ data: [], links: [] })
  },
  users: {
    type: Array,
    default: () => []
  }
})
</script>

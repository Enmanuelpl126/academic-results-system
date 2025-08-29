<template>
  <div class="space-y-6">
    <Publications :users="users ?? []" />

    <!-- Paginación (sin contenedor extra) -->
    <nav v-if="publications && publications.links" class="flex flex-wrap gap-2 items-center px-6 py-3">
      <Link
        v-for="link in publications.links"
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
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue'
import Publications from '@/components/Publications.vue'
import { Link } from '@inertiajs/vue3'

// Configuración para usar el layout de aplicación
defineOptions({
  layout: AppHeaderLayout
})

// Props que llegan desde el servidor (Inertia)
const props = defineProps({
  publications: {
    type: Object,
    default: () => ({ data: [], links: [] })
  },
  users: {
    type: Array,
    default: () => []
  }
})
</script>

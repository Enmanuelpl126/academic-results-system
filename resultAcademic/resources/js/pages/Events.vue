<template>
  <!-- Página principal de Events - el layout se aplica automáticamente -->
  <div class="space-y-6">
    <Events :initialEvents="events?.data ?? []" :users="users ?? []" :key="events?.to ?? 0" />

    <!-- Paginación (igual estilo que Awards.vue) -->
    <nav v-if="events && events.links" class="flex flex-wrap gap-2 items-center px-6 py-3">
      <Link
        v-for="link in events.links"
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
  </div>
</template>

<script setup>
// Importaciones necesarias
import AppLayout from '@/layouts/AppLayout.vue'
import Events from '@/components/Events.vue'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

// Props desde Inertia (EventController@index) - paginador
const props = defineProps({
  events: {
    type: Object,
    default: () => ({ data: [], links: [] })
  },
  users: {
    type: Array,
    default: () => []
  }
})

const events = computed(() => props.events)

// Configuración de la página para Inertia.js
defineOptions({
  layout: AppLayout
})
</script>

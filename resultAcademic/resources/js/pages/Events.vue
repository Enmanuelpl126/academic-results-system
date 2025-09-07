<template>
  <!-- Página principal de Events - el layout se aplica automáticamente -->
  <div class="space-y-6">
    <Events :initialEvents="events?.data ?? []" :users="users ?? []" :key="events?.to ?? 0" />
    <div class="mt-2 px-6">
      <div class="flex justify-end">
        <Pagination :links="events?.links || []" />
      </div>
    </div>
  </div>
</template>

<script setup>
// Importaciones necesarias
import AppLayout from '@/layouts/AppLayout.vue'
import Events from '@/components/Events.vue'
import Pagination from '@/components/Pagination.vue'
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

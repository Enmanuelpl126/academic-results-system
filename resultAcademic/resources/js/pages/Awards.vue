<template>
  <!-- Página principal de Awards - el layout se aplica automáticamente -->
  <div class="space-y-6">
    <Awards :awards="awards?.data ?? []" :users="users ?? []" />
    <div class="mt-2 px-6">
      <div class="flex justify-end">
        <Pagination :links="awards?.links || []" />
      </div>
    </div>
  </div>
</template>

<script setup>
// Importaciones necesarias
import AppLayout from '@/layouts/AppLayout.vue'
import Awards from '@/components/Awards.vue'
import Pagination from '@/components/Pagination.vue'

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

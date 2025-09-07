<template>
  <div class="space-y-6">
    <Publications :users="users ?? []" />
    <div class="mt-2 px-6">
      <div class="flex justify-end">
        <Pagination :links="publications?.links || []" />
      </div>
    </div>
  </div>
</template>

<script setup>
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue'
import Publications from '@/components/Publications.vue'
import Pagination from '@/components/Pagination.vue'

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

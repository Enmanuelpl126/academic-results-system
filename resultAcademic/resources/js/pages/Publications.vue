<template>
  <Publications />
</template>

<script setup>
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue'
import Publications from '@/components/Publications.vue'

// Configuración para usar el layout de aplicación
defineOptions({
  layout: AppHeaderLayout
})
</script>

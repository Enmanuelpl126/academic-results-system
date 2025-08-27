<template>
  <!-- Página principal de Events - el layout se aplica automáticamente -->
  <Events />
</template>

<script setup>
// Importaciones necesarias
import AppLayout from '@/layouts/AppLayout.vue'
import Events from '@/components/Events.vue'

// Configuración de la página para Inertia.js
defineOptions({
  layout: AppLayout
})
</script>

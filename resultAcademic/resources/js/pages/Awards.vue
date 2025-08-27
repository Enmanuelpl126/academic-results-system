<template>
  <!-- Página principal de Awards - el layout se aplica automáticamente -->
  <Awards />
</template>

<script setup>
// Importaciones necesarias
import AppLayout from '@/layouts/AppLayout.vue'
import Awards from '@/components/Awards.vue'

// Configuración de la página para Inertia.js
defineOptions({
  layout: AppLayout
})
</script>

<template>
  <!-- Página principal de Recognitions - el layout se aplica automáticamente -->
  <Recognitions />
</template>

<script setup>
// Importaciones necesarias
import AppLayout from '@/layouts/AppLayout.vue'
import Recognitions from '@/components/Recognitions.vue'

// Configuración de la página para Inertia.js
defineOptions({
  layout: AppLayout
})
</script>

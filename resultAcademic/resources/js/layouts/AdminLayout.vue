<script setup lang="ts">
import AppShell from '@/components/AppShell.vue'
import AppContent from '@/components/AppContent.vue'
</script>

<template>
  <!-- Minimal layout without top navigation/header -->
  <AppShell variant="header">
    <AppContent class="px-4 md:px-6 lg:px-8">
      <slot />
    </AppContent>
  </AppShell>
</template>

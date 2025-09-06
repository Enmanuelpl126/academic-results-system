<script setup lang="ts">
import AppShell from '@/components/AppShell.vue'
import AppHeader from '@/components/AppHeader.vue'
import AppContent from '@/components/AppContent.vue'
</script>

<template>
  <!-- Header-only layout to avoid double sidebars on pages that already have one -->
  <AppShell variant="header">
    <AppHeader />

    <AppContent class="px-4 md:px-6 lg:px-8">
      <!-- Optional page header slot -->
      <div v-if="$slots.header" class="border-b border-sidebar-border/70 py-6">
        <div class="mx-auto max-w-7xl">
          <slot name="header" />
        </div>
      </div>

      <!-- Main page content -->
      <div class="py-6">
        <div class="mx-auto max-w-7xl">
          <slot />
        </div>
      </div>
    </AppContent>
  </AppShell>
</template>

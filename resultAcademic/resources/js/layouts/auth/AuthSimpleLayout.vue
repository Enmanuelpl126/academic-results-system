<script setup lang="ts">
import { Book } from 'lucide-vue-next'

defineProps<{
  title?: string
  description?: string
}>()
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 px-4 py-8">
    <div class="w-full max-w-md bg-white rounded-xl shadow-sm border border-gray-200 p-6">
      <div class="space-y-2 text-center mb-6">
        <div class="flex items-center justify-center gap-2">
          <Book :size="28" class="text-blue-900" />
          <h1 class="text-xl sm:text-2xl font-semibold text-blue-900">Sistema de Gestión de Resultados Académicos</h1>
        </div>
        <p v-if="description" class="text-sm text-gray-600">{{ description }}</p>
      </div>
      <slot />
    </div>
  </div>
</template>

<template>
  <nav v-if="Array.isArray(links) && links.length" class="flex items-center justify-end mt-4" aria-label="Paginación">
    <ul class="inline-flex items-center gap-1">
      <li v-for="(link, idx) in normalizedLinks" :key="idx">
        <button
          v-if="link.url"
          type="button"
          @click="go(link.url)"
          :class="[
            'px-3 py-1.5 rounded-md text-sm border transition-colors',
            link.active
              ? 'bg-blue-600 text-white border-blue-600'
              : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
          ]"
        >
          {{ link.label }}
        </button>
        <span
          v-else
          class="px-3 py-1.5 rounded-md text-sm border border-gray-200 text-gray-400"
        >
          {{ link.label }}
        </span>
      </li>
    </ul>
  </nav>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  links: {
    type: Array,
    default: () => []
  }
})

const go = (url) => {
  if (!url) return
  router.visit(url, { preserveScroll: true })
}

// Normaliza etiquetas a español y limpia HTML innecesario
const normalizedLinks = computed(() => {
  const mapLabel = (raw) => {
    // Remueve etiquetas HTML y entidades tipográficas comunes
    const s = String(raw)
      .replace(/&laquo;|&raquo;/g, '')
      .replace(/<[^>]*>/g, '')
      .trim()
    if (/previous|anterior/i.test(s)) return 'Anterior'
    if (/next|siguiente/i.test(s)) return 'Siguiente'
    return s
  }
  return (props.links || []).map(l => ({
    ...l,
    label: mapLabel(l.label)
  }))
})
</script>

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
  // Previene urls potencialmente peligrosas como "javascript:"
  const u = String(url)
  if (/^\s*javascript:/i.test(u)) return
  router.visit(u, { preserveScroll: true })
}

// Normaliza etiquetas a español y limpia HTML usando un parser seguro
const normalizedLinks = computed(() => {
  const mapLabel = (raw) => {
    // Usa DOMParser para extraer texto plano y decodificar entidades de forma robusta
    const input = String(raw ?? '')
    const doc = new DOMParser().parseFromString(input, 'text/html')
    const s = (doc.body.textContent || '').trim()
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

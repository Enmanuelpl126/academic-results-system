<template>
  <!-- Tabla de publicaciones -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th 
              @click="$emit('sort', 'name')"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
            >
              <div class="flex items-center gap-2">
                Publicación
                <ChevronUpIcon 
                  v-if="sortField === 'name' && sortDirection === 'asc'" 
                  :size="16" 
                  class="text-blue-600" 
                />
                <ChevronDownIcon 
                  v-else-if="sortField === 'name' && sortDirection === 'desc'" 
                  :size="16" 
                  class="text-blue-600" 
                />
                <ChevronsUpDownIcon v-else :size="16" class="text-gray-400" />
              </div>
            </th>
            <th 
              @click="$emit('sort', 'type')"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
            >
              <div class="flex items-center gap-2">
                Tipo
                <ChevronUpIcon 
                  v-if="sortField === 'type' && sortDirection === 'asc'" 
                  :size="16" 
                  class="text-blue-600" 
                />
                <ChevronDownIcon 
                  v-else-if="sortField === 'type' && sortDirection === 'desc'" 
                  :size="16" 
                  class="text-blue-600" 
                />
                <ChevronsUpDownIcon v-else :size="16" class="text-gray-400" />
              </div>
            </th>
            <th 
              @click="$emit('sort', 'date')"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
            >
              <div class="flex items-center gap-2">
                Fecha
                <ChevronUpIcon 
                  v-if="sortField === 'date' && sortDirection === 'asc'" 
                  :size="16" 
                  class="text-blue-600" 
                />
                <ChevronDownIcon 
                  v-else-if="sortField === 'date' && sortDirection === 'desc'" 
                  :size="16" 
                  class="text-blue-600" 
                />
                <ChevronsUpDownIcon v-else :size="16" class="text-gray-400" />
              </div>
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Autores
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Detalles
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              Acciones
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr 
            v-for="publication in publications" 
            :key="publication.id"
            class="hover:bg-gray-50 transition-colors"
          >
            <!-- Nombre de la publicación -->
            <td class="px-6 py-4">
              <div class="text-sm font-medium text-gray-900 line-clamp-2">
                {{ publication.name }}
              </div>
            </td>

            <!-- Tipo con badge -->
            <td class="px-6 py-4">
              <span :class="getTypeBadgeClass(publication.type)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                {{ getTypeLabel(publication.type) }}
              </span>
            </td>

            <!-- Fecha -->
            <td class="px-6 py-4 text-sm text-gray-900">
              {{ formatDate(publication.date) }}
            </td>

            <!-- Autores -->
            <td class="px-6 py-4">
              <div class="text-sm text-gray-900">
                <div v-if="publication.authors.length <= 2">
                  {{ publication.authors.join(', ') }}
                </div>
                <div v-else>
                  {{ publication.authors[0] }}
                  <span class="text-gray-500">y {{ publication.authors.length - 1 }} más</span>
                </div>
              </div>
            </td>

            <!-- Detalles específicos por tipo -->
            <td class="px-6 py-4">
              <div class="text-sm text-gray-600 space-y-1">
                <!-- Journal -->
                <div v-if="publication.type === 'journal'">
                  <div>Vol. {{ publication.volume }}, No. {{ publication.number }}</div>
                  <div v-if="publication.doi" class="text-xs text-blue-600">
                    DOI: {{ publication.doi }}
                  </div>
                </div>
                
                <!-- Book -->
                <div v-else-if="publication.type === 'book'">
                  <div>{{ publication.publisher }}</div>
                  <div class="text-xs">{{ publication.city }}</div>
                </div>
                
                <!-- Book Chapter -->
                <div v-else-if="publication.type === 'book_chapter'">
                  <div class="font-medium">{{ publication.chapterName }}</div>
                  <div class="text-xs">En: {{ publication.bookName }}</div>
                  <div class="text-xs">{{ publication.publisher }}</div>
                </div>
              </div>
            </td>

            <!-- Acciones -->
            <td class="px-6 py-4 text-right text-sm font-medium">
              <div class="flex items-center justify-end gap-2">
                <!-- Enlaces externos -->
                <a 
                  v-if="publication.url" 
                  :href="publication.url" 
                  target="_blank" 
                  rel="noopener noreferrer"
                  class="text-blue-600 hover:text-blue-800 transition-colors"
                  title="Ver publicación"
                >
                  <ExternalLinkIcon :size="16" />
                </a>
                
                <!-- Botón editar -->
                <button
                  @click="$emit('edit', publication)"
                  class="text-indigo-600 hover:text-indigo-900 transition-colors"
                  title="Editar publicación"
                >
                  <EditIcon :size="16" />
                </button>
                
                <!-- Botón eliminar -->
                <button
                  @click="$emit('delete', publication.id)"
                  class="text-red-600 hover:text-red-900 transition-colors"
                  title="Eliminar publicación"
                >
                  <TrashIcon :size="16" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Estado vacío -->
    <div v-if="publications.length === 0" class="text-center py-12">
      <BookOpenIcon :size="48" class="mx-auto text-gray-400 mb-4" />
      <h3 class="text-lg font-medium text-gray-900 mb-2">No hay publicaciones</h3>
      <p class="text-gray-500">Comience agregando su primera publicación científica.</p>
    </div>
  </div>
</template>

<script setup>
import { 
  ChevronUp as ChevronUpIcon,
  ChevronDown as ChevronDownIcon,
  ChevronsUpDown as ChevronsUpDownIcon,
  ExternalLink as ExternalLinkIcon,
  Edit as EditIcon,
  Trash as TrashIcon,
  BookOpen as BookOpenIcon
} from 'lucide-vue-next'

// Props
const props = defineProps({
  publications: {
    type: Array,
    required: true
  },
  sortField: {
    type: String,
    required: true
  },
  sortDirection: {
    type: String,
    required: true,
    validator: (value) => ['asc', 'desc'].includes(value)
  }
})

// Emits
const emit = defineEmits(['sort', 'edit', 'delete'])

// Métodos
const getTypeLabel = (type) => {
  const labels = {
    journal: 'Revista',
    book: 'Libro',
    book_chapter: 'Capítulo'
  }
  return labels[type] || type
}

const getTypeBadgeClass = (type) => {
  const classes = {
    journal: 'bg-blue-100 text-blue-800',
    book: 'bg-green-100 text-green-800',
    book_chapter: 'bg-purple-100 text-purple-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

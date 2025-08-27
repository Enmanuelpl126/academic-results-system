<template>
  <!-- Contenedor principal de publicaciones -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Publicaciones Científicas</h2>
      <button
        @click="showForm = true"
        class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-700 transition-colors"
      >
        <PlusIcon :size="20" />
        Agregar Publicación
      </button>
    </div>

    <!-- Filtros -->
    <div class="mb-6 space-y-4">
      <div class="flex flex-col sm:flex-row flex-wrap gap-4">
        <div class="w-full sm:flex-1 min-w-[200px]">
          <div class="relative">
            <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" :size="20" />
            <input
              type="text"
              placeholder="Buscar publicaciones..."
              v-model="searchQuery"
              class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
          <select
            v-model="typeFilter"
            class="w-full sm:w-auto border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="all">Todos los Tipos</option>
            <option value="journal">Revista</option>
            <option value="book">Libro</option>
            <option value="book_chapter">Capítulo de Libro</option>
          </select>
          <select
            v-model="yearFilter"
            class="w-full sm:w-auto border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option v-for="year in years" :key="year" :value="year">
              {{ year === 'all' ? 'Todos los Años' : year }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Modal de formulario -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl p-4 sm:p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl sm:text-2xl font-bold text-gray-900">
            {{ editingId ? 'Editar Publicación' : 'Agregar Nueva Publicación' }}
          </h3>
          <button @click="closeForm" class="text-gray-500 hover:text-gray-700 transition-colors">
            <XIcon :size="24" />
          </button>
        </div>
        
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Campos básicos -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">
                <div class="flex items-center gap-2">
                  <BookOpenIcon :size="16" class="text-gray-500" />
                  Nombre de Publicación <span class="text-red-500">*</span>
                </div>
              </label>
              <input
                type="text"
                v-model="formData.name"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                placeholder="Ingrese el nombre de la publicación"
                required
              />
            </div>

            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">
                <div class="flex items-center gap-2">
                  <CalendarIcon :size="16" class="text-gray-500" />
                  Fecha <span class="text-red-500">*</span>
                </div>
              </label>
              <input
                type="date"
                v-model="formData.date"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                required
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">
                <div class="flex items-center gap-2">
                  <UsersIcon :size="16" class="text-gray-500" />
                  Autores (Profesores) <span class="text-red-500">*</span>
                </div>
              </label>
              <input
                type="text"
                v-model="formData.authors"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                placeholder="Ingrese los autores (separados por comas)"
                required
              />
            </div>

            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">
                <div class="flex items-center gap-2">
                  <BookOpenIcon :size="16" class="text-gray-500" />
                  Tipo de Publicación <span class="text-red-500">*</span>
                </div>
              </label>
              <select
                v-model="formData.type"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                required
              >
                <option value="journal">Revista</option>
                <option value="book">Libro</option>
                <option value="book_chapter">Capítulo de Libro</option>
              </select>
            </div>
          </div>

          <!-- Campos específicos por tipo -->
          <PublicationTypeFields :type="formData.type" v-model="formData" />

          <!-- Botones -->
          <div class="flex flex-col sm:flex-row justify-end gap-4 pt-4">
            <button
              type="button"
              @click="closeForm"
              class="w-full sm:w-auto px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
            >
              {{ editingId ? 'Actualizar Publicación' : 'Guardar Publicación' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Tabla de publicaciones -->
    <PublicationsTable 
      :publications="filteredAndSortedPublications"
      :sortField="sortField"
      :sortDirection="sortDirection"
      @sort="handleSort"
      @edit="handleEdit"
      @delete="handleDelete"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { 
  Plus as PlusIcon, 
  X as XIcon, 
  Search as SearchIcon, 
  BookOpen as BookOpenIcon, 
  Users as UsersIcon, 
  Calendar as CalendarIcon 
} from 'lucide-vue-next'
import PublicationTypeFields from './PublicationTypeFields.vue'
import PublicationsTable from './PublicationsTable.vue'

// Datos mock
const mockPublications = [
  {
    id: '1',
    name: 'Técnicas Avanzadas de Aprendizaje Automático en Computación Cuántica',
    date: '2024-02-15',
    authors: ['Dr. Juan Pérez', 'Dra. María García', 'Dr. Roberto Martínez'],
    type: 'journal',
    number: '3',
    volume: '15',
    doi: '10.1234/jqc.2024.1234',
    url: 'https://example.com/journal/article'
  }
]

// Estado reactivo
const publications = ref([...mockPublications])
const showForm = ref(false)
const editingId = ref(null)
const searchQuery = ref('')
const sortField = ref('date')
const sortDirection = ref('desc')
const typeFilter = ref('all')
const yearFilter = ref('all')

const formData = ref({
  name: '',
  date: '',
  authors: '',
  type: 'journal',
  number: '',
  volume: '',
  url: '',
  doi: '',
  publisher: '',
  city: '',
  chapterName: '',
  bookName: '',
  bookAuthor: ''
})

// Computed
const years = computed(() => 
  ['all', ...new Set(publications.value.map(p => new Date(p.date).getFullYear().toString()))].sort((a, b) => b.localeCompare(a))
)

const filteredAndSortedPublications = computed(() => {
  return publications.value
    .filter(publication => {
      const matchesSearch = 
        publication.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        publication.authors.some(author => 
          author.toLowerCase().includes(searchQuery.value.toLowerCase())
        )

      const matchesType = typeFilter.value === 'all' || publication.type === typeFilter.value
      const matchesYear = yearFilter.value === 'all' || new Date(publication.date).getFullYear().toString() === yearFilter.value

      return matchesSearch && matchesType && matchesYear
    })
    .sort((a, b) => {
      let comparison = 0
      if (sortField.value === 'date') {
        comparison = new Date(a.date).getTime() - new Date(b.date).getTime()
      } else {
        comparison = a[sortField.value].localeCompare(b[sortField.value])
      }
      return sortDirection.value === 'asc' ? comparison : -comparison
    })
})

// Métodos
const handleSort = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'asc'
  }
}

const handleSubmit = () => {
  const publication = {
    id: editingId.value || Date.now().toString(),
    name: formData.value.name,
    date: formData.value.date,
    authors: formData.value.authors.split(',').map(author => author.trim()),
    type: formData.value.type,
    ...(formData.value.type === 'journal' && {
      number: formData.value.number,
      volume: formData.value.volume,
      url: formData.value.url || undefined,
      doi: formData.value.doi || undefined
    }),
    ...(formData.value.type === 'book' && {
      publisher: formData.value.publisher,
      city: formData.value.city
    }),
    ...(formData.value.type === 'book_chapter' && {
      chapterName: formData.value.chapterName,
      bookName: formData.value.bookName,
      bookAuthor: formData.value.bookAuthor,
      publisher: formData.value.publisher
    })
  }

  if (editingId.value) {
    const index = publications.value.findIndex(p => p.id === editingId.value)
    if (index !== -1) {
      publications.value[index] = publication
    }
  } else {
    publications.value.push(publication)
  }

  closeForm()
}

const handleEdit = (publication) => {
  formData.value = {
    name: publication.name,
    date: publication.date,
    authors: publication.authors.join(', '),
    type: publication.type,
    number: publication.number || '',
    volume: publication.volume || '',
    url: publication.url || '',
    doi: publication.doi || '',
    publisher: publication.publisher || '',
    city: publication.city || '',
    chapterName: publication.chapterName || '',
    bookName: publication.bookName || '',
    bookAuthor: publication.bookAuthor || ''
  }
  editingId.value = publication.id
  showForm.value = true
}

const handleDelete = (id) => {
  if (confirm('¿Está seguro de que desea eliminar esta publicación?')) {
    publications.value = publications.value.filter(p => p.id !== id)
  }
}

const closeForm = () => {
  showForm.value = false
  editingId.value = null
  formData.value = {
    name: '',
    date: '',
    authors: '',
    type: 'journal',
    number: '',
    volume: '',
    url: '',
    doi: '',
    publisher: '',
    city: '',
    chapterName: '',
    bookName: '',
    bookAuthor: ''
  }
}
</script>

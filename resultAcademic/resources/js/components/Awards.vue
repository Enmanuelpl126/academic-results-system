<template>
  <!-- Contenedor principal de la página de premios -->
  <div class="w-full px-2 sm:px-4 lg:px-8 py-8">
    <!-- Encabezado con título y botón de agregar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Awards</h2>
      <button
        @click="showForm = true"
        class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-700 transition-colors"
      >
        <PlusIcon :size="20" />
        Añadir Premio
      </button>
    </div>

    <!-- Controles de filtrado y búsqueda -->
    <div class="mb-6 space-y-4">
      <div class="flex flex-col sm:flex-row flex-wrap gap-4">
        <!-- Barra de búsqueda -->
        <div class="w-full sm:flex-1 min-w-[200px]">
          <div class="relative">
            <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" :size="20" />
            <input
              type="text"
              placeholder="Search awards..."
              v-model="searchQuery"
              class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>
        <!-- Filtro por tipo -->
        <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
          <select
            v-model="typeFilter"
            class="w-full sm:w-auto border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option v-for="type in awardTypes" :key="type" :value="type">
              {{ type === 'all' ? 'All Types' : type.charAt(0).toUpperCase() + type.slice(1) }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Modal de formulario -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl p-4 sm:p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl sm:text-2xl font-bold text-gray-900">
            {{ editingId ? 'Editar Premio' : 'Agregar Premio' }}
          </h3>
          <button
            @click="closeForm"
            class="text-gray-500 hover:text-gray-700 transition-colors"
          >
            <XIcon :size="24" />
          </button>
        </div>
        
        <!-- Formulario -->
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Tipo de premio -->
            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">
                <div class="flex items-center gap-2">
                  <TrophyIcon :size="16" class="text-gray-500" />
                  Tipo de Premio <span class="text-red-500">*</span>
                </div>
              </label>
              <select
                v-model="formData.type"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                required
              >
                <option value="research">Investigación</option>
                <option value="innovation">Innovación</option>
                <option value="academic">Académico</option>
                <option value="industry">Industria</option>
              </select>
            </div>

            <!-- Fecha -->
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

          <!-- Autores -->
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
              placeholder="Ingrese los autores separados por comas"
              required
            />
          </div>

          <!-- Botones del formulario -->
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
              {{ editingId ? 'Actualizar Premio' : 'Guardar Premio' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Tabla de premios (estilo similar a PublicationsTable.vue) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden w-full">
      <div class="overflow-x-auto">
        <table class="min-w-full table-auto divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th 
                @click="handleSort('type')"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors w-48"
              >
                <div class="flex items-center gap-2">
                  Tipo de Premio
                  <ChevronUpIcon v-if="sortField === 'type' && sortDirection === 'asc'" :size="16" class="text-blue-600" />
                  <ChevronDownIcon v-else-if="sortField === 'type' && sortDirection === 'desc'" :size="16" class="text-blue-600" />
                  <ChevronsUpDownIcon v-else :size="16" class="text-gray-400" />
                </div>
              </th>
              <th 
                @click="handleSort('date')"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors w-40"
              >
                <div class="flex items-center gap-2">
                  Fecha
                  <ChevronUpIcon v-if="sortField === 'date' && sortDirection === 'asc'" :size="16" class="text-blue-600" />
                  <ChevronDownIcon v-else-if="sortField === 'date' && sortDirection === 'desc'" :size="16" class="text-blue-600" />
                  <ChevronsUpDownIcon v-else :size="16" class="text-gray-400" />
                </div>
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/2">
                Autores
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr 
              v-for="award in filteredAndSortedAwards" 
              :key="award.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <!-- Tipo con badge -->
              <td class="px-6 py-4">
                <span :class="getTypeBadgeClasses(award.type)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ getTypeLabel(award.type) }}
                </span>
              </td>

              <!-- Fecha -->
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ formatDate(award.date) }}
              </td>

              <!-- Autores -->
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">
                  <div v-if="award.authors && award.authors.length <= 2">
                    {{ award.authors.join(', ') }}
                  </div>
                  <div v-else-if="award.authors && award.authors.length > 2">
                    {{ award.authors[0] }}
                    <span class="text-gray-500">y {{ award.authors.length - 1 }} más</span>
                  </div>
                  <div v-else class="text-gray-400">Sin autores</div>
                </div>
              </td>

              <!-- Acciones -->
              <td class="px-6 py-4 text-right text-sm font-medium">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="handleEdit(award)"
                    class="text-indigo-600 hover:text-indigo-900 transition-colors"
                    title="Editar premio"
                  >
                    <Edit2Icon :size="16" />
                  </button>
                  <button
                    @click="handleDelete(award.id)"
                    class="text-red-600 hover:text-red-900 transition-colors"
                    title="Eliminar premio"
                  >
                    <Trash2Icon :size="16" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Estado vacío -->
      <div v-if="filteredAndSortedAwards.length === 0" class="text-center py-12">
        <TrophyIcon :size="48" class="mx-auto text-gray-400 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay premios</h3>
        <p class="text-gray-500">Comience agregando su primer premio.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
// Importaciones principales
import { ref, computed } from 'vue'
import { 
  Plus as PlusIcon, 
  Edit2 as Edit2Icon, 
  Trash2 as Trash2Icon, 
  X as XIcon, 
  Search as SearchIcon, 
  ChevronUp as ChevronUpIcon, 
  ChevronDown as ChevronDownIcon, 
  ChevronsUpDown as ChevronsUpDownIcon,
  Trophy as TrophyIcon, 
  Building as BuildingIcon, 
  Calendar as CalendarIcon, 
  FileText as FileTextIcon,
  Users as UsersIcon 
} from 'lucide-vue-next'

// Datos mock iniciales
const mockAwards = [
  {
    id: '1',
    title: 'Best Research Paper Award',
    name: 'Best Research Paper Award',
    type: 'research',
    date: '2024-02-15',
    description: 'Awarded for groundbreaking research in quantum computing applications.',
    authors: ['Dr. Juan Pérez', 'Dra. María García']
  },
  {
    id: '2',
    title: 'Innovation Excellence Award',
    name: 'Innovation Excellence Award',
    type: 'innovation',
    date: '2023-11-20',
    description: 'Recognition for innovative solutions in sustainable technology.',
    authors: ['Dr. Roberto Martínez']
  }
]

// Estado reactivo
const awards = ref([...mockAwards])
const showForm = ref(false)
const editingId = ref(null)
const searchQuery = ref('')
const sortField = ref('date')
const sortDirection = ref('desc')
const typeFilter = ref('all')

// Datos del formulario
const formData = ref({
  type: 'research',
  date: '',
  authors: ''
})

// Tipos de premios disponibles
const awardTypes = ['all', 'research', 'innovation', 'academic', 'industry']

// Computed: Lista filtrada y ordenada de premios
const filteredAndSortedAwards = computed(() => {
  return awards.value
    .filter(award => {
      const q = searchQuery.value.toLowerCase()
      const nameMatch = (award.name || award.title || '').toLowerCase().includes(q)
      const descMatch = (award.description || '').toLowerCase().includes(q)
      const authorsMatch = Array.isArray(award.authors) && award.authors.some(a => a.toLowerCase().includes(q))

      const matchesSearch = nameMatch || descMatch || authorsMatch
      const matchesType = typeFilter.value === 'all' || award.type === typeFilter.value

      return matchesSearch && matchesType
    })
    .sort((a, b) => {
      let comparison = 0
      if (sortField.value === 'date') {
        comparison = new Date(a.date).getTime() - new Date(b.date).getTime()
      } else {
        comparison = String(a[sortField.value]).localeCompare(String(b[sortField.value]))
      }
      return sortDirection.value === 'asc' ? comparison : -comparison
    })
})

// Métodos
// Maneja el ordenamiento por campo
const handleSort = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'asc'
  }
}

// Maneja el envío del formulario (crear/editar)
const handleSubmit = () => {
  const payload = {
    type: formData.value.type,
    date: formData.value.date,
    authors: formData.value.authors.split(',').map(a => a.trim()).filter(Boolean)
  }

  if (editingId.value) {
    const index = awards.value.findIndex(a => a.id === editingId.value)
    if (index !== -1) {
      awards.value[index] = { ...awards.value[index], ...payload }
    }
  } else {
    awards.value.push({ id: Date.now().toString(), ...payload })
  }

  closeForm()
}

// Maneja la edición de un premio
const handleEdit = (award) => {
  formData.value = {
    type: award.type,
    date: award.date,
    authors: Array.isArray(award.authors) ? award.authors.join(', ') : (award.authors || '')
  }
  editingId.value = award.id
  showForm.value = true
}

// Maneja la eliminación de un premio
const handleDelete = (id) => {
  if (confirm('Are you sure you want to delete this award?')) {
    awards.value = awards.value.filter(a => a.id !== id)
  }
}

// Cierra el formulario y resetea el estado
const closeForm = () => {
  showForm.value = false
  editingId.value = null
  formData.value = {
    type: 'research',
    date: '',
    authors: ''
  }
}

// Formatea una fecha para mostrar
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

// Obtiene las clases CSS para el badge del tipo de premio
const getTypeBadgeClasses = (type) => {
  const colors = {
    research: 'bg-purple-100 text-purple-800',
    innovation: 'bg-blue-100 text-blue-800',
    academic: 'bg-green-100 text-green-800',
    industry: 'bg-orange-100 text-orange-800'
  }
  return `px-2 py-1 rounded-full text-xs font-medium ${colors[type]}`
}

// Traducción de tipo
const getTypeLabel = (type) => {
  const labels = {
    research: 'Investigación',
    innovation: 'Innovación',
    academic: 'Académico',
    industry: 'Industria'
  }
  return labels[type] || type
}
</script>

<template>
  <!-- Contenedor principal de la página de reconocimientos -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Encabezado con título y botón de agregar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Recognitions & Honors</h2>
      <button
        @click="showForm = true"
        class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-700 transition-colors"
      >
        <PlusIcon :size="20" />
        Add Recognition
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
              placeholder="Buscar reconocimientos..."
              v-model="searchQuery"
              class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>
        <!-- Filtros por tipo y año -->
        <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
          <select
            v-model="typeFilter"
            class="w-full sm:w-auto border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option v-for="type in types" :key="type" :value="type">
              {{ type === 'all' ? 'Todos los tipos' : type }}
            </option>
          </select>
          <select
            v-model="yearFilter"
            class="w-full sm:w-auto border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option v-for="year in years" :key="year" :value="year">
              {{ year === 'all' ? 'Todos los años' : year }}
            </option>
          </select>
        </div>
      </div>
      
      <!-- Botones de ordenamiento -->
      <div class="flex flex-wrap gap-4">
        <button
          @click="handleSort('name')"
          :class="getSortButtonClasses('name')"
        >
          Nombre 
          <ChevronUpIcon v-if="sortField === 'name' && sortDirection === 'asc'" :size="16" />
          <ChevronDownIcon v-else-if="sortField === 'name' && sortDirection === 'desc'" :size="16" />
        </button>
        <button
          @click="handleSort('type')"
          :class="getSortButtonClasses('type')"
        >
          Tipo 
          <ChevronUpIcon v-if="sortField === 'type' && sortDirection === 'asc'" :size="16" />
          <ChevronDownIcon v-else-if="sortField === 'type' && sortDirection === 'desc'" :size="16" />
        </button>
        <button
          @click="handleSort('date')"
          :class="getSortButtonClasses('date')"
        >
          Fecha 
          <ChevronUpIcon v-if="sortField === 'date' && sortDirection === 'asc'" :size="16" />
          <ChevronDownIcon v-else-if="sortField === 'date' && sortDirection === 'desc'" :size="16" />
        </button>
      </div>
    </div>

    <!-- Modal de formulario -->
    <div v-if="showForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl p-4 sm:p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl sm:text-2xl font-bold text-gray-900">
            {{ editingId ? 'Edit Recognition' : 'Add New Recognition' }}
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
          <!-- Nombre -->
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">
              <div class="flex items-center gap-2">
                <AwardIcon :size="16" class="text-gray-500" />
                Nombre
              </div>
            </label>
            <input
              type="text"
              v-model="formData.name"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
              placeholder="Ingrese el nombre"
              required
            />
          </div>

          <!-- Tipo -->
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">
              <div class="flex items-center gap-2">
                <AwardIcon :size="16" class="text-gray-500" />
                Tipo
              </div>
            </label>
            <input
              type="text"
              v-model="formData.type"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
              placeholder="Ingrese el tipo"
              required
            />
          </div>

          <!-- Autores -->
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">
              <div class="flex items-center gap-2">
                <UserCircleIcon :size="16" class="text-gray-500" />
                Autores
              </div>
            </label>
            <input
              type="text"
              v-model="formData.authors"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
              placeholder="Ingrese los autores"
              required
            />
          </div>

          <!-- Fecha -->
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">
              <div class="flex items-center gap-2">
                <AwardIcon :size="16" class="text-gray-500" />
                Fecha
              </div>
            </label>
            <input
              type="date"
              v-model="formData.date"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
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
              Cancel
            </button>
            <button
              type="submit"
              class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
            >
              {{ editingId ? 'Update Recognition' : 'Save Recognition' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Grid de reconocimientos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="recognition in filteredAndSortedRecognitions" :key="recognition.id" class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ recognition.name }}</h3>
            <p class="text-sm text-gray-600">{{ recognition.type }}</p>
            <p class="text-sm text-gray-600">{{ recognition.authors }}</p>
            <p class="text-sm text-gray-500">{{ formatDate(recognition.date) }}</p>
          </div>
          <div class="flex gap-2">
            <button
              @click="handleEdit(recognition)"
              class="text-blue-600 hover:text-blue-900"
            >
              <Edit2Icon :size="18" />
            </button>
            <button
              @click="handleDelete(recognition.id)"
              class="text-red-600 hover:text-red-900"
            >
              <Trash2Icon :size="18" />
            </button>
          </div>
        </div>
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
  Award as AwardIcon, 
  Search as SearchIcon, 
  ChevronUp as ChevronUpIcon, 
  ChevronDown as ChevronDownIcon, 
  UserCircle as UserCircleIcon
} from 'lucide-vue-next'

// Datos mock iniciales
const mockRecognitions = [
  {
    id: '1',
    name: 'Outstanding Research Contribution',
    type: 'Premio',
    authors: 'A. Einstein, N. Bohr',
    date: '2024-02-15'
  },
  {
    id: '2',
    name: 'Excellence in Innovation',
    type: 'Reconocimiento',
    authors: 'G. Hinton, Y. LeCun',
    date: '2023-11-10'
  }
]

// Estado reactivo
const recognitions = ref([...mockRecognitions])
const showForm = ref(false)
const editingId = ref(null)
const searchQuery = ref('')
const sortField = ref('date')
const sortDirection = ref('desc')
const typeFilter = ref('all')
const yearFilter = ref('all')

// Datos del formulario
const formData = ref({
  name: '',
  type: '',
  authors: '',
  date: ''
})

// Computed: Lista de tipos únicos para el filtro
const types = computed(() => 
  ['all', ...new Set(recognitions.value.map(r => r.type).filter(Boolean))]
)

// Computed: Lista de años únicos para el filtro
const years = computed(() => 
  ['all', ...new Set(recognitions.value.map(r => 
    new Date(r.date).getFullYear().toString()
  ))].sort((a, b) => b.localeCompare(a))
)

// Computed: Lista filtrada y ordenada de reconocimientos
const filteredAndSortedRecognitions = computed(() => {
  return recognitions.value
    .filter(recognition => {
      const q = searchQuery.value.toLowerCase()
      const matchesSearch = 
        (recognition.name || '').toLowerCase().includes(q) ||
        (recognition.type || '').toLowerCase().includes(q) ||
        (recognition.authors || '').toLowerCase().includes(q)

      const matchesType = typeFilter.value === 'all' || recognition.type === typeFilter.value
      const matchesYear = yearFilter.value === 'all' || new Date(recognition.date).getFullYear().toString() === yearFilter.value

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
  const recognition = {
    id: editingId.value || Date.now().toString(),
    ...formData.value
  }

  if (editingId.value) {
    const index = recognitions.value.findIndex(r => r.id === editingId.value)
    if (index !== -1) {
      recognitions.value[index] = recognition
    }
  } else {
    recognitions.value.push(recognition)
  }

  closeForm()
}

// Maneja la edición de un reconocimiento
const handleEdit = (recognition) => {
  formData.value = {
    name: recognition.name || '',
    type: recognition.type || '',
    authors: recognition.authors || '',
    date: recognition.date || ''
  }
  editingId.value = recognition.id
  showForm.value = true
}

// Maneja la eliminación de un reconocimiento
const handleDelete = (id) => {
  if (confirm('Are you sure you want to delete this recognition?')) {
    recognitions.value = recognitions.value.filter(r => r.id !== id)
  }
}

// Cierra el formulario y resetea el estado
const closeForm = () => {
  showForm.value = false
  editingId.value = null
  formData.value = {
    name: '',
    type: '',
    authors: '',
    date: ''
  }
}

// Formatea una fecha para mostrar
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

// Obtiene las clases CSS para los botones de ordenamiento
const getSortButtonClasses = (field) => {
  const baseClasses = 'flex items-center gap-1 px-3 py-1 rounded-lg'
  const activeClasses = sortField.value === field ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'
  return `${baseClasses} ${activeClasses}`
}
</script>

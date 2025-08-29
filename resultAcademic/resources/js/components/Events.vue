<template>
  <!-- Contenedor principal de la página de eventos -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Encabezado con título y botón de agregar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Events</h2>
      <button
        @click="showForm = true"
        class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-700 transition-colors"
      >
        <PlusIcon :size="20" />
        Add Event
      </button>
    </div>

    <!-- Controles de búsqueda y filtros -->
    <div class="mb-6 space-y-4">
      <div class="flex flex-col sm:flex-row flex-wrap gap-4">
        <!-- Barra de búsqueda -->
        <div class="w-full sm:flex-1 min-w-[200px]">
          <div class="relative">
            <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" :size="20" />
            <input
              type="text"
              placeholder="Buscar eventos..."
              v-model="searchQuery"
              class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
        </div>
        <!-- Filtro por categoría -->
        <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
          <select
            v-model="categoryFilter"
            class="w-full sm:w-auto border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option v-for="cat in categories" :key="cat" :value="cat">
              {{ cat === 'all' ? 'Todas las categorías' : cat }}
            </option>
          </select>
        </div>
        <!-- Filtro por año -->
        <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
          <select
            v-model="yearFilter"
            class="w-full sm:w-auto border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option v-for="y in years" :key="y" :value="y">
              {{ y === 'all' ? 'Todos los años' : y }}
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
            {{ editingId ? 'Edit Event' : 'Add New Event' }}
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
          <!-- Nombre del evento -->
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">
              <div class="flex items-center gap-2">
                <CalendarIcon :size="16" class="text-gray-500" />
                Nombre
              </div>
            </label>
            <input
              type="text"
              v-model="formData.name"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
              placeholder="Ingrese el nombre del evento"
              required
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Autores Ponencia -->
            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">
                <div class="flex items-center gap-2">
                  <UserCircleIcon :size="16" class="text-gray-500" />
                  Autores Ponencia
                </div>
              </label>
              <input
                type="text"
                v-model="formData.authors"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                placeholder="Ingrese los autores de la ponencia"
                required
              />
            </div>

            <!-- Categoria -->
            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">
                <div class="flex items-center gap-2">
                  <FileTextIcon :size="16" class="text-gray-500" />
                  Categoria
                </div>
              </label>
              <input
                type="text"
                v-model="formData.category"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                placeholder="Ingrese la categoría"
                required
              />
            </div>
          </div>

          <!-- Fecha -->
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">
              <div class="flex items-center gap-2">
                <CalendarIcon :size="16" class="text-gray-500" />
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
              {{ editingId ? 'Update Event' : 'Save Event' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Grid de eventos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="event in filteredAndSortedEvents" :key="event.id" class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-start mb-4">
          <div class="w-full">
            <h3 class="text-lg font-semibold text-gray-900">{{ event.name }}</h3>
            <div class="flex items-center gap-2 text-sm text-gray-600 mt-1">
              <UserCircleIcon :size="16" class="text-gray-400" />
              {{ event.authors }}
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600 mt-1">
              <FileTextIcon :size="16" class="text-gray-400" />
              {{ event.category }}
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mt-1">
              <CalendarIcon :size="16" class="text-gray-400" />
              {{ formatDate(event.date) }}
            </div>
          </div>
          <div class="flex gap-2 ml-3 shrink-0">
            <button
              @click="handleEdit(event)"
              class="text-blue-600 hover:text-blue-900"
            >
              <Edit2Icon :size="18" />
            </button>
            <button
              @click="handleDelete(event.id)"
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
  Calendar as CalendarIcon, 
  FileText as FileTextIcon, 
  Search as SearchIcon, 
  UserCircle as UserCircleIcon 
} from 'lucide-vue-next'

// Props recibidas desde la página Inertia
const props = defineProps({
  initialEvents: {
    type: Array,
    default: () => []
  }
})

// Estado reactivo: usar props.initialEvents; si vienen vacíos puedes cargar valores por defecto si lo deseas
const events = ref([...(props.initialEvents || [])])
const showForm = ref(false)
const editingId = ref(null)
const searchQuery = ref('')
const sortField = ref('date')
const sortDirection = ref('desc')
const categoryFilter = ref('all')
const yearFilter = ref('all')

// Datos del formulario
const formData = ref({
  name: '',
  authors: '',
  category: '',
  date: ''
})

// Categorías disponibles construidas desde los datos
const categories = computed(() => {
  const set = new Set(
    events.value
      .map(e => (e.category || '').trim())
      .filter(Boolean)
  )
  return ['all', ...Array.from(set)]
})

// Años disponibles construidos desde los datos
const years = computed(() => {
  const set = new Set(
    events.value
      .map(e => new Date(e.date))
      .filter(d => !isNaN(d))
      .map(d => d.getFullYear())
  )
  return ['all', ...Array.from(set).sort((a, b) => b - a)]
})

// Computed: Lista filtrada y ordenada de eventos
const filteredAndSortedEvents = computed(() => {
  return events.value
    .filter(event => {
      const q = searchQuery.value.toLowerCase()
      const matchesSearch = 
        event.name.toLowerCase().includes(q) ||
        (event.authors || '').toLowerCase().includes(q) ||
        (event.category || '').toLowerCase().includes(q)

      const matchesCategory = categoryFilter.value === 'all' || (event.category || '') === categoryFilter.value
      const eventYear = new Date(event.date).getFullYear()
      const matchesYear = yearFilter.value === 'all' || eventYear === yearFilter.value

      return matchesSearch && matchesCategory && matchesYear
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
  const event = {
    id: editingId.value || Date.now().toString(),
    ...formData.value
  }

  if (editingId.value) {
    const index = events.value.findIndex(e => e.id === editingId.value)
    if (index !== -1) {
      events.value[index] = event
    }
  } else {
    events.value.push(event)
  }

  closeForm()
}

// Maneja la edición de un evento
const handleEdit = (event) => {
  formData.value = {
    name: event.name || '',
    authors: event.authors || '',
    category: event.category || '',
    date: event.date || ''
  }
  editingId.value = event.id
  showForm.value = true
}

// Maneja la eliminación de un evento
const handleDelete = (id) => {
  if (confirm('Are you sure you want to delete this event?')) {
    events.value = events.value.filter(e => e.id !== id)
  }
}

// Cierra el formulario y resetea el estado
const closeForm = () => {
  showForm.value = false
  editingId.value = null
  formData.value = {
    name: '',
    authors: '',
    category: '',
    date: ''
  }
}

// Formatea una fecha para mostrar
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

// Obtiene el componente de icono según el tipo de evento
const getEventTypeIcon = (type) => {
  switch (type) {
    case 'conference':
      return CalendarIcon
    case 'workshop':
      return UserCircleIcon
    case 'seminar':
      return FileTextIcon
    default:
      return CalendarIcon
  }
}

// Obtiene las clases CSS para el icono del tipo de evento
const getEventTypeIconClass = (type) => {
  switch (type) {
    case 'conference':
      return 'text-blue-500'
    case 'workshop':
      return 'text-green-500'
    case 'seminar':
      return 'text-purple-500'
    default:
      return 'text-blue-500'
  }
}

// Obtiene las clases CSS para el badge del rol
const getRoleBadgeClasses = (role) => {
  const colors = {
    speaker: 'bg-blue-100 text-blue-800',
    attendee: 'bg-gray-100 text-gray-800',
    organizer: 'bg-green-100 text-green-800'
  }
  return `px-2 py-1 rounded-full text-xs font-medium ${colors[role]}`
}
</script>

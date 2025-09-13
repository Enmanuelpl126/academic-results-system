<template>
  <!-- Contenedor principal de la página de eventos -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Encabezado con título y botón de agregar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Eventos</h2>
      <button
        @click="openCreateForm"
        class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-700 transition-colors"
      >
        <PlusIcon :size="20" />
        Agregar Evento
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
        @click="handleSort('category')"
        :class="getSortButtonClasses('category')"
      >
        Categoria
        <ChevronUpIcon v-if="sortField === 'category' && sortDirection === 'asc'" :size="16" />
        <ChevronDownIcon v-else-if="sortField === 'category' && sortDirection === 'desc'" :size="16" />
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
            <!-- Autores (Usuarios del sistema) -->
            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">
                <div class="flex items-center gap-2">
                  <UsersIcon :size="16" class="text-gray-500" />
                  Autores (Usuarios del sistema) <span class="text-red-500">*</span>
                </div>
              </label>
              <!-- Chips seleccionados -->
              <div v-if="formData.authors.length" class="flex flex-wrap gap-2 mb-2">
                <span v-for="id in formData.authors" :key="id" class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full border border-blue-200">
                  {{ usersMap.get(id) ?? `ID ${id}` }}
                  <button type="button" class="text-blue-600 hover:text-blue-800" @click="removeUser(id)">
                    <XIcon :size="14" />
                  </button>
                </span>
              </div>
              <!-- Input de búsqueda -->
              <div class="relative" ref="userComboboxRef">
                <input
                  type="text"
                  v-model="userQuery"
                  @focus="showUserDropdown = true; ensureInitialSearch()"
                  @input="onUserQueryInput"
                  @keydown.esc.prevent="showUserDropdown = false"
                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                  placeholder="Buscar usuarios por nombre..."
                  autocomplete="off"
                />
                <!-- Dropdown resultados -->
                <div v-if="showUserDropdown" class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-auto">
                  <div v-if="usersLoading" class="px-3 py-2 text-gray-500 text-sm">Cargando...</div>
                  <template v-else>
                    <button
                      v-for="u in searchResults"
                      :key="u.id"
                      type="button"
                      class="w-full text-left px-3 py-2 hover:bg-gray-50 flex items-center justify-between"
                      @click="selectUser(u)"
                    >
                      <span>{{ u.name }}</span>
                      <span v-if="formData.authors.includes(u.id)" class="text-xs text-green-600">Seleccionado</span>
                    </button>
                    <button
                      v-if="nextUsersPageUrl"
                      type="button"
                      class="w-full text-center px-3 py-2 text-blue-600 hover:bg-blue-50 border-t"
                      @click="loadMoreUsers"
                    >
                      Cargar más
                    </button>
                    <div v-if="!searchResults.length && !usersLoading" class="px-3 py-2 text-gray-500 text-sm">Sin resultados</div>
                  </template>
                </div>
              </div>
            </div>

            <!-- Categoria -->
            <div class="space-y-1">
              <label class="block text-sm font-medium text-gray-700">
                <div class="flex items-center gap-2">
                  <FileTextIcon :size="16" class="text-gray-500" />
                  Categoria
                </div>
              </label>
              <select
                v-model="formData.category"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                required
              >
                <option value="" disabled>Seleccione una categoría</option>
                <option value="Institucional">Institucional</option>
                <option value="Municipal">Municipal</option>
                <option value="Territorial">Territorial</option>
                <option value="Internacional">Internacional</option>
                <option value="Nacional">Nacional</option>
              </select>
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
              {{ Array.isArray(event.authors) ? event.authors.join(', ') : (event.authors || '') }}
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
              v-if="event.can_edit"
              @click="handleEdit(event)"
              class="text-blue-600 hover:text-blue-900"
            >
              <Edit2Icon :size="18" />
            </button>
            <button
              v-if="canDelete"
              @click="openDeleteModal(event)"
              class="text-red-600 hover:text-red-900"
            >
              <Trash2Icon :size="18" />
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Estado vacío cuando no hay resultados -->
    <div v-if="!filteredAndSortedEvents.length" class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center text-gray-500">
      No hay eventos que coincidan con el criterio de búsqueda
      <span v-if="searchQuery">: "{{ searchQuery }}"</span>.
    </div>
    <!-- Modal de confirmación de eliminación -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 z-50 flex items-center justify-center"
      aria-modal="true"
      role="dialog"
    >
      <!-- Overlay -->
      <div
        class="absolute inset-0 bg-black/40"
        @click.self="closeDeleteModal"
      ></div>
      <!-- Contenido del modal -->
      <div class="relative z-10 w-full max-w-md bg-white rounded-xl shadow-lg border border-gray-200 p-6">
        <div class="flex items-start gap-3">
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Confirmar eliminación</h3>
            <p class="text-sm text-gray-600 mb-3">
              ¿Seguro que deseas eliminar este evento?
            </p>
            <div v-if="eventToDelete" class="text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg p-3">
              <div><span class="font-medium text-gray-900">Nombre:</span> {{ eventToDelete.name }}</div>
              <div><span class="font-medium text-gray-900">Fecha:</span> {{ formatDate(eventToDelete.date) }}</div>
              <div v-if="Array.isArray(eventToDelete.authors) && eventToDelete.authors.length"><span class="font-medium text-gray-900">Autores:</span> {{ eventToDelete.authors.join(', ') }}</div>
            </div>
          </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
          <button type="button" @click="closeDeleteModal" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancelar</button>
          <button v-if="canDelete" type="button" @click="confirmDelete" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Eliminar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Importaciones principales
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { 
  Plus as PlusIcon, 
  Edit2 as Edit2Icon, 
  Trash2 as Trash2Icon, 
  X as XIcon, 
  Calendar as CalendarIcon, 
  FileText as FileTextIcon, 
  Search as SearchIcon, 
  UserCircle as UserCircleIcon,
  Users as UsersIcon, 
  ChevronUp as ChevronUpIcon,
  ChevronDown as ChevronDownIcon
} from 'lucide-vue-next'

// Props recibidas desde la página Inertia
const props = defineProps({
  initialEvents: {
    type: Array,
    default: () => []
  },
  users: {
    type: Array,
    default: () => []
  }
})

// Estado reactivo: usar props.initialEvents; si vienen vacíos puedes cargar valores por defecto si lo deseas
const events = ref([...(props.initialEvents || [])])

// Mantener sincronizado el estado local cuando Inertia actualice las props
watch(
  () => props.initialEvents,
  (newVal) => {
    events.value = [...(newVal || [])]
  }
)
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
  authors: [], // se inicializa más abajo según currentUserId
  category: '',
  date: ''
})

// Usuario actual (para preseleccionar)
const page = usePage()
const currentUserId = page?.props?.auth?.user?.id ?? null
const currentUserName = page?.props?.auth?.user?.name ?? null

// Permisos
const canDelete = computed(() => {
  const perms = page?.props?.auth?.permissions || []
  if (!Array.isArray(perms)) return false
  return (
    perms.includes('delete_any_result') ||
    perms.includes('delete_department_result') ||
    perms.includes('delete_own_result')
  )
})

// ========== Autocomplete de usuarios ==========
const userQuery = ref('')
const usersLoading = ref(false)
const searchResults = ref([])
const showUserDropdown = ref(false)
const nextUsersPageUrl = ref(null)
const usersMap = new Map()
const userComboboxRef = ref(null)

// Semilla inicial del mapa con props.users y usuario actual
if (Array.isArray(props.users)) {
  props.users.forEach(u => usersMap.set(u.id, u.name))
}
if (currentUserId && currentUserName) {
  usersMap.set(currentUserId, currentUserName)
}

// Inicializar autores por defecto con el usuario autenticado
if (currentUserId) {
  formData.value.authors = [currentUserId]
}

let userSearchDebounce = null
const onUserQueryInput = () => {
  if (userSearchDebounce) clearTimeout(userSearchDebounce)
  userSearchDebounce = setTimeout(() => {
    fetchUsers(userQuery.value)
  }, 300)
}

const ensureInitialSearch = () => {
  if (!searchResults.value.length && !usersLoading.value) {
    fetchUsers('')
  }
}

const fetchUsers = async (q = '', url = null) => {
  try {
    usersLoading.value = true
    const endpoint = url || route('users.search', { q, per_page: 15 })
    const res = await fetch(endpoint)
    const data = await res.json()
    if (!url) {
      searchResults.value = data.data || []
    } else {
      searchResults.value = [...searchResults.value, ...(data.data || [])]
    }
    ;(data.data || []).forEach(u => usersMap.set(u.id, u.name))
    nextUsersPageUrl.value = data.next_page_url
  } catch (e) {
    // noop
  } finally {
    usersLoading.value = false
  }
}

const loadMoreUsers = () => {
  if (nextUsersPageUrl.value) {
    fetchUsers(userQuery.value, nextUsersPageUrl.value)
  }
}

const selectUser = (user) => {
  if (!formData.value.authors.includes(user.id)) {
    formData.value.authors.push(user.id)
    usersMap.set(user.id, user.name)
  }
  showUserDropdown.value = false
}

const removeUser = (id) => {
  formData.value.authors = formData.value.authors.filter(a => a !== id)
}

// Cierre por clic fuera
const handleClickOutside = (e) => {
  if (!showUserDropdown.value) return
  const el = userComboboxRef.value
  if (el && !el.contains(e.target)) {
    showUserDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside)
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

// Años disponibles construidos desde los datos (parse manual para evitar timezone)
const years = computed(() => {
  const toYear = (dateString) => {
    if (!dateString) return null
    const [y] = String(dateString).split('-')
    const yr = Number(y)
    return Number.isFinite(yr) ? yr : null
  }
  const set = new Set(
    events.value
      .map(e => toYear(e.date))
      .filter((yr) => yr !== null)
  )
  return ['all', ...Array.from(set).sort((a, b) => b - a)]
})

// Computed: Lista filtrada y ordenada de eventos
const filteredAndSortedEvents = computed(() => {
  return events.value
    .filter(event => {
      const q = searchQuery.value.toLowerCase()
      const authorsText = Array.isArray(event.authors)
        ? event.authors.join(' ').toLowerCase()
        : String(event.authors || '').toLowerCase()
      const nameText = String(event.name || '').toLowerCase()
      const categoryText = String(event.category || '').toLowerCase()
      const dateRaw = String(event.date || '')
      const dateText = String(formatDate(event.date) || '').toLowerCase()
      const matchesSearch = 
        nameText.includes(q) ||
        categoryText.includes(q) ||
        authorsText.includes(q) ||
        dateRaw.toLowerCase().includes(q) ||
        dateText.includes(q)

      const matchesCategory = categoryFilter.value === 'all' || (event.category || '') === categoryFilter.value
      const d = toLocalDateObj(event.date)
      const eventYear = d ? d.getFullYear() : null
      const matchesYear = yearFilter.value === 'all' || eventYear === yearFilter.value

      return matchesSearch && matchesCategory && matchesYear
    })
    .sort((a, b) => {
      let comparison = 0
      if (sortField.value === 'date') {
        const da = toLocalDateObj(a.date)
        const db = toLocalDateObj(b.date)
        const ta = da ? da.getTime() : 0
        const tb = db ? db.getTime() : 0
        comparison = ta - tb
      } else {
        comparison = String(a[sortField.value] || '').localeCompare(String(b[sortField.value] || ''))
      }
      return sortDirection.value === 'asc' ? comparison : -comparison
    })
})

// Métodos
// Maneja el ordenamiento por campo
// Abrir formulario de creación con el usuario autenticado preseleccionado
const openCreateForm = () => {
  editingId.value = null
  formData.value = {
    name: '',
    authors: currentUserId ? [currentUserId] : [],
    category: '',
    date: ''
  }
  showForm.value = true
}
// Maneja el ordenamiento por campo
const handleSort = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'asc'
  }
}

// Clases para botones de ordenamiento
const getSortButtonClasses = (field) => {
  const isActive = sortField.value === field
  const base = 'flex items-center gap-1 px-3 py-1 rounded-lg'
  return isActive
    ? `${base} bg-blue-100 text-blue-700`
    : `${base} bg-gray-100 text-gray-700`
}

// Maneja el envío del formulario (crear/editar)
const handleSubmit = () => {
  const payload = {
    name: formData.value.name,
    category: formData.value.category || null,
    date: formData.value.date || null,
    description: null, // El formulario actual no tiene description; dejar null por ahora
    authors: formData.value.authors,
  }

  if (editingId.value) {
    router.put(`/events/${editingId.value}`, payload, {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({
          only: ['events'],
          onSuccess: () => closeForm()
        })
      }
    })
  } else {
    router.post('/events', payload, {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({
          only: ['events'],
          onSuccess: () => closeForm()
        })
      }
    })
  }
}

// Maneja la edición de un evento
const handleEdit = (event) => {
  formData.value = {
    name: event.name || '',
    authors: Array.isArray(event.author_ids) ? [...event.author_ids] : [],
    category: event.category || '',
    date: event.date || ''
  }
  // Poblar mapa de nombres si vienen alineados (opcional)
  if (Array.isArray(event.authors) && Array.isArray(event.author_ids) && event.authors.length === event.author_ids.length) {
    for (let i = 0; i < event.author_ids.length; i++) {
      usersMap.set(event.author_ids[i], event.authors[i])
    }
  }
  editingId.value = event.id
  showForm.value = true
}

// Estado y lógica para modal de eliminación
const showDeleteModal = ref(false)
const eventToDelete = ref(null)

const openDeleteModal = (event) => {
  eventToDelete.value = event
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (!eventToDelete.value) return
  const id = eventToDelete.value.id
  router.delete(`/events/${id}`, {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ['events'] })
      closeDeleteModal()
    }
  })
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  eventToDelete.value = null
}

// Cierra el formulario y resetea el estado
const closeForm = () => {
  showForm.value = false
  editingId.value = null
  formData.value = {
    name: '',
    authors: currentUserId ? [currentUserId] : [],
    category: '',
    date: ''
  }
}

// Helpers de fecha (parse local para evitar desfase por timezone)
const toLocalDateObj = (dateString) => {
  if (!dateString) return null
  const [y, m, d] = String(dateString).split('-').map(Number)
  if (!Number.isFinite(y) || !Number.isFinite(m) || !Number.isFinite(d)) return null
  return new Date(y, m - 1, d)
}

// Formatea una fecha para mostrar
const formatDate = (dateString) => {
  const d = toLocalDateObj(dateString)
  return d ? d.toLocaleDateString() : ''
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

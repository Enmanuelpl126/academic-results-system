<template>
  <!-- Contenedor principal de la página de reconocimientos -->
  <div class="w-full min-h-screen px-4 sm:px-6 lg:px-8 py-8">
    <!-- Encabezado con título y botón de agregar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Recognitions & Honors</h2>
      <button
        @click="openCreateForm"
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
              v-model="form.name"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
              placeholder="Ingrese el nombre del reconocimiento"
              required
            />
            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
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
              v-model="form.type"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
              placeholder="Ingrese el tipo"
              required
            />
            <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
          </div>

          <!-- Autores (Usuarios del sistema) -->
          <div class="space-y-1">
            <label class="block text-sm font-medium text-gray-700">
              <div class="flex items-center gap-2">
                <UsersIcon :size="16" class="text-gray-500" />
                Autores (Usuarios del sistema) <span class="text-red-500">*</span>
              </div>
            </label>
            <!-- Chips seleccionados -->
            <div v-if="form.authors.length" class="flex flex-wrap gap-2 mb-2">
              <span v-for="id in form.authors" :key="id" class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full border border-blue-200">
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
                    <span v-if="form.authors.includes(u.id)" class="text-xs text-green-600">Seleccionado</span>
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
              v-model="form.date"
              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
            />
            <p v-if="form.errors.date" class="mt-1 text-sm text-red-600">{{ form.errors.date }}</p>
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
              :disabled="form.processing"
              :class="[
                'w-full sm:w-auto px-6 py-2.5 rounded-lg transition-colors font-medium',
                form.processing ? 'bg-blue-400 cursor-not-allowed text-white' : 'bg-blue-600 hover:bg-blue-700 text-white'
              ]"
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
            <p class="text-sm text-gray-600">{{ Array.isArray(recognition.authors) ? recognition.authors.join(', ') : (recognition.authors || '') }}</p>
            <p class="text-sm text-gray-500">{{ formatDate(recognition.date) }}</p>
          </div>
          <div class="flex gap-2" v-if="recognition.can_edit || canDelete">
            <button
              v-if="recognition.can_edit"
              @click="handleEdit(recognition)"
              class="text-blue-600 hover:text-blue-900"
            >
              <Edit2Icon :size="18" />
            </button>
            <button
              v-if="canDelete"
              @click="openDeleteModal(recognition)"
              class="text-red-600 hover:text-red-900"
            >
              <Trash2Icon :size="18" />
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Estado vacío cuando no hay resultados -->
    <div v-if="!filteredAndSortedRecognitions.length" class="mt-6 w-full bg-white border border-gray-200 rounded-xl p-6 text-center text-gray-600">
      No hay reconocimientos que coincidan con el criterio de búsqueda
      <span v-if="searchQuery">: "{{ searchQuery }}"</span>.
    </div>

    <!-- Paginación -->
    <div class="mt-4 flex justify-end">
      <Pagination :links="props.recognitions?.links || []" />
    </div>
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
            ¿Seguro que deseas eliminar este reconocimiento?
          </p>
          <div v-if="recognitionToDelete" class="text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg p-3">
            <div v-if="recognitionToDelete.name"><span class="font-medium text-gray-900">Nombre:</span> {{ recognitionToDelete.name }}</div>
            <div v-if="recognitionToDelete.type"><span class="font-medium text-gray-900">Tipo:</span> {{ recognitionToDelete.type }}</div>
            <div v-if="recognitionToDelete.date"><span class="font-medium text-gray-900">Fecha:</span> {{ formatDate(recognitionToDelete.date) }}</div>
            <div v-if="Array.isArray(recognitionToDelete.authors) && recognitionToDelete.authors.length"><span class="font-medium text-gray-900">Autores:</span> {{ recognitionToDelete.authors.join(', ') }}</div>
          </div>
        </div>
      </div>
      <div class="mt-6 flex justify-end gap-3">
        <button type="button" @click="closeDeleteModal" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancelar</button>
        <button v-if="canDelete" type="button" @click="confirmDelete" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Eliminar</button>
      </div>
    </div>
  </div>
</template>

<script setup>
// Importaciones principales
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { router, usePage, useForm } from '@inertiajs/vue3'
import Pagination from './Pagination.vue'
import { 
  Plus as PlusIcon, 
  Edit2 as Edit2Icon, 
  Trash2 as Trash2Icon, 
  X as XIcon, 
  Award as AwardIcon, 
  Search as SearchIcon, 
  ChevronUp as ChevronUpIcon, 
  ChevronDown as ChevronDownIcon, 
  UserCircle as UserCircleIcon,
  Users as UsersIcon
} from 'lucide-vue-next'

// Props desde la página Inertia
const props = defineProps({
  recognitions: {
    type: Object,
    default: () => ({ data: [], links: [] })
  },
  users: {
    type: Array,
    default: () => []
  }
})

// Estado reactivo usando datos del servidor
const recognitions = ref([...(props.recognitions?.data || [])])
watch(
  () => props.recognitions,
  (val) => {
    recognitions.value = [...(val?.data || [])]
  }
)
const showForm = ref(false)
const editingId = ref(null)
const searchQuery = ref('')
const sortField = ref('date')
const sortDirection = ref('desc')
const typeFilter = ref('all')
const yearFilter = ref('all')

// Usuario actual (para preseleccionar)
const page = usePage()
const currentUserId = page?.props?.auth?.user?.id ?? null
const currentUserName = page?.props?.auth?.user?.name ?? null

// Permisos
const canDelete = computed(() => {
  const perms = page?.props?.auth?.permissions || []
  return Array.isArray(perms) && perms.includes('delete_any_result')
})

// Datos del formulario
const form = useForm({
  name: '',
  type: '',
  date: '',
  description: null,
  authors: currentUserId ? [currentUserId] : [],
})

// ========== Autocomplete de usuarios (idéntico a Events.vue) ==========
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
  if (!form.authors.includes(user.id)) {
    form.authors.push(user.id)
    usersMap.set(user.id, user.name)
  }
  showUserDropdown.value = false
}

const removeUser = (id) => {
  form.authors = form.authors.filter(a => a !== id)
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

// Computed: Lista de tipos únicos para el filtro
const types = computed(() => 
  ['all', ...new Set(recognitions.value.map(r => r.type).filter(Boolean))]
)

// Computed: Lista de años únicos para el filtro
const years = computed(() => {
  const toYear = (dateString) => {
    if (!dateString) return null
    const [y] = String(dateString).split('-')
    const yr = Number(y)
    return Number.isFinite(yr) ? yr : null
  }
  const set = new Set(
    recognitions.value
      .map(r => toYear(r.date))
      .filter((yr) => yr !== null)
  )
  return ['all', ...Array.from(set).sort((a, b) => b - a)]
})

// Computed: Lista filtrada y ordenada de reconocimientos
const filteredAndSortedRecognitions = computed(() => {
  return recognitions.value
    .filter(recognition => {
      const q = searchQuery.value.toLowerCase()
      const authorsText = Array.isArray(recognition.authors)
        ? recognition.authors.join(' ').toLowerCase()
        : String(recognition.authors || '').toLowerCase()
      const nameText = String(recognition.name || '').toLowerCase()
      const typeText = String(recognition.type || '').toLowerCase()
      const dateRaw = String(recognition.date || '')
      const dateText = String(formatDate(recognition.date) || '').toLowerCase()
      const matchesSearch = 
        nameText.includes(q) ||
        typeText.includes(q) ||
        authorsText.includes(q) ||
        dateRaw.toLowerCase().includes(q) ||
        dateText.includes(q)

      const matchesType = typeFilter.value === 'all' || (recognition.type || '') === typeFilter.value
      const d = toLocalDateObj(recognition.date)
      const recYear = d ? d.getFullYear() : null
      const matchesYear = yearFilter.value === 'all' || recYear === yearFilter.value

      return matchesSearch && matchesType && matchesYear
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
const handleSort = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortField.value = field
    sortDirection.value = 'asc'
  }
}

// Abrir formulario de creación con el usuario autenticado preseleccionado
const openCreateForm = () => {
  editingId.value = null
  form.reset()
  form.clearErrors()
  form.name = ''
  form.type = ''
  form.date = ''
  form.description = null
  form.authors = currentUserId ? [currentUserId] : []
  showForm.value = true
}

// Maneja el envío del formulario (crear/editar)
const handleSubmit = () => {
  const options = {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ['recognitions'], onSuccess: () => closeForm() })
    },
    onError: () => {
      // Mantener el modal abierto para mostrar errores
      showForm.value = true
    },
  }

  if (!editingId.value) {
    form.post('/recognitions', options)
    return
  }

  form.put(route('recognitions.update', editingId.value), options)
}

// Maneja la edición de un reconocimiento
const handleEdit = (recognition) => {
  form.clearErrors()
  form.name = recognition.name || ''
  form.type = recognition.type || ''
  form.date = recognition.date || ''
  form.description = null
  form.authors = Array.isArray(recognition.author_ids) ? [...recognition.author_ids] : []
  // Poblar mapa de nombres si vienen alineados (opcional)
  if (Array.isArray(recognition.authors) && Array.isArray(recognition.author_ids) && recognition.authors.length === recognition.author_ids.length) {
    for (let i = 0; i < recognition.author_ids.length; i++) {
      usersMap.set(recognition.author_ids[i], recognition.authors[i])
    }
  }
  editingId.value = recognition.id
  showForm.value = true
}

// Estado y lógica para modal de eliminación
const showDeleteModal = ref(false)
const recognitionToDelete = ref(null)

const openDeleteModal = (recognition) => {
  recognitionToDelete.value = recognition
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  recognitionToDelete.value = null
}

const confirmDelete = () => {
  if (!recognitionToDelete.value) return
  const id = recognitionToDelete.value.id
  router.delete(route('recognitions.destroy', id), {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ['recognitions'] })
      closeDeleteModal()
    }
  })
}

// Cierra el formulario y resetea el estado
const closeForm = () => {
  showForm.value = false
  editingId.value = null
  form.reset()
  form.clearErrors()
  form.name = ''
  form.type = ''
  form.date = ''
  form.description = null
  form.authors = currentUserId ? [currentUserId] : []
}

// Formatea una fecha para mostrar
const toLocalDateObj = (dateString) => {
  if (!dateString) return null
  const [y, m, d] = String(dateString).split('-').map(Number)
  if (!Number.isFinite(y) || !Number.isFinite(m) || !Number.isFinite(d)) return null
  return new Date(y, m - 1, d)
}

const formatDate = (dateString) => {
  const d = toLocalDateObj(dateString)
  return d ? d.toLocaleDateString() : ''
}

// Obtiene las clases CSS para los botones de ordenamiento
const getSortButtonClasses = (field) => {
  const baseClasses = 'flex items-center gap-1 px-3 py-1 rounded-lg'
  const activeClasses = sortField.value === field ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'
  return `${baseClasses} ${activeClasses}`
}
</script>

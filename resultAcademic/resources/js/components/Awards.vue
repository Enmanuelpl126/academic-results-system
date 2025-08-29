<template>
  <!-- Contenedor principal de la página de premios -->
  <div class="w-full px-2 sm:px-4 lg:px-8 py-8">
    <!-- Encabezado con título y botón de agregar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Premios</h2>
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
                v-model="form.type"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                required
              >
                <option value="Academia de Ciencias de Cuba">Academia de Ciencias de Cuba</option>
                <option value="CITMA Provincial">CITMA Provincial</option>
              </select>
              <p v-if="form.errors.type" class="text-sm text-red-600 mt-1">{{ form.errors.type }}</p>
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
                v-model="form.date"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
                required
              />
              <p v-if="form.errors.date" class="text-sm text-red-600 mt-1">{{ form.errors.date }}</p>
            </div>
          </div>

          <!-- Autores (combobox con búsqueda) -->
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
            <p v-if="form.errors.authors" class="text-sm text-red-600 mt-1">{{ form.errors.authors }}</p>
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
              :disabled="form.processing"
              class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
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
                  <div v-if="award.authors && award.authors.length">
                    {{ award.authors.join(', ') }}
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
                    @click="openDeleteModal(award)"
                    class="text-red-600 hover:text-red-900 transition-colors"
                    title="Eliminar premio"
                  >
                    <Trash2Icon :size="16" />
                  </button>
                </div>
              </td>
            </tr>
            <!-- Estado vacío cuando no hay resultados -->
            <tr v-if="!filteredAndSortedAwards.length">
              <td colspan="4" class="px-6 py-6 text-center text-gray-500">
                No hay premios que coincidan con el criterio de búsqueda
                <span v-if="searchQuery">: "{{ searchQuery }}"</span>.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
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
          ¿Seguro que deseas eliminar este premio?
        </p>
        <div v-if="awardToDelete" class="text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg p-3">
          <div><span class="font-medium text-gray-900">Tipo:</span> {{ awardToDelete.type }}</div>
          <div><span class="font-medium text-gray-900">Fecha:</span> {{ formatDate(awardToDelete.date) }}</div>
          <div v-if="awardToDelete.authors && awardToDelete.authors.length"><span class="font-medium text-gray-900">Autores:</span> {{ awardToDelete.authors.join(', ') }}</div>
        </div>
      </div>
    </div>
    <div class="mt-6 flex justify-end gap-3">
      <button type="button" @click="closeDeleteModal" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancelar</button>
      <button type="button" @click="confirmDelete" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Eliminar</button>
    </div>
  </div>
</div>
</template>

<script setup>
// Importaciones principales
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
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

// Props desde el padre (página Inertia)
const props = defineProps({
  awards: {
    type: Array,
    default: () => []
  },
  users: {
    type: Array,
    default: () => []
  }
})

// Estado reactivo
// Creamos un estado local a partir de la prop para permitir ediciones locales
const awards = ref([...(props.awards || [])])
const showForm = ref(false)
const editingId = ref(null)
const searchQuery = ref('')
const sortField = ref('date')
const sortDirection = ref('desc')
const typeFilter = ref('all')

// Obtener usuario actual
const page = usePage()
const currentUserId = page?.props?.auth?.user?.id ?? null
const currentUserName = page?.props?.auth?.user?.name ?? null

// Formulario con useForm (se envían type, date y authors)
const form = useForm({
  type: 'Academia de Ciencias de Cuba',
  date: '',
  authors: currentUserId ? [currentUserId] : []
})

// Campo local de autores ya controlado en form.authors; se elimina authorsInput

// Sincroniza el estado local cuando cambian los premios recibidos por props (paginación)
watch(
  () => props.awards,
  (newVal) => {
    awards.value = [...(newVal || [])]
  }
)

// Tipos de premios disponibles (para filtros)
const awardTypes = ['all', 'Academia de Ciencias de Cuba', 'CITMA Provincial']

// Computed: Lista filtrada y ordenada de premios
const filteredAndSortedAwards = computed(() => {
  return awards.value
    .filter(award => {
      const q = searchQuery.value.toLowerCase()
      const nameMatch = (award.name || award.title || '').toLowerCase().includes(q)
      const descMatch = (award.description || '').toLowerCase().includes(q)
      const authorsMatch = Array.isArray(award.authors) && award.authors.some(a => a.toLowerCase().includes(q))
      const typeMatch = String(award.type || '').toLowerCase().includes(q)
      const dateMatch = String(award.date || '').toLowerCase().includes(q)

      const matchesSearch = nameMatch || descMatch || authorsMatch || typeMatch || dateMatch
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
  // Cerrar dropdown antes de enviar
  showUserDropdown.value = false
  if (editingId.value) {
    form.put(route('awards.update', editingId.value), {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['awards'] })
        closeForm()
      }
    })
    return
  }

  form.post(route('awards.store'), {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ['awards'] })
      closeForm()
    }
  })
}

// Maneja la edición de un premio
const handleEdit = (award) => {
  form.type = award.type
  // Normalizar fecha a formato YYYY-MM-DD para el input date
  form.date = award?.date ? String(award.date).slice(0, 10) : ''
  // Cargar ids de autores
  form.authors = Array.isArray(award.author_ids) ? [...award.author_ids] : []
  // Poblar mapa de nombres desde el award si vienen alineados
  if (Array.isArray(award.authors) && Array.isArray(award.author_ids) && award.authors.length === award.author_ids.length) {
    for (let i = 0; i < award.author_ids.length; i++) {
      usersMap.set(award.author_ids[i], award.authors[i])
    }
  }
  editingId.value = award.id
  showForm.value = true
}

// Estado y lógica para modal de eliminación
const showDeleteModal = ref(false)
const awardToDelete = ref(null)

const openDeleteModal = (award) => {
  awardToDelete.value = award
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (!awardToDelete.value) return
  const id = awardToDelete.value.id
  router.delete(route('awards.destroy', id), {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: ['awards'] })
      closeDeleteModal()
    },
    onFinish: () => {
      // Asegurar cierre incluso si hay error (podemos mantener abierto si prefieres)
    }
  })
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  awardToDelete.value = null
}

// Cierra el formulario y resetea el estado
const closeForm = () => {
  showForm.value = false
  editingId.value = null
  form.reset()
  form.clearErrors()
  form.type = 'Academia de Ciencias de Cuba'
  form.authors = currentUserId ? [currentUserId] : []
  showUserDropdown.value = false
}

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
    // Poblar mapa de nombres
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
  // Mantener abierto o cerrar; cerramos para evitar bloquear la UI
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

const handleGlobalKeydown = (e) => {
  if (e.key === 'Escape' && showDeleteModal.value) {
    closeDeleteModal()
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
  document.addEventListener('keydown', handleGlobalKeydown)
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside)
  document.removeEventListener('keydown', handleGlobalKeydown)
})

// Formatea fecha como DD-MM-YYYY evitando timezone
const formatDate = (dateString) => {
  if (!dateString) return ''
  // Tomar solo la parte de fecha (YYYY-MM-DD)
  const s = String(dateString).slice(0, 10)
  const [y, m, d] = s.split('-')
  if (y && m && d) return `${d}-${m}-${y}`
  // Fallback
  return dateString
}

// Obtiene las clases CSS para el badge del tipo de premio
const getTypeBadgeClasses = (type) => {
  const colors = {
    'Academia de Ciencias de Cuba': 'bg-purple-100 text-purple-800',
    'CITMA Provincial': 'bg-blue-100 text-blue-800',
  }
  return `px-2 py-1 rounded-full text-xs font-medium ${colors[type] || 'bg-gray-100 text-gray-800'}`
}

// Traducción de tipo
const getTypeLabel = (type) => {
  const labels = {
    'Academia de Ciencias de Cuba': 'Academia de Ciencias de Cuba',
    'CITMA Provincial': 'CITMA Provincial',
  }
  return labels[type] || type
}
</script>

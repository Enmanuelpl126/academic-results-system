<template>
  <!-- Contenedor principal de publicaciones -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Publicaciones Científicas</h2>
      <button
        v-if="canCreate"
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
            <option value="Revista">Revista</option>
            <option value="Libro">Libro</option>
            <option value="Capitulo de Libro">Capítulo de Libro</option>
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
                  Autores (Usuarios del sistema) <span class="text-red-500">*</span>
                </div>
              </label>
              <!-- Chips seleccionados -->
              <div v-if="selectedAuthors.length" class="flex flex-wrap gap-2 mb-2">
                <span v-for="id in selectedAuthors" :key="id" class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full border border-blue-200">
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
                  @keydown.tab.prevent="handleUserTabAway"
                  @blur="handleUserInputBlur"
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
                      @mousedown.prevent="selectUser(u)"
                    >
                      <span>{{ u.name }}</span>
                      <span v-if="selectedAuthors.includes(u.id)" class="text-xs text-green-600">Seleccionado</span>
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

  <!-- Modal de confirmación de eliminación (igual a Awards.vue en estructura y textos) -->
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
            ¿Seguro que deseas eliminar esta publicación?
          </p>
          <div v-if="publicationToDelete" class="text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg p-3">
            <div v-if="publicationToDelete.name"><span class="font-medium text-gray-900">Nombre:</span> {{ publicationToDelete.name }}</div>
            <div v-if="publicationToDelete.type"><span class="font-medium text-gray-900">Tipo:</span> {{ publicationToDelete.type }}</div>
            <div v-if="publicationToDelete.date"><span class="font-medium text-gray-900">Fecha:</span> {{ formatDate(publicationToDelete.date) }}</div>
            <div v-if="publicationToDelete.authors && publicationToDelete.authors.length"><span class="font-medium text-gray-900">Autores:</span> {{ publicationToDelete.authors.join(', ') }}</div>
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
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
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

// Props y estado desde Inertia (backend)
const props = defineProps({
  users: {
    type: Array,
    default: () => []
  }
})
const page = usePage()
const publications = ref(page?.props?.publications?.data ?? [])
// Sincronizar cuando cambie la paginación/datos en props
watch(
  () => page?.props?.publications,
  (val) => {
    publications.value = val?.data ?? []
  },
  { immediate: false }
)
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
  // authorIds gestionado aparte
  type: 'journal',
  magazineName: '',
  number: '',
  volume: '',
  doi: '',
  publisher: '',
  city: '',
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

// Permiso de eliminación desde props de Inertia (any/department/own)
const canDelete = computed(() => {
  const perms = page?.props?.auth?.permissions || []
  if (!Array.isArray(perms)) return false
  return (
    perms.includes('delete_any_result') ||
    perms.includes('delete_department_result') ||
    perms.includes('delete_own_result')
  )
})

// Permiso de creación
const canCreate = computed(() => {
  const perms = page?.props?.auth?.permissions || []
  return Array.isArray(perms) && perms.includes('create_result')
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

// ====== Autores: estado y lógica básica para el combobox ======
const currentUserId = page?.props?.auth?.user?.id ?? null
const currentUserName = page?.props?.auth?.user?.name ?? null
const authorIds = ref([])
const selectedAuthors = computed(() => authorIds.value || [])

// Mapa id -> nombre para chips
const usersMap = new Map()

// Estado del combobox
const userQuery = ref('')
const showUserDropdown = ref(false)
const usersLoading = ref(false)
const searchResults = ref(Array.isArray(props.users) ? [...props.users] : [])
const nextUsersPageUrl = ref(null)
const userComboboxRef = ref(null)

// Inicializa resultados si están vacíos al enfocar
const ensureInitialSearch = () => {
  if (!searchResults.value?.length && Array.isArray(props.users)) {
    searchResults.value = [...props.users]
  }
}

// Búsqueda local simple sobre props.users (fallback rápido)
const onUserQueryInput = () => {
  const q = userQuery.value?.toLowerCase?.() ?? ''
  if (!q) {
    searchResults.value = Array.isArray(props.users) ? [...props.users] : []
    return
  }
  const base = Array.isArray(props.users) ? props.users : []
  searchResults.value = base.filter(u => (u.name || '').toLowerCase().includes(q))
}

const selectUser = (u) => {
  if (!u || u.id == null) return
  if (!authorIds.value.includes(u.id)) {
    authorIds.value = [...authorIds.value, u.id]
  }
  if (u.name) usersMap.set(u.id, u.name)
  showUserDropdown.value = false
  // Limpiar la búsqueda y quitar foco del input para evitar comportamientos raros
  userQuery.value = ''
  // Restaurar resultados por defecto
  searchResults.value = Array.isArray(props.users) ? [...props.users] : []
  nextTick(() => {
    const el = userComboboxRef.value?.querySelector('input')
    if (el && typeof el.blur === 'function') {
      el.blur()
    }
  })
}

const removeUser = (id) => {
  authorIds.value = authorIds.value.filter(x => x !== id)
}

const loadMoreUsers = () => {
  // Placeholder: si más adelante hay API, aquí se implementa la paginación
  nextUsersPageUrl.value = null
}

const handleUserInputBlur = () => {
  // Si el blur no proviene de seleccionar (cubierto por mousedown), limpiar
  showUserDropdown.value = false
  userQuery.value = ''
  searchResults.value = Array.isArray(props.users) ? [...props.users] : []
}

const handleUserTabAway = () => {
  showUserDropdown.value = false
  userQuery.value = ''
  searchResults.value = Array.isArray(props.users) ? [...props.users] : []
  // dejar que el tab continúe hacia el siguiente elemento
}

// Cerrar dropdown al hacer click fuera del combobox
let __onDocPointer = null
let __onDocFocus = null
onMounted(() => {
  // Prefijar el usuario autenticado como autor por defecto
  if (currentUserId != null) {
    authorIds.value = [currentUserId]
    const fromProps = Array.isArray(props.users) ? props.users.find(u => u.id === currentUserId) : null
    const name = fromProps?.name || currentUserName || `ID ${currentUserId}`
    usersMap.set(currentUserId, name)
  }
  __onDocPointer = (e) => {
    const el = userComboboxRef.value
    if (!el) return
    if (!el.contains(e.target)) {
      showUserDropdown.value = false
    }
  }
  __onDocFocus = __onDocPointer
  document.addEventListener('mousedown', __onDocPointer)
  document.addEventListener('touchstart', __onDocPointer, { passive: true })
  document.addEventListener('focusin', __onDocFocus)
})

onBeforeUnmount(() => {
  if (__onDocPointer) {
    document.removeEventListener('mousedown', __onDocPointer)
    document.removeEventListener('touchstart', __onDocPointer)
  }
  if (__onDocFocus) {
    document.removeEventListener('focusin', __onDocFocus)
  }
  __onDocPointer = null
  __onDocFocus = null
})

// Utilidad para formatear fecha como DD-MM-YYYY, similar a Awards.vue
const formatDate = (dateString) => {
  if (!dateString) return ''
  const s = String(dateString).slice(0, 10)
  const [y, m, d] = s.split('-')
  return (y && m && d) ? `${d}-${m}-${y}` : dateString
}

const handleSubmit = () => {
  // Construir payload para backend
  const payload = {
    name: formData.value.name,
    date: formData.value.date,
    type: formData.value.type, // backend normaliza a español
    author_ids: authorIds.value.length ? [...authorIds.value] : undefined
  }

  if (formData.value.type === 'journal') {
    Object.assign(payload, {
      magazineName: formData.value.magazineName,
      number: formData.value.number,
      volume: formData.value.volume,
      doi: formData.value.doi || undefined
    })
  } else if (formData.value.type === 'book') {
    Object.assign(payload, {
      publisher: formData.value.publisher,
      city: formData.value.city
    })
  } else if (formData.value.type === 'book_chapter') {
    Object.assign(payload, {
      bookName: formData.value.bookName,
      bookAuthor: formData.value.bookAuthor,
      publisher: formData.value.publisher,
      city: formData.value.city
    })
  }

  if (editingId.value) {
    router.put(`/publications/${editingId.value}`, payload, {
      onSuccess: () => {
        closeForm()
      }
    })
  } else {
    router.post('/publications', payload, {
      onSuccess: () => {
        closeForm()
      }
    })
  }
}

const handleEdit = (publication) => {
  formData.value = {
    name: publication.name,
    date: publication.date,
    // Mapear tipo del listado (español) a lo que requiere el formulario (inglés)
    type: publication.type === 'Revista' ? 'journal' : publication.type === 'Libro' ? 'book' : 'book_chapter',
    magazineName: publication.magazineName || '',
    number: publication.number || '',
    volume: publication.volume || '',
    doi: publication.doi || '',
    publisher: publication.publisher || '',
    city: publication.city || '',
    bookName: publication.bookName || '',
    bookAuthor: publication.bookAuthor || ''
  }
  // Cargar ids y nombres de autores
  authorIds.value = Array.isArray(publication.author_ids) ? [...publication.author_ids] : []
  if (Array.isArray(publication.authors) && Array.isArray(publication.author_ids) && publication.authors.length === publication.author_ids.length) {
    for (let i = 0; i < publication.author_ids.length; i++) {
      usersMap.set(publication.author_ids[i], publication.authors[i])
    }
  }
  editingId.value = publication.id
  showForm.value = true
}

// Estado y lógica para modal de eliminación (alineado con Awards.vue)
const showDeleteModal = ref(false)
const publicationToDelete = ref(null)

const openDeleteModal = (publication) => {
  if (!canDelete.value) return
  publicationToDelete.value = publication || null
  showDeleteModal.value = !!publicationToDelete.value
}

// El handler que recibe el id desde la tabla abre el modal buscando la publicación
const handleDelete = (id) => {
  if (!id) return
  const pub = publications.value.find(p => p.id === id)
  openDeleteModal(pub)
}

const confirmDelete = () => {
  if (!publicationToDelete.value) return
  const id = publicationToDelete.value.id
  router.delete(`/publications/${id}`, {
    onSuccess: () => {
      publications.value = publications.value.filter(p => p.id !== id)
      closeDeleteModal()
    }
  })
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  publicationToDelete.value = null
}

const closeForm = () => {
  showForm.value = false
  editingId.value = null
  formData.value = {
    name: '',
    date: '',
    type: 'journal',
    magazineName: '',
    number: '',
    volume: '',
    doi: '',
    publisher: '',
    city: '',
    bookName: '',
    bookAuthor: ''
  }
  // Reiniciar selección de autores al usuario actual si existe
  if (currentUserId != null) {
    authorIds.value = [currentUserId]
    const fromProps = Array.isArray(props.users) ? props.users.find(u => u.id === currentUserId) : null
    const name = fromProps?.name || currentUserName || `ID ${currentUserId}`
    usersMap.set(currentUserId, name)
  } else {
    authorIds.value = []
  }
}
</script>

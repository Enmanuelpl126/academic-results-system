<script setup lang="ts">
import { ref, reactive, computed, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import type { User } from '@/types'

interface Props {
  user: Partial<User & {
    roles?: Array<{ id?: string | number; name?: string; label?: string }>
    department?: { id?: number|string; name?: string }
    is_enabled?: boolean
    last_login_at?: string | null
    created_at?: string | null
  }>
  stats?: {
    publications?: number
    awards?: number
    recognitions?: number
    events?: number
  }
  departments?: Array<{ id: number | string; name: string }>
  savedKey?: number
}

const props = defineProps<Props>()
const emit = defineEmits<{
  (e: 'save', payload: any): void
  (e: 'cancel'): void
}>()

const isEditing = ref(false)
const isSaving = ref(false)
const page = usePage()

const primaryRole = computed(() => {
  const r = props.user?.roles?.[0]
  return r?.label || r?.name || 'Usuario'
})

// Cerrar edición cuando el padre indique éxito (cambiando savedKey)
watch(
  () => props.savedKey,
  () => {
    if (isEditing.value) {
      isEditing.value = false
    }
  }
)
// Lista de departamentos (prop o fallback a page.props)
const departmentsList = computed(() => {
  const fromProp = (props as any)?.departments
  if (Array.isArray(fromProp)) return fromProp
  const fromPage = (page.props as any)?.departments
  return Array.isArray(fromPage) ? fromPage : []
})

const roleBadgeClass = computed(() => {
  const name = (props.user?.roles?.[0]?.name || '').toString()
  // Estilo simple coherente con Admin.vue
  if (name === 'admin') return 'bg-purple-100 text-purple-700'
  if (name === 'jefe_departamento' || name === 'jefe-departamento') return 'bg-blue-100 text-blue-700'
  return 'bg-gray-100 text-gray-700'
})

const form = reactive({
  name: props.user?.name || '',
  email: (props.user as any)?.email || '',
  ci: (props.user as any)?.ci || '',
  department_id: (props.user as any)?.department_id || (props.user?.department as any)?.id || '',
  teaching_category: (props.user as any)?.teaching_category || '',
  scientific_category: (props.user as any)?.scientific_category || '',
  professional_level: (props.user as any)?.professional_level || ''
})

const errors = reactive<{ [k: string]: string | null }>({
  name: null,
  email: null,
  ci: null,
  professional_level: null
})

// Sincronizar errores de validación de Inertia cuando existan
watch(
  () => (page.props as any)?.errors,
  (serverErrors: any) => {
    if (!serverErrors) return
    errors.name = serverErrors.name || null
    errors.email = serverErrors.email || null
    errors.ci = serverErrors.ci || null
    errors.professional_level = serverErrors.professional_level || null
  }
)

function startEdit() {
  isEditing.value = true
  // Reset de errores
  errors.name = null
}

function cancelEdit() {
  // Restaurar valores originales
  form.name = props.user?.name || ''
  form.email = (props.user as any)?.email || ''
  form.ci = (props.user as any)?.ci || ''
  form.department_id = (props.user as any)?.department_id || (props.user?.department as any)?.id || ''
  form.teaching_category = (props.user as any)?.teaching_category || ''
  form.scientific_category = (props.user as any)?.scientific_category || ''
  form.professional_level = (props.user as any)?.professional_level || ''

  isEditing.value = false
  emit('cancel')
}

async function save() {
  // Validación mínima
  errors.name = !form.name ? 'El nombre es requerido' : null
  // email requerido por el backend, aunque sea de solo lectura aquí
  errors.email = !form.email ? 'El email es requerido' : null
  // CI opcional pero si viene, 11 dígitos
  errors.ci = form.ci && !/^[0-9]{11}$/.test(form.ci) ? 'El CI debe contener 11 dígitos' : null
  errors.professional_level = !form.professional_level ? 'Seleccione el nivel profesional' : null
  if (errors.name || errors.email || errors.ci || errors.professional_level) return

  isSaving.value = true
  try {
    emit('save', {
      name: form.name,
      email: form.email,
      ci: form.ci || null,
      teaching_category: form.teaching_category || null,
      scientific_category: form.scientific_category || null,
      professional_level: form.professional_level
    })
    // El padre decide el éxito. Mantenemos el modo edición por si hay errores del servidor.
  } finally {
    isSaving.value = false
  }
}
</script>

<template>
  <div class="w-full">
    <!-- Encabezado perfil -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-start gap-4">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <h2 class="text-2xl font-bold text-gray-900 truncate">{{ form.name || props.user?.name || '—' }}</h2>
            <span class="px-2 py-0.5 rounded-full text-xs" :class="roleBadgeClass">{{ primaryRole }}</span>
          </div>
          <div class="mt-1 text-sm text-gray-500 truncate">{{ props.user?.email || '—' }}</div>
          <div class="mt-2 text-xs">
            <span :class="[props.user?.is_enabled ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700', 'px-2 py-0.5 rounded-full']">
              {{ props.user?.is_enabled ? 'Habilitada' : 'Deshabilitada' }}
            </span>
          </div>
        </div>

        <div class="shrink-0 flex items-start gap-2">
          <button v-if="!isEditing" @click="startEdit" type="button" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
            Editar
          </button>
          <div v-else class="flex items-center gap-2">
            <button @click="cancelEdit" type="button" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancelar</button>
            <button @click="save" type="button" :disabled="isSaving" :class="['px-4 py-2 rounded-lg text-white', isSaving ? 'bg-blue-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700']">
              {{ isSaving ? 'Guardando...' : 'Guardar' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenido -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
      <!-- Información básica -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Información</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Nombre -->
          <div class="sm:col-span-2">
            <label class="block text-sm text-gray-700 mb-1">Nombre</label>
            <template v-if="isEditing">
              <input type="text" v-model="form.name" :class="['w-full rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500', errors.name ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300']" required />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </template>
            <template v-else>
              <div class="text-gray-900">{{ props.user?.name || '—' }}</div>
            </template>
          </div>

          <!-- Email (solo lectura) -->
          <div>
            <label class="block text-sm text-gray-700 mb-1">Email</label>
            <div class="text-gray-900 truncate">{{ props.user?.email || '—' }}</div>
          </div>

          <!-- Departamento (solo lectura desde el perfil) -->
          <div>
            <label class="block text-sm text-gray-700 mb-1">Departamento</label>
            <div class="text-gray-900">{{ props.user?.department?.name || 'Sin departamento' }}</div>
          </div>

          <!-- Categorías (solo lectura) -->
          <div>
            <label class="block text-sm text-gray-700 mb-1">Categoría Docente</label>
            <template v-if="isEditing">
              <select v-model="form.teaching_category" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Ninguna</option>
                <option value="profesor_principal">Profesor Principal</option>
                <option value="profesor_ayudante">Profesor Ayudante</option>
                <option value="profesor_entrenador">Profesor entrenador</option>
              </select>
            </template>
            <template v-else>
              <div class="text-gray-900">{{ (props.user as any)?.teaching_category || (props.user as any)?.docente_category || '—' }}</div>
            </template>
          </div>
          <div>
            <label class="block text-sm text-gray-700 mb-1">Categoría Científica</label>
            <template v-if="isEditing">
              <select v-model="form.scientific_category" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">Ninguna</option>
                <option value="aspirante">Aspirante a Investigador</option>
                <option value="investigador_agregado">Investigador agregado</option>
                <option value="investigador_titular">Investigador Titular</option>
              </select>
            </template>
            <template v-else>
              <div class="text-gray-900">{{ (props.user as any)?.scientific_category || '—' }}</div>
            </template>
          </div>

          <!-- Nivel profesional -->
          <div class="sm:col-span-2">
            <label class="block text-sm text-gray-700 mb-1">Nivel Profesional</label>
            <template v-if="isEditing">
              <select v-model="form.professional_level" :class="['w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500', errors.professional_level ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : '']">
                <option value="">Seleccionar</option>
                <option value="tecnico">Técnico</option>
                <option value="especialista">Especialista</option>
                <option value="obrero">Obrero</option>
                <option value="licenciado">Licenciado</option>
                <option value="master">Master</option>
                <option value="doctor_en_ciencias">Doctor en Ciencias</option>
              </select>
              <p v-if="errors.professional_level" class="mt-1 text-sm text-red-600">{{ errors.professional_level }}</p>
            </template>
            <template v-else>
              <div class="text-gray-900">{{ (props.user as any)?.professional_level || '—' }}</div>
            </template>
          </div>

          <!-- CI -->
          <div class="sm:col-span-2">
            <label class="block text-sm text-gray-700 mb-1">CI</label>
            <template v-if="isEditing">
              <input type="text" v-model="form.ci" maxlength="11" inputmode="numeric" pattern="^[0-9]{11}$" title="Debe contener 11 dígitos" :class="['w-full rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500', errors.ci ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300']" />
              <p v-if="errors.ci" class="mt-1 text-sm text-red-600">{{ errors.ci }}</p>
            </template>
            <template v-else>
              <div class="text-gray-900">{{ (props.user as any)?.ci || '—' }}</div>
            </template>
          </div>
        </div>
      </div>

      <!-- Preferencias y contacto -->
      
      <!-- Estadísticas -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="border border-gray-100 rounded-lg p-4 text-center">
            <div class="text-sm text-gray-500">Publicaciones</div>
            <div class="text-2xl font-bold text-gray-900">{{ props.stats?.publications ?? 0 }}</div>
          </div>
          <div class="border border-gray-100 rounded-lg p-4 text-center">
            <div class="text-sm text-gray-500">Premios</div>
            <div class="text-2xl font-bold text-gray-900">{{ props.stats?.awards ?? 0 }}</div>
          </div>
          <div class="border border-gray-100 rounded-lg p-4 text-center">
            <div class="text-sm text-gray-500">Reconocimientos</div>
            <div class="text-2xl font-bold text-gray-900">{{ props.stats?.recognitions ?? 0 }}</div>
          </div>
          <div class="border border-gray-100 rounded-lg p-4 text-center">
            <div class="text-sm text-gray-500">Eventos</div>
            <div class="text-2xl font-bold text-gray-900">{{ props.stats?.events ?? 0 }}</div>
          </div>
        </div>
      </div>

      <!-- Actividad -->
      <div class="bg-white rounded-lg shadow p-6 lg:col-span-2">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Actividad</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <div class="text-sm text-gray-500">Último inicio de sesión</div>
            <div class="text-gray-900">{{ props.user?.last_login_at || '—' }}</div>
          </div>
          <div>
            <div class="text-sm text-gray-500">Miembro desde</div>
            <div class="text-gray-900">{{ props.user?.created_at || '—' }}</div>
          </div>
        </div>
        <!-- Importante: no incluir NUNCA cambio de contraseña -->
      </div>
    </div>
  </div>
</template>

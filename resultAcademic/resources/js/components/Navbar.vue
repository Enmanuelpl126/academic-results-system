<template>
  <!-- Barra de navegación principal fija con sombra -->
  <nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex-shrink-0">
          <h1 class="text-xl font-bold text-gray-900">
            Scientific Achievements
          </h1>
        </div>

        <!-- Navegación de escritorio: items principales, acceso admin y menú de usuario -->
        <div class="hidden md:flex md:items-center md:gap-2">
          <NavItem
            v-for="item in mainNavItems"
            :key="item.id"
            :icon="item.icon"
            :label="item.label"
            :active="activeSection === item.id"
            @click="handleSectionChange(item.id)"
          />
          <NavItem
            v-if="isAdmin"
            :icon="UsersIcon"
            label="User Management"
            :active="activeSection === 'admin'"
            @click="handleSectionChange('admin')"
            variant="admin"
          />
          <!-- Menú de usuario -->
          <div class="relative ml-2" @keydown.escape.stop="isUserMenuOpen = false">
            <button
              @click="toggleUserMenu"
              class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-800"
              aria-haspopup="menu"
              :aria-expanded="isUserMenuOpen ? 'true' : 'false'"
            >
              <UserCircleIcon :size="22" class="text-gray-600" />
              <span class="font-medium" v-text="currentUser?.value?.name || 'Usuario'" />
            </button>
            <!-- Dropdown -->
            <div
              v-if="isUserMenuOpen"
              class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg ring-1 ring-black/5 py-2 z-50"
              role="menu"
            >
              <button
                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                @click="() => { handleSectionChange('profile'); isUserMenuOpen = false }"
                role="menuitem"
              >
                <UserCircleIcon :size="18" />
                Perfil
              </button>
              <button
                v-if="isAdmin"
                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                @click="() => { handleSectionChange('admin'); isUserMenuOpen = false }"
                role="menuitem"
              >
                <UsersIcon :size="18" />
                Administración
              </button>
              <div class="my-1 h-px bg-gray-100"></div>
              <button
                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"
                @click="() => { handleLogout(); isUserMenuOpen = false }"
                role="menuitem"
              >
                <LogOutIcon :size="18" />
                Cerrar sesión
              </button>
            </div>
          </div>
        </div>

        <!-- Controles móviles: botón admin (si aplica), menú usuario y toggle de menú -->
        <div class="flex items-center gap-2 md:hidden">
          <button
            v-if="isAdmin"
            @click="handleSectionChange('admin')"
            :class="[
              'inline-flex items-center justify-center p-2 rounded-md',
              activeSection === 'admin'
                ? 'text-purple-700 bg-purple-100'
                : 'text-purple-600 hover:text-purple-900 hover:bg-purple-50'
            ]"
            aria-label="User Management"
          >
            <UsersIcon :size="20" />
          </button>
          <!-- Menú usuario móvil -->
          <div class="relative" @keydown.escape.stop="isUserMenuOpenMobile = false">
            <button
              @click="toggleUserMenuMobile"
              class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100"
              aria-haspopup="menu"
              :aria-expanded="isUserMenuOpenMobile ? 'true' : 'false'"
              aria-label="User Menu"
            >
              <UserCircleIcon :size="20" />
            </button>
            <div
              v-if="isUserMenuOpenMobile"
              class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg ring-1 ring-black/5 py-2 z-50"
              role="menu"
            >
              <button
                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                @click="() => { handleSectionChange('profile'); isUserMenuOpenMobile = false }"
                role="menuitem"
              >
                <UserCircleIcon :size="18" />
                Perfil
              </button>
              <button
                v-if="isAdmin"
                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                @click="() => { handleSectionChange('admin'); isUserMenuOpenMobile = false }"
                role="menuitem"
              >
                <UsersIcon :size="18" />
                Administración
              </button>
              <div class="my-1 h-px bg-gray-100"></div>
              <button
                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"
                @click="() => { handleLogout(); isUserMenuOpenMobile = false }"
                role="menuitem"
              >
                <LogOutIcon :size="18" />
                Cerrar sesión
              </button>
            </div>
          </div>
          <button
            @click="toggleMenu"
            class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100"
          >
            <XIcon v-if="isMenuOpen" :size="24" />
            <MenuIcon v-else :size="24" />
          </button>
        </div>
      </div>
    </div>

    <!-- Menú móvil desplegable con los mismos items principales -->
    <div :class="['md:hidden', isMenuOpen ? 'block' : 'hidden']">
      <div class="px-2 pt-2 pb-3 space-y-1">
        <NavItem
          v-for="item in mainNavItems"
          :key="item.id"
          :icon="item.icon"
          :label="item.label"
          :active="activeSection === item.id"
          @click="handleSectionChange(item.id)"
          class-name="w-full"
        />
      </div>
    </div>
  </nav>
</template>

<script setup>
// Importaciones principales
import { ref } from 'vue'
import NavItem from './NavItem.vue'
import { useUser } from '../composables/useUser.js'
// Iconos usados desde lucide-vue-next, renombrados con sufijo Icon
import { 
  BookOpen as BookOpenIcon,
  Award as AwardIcon,
  Calendar as CalendarIcon,
  Trophy as TrophyIcon,
  Lightbulb as LightbulbIcon,
  Menu as MenuIcon,
  X as XIcon,
  Users as UsersIcon,
  LogOut as LogOutIcon,
  UserCircle as UserCircleIcon
} from 'lucide-vue-next'

// Props
// - activeSection: identifica qué sección está activa para resaltar el ítem correspondiente
const props = defineProps({
  activeSection: {
    type: String,
    required: true
  }
})

// Eventos emitidos al componente padre
// - section-change: notifica cambios de sección (útil para navegación con Inertia)
const emit = defineEmits(['section-change'])

// Estado local
// - isMenuOpen: visibilidad del menú principal móvil
// - isUserMenuOpen: visibilidad del dropdown de usuario en desktop
// - isUserMenuOpenMobile: visibilidad del dropdown de usuario en móvil
const isMenuOpen = ref(false)
const isUserMenuOpen = ref(false)
const isUserMenuOpenMobile = ref(false)

// Contexto de usuario
// - isAdmin: habilita opciones administrativas
// - setCurrentUser: permite cerrar sesión
const { isAdmin, setCurrentUser, currentUser } = useUser()

// Métodos
// Alterna visibilidad del menú móvil
const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

// Alterna menús de usuario
const toggleUserMenu = () => {
  isUserMenuOpen.value = !isUserMenuOpen.value
}
const toggleUserMenuMobile = () => {
  isUserMenuOpenMobile.value = !isUserMenuOpenMobile.value
}

// Cambia de sección y cierra el menú móvil si está abierto
const handleSectionChange = (section) => {
  emit('section-change', section)
  isMenuOpen.value = false
}

// Cierra la sesión del usuario y redirige al dashboard
const handleLogout = () => {
  setCurrentUser(null)
  emit('section-change', 'dashboard')
}

// Definición de los elementos principales de navegación
const mainNavItems = [
  { icon: BookOpenIcon, label: "Publications", id: "publications" },
  { icon: AwardIcon, label: "Recognitions", id: "recognitions" },
  { icon: CalendarIcon, label: "Events", id: "events" },
  { icon: TrophyIcon, label: "Awards", id: "awards" },
  { icon: LightbulbIcon, label: "Patents", id: "patents" },
]
</script>

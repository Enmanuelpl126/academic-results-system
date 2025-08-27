import { ref, computed, provide, inject } from 'vue'

// Símbolo único para la inyección
const USER_CONTEXT_KEY = Symbol('userContext')

// Estado global del usuario
const currentUser = ref(null)

// Composable para usar el contexto de usuario
export function useUser() {
  const setCurrentUser = (user) => {
    currentUser.value = user
  }

  const isAdmin = computed(() => {
    return currentUser.value?.role === 'admin' || currentUser.value?.is_admin === true
  })

  const isAuthenticated = computed(() => {
    return currentUser.value !== null
  })

  return {
    currentUser,
    setCurrentUser,
    isAdmin,
    isAuthenticated
  }
}

// Función para proveer el contexto de usuario
export function provideUserContext() {
  const userContext = useUser()
  provide(USER_CONTEXT_KEY, userContext)
  return userContext
}

// Función para inyectar el contexto de usuario
export function injectUserContext() {
  const userContext = inject(USER_CONTEXT_KEY)
  if (!userContext) {
    throw new Error('useUser debe ser usado dentro de un proveedor de contexto de usuario')
  }
  return userContext
}

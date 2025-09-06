<template>
  <!-- Contenedor principal de administración -->
  <div class="w-full min-h-screen bg-gray-50 flex">
    <!-- Barra lateral -->
    <div class="w-64 bg-white shadow-sm border-r border-gray-200">
      <div class="p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Administración</h2>
        <nav class="space-y-2">
          <button
            @click="activeSection = 'users'"
            :class="getSidebarItemClasses('users')"
          >
            <UsersIcon :size="20" />
            <span>Usuarios</span>
          </button>
          <button
            @click="activeSection = 'departments'"
            :class="getSidebarItemClasses('departments')"
          >
            <BuildingIcon :size="20" />
            <span>Departamentos</span>
          </button>
          <button
            @click="activeSection = 'roles'"
            :class="getSidebarItemClasses('roles')"
          >
            <ShieldIcon :size="20" />
            <span>Roles</span>
          </button>
        </nav>
      </div>

  <!-- Modal de detalles de usuario -->
  <div v-if="showUserDetails && selectedUser" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-2xl">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-900">Detalles del Usuario</h3>
        <button @click="closeUserDetails" class="text-gray-500 hover:text-gray-700">
          <XIcon :size="24" />
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <div class="text-sm text-gray-500">Nombre</div>
          <div class="text-gray-900 font-medium">{{ selectedUser.name || '—' }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-500">CI</div>
          <div class="text-gray-900 font-medium">{{ selectedUser.ci || '—' }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Departamento</div>
          <div class="text-gray-900 font-medium">{{ selectedUser.department?.name || 'Sin departamento' }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Categoría Docente</div>
          <div class="text-gray-900 font-medium">{{ selectedUser.teaching_category || '—' }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Categoría Científica</div>
          <div class="text-gray-900 font-medium">{{ selectedUser.scientific_category || '—' }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Nivel Profesional</div>
          <div class="text-gray-900 font-medium">{{ selectedUser.professional_level || '—' }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Email</div>
          <div class="text-gray-900 font-medium">{{ selectedUser.email || '—' }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Rol</div>
          <div class="text-gray-900 font-medium">{{ getRoleLabel((selectedUser.roles?.[0]?.name) || 'profesor') }}</div>
        </div>
        <div>
          <div class="text-sm text-gray-500">Estado</div>
          <div class="text-gray-900 font-medium">{{ selectedUser.email_verified_at ? 'Activo' : 'Pendiente' }}</div>
        </div>
      </div>

      <div class="flex justify-end gap-3 pt-6">
        <button
          type="button"
          @click="closeUserDetails"
          class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
        >
          Cerrar
        </button>
      </div>
    </div>
  </div>
    </div>

    <!-- Contenido principal -->
    <div class="flex-1 p-8">
      <!-- Vista de Usuarios -->
      <div v-if="activeSection === 'users'">
        <div class="mb-6">
          <h3 class="text-2xl font-bold text-gray-900 mb-2">Gestión de Usuarios</h3>
          <p class="text-gray-600">Administra los usuarios del sistema</p>
        </div>

        <!-- Controles de búsqueda y filtros -->
        <div class="mb-6 space-y-4">
          <!-- Fila 1: barra de búsqueda amplia -->
          <div class="w-full">
            <div class="relative">
              <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" :size="20" />
              <input
                type="text"
                placeholder="Buscar usuarios..."
                v-model="userSearchQuery"
                class="pl-10 pr-4 py-3 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>

          <!-- Fila 2: filtros y botón debajo -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 items-stretch">
            <!-- Filtro por Rol -->
            <div>
              <select
                v-model="roleFilter"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Todos los roles</option>
                <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.label }}</option>
              </select>
            </div>

            <!-- Filtro por Departamento -->
            <div>
              <select
                v-model="departmentFilter"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Todos los departamentos</option>
                <option v-for="dept in departments" :key="dept.id" :value="String(dept.id)">{{ dept.name }}</option>
              </select>
            </div>

            <!-- Botón Agregar Usuario (alineado a la derecha en pantallas grandes) -->
            <div class="flex lg:justify-end">
              <button
                @click="openUserForm"
                class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-blue-700 transition-colors"
              >
                <PlusIcon :size="20" />
                Agregar Usuario
              </button>
            </div>
          </div>
        </div>

        <!-- Tabla de usuarios (ancho fijo y columnas definidas) -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full table-fixed divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">
                  Usuario
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/4">
                  Email
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/5">
                  Departamento
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">
                  Rol
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
                  Acciones
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="user in filteredUsers"
                :key="user.id"
                :class="['hover:bg-gray-50', !user.is_enabled ? 'opacity-60' : '']"
              >
                <td class="px-6 py-4 whitespace-nowrap w-1/4">
                  <div class="flex items-center">
                    <UserCircleIcon :size="40" class="text-gray-400" />
                    <div class="ml-4 max-w-[12rem] overflow-hidden text-ellipsis">
                      <div class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</div>
                      <div class="mt-1 text-xs">
                        <span
                          :class="user.is_enabled ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700'"
                          class="px-2 py-0.5 rounded-full"
                        >
                          {{ user.is_enabled ? 'Habilitado' : 'Deshabilitado' }}
                        </span>
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 w-1/4">
                  <span class="block max-w-[14rem] truncate">{{ user.email }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900 w-1/5">
                  <span class="block whitespace-normal break-words">{{ user.department?.name || 'Sin departamento' }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap w-1/6">
                  <span :class="getRoleBadgeClasses((user.roles?.[0]?.name) || 'profesor')">
                    {{ getRoleLabel((user.roles?.[0]?.name) || 'profesor') }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium w-24">
                  <button
                    @click="openUserDetails(user)"
                    class="text-gray-700 hover:text-gray-900 mr-3"
                  >
                    <EyeIcon :size="18" />
                  </button>
                  <button
                    @click="editUser(user)"
                    class="text-blue-600 hover:text-blue-900 mr-3"
                  >
                    <Edit2Icon :size="18" />
                  </button>
                  <button
                    @click="openStatusModal(user)"
                    :disabled="user.id === currentUserId"
                    :title="user.id === currentUserId ? 'No puedes deshabilitar tu propia cuenta' : (user.is_enabled ? 'Deshabilitar usuario' : 'Habilitar usuario')"
                    :class="[
                      user.is_enabled ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900',
                      user.id === currentUserId ? 'opacity-50 cursor-not-allowed hover:text-inherit' : ''
                    ]"
                  >
                    <component :is="user.is_enabled ? BanIcon : CheckCircleIcon" :size="18" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          
          <!-- Estado vacío -->
          <div v-if="!filteredUsers.length" class="p-6 text-center text-gray-500">
            No hay usuarios que coincidan con el criterio de búsqueda
            <span v-if="userSearchQuery">: "{{ userSearchQuery }}"</span>.
          </div>
        </div>
      </div>

      <!-- Vista de Departamentos -->
      <div v-if="activeSection === 'departments'">
        <div class="mb-6">
          <h3 class="text-2xl font-bold text-gray-900 mb-2">Gestión de Departamentos</h3>
          <p class="text-gray-600">Administra los departamentos de la organización</p>
        </div>

        <!-- Controles -->
        <div class="mb-6 flex flex-col sm:flex-row gap-4">
          <div class="flex-1">
            <div class="relative">
              <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" :size="20" />
              <input
                type="text"
                placeholder="Buscar departamentos..."
                v-model="departmentSearchQuery"
                class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>
          <button
            @click="openDepartmentForm"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-blue-700 transition-colors"
          >
            <PlusIcon :size="20" />
            Agregar Departamento
          </button>
        </div>

        <!-- Grid de departamentos -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="department in filteredDepartments" :key="department.id" class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-start mb-4">
              <div class="w-full">
                <h4 class="text-lg font-semibold text-gray-900">{{ department.name }}</h4>
                <p class="text-sm text-gray-600 mt-1">{{ department.description || 'Sin descripción' }}</p>
                <div class="flex items-center gap-2 text-sm text-gray-500 mt-2">
                  <UsersIcon :size="16" class="text-gray-400" />
                  {{ department.users_count || 0 }} usuarios
                </div>
              </div>
              <div class="flex gap-2 ml-3 shrink-0">
                <button
                  @click="editDepartment(department)"
                  class="text-blue-600 hover:text-blue-900"
                >
                  <Edit2Icon :size="18" />
                </button>
                <button
                  @click="deleteDepartment(department)"
                  class="text-red-600 hover:text-red-900"
                >
                  <Trash2Icon :size="18" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Estado vacío -->
        <div v-if="!filteredDepartments.length" class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center text-gray-500">
          No hay departamentos que coincidan con el criterio de búsqueda
          <span v-if="departmentSearchQuery">: "{{ departmentSearchQuery }}"</span>.
        </div>
      </div>

      <!-- Vista de Roles -->
      <div v-if="activeSection === 'roles'">
        <div class="mb-6">
          <h3 class="text-2xl font-bold text-gray-900 mb-2">Gestión de Roles</h3>
          <p class="text-gray-600">Administra los roles y permisos del sistema</p>
        </div>

        <!-- Grid de roles -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="role in roles" :key="role.id" class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-start mb-4">
              <div class="w-full">
                <div class="flex items-center gap-2 mb-2">
                  <ShieldIcon :size="20" :class="getRoleIconClass(role.id)" />
                  <h4 class="text-lg font-semibold text-gray-900">{{ role.label }}</h4>
                </div>
                <p class="text-sm text-gray-600 mb-3">{{ role.description }}</p>
                <div class="flex items-center gap-2 text-sm text-gray-500">
                  <UsersIcon :size="16" class="text-gray-400" />
                  {{ role.users_count || 0 }} usuarios
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de formulario de usuario -->
  <div v-if="showUserForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-2xl max-h-[85vh] overflow-y-auto">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-900">
          {{ editingUserId ? 'Editar Usuario' : 'Agregar Usuario' }}
        </h3>
        <button @click="closeUserForm" class="text-gray-500 hover:text-gray-700">
          <XIcon :size="24" />
        </button>
      </div>
      
      <form @submit.prevent="handleUserSubmit" class="space-y-4" :key="formOpenKey" autocomplete="off">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
            <input
              type="text"
              v-model="userFormData.name"
              :class="['w-full rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500', formErrors.name ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300']"
              required
            />
            <p v-if="formErrors.name" class="mt-1 text-sm text-red-600">{{ formErrors.name }}</p>
          </div>
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">CI</label>
            <input
              type="text"
              v-model="userFormData.ci"
              :class="['w-full rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500', formErrors.ci ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300']"
              placeholder="11 dígitos"
              maxlength="11"
              pattern="^[0-9]{11}$"
              inputmode="numeric"
              title="Debe contener 11 dígitos"
              @input="onCiInput"
              required
            />
            <p v-if="formErrors.ci" class="mt-1 text-sm text-red-600">{{ formErrors.ci }}</p>
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              type="email"
              v-model="userFormData.email"
              :class="['w-full rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500', formErrors.email ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300']"
              required
            />
            <p v-if="formErrors.email" class="mt-1 text-sm text-red-600">{{ formErrors.email }}</p>
          </div>
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
            <select
              v-model="userFormData.department_id"
              :class="['w-full rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500', formErrors.department_id ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300']"
            >
              <option value="">Sin departamento</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </select>
            <p v-if="formErrors.department_id" class="mt-1 text-sm text-red-600">{{ formErrors.department_id }}</p>
          </div>
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
            <select
              v-model="userFormData.role"
              :class="['w-full rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500', formErrors.role ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300']"
              required
            >
              <option v-for="role in roles" :key="role.id" :value="role.id">
                {{ role.label }}
              </option>
            </select>
            <p v-if="formErrors.role" class="mt-1 text-sm text-red-600">{{ formErrors.role }}</p>
          </div>
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Categoría Docente</label>
            <select
              v-model="userFormData.docente_category"
              :class="['w-full rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500', formErrors.docente_category ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300']"
            >
              <option value="">Ninguna</option>
              <option value="profesor_principal">Profesor Principal</option>
              <option value="profesor_ayudante">Profesor Ayudante</option>
              <option value="profesor_entrenador">Profesor entrenador</option>
            </select>
            <p v-if="formErrors.docente_category" class="mt-1 text-sm text-red-600">{{ formErrors.docente_category }}</p>
          </div>
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Categoría Científica</label>
            <select
              v-model="userFormData.scientific_category"
              :class="['w-full rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500', formErrors.scientific_category ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300']"
            >
              <option value="">Ninguna</option>
              <option value="aspirante">Aspirante a Investigador</option>
              <option value="investigador_agregado">Investigador agregado</option>
              <option value="investigador_titular">Investigador Titular</option>
            </select>
            <p v-if="formErrors.scientific_category" class="mt-1 text-sm text-red-600">{{ formErrors.scientific_category }}</p>
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nivel Profesional</label>
            <select
              v-model="userFormData.professional_level"
              :class="['w-full rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500', formErrors.professional_level ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300']"
              required
            >
              <option value="">Seleccionar</option>
              <option value="tecnico">Técnico</option>
              <option value="especialista">Especialista</option>
              <option value="obrero">Obrero</option>
              <option value="licenciado">Licenciado</option>
              <option value="master">Master</option>
              <option value="doctor_en_ciencias">Doctor en Ciencias</option>
            </select>
            <p v-if="formErrors.professional_level" class="mt-1 text-sm text-red-600">{{ formErrors.professional_level }}</p>
          </div>
        </div>
        
        <!-- Toggle cambiar contraseña al editar -->
        <div v-if="editingUserId" class="flex items-center gap-2 pt-2">
          <input id="toggle-change-password" type="checkbox" v-model="changePassword" class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
          <label for="toggle-change-password" class="text-sm text-gray-700 select-none">Cambiar contraseña</label>
        </div>

        <!-- Campos de contraseña (crear o editar con toggle) -->
        <div v-if="!editingUserId || changePassword" class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
            <input
              type="password"
              v-model="userFormData.password"
              :class="['w-full rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500', formErrors.password ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300']"
              placeholder="Mínimo 8 caracteres"
              minlength="8"
              autocomplete="new-password"
              name="new-password"
              pattern="^(?=.*[A-Za-z])(?=.*\\d)(?=.*[^A-Za-z\\d]).{8,}$"
              title="Debe contener al menos una letra, un número y un carácter especial"
              :required="!editingUserId || changePassword"
            />
            <p v-if="formErrors.password" class="mt-1 text-sm text-red-600">{{ formErrors.password }}</p>
          </div>
          <div class="md:col-span-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
            <input
              type="password"
              v-model="userFormData.password_confirmation"
              :class="['w-full rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500', formErrors.password_confirmation ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300']"
              minlength="8"
              autocomplete="new-password"
              name="new-password-confirmation"
              pattern="^(?=.*[A-Za-z])(?=.*\\d)(?=.*[^A-Za-z\\d]).{8,}$"
              title="Debe contener al menos una letra, un número y un carácter especial"
              :required="!editingUserId || changePassword"
            />
            <p v-if="formErrors.password_confirmation" class="mt-1 text-sm text-red-600">{{ formErrors.password_confirmation }}</p>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4">
          <button
            type="button"
            @click="closeUserForm"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            :class="['px-4 py-2 rounded-lg text-white', isSubmitting ? 'bg-blue-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700']"
          >
            {{ isSubmitting ? (editingUserId ? 'Actualizando...' : 'Creando...') : (editingUserId ? 'Actualizar' : 'Crear') }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal de formulario de departamento -->
  <div v-if="showDepartmentForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-bold text-gray-900">
          {{ editingDepartmentId ? 'Editar Departamento' : 'Agregar Departamento' }}
        </h3>
        <button @click="closeDepartmentForm" class="text-gray-500 hover:text-gray-700">
          <XIcon :size="24" />
        </button>
      </div>
      
      <form @submit.prevent="handleDepartmentSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
          <input
            type="text"
            v-model="departmentFormData.name"
            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            required
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
          <textarea
            v-model="departmentFormData.description"
            rows="3"
            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            placeholder="Descripción del departamento (opcional)"
          ></textarea>
        </div>
        
        <div class="flex justify-end gap-3 pt-4">
          <button
            type="button"
            @click="closeDepartmentForm"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            {{ editingDepartmentId ? 'Actualizar' : 'Crear' }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal de confirmación Habilitar/Deshabilitar usuario -->
  <div
    v-if="showStatusModal"
    class="fixed inset-0 z-50 flex items-center justify-center"
    aria-modal="true"
    role="dialog"
  >
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/40" @click.self="closeStatusModal"></div>
    <!-- Contenido -->
    <div class="relative z-10 w-full max-w-md bg-white rounded-xl shadow-lg border border-gray-200 p-6">
      <div class="flex items-start gap-3">
        <div class="flex-1">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Confirmar acción</h3>
          <p class="text-sm text-gray-600 mb-3">
            <template v-if="userToToggle?.id === currentUserId">
              No puedes deshabilitar tu propia cuenta.
            </template>
            <template v-else>
              {{ userToToggle?.is_enabled ? '¿Seguro que deseas deshabilitar este usuario?' : '¿Seguro que deseas habilitar este usuario?' }}
            </template>
          </p>
          <div v-if="userToToggle" class="text-sm text-gray-700 bg-gray-50 border border-gray-200 rounded-lg p-3">
            <div><span class="font-medium text-gray-900">Nombre:</span> {{ userToToggle.name }}</div>
            <div><span class="font-medium text-gray-900">Email:</span> {{ userToToggle.email }}</div>
            <div><span class="font-medium text-gray-900">Estado actual:</span> {{ userToToggle.is_enabled ? 'Habilitado' : 'Deshabilitado' }}</div>
          </div>
        </div>
      </div>
      <div class="mt-6 flex justify-end gap-3">
        <button type="button" @click="closeStatusModal" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancelar</button>
        <button type="button" @click="confirmStatusToggle" :disabled="userToToggle?.id === currentUserId" :class="['px-4 py-2 rounded-lg text-white', (userToToggle?.is_enabled ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'), (userToToggle?.id === currentUserId ? 'opacity-50 cursor-not-allowed' : '')]">
          {{ userToToggle?.is_enabled ? 'Deshabilitar' : 'Habilitar' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import {
  Users as UsersIcon,
  Building as BuildingIcon,
  Shield as ShieldIcon,
  Search as SearchIcon,
  Plus as PlusIcon,
  Edit2 as Edit2Icon,
  Trash2 as Trash2Icon,
  Ban as BanIcon,
  CheckCircle as CheckCircleIcon,
  X as XIcon,
  UserCircle as UserCircleIcon,
  Eye as EyeIcon
} from 'lucide-vue-next'

// Props
const props = defineProps({
  initialUsers: {
    type: Array,
    default: () => []
  },
  initialDepartments: {
    type: Array,
    default: () => []
  }
})

// Estado reactivo
const activeSection = ref('users')
const users = ref([...(props.initialUsers || [])])
const departments = ref([...(props.initialDepartments || [])])

// Mantener sincronizados los datos locales con las props de Inertia tras reloads parciales
watch(
  () => props.initialUsers,
  (val) => {
    users.value = [...(val || [])]
  }
)
watch(
  () => props.initialDepartments,
  (val) => {
    departments.value = [...(val || [])]
  }
)

// Búsquedas
const userSearchQuery = ref('')
const roleFilter = ref('')
const departmentFilter = ref('')
const departmentSearchQuery = ref('')

// Formularios
const showUserForm = ref(false)
// Clave reactiva para forzar re-render del formulario y evitar autofill del navegador
const formOpenKey = ref(0)
const showDepartmentForm = ref(false)
const editingUserId = ref(null)
const editingDepartmentId = ref(null)
const isSubmitting = ref(false)
const formErrors = ref({})
// Toggle cambiar contraseña en edición
const changePassword = ref(false)
// Detalles de usuario
const showUserDetails = ref(false)
const selectedUser = ref(null)
// Estado para confirmar habilitar/deshabilitar
const showStatusModal = ref(false)
const userToToggle = ref(null)

// Usuario autenticado (para evitar auto-deshabilitar)
const page = usePage()
const currentUserId = page?.props?.auth?.user?.id ?? null

const userFormData = ref({
  name: '',
  email: '',
  ci: '',
  department_id: '',
  role: 'profesor',
  docente_category: '',
  scientific_category: '',
  professional_level: '',
  password: '',
  password_confirmation: ''
})

const departmentFormData = ref({
  name: '',
  description: ''
})

// Roles del sistema (Spatie) según seeder, etiquetados en español
const roles = ref([
  { id: 'admin', label: 'Administrador' },
  { id: 'directive', label: 'Directivo' },
  { id: 'head_dp', label: 'Jefe de Departamento' },
  { id: 'profesor', label: 'Profesor' },
])

// Computed
const filteredUsers = computed(() => {
  const query = userSearchQuery.value.toLowerCase()
  const selectedRole = roleFilter.value
  const selectedDept = departmentFilter.value

  return users.value
    .filter(user => {
      // Texto libre
      const matchesText = (
        user.name?.toLowerCase().includes(query) ||
        user.email?.toLowerCase().includes(query) ||
        (user.department?.name || '').toLowerCase().includes(query)
      )
      if (query && !matchesText) return false

      // Rol (Spatie: primer rol)
      if (selectedRole) {
        const userRole = user.roles && user.roles[0] ? user.roles[0].name : ''
        if (userRole !== selectedRole) return false
      }

      // Departamento por id
      if (selectedDept) {
        const userDeptId = String(user.department_id || user.department?.id || '')
        if (userDeptId !== selectedDept) return false
      }

      return true
    })
})

const filteredDepartments = computed(() => {
  const query = departmentSearchQuery.value.toLowerCase()
  return departments.value.filter(dept =>
    dept.name.toLowerCase().includes(query) ||
    (dept.description || '').toLowerCase().includes(query)
  )
})

// Métodos
const getSidebarItemClasses = (section) => {
  const base = 'w-full flex items-center gap-3 px-4 py-3 text-left rounded-lg transition-colors'
  return activeSection.value === section
    ? `${base} bg-blue-100 text-blue-700`
    : `${base} text-gray-700 hover:bg-gray-100`
}

const getRoleBadgeClasses = (role) => {
  const base = 'px-2 py-1 rounded-full text-xs font-medium'
  return role === 'admin'
    ? `${base} bg-purple-100 text-purple-800`
    : `${base} bg-gray-100 text-gray-800`
}

const getStatusBadgeClasses = (verified) => {
  const base = 'px-2 py-1 rounded-full text-xs font-medium'
  return verified
    ? `${base} bg-green-100 text-green-800`
    : `${base} bg-yellow-100 text-yellow-800`
}

const getRoleLabel = (role) => {
  const roleObj = roles.value.find(r => r.id === role)
  return roleObj ? roleObj.label : 'Usuario'
}

const getRoleIconClass = (role) => {
  return role === 'admin' ? 'text-purple-600' : 'text-gray-600'
}

// Normaliza el campo CI: solo dígitos y máximo 11
const onCiInput = (e) => {
  const digits = String(e?.target?.value ?? '')
    .replace(/\D/g, '')
    .slice(0, 11)
  userFormData.value.ci = digits
}

// Formulario de usuario
const openUserForm = () => {
  editingUserId.value = null
  formErrors.value = {}
  userFormData.value = {
    name: '',
    email: '',
    ci: '',
    department_id: '',
    role: 'profesor',
    docente_category: '',
    scientific_category: '',
    professional_level: '',
    password: '',
    password_confirmation: ''
  }
  changePassword.value = false
  // Forzar re-render para limpiar inputs y evitar valores por defecto/autofill
  formOpenKey.value++
  showUserForm.value = true
}

const editUser = (user) => {
  editingUserId.value = user.id
  formErrors.value = {}
  userFormData.value = {
    name: user.name,
    email: user.email,
    ci: user.ci || '',
    department_id: user.department_id || '',
    role: (user.roles && user.roles[0] && user.roles[0].name) ? user.roles[0].name : 'profesor',
    docente_category: user.teaching_category || '',
    scientific_category: user.scientific_category || '',
    professional_level: user.professional_level || '',
    password: '',
    password_confirmation: ''
  }
  changePassword.value = false
  // Asegurar re-render también al editar
  formOpenKey.value++
  showUserForm.value = true
}

const closeUserForm = () => {
  showUserForm.value = false
  editingUserId.value = null
  isSubmitting.value = false
  formErrors.value = {}
}

const handleUserSubmit = () => {
  // Sanitizar y validar CI (REQUERIDO: exactamente 11 dígitos)
  const ciSanitized = String(userFormData.value.ci || '').replace(/\D/g, '')
  formErrors.value = { ...formErrors.value, ci: undefined, professional_level: undefined }
  if (ciSanitized.length !== 11) {
    formErrors.value.ci = 'El CI es requerido y debe contener exactamente 11 dígitos.'
    // mantener entradas de contraseña en blanco ante errores
    userFormData.value.password = ''
    userFormData.value.password_confirmation = ''
    return
  }
  // Validar nivel profesional requerido
  if (!userFormData.value.professional_level) {
    formErrors.value.professional_level = 'El nivel profesional es requerido.'
    userFormData.value.password = ''
    userFormData.value.password_confirmation = ''
    return
  }

  const payload = {
    name: userFormData.value.name,
    email: userFormData.value.email,
    ci: ciSanitized || null,
    department_id: userFormData.value.department_id || null,
    role: userFormData.value.role,
    teaching_category: userFormData.value.docente_category || null,
    scientific_category: userFormData.value.scientific_category || null,
    professional_level: userFormData.value.professional_level || null,
  }

  // Validación/envío de contraseña
  if (!editingUserId.value || changePassword.value) {
    if (!userFormData.value.password || userFormData.value.password.length < 8) {
      alert('La contraseña debe tener al menos 8 caracteres.')
      return
    }
    if (userFormData.value.password !== userFormData.value.password_confirmation) {
      alert('Las contraseñas no coinciden.')
      return
    }
    // Validación de complejidad ya está también en patrón HTML y backend
    payload.password = userFormData.value.password
    payload.password_confirmation = userFormData.value.password_confirmation
  }
  isSubmitting.value = true
  formErrors.value = {}

  const commonHandlers = {
    onStart: () => { isSubmitting.value = true },
    onError: (errors) => {
      formErrors.value = errors || {}
      // Mostrar el primer error de forma visible si no hay UI específica
      const firstKey = Object.keys(formErrors.value)[0]
      if (firstKey) {
        alert(String(formErrors.value[firstKey]))
      }
    },
    onFinish: () => { isSubmitting.value = false },
  }

  if (editingUserId.value) {
    router.put(`/admin/users/${editingUserId.value}`, payload, {
      ...commonHandlers,
      onSuccess: () => {
        // limpiar campos sensibles
        userFormData.value.password = ''
        userFormData.value.password_confirmation = ''
        closeUserForm()
        router.reload({ only: ['initialUsers'] })
      },
    })
  } else {
    router.post('/admin/users', payload, {
      ...commonHandlers,
      onSuccess: () => {
        // limpiar campos sensibles
        userFormData.value.password = ''
        userFormData.value.password_confirmation = ''
        closeUserForm()
        router.reload({ only: ['initialUsers'] })
      },
    })
  }
}

const openStatusModal = (user) => {
  userToToggle.value = user
  showStatusModal.value = true
}

const closeStatusModal = () => {
  showStatusModal.value = false
  userToToggle.value = null
}

const confirmStatusToggle = () => {
  if (!userToToggle.value) return
  const id = userToToggle.value.id
  const next = !userToToggle.value.is_enabled
  router.put(`/admin/users/${id}/status`, { is_enabled: next }, {
    onSuccess: () => {
      closeStatusModal()
      router.reload({ only: ['initialUsers'] })
    },
    onError: () => {
      // Mantener modal abierto para mostrar posibles errores si los mapeas a la UI
    }
  })
}

// Detalles de usuario
const openUserDetails = (user) => {
  selectedUser.value = user
  showUserDetails.value = true
}

const closeUserDetails = () => {
  showUserDetails.value = false
  selectedUser.value = null
}

// Formulario de departamento
const openDepartmentForm = () => {
  editingDepartmentId.value = null
  departmentFormData.value = {
    name: '',
    description: ''
  }
  showDepartmentForm.value = true
}

const editDepartment = (department) => {
  editingDepartmentId.value = department.id
  departmentFormData.value = {
    name: department.name,
    description: department.description || ''
  }
  showDepartmentForm.value = true
}

const closeDepartmentForm = () => {
  showDepartmentForm.value = false
  editingDepartmentId.value = null
}

const handleDepartmentSubmit = () => {
  const payload = { ...departmentFormData.value }
  
  if (editingDepartmentId.value) {
    router.put(`/admin/departments/${editingDepartmentId.value}`, payload, {
      onSuccess: () => {
        closeDepartmentForm()
        router.reload({ only: ['initialDepartments'] })
      }
    })
  } else {
    router.post('/admin/departments', payload, {
      onSuccess: () => {
        closeDepartmentForm()
        router.reload({ only: ['initialDepartments'] })
      }
    })
  }
}

const deleteDepartment = (department) => {
  if (confirm(`¿Seguro que deseas eliminar el departamento "${department.name}"?`)) {
    router.delete(`/admin/departments/${department.id}`, {
      onSuccess: () => {
        router.reload({ only: ['initialDepartments'] })
      }
    })
  }
}
</script>

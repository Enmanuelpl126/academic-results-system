<template>
  <!-- Campos específicos según el tipo de publicación -->
  <div class="space-y-6">
    <!-- Campos para Journal -->
    <div v-if="type === 'journal'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">
          <div class="flex items-center gap-2">
            <HashIcon :size="16" class="text-gray-500" />
            Número <span class="text-red-500">*</span>
          </div>
        </label>
        <input
          type="text"
          :value="modelValue.number"
          @input="updateField('number', $event.target.value)"
          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
          placeholder="Número de la revista"
          required
        />
      </div>

      <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">
          <div class="flex items-center gap-2">
            <BookIcon :size="16" class="text-gray-500" />
            Volumen <span class="text-red-500">*</span>
          </div>
        </label>
        <input
          type="text"
          :value="modelValue.volume"
          @input="updateField('volume', $event.target.value)"
          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
          placeholder="Volumen de la revista"
          required
        />
      </div>

      <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">
          <div class="flex items-center gap-2">
            <LinkIcon :size="16" class="text-gray-500" />
            URL (Opcional)
          </div>
        </label>
        <input
          type="url"
          :value="modelValue.url"
          @input="updateField('url', $event.target.value)"
          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
          placeholder="https://ejemplo.com/articulo"
        />
      </div>

      <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">
          <div class="flex items-center gap-2">
            <FileTextIcon :size="16" class="text-gray-500" />
            DOI (Opcional)
          </div>
        </label>
        <input
          type="text"
          :value="modelValue.doi"
          @input="updateField('doi', $event.target.value)"
          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
          placeholder="10.1234/ejemplo.2024.1234"
        />
      </div>
    </div>

    <!-- Campos para Book -->
    <div v-else-if="type === 'book'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">
          <div class="flex items-center gap-2">
            <BuildingIcon :size="16" class="text-gray-500" />
            Editorial <span class="text-red-500">*</span>
          </div>
        </label>
        <input
          type="text"
          :value="modelValue.publisher"
          @input="updateField('publisher', $event.target.value)"
          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
          placeholder="Nombre de la editorial"
          required
        />
      </div>

      <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">
          <div class="flex items-center gap-2">
            <MapPinIcon :size="16" class="text-gray-500" />
            Ciudad <span class="text-red-500">*</span>
          </div>
        </label>
        <input
          type="text"
          :value="modelValue.city"
          @input="updateField('city', $event.target.value)"
          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
          placeholder="Ciudad de publicación"
          required
        />
      </div>
    </div>

    <!-- Campos para Book Chapter -->
    <div v-else-if="type === 'book_chapter'" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-1">
          <label class="block text-sm font-medium text-gray-700">
            <div class="flex items-center gap-2">
              <FileTextIcon :size="16" class="text-gray-500" />
              Nombre del Capítulo <span class="text-red-500">*</span>
            </div>
          </label>
          <input
            type="text"
            :value="modelValue.chapterName"
            @input="updateField('chapterName', $event.target.value)"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
            placeholder="Título del capítulo"
            required
          />
        </div>

        <div class="space-y-1">
          <label class="block text-sm font-medium text-gray-700">
            <div class="flex items-center gap-2">
              <BookOpenIcon :size="16" class="text-gray-500" />
              Nombre del Libro <span class="text-red-500">*</span>
            </div>
          </label>
          <input
            type="text"
            :value="modelValue.bookName"
            @input="updateField('bookName', $event.target.value)"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
            placeholder="Título del libro"
            required
          />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-1">
          <label class="block text-sm font-medium text-gray-700">
            <div class="flex items-center gap-2">
              <UserIcon :size="16" class="text-gray-500" />
              Autor del Libro <span class="text-red-500">*</span>
            </div>
          </label>
          <input
            type="text"
            :value="modelValue.bookAuthor"
            @input="updateField('bookAuthor', $event.target.value)"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
            placeholder="Autor principal del libro"
            required
          />
        </div>

        <div class="space-y-1">
          <label class="block text-sm font-medium text-gray-700">
            <div class="flex items-center gap-2">
              <BuildingIcon :size="16" class="text-gray-500" />
              Editorial <span class="text-red-500">*</span>
            </div>
          </label>
          <input
            type="text"
            :value="modelValue.publisher"
            @input="updateField('publisher', $event.target.value)"
            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition-colors"
            placeholder="Nombre de la editorial"
            required
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  Hash as HashIcon,
  Book as BookIcon,
  Link as LinkIcon,
  FileText as FileTextIcon,
  Building as BuildingIcon,
  MapPin as MapPinIcon,
  BookOpen as BookOpenIcon,
  User as UserIcon
} from 'lucide-vue-next'

// Props
const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (value) => ['journal', 'book', 'book_chapter'].includes(value)
  },
  modelValue: {
    type: Object,
    required: true
  }
})

// Emits
const emit = defineEmits(['update:modelValue'])

// Métodos
const updateField = (field, value) => {
  emit('update:modelValue', {
    ...props.modelValue,
    [field]: value
  })
}
</script>

<style scoped>
/* Estilos específicos si son necesarios */
</style>

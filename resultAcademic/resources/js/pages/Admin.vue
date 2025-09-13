<template>
  <Head title="Administración" />
  
  <AdminLayout>
    <div class="fixed right-4 top-4 z-50">
      <Button as-child>
        <Link :href="route('publications')" class="inline-flex items-center gap-2">
          <ArrowLeft class="h-4 w-4" />
          <span>Volver</span>
        </Link>
      </Button>
    </div>
    <AdminComponent 
      :initial-users="initialUsers" 
      :initial-departments="initialDepartments" 
    />
  </AdminLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import AdminComponent from '@/components/Admin.vue'
import { Button } from '@/components/ui/button'
import { ArrowLeft } from 'lucide-vue-next'

defineProps({
  initialUsers: {
    type: Array,
    default: () => []
  },
  initialDepartments: {
    type: Array,
    default: () => []
  }
})
</script>

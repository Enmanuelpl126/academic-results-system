<script setup lang="ts">
import NoNavLayout from '@/layouts/NoNavLayout.vue';
import UserProfileCard from '@/components/UserProfileCard.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { computed, ref } from 'vue';

const page = usePage();
const user = (page.props as any)?.user || (page.props as any)?.auth?.user;
const stats = computed(() => ({
  publications: (page.props as any)?.publications_count ?? 0,
  awards: (page.props as any)?.awards_count ?? 0,
  recognitions: (page.props as any)?.recognitions_count ?? 0,
  events: (page.props as any)?.events_count ?? 0,
}));
const departments = computed(() => (page.props as any)?.departments ?? []);

// Trigger to notify the child component to exit edit mode after a successful save
const savedKey = ref(0);

// Keep a local copy of the user so the UI can reflect changes immediately on success
const userLocal = ref<any>({ ...(user || {}) });

function handleSave(payload: Record<string, any>) {
  router.patch('/settings/profile', payload, {
    preserveScroll: true,
    onSuccess: () => {
      // Optimistically merge payload into local user so UI updates without full reload
      Object.assign(userLocal.value, payload);
      // Increase key to signal UserProfileCard to close edit mode
      savedKey.value++;
    },
  });
}

function goBack() {
  if (window.history.length > 1) {
    window.history.back();
  } else {
    router.visit('/');
  }
}
</script>

<template>
    <NoNavLayout>
        <Head title="Profile" />

        <div class="space-y-6">
            <div class="flex items-center justify-between">
              <button type="button" @click="goBack" class="inline-flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12l7.5-7.5M3 12h18" />
                </svg>
                Volver
              </button>
              <div></div>
            </div>
            <Heading title="Profile" description="View your public profile information" />

            <div class="grid grid-cols-1">
                <UserProfileCard
                  :user="userLocal"
                  :stats="stats"
                  :departments="departments"
                  :savedKey="savedKey"
                  @save="handleSave"
                />
            </div>
        </div>
    </NoNavLayout>
</template>

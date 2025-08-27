<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import Navbar from '@/components/Navbar.vue';
import AppShell from '@/components/AppShell.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const activeSection = computed(() => page.url.substring(1) || 'dashboard');
const contentMaxWidth = computed(() =>
  activeSection.value === 'awards' ? 'max-w-screen-2xl' : 'max-w-7xl'
);
</script>

<template>
    <AppShell class="flex-col">
        <Navbar :active-section="activeSection" @section-change="(section: string) => $inertia.visit(`/${section}`)" />
        <AppContent :maxWidth="contentMaxWidth">
            <slot />
        </AppContent>
    </AppShell>
</template>

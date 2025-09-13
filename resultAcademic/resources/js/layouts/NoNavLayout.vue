<script setup lang="ts">
import AppShell from '@/components/AppShell.vue';
import AppContent from '@/components/AppContent.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell class="flex-col">
        <!-- no Navbar here intentionally -->
        <AppContent>
            <slot />
        </AppContent>
    </AppShell>
</template>

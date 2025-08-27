<script setup lang="ts">
import AppLayout from '@/layouts/app/AppHeaderLayout.vue';
import { provideUserContext } from '@/composables/useUser';

provideUserContext();
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>

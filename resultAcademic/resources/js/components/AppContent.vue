<script setup lang="ts">
import { SidebarInset } from '@/components/ui/sidebar';
import { computed } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
    class?: string;
    maxWidth?: string; // Tailwind max-w-* utility, default 'max-w-7xl'
}

const props = defineProps<Props>();
const className = computed(() => props.class);
const maxWidthClass = computed(() => props.maxWidth ?? 'max-w-7xl');
</script>

<template>
    <SidebarInset v-if="props.variant === 'sidebar'" :class="className">
        <slot />
    </SidebarInset>
    <main v-else class="mx-auto flex h-full w-full flex-1 flex-col gap-4 rounded-xl" :class="[maxWidthClass, className]">
        <slot />
    </main>
</template>

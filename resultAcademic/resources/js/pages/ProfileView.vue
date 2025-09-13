<script setup lang="ts">
import NoNavLayout from '@/layouts/NoNavLayout.vue';
import UserProfileCard from '@/components/UserProfileCard.vue';
import { Head, usePage } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { computed } from 'vue';

const page = usePage();
const user = (page.props as any)?.user || (page.props as any)?.auth?.user;
const stats = computed(() => ({
  publications: (page.props as any)?.publications_count ?? 0,
  awards: (page.props as any)?.awards_count ?? 0,
  recognitions: (page.props as any)?.recognitions_count ?? 0,
  events: (page.props as any)?.events_count ?? 0,
}));
const departments = computed(() => (page.props as any)?.departments ?? []);
</script>

<template>
    <NoNavLayout>
        <Head title="Profile" />

        <div class="space-y-6">
            <Heading title="Profile" description="View your public profile information" />

            <div class="grid grid-cols-1">
                <UserProfileCard :user="user" :stats="stats" :departments="departments" />
            </div>
        </div>
    </NoNavLayout>
</template>

<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import type { User } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { LogOut, Settings, Users } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    user: User;
}

const handleLogout = () => {
    router.flushAll();
};

const page = usePage();
const canAdmin = computed(() => {
    const auth = (page?.props as any)?.auth || {};
    if (auth?.can_admin === true) return true;
    const perms = auth?.permissions || [];
    return Array.isArray(perms) && perms.includes('admin_system');
});

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full" :href="route('profile.edit')" prefetch as="button">
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem v-if="canAdmin" :as-child="true">
            <Link class="block w-full" :href="route('admin.index')" prefetch as="button">
                <Users class="mr-2 h-4 w-4" />
                Administración
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link class="block w-full" method="post" :href="route('logout')" @click="handleLogout" as="button">
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>

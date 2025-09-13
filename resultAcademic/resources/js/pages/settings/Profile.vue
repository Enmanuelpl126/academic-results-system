<script setup lang="ts">
import { Form, Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import UserProfileCard from '@/components/UserProfileCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage();
const user = page.props.auth.user as User;
const departments = computed(() => (page.props as any)?.departments ?? []);
const currentUser = ref<User>({ ...(user as any) });
const savedKey = ref(0);

// Mapear contadores de Inertia a las props que espera el componente
const stats = computed(() => ({
    publications: (page.props as any)?.publications_count ?? 0,
    awards: (page.props as any)?.awards_count ?? 0,
    recognitions: (page.props as any)?.recognitions_count ?? 0,
    events: (page.props as any)?.events_count ?? 0,
}));

function onSave(payload: { name: string; ci?: string | null; department_id?: number | string | null; teaching_category?: string | null; scientific_category?: string | null; professional_level: string }) {
    router.patch(route('profile.update'), {
        name: payload.name,
        email: (user.email || '').toLowerCase(),
        ci: payload.ci ?? null,
        department_id: payload.department_id ?? null,
        teaching_category: payload.teaching_category ?? null,
        scientific_category: payload.scientific_category ?? null,
        professional_level: payload.professional_level,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            currentUser.value = {
                ...(currentUser.value as any),
                name: payload.name,
                ci: payload.ci ?? null,
                department_id: payload.department_id ?? null,
                teaching_category: payload.teaching_category ?? null,
                scientific_category: payload.scientific_category ?? null,
                professional_level: payload.professional_level,
                department: (() => {
                    const list = departments.value as any[];
                    const found = list.find((d) => String(d.id) === String(payload.department_id ?? ''));
                    return found ? { id: found.id, name: found.name } : null;
                })(),
            } as User;
            savedKey.value++;
        },
        onError: (errors) => {
            // Opcional: aquí podríamos mostrar un toast; los errores se reflejan en UserProfileCard por el watcher.
            console.warn('Profile update failed', errors);
        }
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <UserProfileCard :user="currentUser" :stats="stats" :departments="departments" :saved-key="savedKey" @save="onSave" />

                <HeadingSmall title="Profile information" description="Update your name and email address" />

                <Form method="patch" :action="route('profile.update')" class="space-y-6" v-slot="{ errors, processing, recentlySuccessful }">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            name="name"
                            :default-value="user.name"
                            required
                            autocomplete="name"
                            placeholder="Full name"
                        />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email address</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            name="email"
                            :default-value="user.email"
                            required
                            autocomplete="username"
                            placeholder="Email address"
                        />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing">Save</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div>
                </Form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>

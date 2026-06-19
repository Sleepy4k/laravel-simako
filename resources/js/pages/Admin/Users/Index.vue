<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Pagination from '@/components/ui/Pagination.vue'
import type { User, Paginated } from '@/types/models'
import * as AdminUserController from '@/actions/App/Http/Controllers/Admin/UserController'

const props = defineProps<{
    users: Paginated<User>
    filters: { search?: string }
}>()

const search = ref(props.filters.search ?? '')

let debounce: ReturnType<typeof setTimeout>
watch(search, () => {
    clearTimeout(debounce)
    debounce = setTimeout(() => {
        router.visit(AdminUserController.index.url(), {
            data: { search: search.value || undefined },
            preserveState: true,
            replace: true,
        })
    }, 400)
})
</script>

<template>
    <DashboardLayout>
        <div>
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-6">Pengguna</h1>

            <div class="mb-4">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama, email, atau nomor HP..."
                    class="w-full max-w-md px-4 py-2 text-sm bg-white border border-(--color-border) focus:outline-none focus:border-(--color-primary)"
                />
            </div>

            <div v-if="props.users.data.length > 0" class="bg-white overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-(--color-surface) text-(--color-text-secondary)">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium">Nama</th>
                            <th class="text-left px-4 py-3 font-medium">Email</th>
                            <th class="text-left px-4 py-3 font-medium">Nomor HP</th>
                            <th class="text-left px-4 py-3 font-medium">Jenis Kelamin</th>
                            <th class="text-left px-4 py-3 font-medium">Status</th>
                            <th class="text-left px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-(--color-border)">
                        <tr v-for="user in props.users.data" :key="user.id">
                            <td class="px-4 py-3 font-medium text-(--color-text-primary)">{{ user.userProfile?.name ?? '-' }}</td>
                            <td class="px-4 py-3 text-(--color-text-secondary)">{{ user.email }}</td>
                            <td class="px-4 py-3 text-(--color-text-secondary)">{{ user.phone ?? '-' }}</td>
                            <td class="px-4 py-3 text-(--color-text-secondary)">{{ user.userProfile?.gender === 'male' ? 'Laki-laki' : user.userProfile?.gender === 'female' ? 'Perempuan' : '-' }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="user.is_active ? 'success' : 'neutral'" :label="user.is_active ? 'Aktif' : 'Nonaktif'" />
                            </td>
                            <td class="px-4 py-3">
                                <Link
                                    :href="AdminUserController.show.url(user.id)"
                                    class="text-sm font-medium text-(--color-primary) hover:text-(--color-primary-hover)"
                                >
                                    Detail
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="bg-white p-12 text-center text-(--color-text-secondary)">
                <p>Tidak ada pengguna ditemukan.</p>
            </div>

            <Pagination v-if="props.users.last_page > 1" :links="props.users.links" />
        </div>
    </DashboardLayout>
</template>

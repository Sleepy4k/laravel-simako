<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router, Head } from '@inertiajs/vue3'
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
    <Head title="Pengguna" />
    <DashboardLayout>
        <div>
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-xl font-black text-slate-900">Pengguna</h1>
                <p class="text-sm text-slate-500 mt-0.5">{{ props.users.total }} total pengguna</p>
            </div>

            <!-- Search -->
            <div class="mb-4">
                <div class="relative max-w-sm">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama, email, atau nomor HP..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-(--color-primary)/15 focus:border-(--color-primary) transition-all placeholder:text-slate-400"
                    />
                </div>
            </div>

            <!-- Table -->
            <div v-if="props.users.data.length > 0" class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Nama</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Email</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Nomor HP</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Jenis Kelamin</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="user in props.users.data" :key="user.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ user.userProfile?.name ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ user.email }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ user.phone ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ user.userProfile?.gender === 'male' ? 'Laki-laki' : user.userProfile?.gender === 'female' ? 'Perempuan' : '-' }}</td>
                                <td class="px-5 py-4">
                                    <Badge :variant="user.is_active ? 'success' : 'neutral'" :label="user.is_active ? 'Aktif' : 'Nonaktif'" />
                                </td>
                                <td class="px-5 py-4">
                                    <Link
                                        :href="AdminUserController.show.url(user.id)"
                                        class="text-sm font-semibold text-(--color-primary) hover:text-(--color-primary-hover) transition-colors"
                                    >
                                        Detail
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-else class="bg-white border border-slate-200 rounded-2xl p-16 text-center shadow-sm">
                <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Tidak ada pengguna ditemukan</p>
            </div>

            <Pagination v-if="props.users.last_page > 1" :links="props.users.links" />
        </div>
    </DashboardLayout>
</template>

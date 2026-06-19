<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router, Head } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Pagination from '@/components/ui/Pagination.vue'
import type { Kost, Paginated } from '@/types/models'
import * as AdminKostController from '@/actions/App/Http/Controllers/Admin/KostController'

const props = defineProps<{
    kosts: Paginated<Kost>
    filters?: { search?: string; status?: string }
}>()

const search = ref(props.filters?.search ?? '')

let debounce: ReturnType<typeof setTimeout>
watch(search, () => {
    clearTimeout(debounce)
    debounce = setTimeout(() => {
        router.visit(AdminKostController.index.url(), {
            data: { search: search.value || undefined },
            preserveState: true,
            replace: true,
        })
    }, 400)
})

function statusVariant(status: string): 'success' | 'neutral' | 'error' {
    const map: Record<string, 'success' | 'neutral' | 'error'> = { active: 'success', draft: 'neutral', inactive: 'error' }
    return map[status] ?? 'neutral'
}

function statusLabel(status: string): string {
    const labels: Record<string, string> = { active: 'Aktif', draft: 'Draft', inactive: 'Nonaktif' }
    return labels[status] ?? status
}
</script>

<template>
    <Head title="Kost" />
    <DashboardLayout>
        <div>
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-xl font-black text-slate-900">Kost</h1>
                <p class="text-sm text-slate-500 mt-0.5">{{ props.kosts.total }} total kost</p>
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
                        placeholder="Cari nama kost..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-(--color-primary)/15 focus:border-(--color-primary) transition-all placeholder:text-slate-400"
                    />
                </div>
            </div>

            <!-- Table -->
            <div v-if="props.kosts.data.length > 0" class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Nama Kost</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Tenant</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Kota</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Kamar</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="kost in props.kosts.data" :key="kost.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ kost.name }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ kost.tenant?.userProfile?.name ?? kost.tenant?.email ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ kost.address?.city ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ kost.available_rooms }}/{{ kost.total_rooms }}</td>
                                <td class="px-5 py-4">
                                    <Badge :variant="statusVariant(kost.status)" :label="statusLabel(kost.status)" />
                                </td>
                                <td class="px-5 py-4">
                                    <Link
                                        :href="AdminKostController.show.url(kost.id)"
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Tidak ada kost ditemukan</p>
            </div>

            <Pagination v-if="props.kosts.last_page > 1" :links="props.kosts.links" />
        </div>
    </DashboardLayout>
</template>

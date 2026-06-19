<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router, Head } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Pagination from '@/components/ui/Pagination.vue'
import type { User, Paginated } from '@/types/models'
import * as AdminTenantController from '@/actions/App/Http/Controllers/Admin/TenantController'

const props = defineProps<{
    tenants: Paginated<User>
    filters: { search?: string; status?: string }
}>()

const search = ref(props.filters.search ?? '')
const statusFilter = ref(props.filters.status ?? '')

function applyFilters() {
    router.visit(AdminTenantController.index.url(), {
        data: {
            search: search.value || undefined,
            status: statusFilter.value || undefined,
        },
        preserveState: true,
        replace: true,
    })
}

let debounce: ReturnType<typeof setTimeout>
watch(search, () => {
    clearTimeout(debounce)
    debounce = setTimeout(applyFilters, 400)
})

watch(statusFilter, applyFilters)
</script>

<template>
    <Head title="Tenant" />
    <DashboardLayout>
        <div>
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-xl font-black text-slate-900">Tenant</h1>
                <p class="text-sm text-slate-500 mt-0.5">{{ props.tenants.total }} total tenant</p>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-3 mb-4">
                <div class="relative max-w-sm w-full">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama, email..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-(--color-primary)/15 focus:border-(--color-primary) transition-all placeholder:text-slate-400"
                    />
                </div>
                <select
                    v-model="statusFilter"
                    class="px-3.5 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-(--color-primary)/15 focus:border-(--color-primary) transition-all text-slate-700"
                >
                    <option value="">Semua Status</option>
                    <option value="verified">Terverifikasi</option>
                    <option value="unverified">Belum Terverifikasi</option>
                    <option value="suspended">Ditangguhkan</option>
                </select>
            </div>

            <!-- Table -->
            <div v-if="props.tenants.data.length > 0" class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Nama / Bisnis</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Email</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Verifikasi</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="tenant in props.tenants.data" :key="tenant.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">{{ tenant.userProfile?.name ?? '-' }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ tenant.tenantProfile?.business_name ?? '' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-500">{{ tenant.email }}</td>
                                <td class="px-5 py-4">
                                    <Badge
                                        :variant="tenant.tenantProfile?.is_verified ? 'success' : 'warning'"
                                        :label="tenant.tenantProfile?.is_verified ? 'Terverifikasi' : 'Belum'"
                                    />
                                </td>
                                <td class="px-5 py-4">
                                    <Badge
                                        :variant="tenant.tenantProfile?.is_suspended ? 'error' : 'success'"
                                        :label="tenant.tenantProfile?.is_suspended ? 'Ditangguhkan' : 'Aktif'"
                                    />
                                </td>
                                <td class="px-5 py-4">
                                    <Link
                                        :href="AdminTenantController.show.url(tenant.id)"
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Tidak ada tenant ditemukan</p>
            </div>

            <Pagination v-if="props.tenants.last_page > 1" :links="props.tenants.links" />
        </div>
    </DashboardLayout>
</template>

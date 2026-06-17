<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
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
    <DashboardLayout>
        <div>
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-6">Tenant</h1>

            <div class="flex gap-3 mb-4 flex-wrap">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama, email..."
                    class="px-4 py-2 text-sm bg-white border border-(--color-border) focus:outline-none focus:border-(--color-primary) w-full max-w-xs"
                />
                <select
                    v-model="statusFilter"
                    class="px-3 py-2 text-sm bg-white border border-(--color-border) focus:outline-none focus:border-(--color-primary)"
                >
                    <option value="">Semua Status</option>
                    <option value="verified">Terverifikasi</option>
                    <option value="unverified">Belum Terverifikasi</option>
                    <option value="suspended">Ditangguhkan</option>
                </select>
            </div>

            <div v-if="props.tenants.data.length > 0" class="bg-white overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-(--color-surface) text-(--color-text-secondary)">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium">Nama / Bisnis</th>
                            <th class="text-left px-4 py-3 font-medium">Email</th>
                            <th class="text-left px-4 py-3 font-medium">Verifikasi</th>
                            <th class="text-left px-4 py-3 font-medium">Status</th>
                            <th class="text-left px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-(--color-border)">
                        <tr v-for="tenant in props.tenants.data" :key="tenant.id">
                            <td class="px-4 py-3">
                                <p class="font-medium text-(--color-text-primary)">{{ tenant.userProfile?.name ?? '-' }}</p>
                                <p class="text-xs text-(--color-text-secondary)">{{ tenant.tenantProfile?.business_name ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3 text-(--color-text-secondary)">{{ tenant.email }}</td>
                            <td class="px-4 py-3">
                                <Badge
                                    :variant="tenant.tenantProfile?.is_verified ? 'success' : 'warning'"
                                    :label="tenant.tenantProfile?.is_verified ? 'Terverifikasi' : 'Belum'"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <Badge
                                    :variant="tenant.tenantProfile?.is_suspended ? 'error' : 'success'"
                                    :label="tenant.tenantProfile?.is_suspended ? 'Ditangguhkan' : 'Aktif'"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <Link
                                    :href="AdminTenantController.show.url(tenant.id)"
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
                <p>Tidak ada tenant ditemukan.</p>
            </div>

            <Pagination v-if="props.tenants.last_page > 1" :links="props.tenants.links" />
        </div>
    </DashboardLayout>
</template>

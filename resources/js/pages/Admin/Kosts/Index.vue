<script setup lang="ts">
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
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
    <DashboardLayout>
        <div>
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-6">Kost</h1>

            <div class="mb-4">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama kost..."
                    class="w-full max-w-md px-4 py-2 text-sm bg-white border border-(--color-border) focus:outline-none focus:border-(--color-primary)"
                />
            </div>

            <div v-if="props.kosts.data.length > 0" class="bg-white overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-(--color-surface) text-(--color-text-secondary)">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium">Nama Kost</th>
                            <th class="text-left px-4 py-3 font-medium">Tenant</th>
                            <th class="text-left px-4 py-3 font-medium">Kota</th>
                            <th class="text-left px-4 py-3 font-medium">Kamar</th>
                            <th class="text-left px-4 py-3 font-medium">Status</th>
                            <th class="text-left px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-(--color-border)">
                        <tr v-for="kost in props.kosts.data" :key="kost.id">
                            <td class="px-4 py-3 font-medium text-(--color-text-primary)">{{ kost.name }}</td>
                            <td class="px-4 py-3 text-(--color-text-secondary)">{{ kost.tenant?.userProfile?.name ?? kost.tenant?.email ?? '-' }}</td>
                            <td class="px-4 py-3 text-(--color-text-secondary)">{{ kost.address?.city ?? '-' }}</td>
                            <td class="px-4 py-3 text-(--color-text-secondary)">{{ kost.available_rooms }}/{{ kost.total_rooms }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="statusVariant(kost.status)" :label="statusLabel(kost.status)" />
                            </td>
                            <td class="px-4 py-3">
                                <Link
                                    :href="AdminKostController.show.url(kost.id)"
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
                <p>Tidak ada kost ditemukan.</p>
            </div>

            <Pagination v-if="props.kosts.last_page > 1" :links="props.kosts.links" />
        </div>
    </DashboardLayout>
</template>

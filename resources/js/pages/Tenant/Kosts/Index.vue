<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Button from '@/components/ui/Button.vue'
import Pagination from '@/components/ui/Pagination.vue'
import type { Kost, Paginated } from '@/types/models'
import * as TenantKostController from '@/actions/App/Http/Controllers/Tenant/KostController'

const props = defineProps<{
    kosts: Paginated<Kost>
}>()

function statusVariant(status: string): 'success' | 'neutral' | 'error' {
    const map: Record<string, 'success' | 'neutral' | 'error'> = {
        active: 'success', draft: 'neutral', inactive: 'error',
    }
    return map[status] ?? 'neutral'
}

function statusLabel(status: string): string {
    const labels: Record<string, string> = { active: 'Aktif', draft: 'Draft', inactive: 'Nonaktif' }
    return labels[status] ?? status
}

function deleteKost(id: number) {
    if (confirm('Yakin ingin menghapus kost ini?')) {
        router.delete(TenantKostController.destroy.url(id))
    }
}
</script>

<template>
    <DashboardLayout>
        <div>
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-xl font-bold text-(--color-text-primary)">Kost Saya</h1>
                <Link :href="TenantKostController.create.url()">
                    <Button size="sm">+ Tambah Kost</Button>
                </Link>
            </div>

            <div v-if="props.kosts.data.length > 0" class="bg-white overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-(--color-surface) text-(--color-text-secondary)">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium">Nama Kost</th>
                            <th class="text-left px-4 py-3 font-medium">Kota</th>
                            <th class="text-left px-4 py-3 font-medium">Kamar</th>
                            <th class="text-left px-4 py-3 font-medium">Status</th>
                            <th class="text-left px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-(--color-border)">
                        <tr v-for="kost in props.kosts.data" :key="kost.id">
                            <td class="px-4 py-3 font-medium text-(--color-text-primary)">{{ kost.name }}</td>
                            <td class="px-4 py-3 text-(--color-text-secondary)">{{ kost.address?.city ?? '-' }}</td>
                            <td class="px-4 py-3 text-(--color-text-secondary)">{{ kost.available_rooms }}/{{ kost.total_rooms }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="statusVariant(kost.status)" :label="statusLabel(kost.status)" />
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <Link
                                        :href="TenantKostController.edit.url(kost.id)"
                                        class="text-sm text-(--color-secondary) font-medium hover:underline"
                                    >
                                        Edit
                                    </Link>
                                    <Link
                                        :href="`/dashboard/kosts/${kost.id}/rooms`"
                                        class="text-sm text-(--color-text-secondary) font-medium hover:text-(--color-text-primary)"
                                    >
                                        Kelola Kamar
                                    </Link>
                                    <button
                                        class="text-sm text-(--color-primary) font-medium hover:underline"
                                        @click="deleteKost(kost.id)"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="bg-white p-12 text-center text-(--color-text-secondary)">
                <p>Anda belum memiliki kost.</p>
                <Link :href="TenantKostController.create.url()" class="mt-3 inline-block text-sm font-semibold text-(--color-primary)">
                    Tambah kost pertama Anda
                </Link>
            </div>

            <Pagination v-if="props.kosts.last_page > 1" :links="props.kosts.links" />
        </div>
    </DashboardLayout>
</template>

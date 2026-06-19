<script setup lang="ts">
import { ref } from 'vue'
import { Link, router, Head } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Button from '@/components/ui/Button.vue'
import Pagination from '@/components/ui/Pagination.vue'
import Modal from '@/components/ui/Modal.vue'
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

const showDeleteModal = ref(false)
const kostToDelete = ref<number | null>(null)
const deleting = ref(false)

function confirmDelete(id: number) {
    kostToDelete.value = id
    showDeleteModal.value = true
}

function doDelete() {
    if (kostToDelete.value !== null) {
        deleting.value = true
        router.delete(TenantKostController.destroy.url(kostToDelete.value), {
            onSuccess: () => {
                showDeleteModal.value = false
                kostToDelete.value = null
                deleting.value = false
            },
            onError: () => {
                deleting.value = false
            }
        })
    }
}
</script>

<template>
    <Head title="Kost Saya" />
    <DashboardLayout>
        <div>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-black text-slate-900">Kost Saya</h1>
                    <p class="text-sm text-slate-500 mt-0.5">{{ props.kosts.total }} kost terdaftar</p>
                </div>
                <Link :href="TenantKostController.create.url()">
                    <Button size="sm">+ Tambah Kost</Button>
                </Link>
            </div>

            <div v-if="props.kosts.data.length > 0" class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Nama Kost</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Kota</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Kamar</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="kost in props.kosts.data" :key="kost.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ kost.name }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ kost.address?.city ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-500">{{ kost.available_rooms }}/{{ kost.total_rooms }}</td>
                                <td class="px-5 py-4">
                                    <Badge :variant="statusVariant(kost.status)" :label="statusLabel(kost.status)" />
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-4">
                                        <Link
                                            :href="TenantKostController.edit.url(kost.id)"
                                            class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors"
                                        >
                                            Edit
                                        </Link>
                                        <Link
                                            :href="`/dashboard/kosts/${kost.id}/rooms`"
                                            class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition-colors"
                                        >
                                            Kamar
                                        </Link>
                                        <button
                                            class="text-sm font-semibold text-red-500 hover:text-red-700 transition-colors cursor-pointer"
                                            @click="confirmDelete(kost.id)"
                                        >
                                            Hapus
                                        </button>
                                    </div>
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
                <p class="text-sm font-semibold text-slate-700">Belum ada kost</p>
                <Link :href="TenantKostController.create.url()" class="mt-3 inline-block text-sm font-semibold text-(--color-primary) hover:underline">
                    Tambah kost pertama Anda
                </Link>
            </div>

            <Pagination v-if="props.kosts.last_page > 1" :links="props.kosts.links" />

            <!-- Delete Confirmation Modal -->
            <Modal
                :open="showDeleteModal"
                title="Hapus Kost?"
                message="Tindakan ini tidak dapat dibatalkan. Menghapus kost ini juga akan menghapus semua kamar dan booking yang berkaitan dengan kost ini."
                confirm-label="Ya, Hapus"
                confirm-variant="danger"
                :loading="deleting"
                @confirm="doDelete"
                @cancel="showDeleteModal = false; kostToDelete = null"
            />
        </div>
    </DashboardLayout>
</template>

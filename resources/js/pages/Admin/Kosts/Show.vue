<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import type { Kost } from '@/types/models'
import * as AdminKostController from '@/actions/App/Http/Controllers/Admin/KostController'

const props = defineProps<{
    kost: Kost
}>()

function statusVariant(status: string): 'success' | 'neutral' | 'error' {
    const map: Record<string, 'success' | 'neutral' | 'error'> = { active: 'success', draft: 'neutral', inactive: 'error' }
    return map[status] ?? 'neutral'
}

function statusLabel(status: string): string {
    const labels: Record<string, string> = { active: 'Aktif', draft: 'Draft', inactive: 'Nonaktif' }
    return labels[status] ?? status
}

const typeLabel = computed(() => {
    const map: Record<string, string> = { putra: 'Putra', putri: 'Putri', campur: 'Campur' }
    return map[props.kost.type] ?? props.kost.type
})
</script>

<template>
    <DashboardLayout>
        <div class="max-w-2xl">
            <Link :href="AdminKostController.index.url()" class="text-sm text-(--color-text-secondary) hover:text-(--color-primary) mb-6 inline-block">← Kembali</Link>

            <div class="bg-white p-5 mb-4">
                <div class="flex items-start justify-between mb-4">
                    <h1 class="text-xl font-bold text-(--color-text-primary)">{{ props.kost.name }}</h1>
                    <div class="flex gap-2">
                        <Badge :variant="statusVariant(props.kost.status)" :label="statusLabel(props.kost.status)" />
                        <Badge variant="neutral" :label="typeLabel" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                    <div>
                        <p class="text-(--color-text-secondary)">Pemilik</p>
                        <p class="font-medium text-(--color-text-primary)">{{ props.kost.tenant?.userProfile?.name ?? props.kost.tenant?.email ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-(--color-text-secondary)">Kota</p>
                        <p class="font-medium text-(--color-text-primary)">{{ props.kost.address?.city ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-(--color-text-secondary)">Total Kamar</p>
                        <p class="font-medium text-(--color-text-primary)">{{ props.kost.total_rooms }}</p>
                    </div>
                    <div>
                        <p class="text-(--color-text-secondary)">Kamar Tersedia</p>
                        <p class="font-medium text-(--color-text-primary)">{{ props.kost.available_rooms }}</p>
                    </div>
                </div>

                <div v-if="props.kost.address" class="text-sm text-(--color-text-secondary)">
                    {{ [props.kost.address.street, props.kost.address.district, props.kost.address.city, props.kost.address.province].filter(Boolean).join(', ') }}
                </div>
            </div>

            <div v-if="props.kost.description" class="bg-white p-5 mb-4">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-2">Deskripsi</p>
                <p class="text-sm text-(--color-text-primary) leading-relaxed">{{ props.kost.description }}</p>
            </div>

            <div v-if="props.kost.rooms?.length" class="bg-white p-5">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-3">Kamar ({{ props.kost.rooms.length }})</p>
                <div class="space-y-2">
                    <div v-for="room in props.kost.rooms" :key="room.id" class="flex items-center justify-between py-2 border-b border-(--color-border) last:border-0 text-sm">
                        <span class="text-(--color-text-primary)">{{ room.name }}</span>
                        <span class="text-xs" :class="room.is_available ? 'text-green-700' : 'text-gray-500'">
                            {{ room.is_available ? 'Tersedia' : 'Terisi' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

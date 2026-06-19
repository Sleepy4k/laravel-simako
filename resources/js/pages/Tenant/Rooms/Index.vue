<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Button from '@/components/ui/Button.vue'
import type { Kost } from '@/types/models'
import * as TenantRoomController from '@/actions/App/Http/Controllers/Tenant/RoomController'

const props = defineProps<{
    kost: Kost
}>()

function formatCurrency(amount: number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount)
}

const periodLabel: Record<string, string> = {
    monthly: 'Bln', quarterly: '3Bln', semi_annual: '6Bln', annual: 'Thn',
}
</script>

<template>
    <Head :title="`Kamar - ${props.kost.name}`" />
    <DashboardLayout>
        <div>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold text-(--color-text-primary)">Kamar - {{ props.kost.name }}</h1>
                    <Link :href="`/dashboard/kosts`" class="text-sm text-(--color-text-secondary) hover:text-(--color-primary)">← Kembali ke daftar kost</Link>
                </div>
                <Link :href="TenantRoomController.create.url(props.kost.id)">
                    <Button size="sm">+ Tambah Kamar</Button>
                </Link>
            </div>

            <div v-if="props.kost.rooms?.length" class="bg-white overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-(--color-surface) text-(--color-text-secondary)">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium">Nama Kamar</th>
                            <th class="text-left px-4 py-3 font-medium">Lantai</th>
                            <th class="text-left px-4 py-3 font-medium">Ukuran</th>
                            <th class="text-left px-4 py-3 font-medium">Status</th>
                            <th class="text-left px-4 py-3 font-medium">Harga</th>
                            <th class="text-left px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-(--color-border)">
                        <tr v-for="room in props.kost.rooms" :key="room.id">
                            <td class="px-4 py-3 font-medium text-(--color-text-primary)">{{ room.name }}</td>
                            <td class="px-4 py-3 text-(--color-text-secondary)">{{ room.floor ?? '-' }}</td>
                            <td class="px-4 py-3 text-(--color-text-secondary)">{{ room.size_sqm ? `${room.size_sqm} m²` : '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-0.5" :class="room.is_available ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'">
                                    {{ room.is_available ? 'Tersedia' : 'Terisi' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-(--color-text-secondary)">
                                <span v-if="room.prices?.length">
                                    <span v-for="p in room.prices" :key="p.id" class="mr-2 text-xs">
                                        {{ periodLabel[p.period] }}: {{ formatCurrency(p.price) }}
                                    </span>
                                </span>
                                <span v-else>-</span>
                            </td>
                            <td class="px-4 py-3">
                                <Link
                                    :href="TenantRoomController.edit.url({ kost: props.kost.id, room: room.id })"
                                    class="text-sm text-(--color-secondary) font-medium hover:underline"
                                >
                                    Edit
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="bg-white p-12 text-center text-(--color-text-secondary)">
                <p>Kost ini belum memiliki kamar.</p>
                <Link :href="TenantRoomController.create.url(props.kost.id)" class="mt-3 inline-block text-sm font-semibold text-(--color-primary)">
                    Tambah kamar pertama
                </Link>
            </div>
        </div>
    </DashboardLayout>
</template>

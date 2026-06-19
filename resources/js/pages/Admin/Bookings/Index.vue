<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Pagination from '@/components/ui/Pagination.vue'
import type { Booking, Paginated } from '@/types/models'
import * as AdminBookingController from '@/actions/App/Http/Controllers/Admin/BookingController'

const props = defineProps<{
    bookings: Paginated<Booking>
    filters?: Record<string, string>
}>()

function statusVariant(status: string): 'warning' | 'blue' | 'success' | 'neutral' | 'error' {
    const map: Record<string, 'warning' | 'blue' | 'success' | 'neutral' | 'error'> = {
        pending: 'warning', approved: 'blue', active: 'success', ended: 'neutral', cancelled: 'error',
    }
    return map[status] ?? 'neutral'
}

function statusLabel(status: string): string {
    const labels: Record<string, string> = {
        pending: 'Menunggu', approved: 'Disetujui', active: 'Aktif', ended: 'Selesai', cancelled: 'Dibatalkan',
    }
    return labels[status] ?? status
}

function formatDate(date: string) {
    return new Intl.DateTimeFormat('id-ID', { year: 'numeric', month: 'short', day: 'numeric' }).format(new Date(date))
}
</script>

<template>
    <Head title="Booking" />
    <DashboardLayout>
        <div>
            <div class="mb-6">
                <h1 class="text-xl font-black text-slate-900">Semua Booking</h1>
                <p class="text-sm text-slate-500 mt-0.5">{{ props.bookings.total }} total booking</p>
            </div>

            <div v-if="props.bookings.data.length > 0" class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Penyewa</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Kamar / Kost</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Mulai</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="booking in props.bookings.data" :key="booking.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ booking.user?.userProfile?.name ?? booking.user?.email ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <p class="font-medium text-slate-900">{{ booking.room?.name ?? '-' }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ booking.room?.kost?.name ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-500">{{ formatDate(booking.start_date) }}</td>
                                <td class="px-5 py-4">
                                    <Badge :variant="statusVariant(booking.status)" :label="statusLabel(booking.status)" />
                                </td>
                                <td class="px-5 py-4">
                                    <Link
                                        :href="AdminBookingController.show.url(booking.id)"
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
                <p class="text-sm font-semibold text-slate-700">Tidak ada booking ditemukan</p>
            </div>

            <Pagination v-if="props.bookings.last_page > 1" :links="props.bookings.links" />
        </div>
    </DashboardLayout>
</template>

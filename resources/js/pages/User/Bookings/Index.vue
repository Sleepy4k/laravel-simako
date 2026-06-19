<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Pagination from '@/components/ui/Pagination.vue'
import type { Booking, Paginated } from '@/types/models'
import * as UserBookingController from '@/actions/App/Http/Controllers/User/BookingController'

const props = defineProps<{
    bookings: Paginated<Booking>
}>()

function statusVariant(status: string): 'warning' | 'blue' | 'success' | 'neutral' | 'error' {
    switch (status) {
        case 'pending': return 'warning'
        case 'approved': return 'blue'
        case 'active': return 'success'
        case 'ended': return 'neutral'
        case 'cancelled': return 'error'
        default: return 'neutral'
    }
}

function statusLabel(status: string): string {
    const labels: Record<string, string> = {
        pending: 'Menunggu',
        approved: 'Disetujui',
        active: 'Aktif',
        ended: 'Selesai',
        cancelled: 'Dibatalkan',
    }
    return labels[status] ?? status
}

function formatDate(date: string) {
    return new Intl.DateTimeFormat('id-ID', { year: 'numeric', month: 'short', day: 'numeric' }).format(new Date(date))
}
</script>

<template>
    <Head title="Booking Saya" />
    <DashboardLayout>
        <div>
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-6">Booking Saya</h1>

            <div v-if="props.bookings.data.length > 0" class="bg-white overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-(--color-surface) text-(--color-text-secondary)">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium">Kamar / Kost</th>
                            <th class="text-left px-4 py-3 font-medium">Mulai</th>
                            <th class="text-left px-4 py-3 font-medium">Status</th>
                            <th class="text-left px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-(--color-border)">
                        <tr v-for="booking in props.bookings.data" :key="booking.id">
                            <td class="px-4 py-3">
                                <p class="font-medium text-(--color-text-primary)">{{ booking.room?.name ?? '-' }}</p>
                                <p class="text-xs text-(--color-text-secondary)">{{ booking.room?.kost?.name ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-(--color-text-secondary)">{{ formatDate(booking.start_date) }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="statusVariant(booking.status)" :label="statusLabel(booking.status)" />
                            </td>
                            <td class="px-4 py-3">
                                <Link
                                    :href="UserBookingController.show.url(booking.id)"
                                    class="text-sm font-medium text-(--color-primary) hover:text-(--color-primary-hover)"
                                >
                                    Lihat Detail
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="bg-white p-12 text-center text-(--color-text-secondary)">
                <p>Anda belum memiliki booking.</p>
                <a href="/kosts" class="mt-3 inline-block text-sm font-semibold text-(--color-primary)">Cari kost sekarang</a>
            </div>

            <Pagination v-if="props.bookings.last_page > 1" :links="props.bookings.links" />
        </div>
    </DashboardLayout>
</template>

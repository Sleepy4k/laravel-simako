<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import type { Booking } from '@/types/models'
import * as AdminBookingController from '@/actions/App/Http/Controllers/Admin/BookingController'

const props = defineProps<{
    booking: Booking
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

function paymentStatusLabel(status: string): string {
    const labels: Record<string, string> = {
        unpaid: 'Belum Bayar', pending_verification: 'Menunggu Verifikasi', paid: 'Lunas', declined: 'Ditolak',
    }
    return labels[status] ?? status
}

function formatDate(date: string) {
    return new Intl.DateTimeFormat('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }).format(new Date(date))
}

function formatCurrency(amount: number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount)
}
</script>

<template>
    <DashboardLayout>
        <div class="max-w-2xl">
            <Link :href="AdminBookingController.index.url()" class="text-sm text-(--color-text-secondary) hover:text-(--color-primary) mb-6 inline-block">← Kembali</Link>

            <div class="flex items-center gap-3 mb-6">
                <h1 class="text-xl font-bold text-(--color-text-primary)">Detail Booking #{{ props.booking.id }}</h1>
                <Badge :variant="statusVariant(props.booking.status)" :label="statusLabel(props.booking.status)" />
            </div>

            <!-- Penyewa -->
            <div class="bg-white p-5 mb-4">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-3">Penyewa</p>
                <p class="text-base font-semibold text-(--color-text-primary)">{{ props.booking.user?.userProfile?.name ?? props.booking.user?.email ?? '-' }}</p>
                <p v-if="props.booking.user?.email" class="text-sm text-(--color-text-secondary)">{{ props.booking.user.email }}</p>
                <p v-if="props.booking.user?.phone" class="text-sm text-(--color-text-secondary)">{{ props.booking.user.phone }}</p>
            </div>

            <!-- Kamar -->
            <div class="bg-white p-5 mb-4">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-3">Kamar</p>
                <p class="text-base font-semibold text-(--color-text-primary)">{{ props.booking.room?.kost?.name ?? '-' }}</p>
                <p class="text-sm text-(--color-text-secondary)">Kamar: {{ props.booking.room?.name ?? '-' }}</p>
                <p class="text-sm text-(--color-text-secondary) mt-1">Mulai: {{ formatDate(props.booking.start_date) }}</p>
                <p v-if="props.booking.end_date" class="text-sm text-(--color-text-secondary)">Selesai: {{ formatDate(props.booking.end_date) }}</p>
                <p v-if="props.booking.cancellation_reason" class="text-sm text-(--color-primary) mt-2">Alasan batal: {{ props.booking.cancellation_reason }}</p>
            </div>

            <!-- Pembayaran -->
            <div v-if="props.booking.payments?.length" class="bg-white p-5">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-3">Pembayaran</p>
                <div class="space-y-2">
                    <div
                        v-for="payment in props.booking.payments"
                        :key="payment.id"
                        class="flex items-center justify-between py-2 border-b border-(--color-border) last:border-0"
                    >
                        <div>
                            <p class="text-sm text-(--color-text-primary)">{{ formatDate(payment.period_start) }}</p>
                            <p class="text-xs text-(--color-text-secondary)">{{ formatCurrency(payment.amount) }}</p>
                        </div>
                        <span class="text-xs text-(--color-text-secondary)">{{ paymentStatusLabel(payment.status) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

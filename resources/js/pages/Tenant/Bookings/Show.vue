<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Button from '@/components/ui/Button.vue'
import type { Booking } from '@/types/models'
import * as TenantBookingController from '@/actions/App/Http/Controllers/Tenant/BookingController'

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

const showRejectInput = ref(false)
const approveForm = useForm({})
const rejectForm = useForm({ cancellation_reason: '' })

function approve() {
    approveForm.patch(TenantBookingController.approve.url(props.booking.id))
}

function reject() {
    rejectForm.patch(TenantBookingController.reject.url(props.booking.id))
}
</script>

<template>
    <DashboardLayout>
        <div class="max-w-2xl">
            <div class="flex items-center gap-3 mb-6">
                <h1 class="text-xl font-bold text-(--color-text-primary)">Detail Booking</h1>
                <Badge :variant="statusVariant(props.booking.status)" :label="statusLabel(props.booking.status)" />
            </div>

            <!-- User info -->
            <div class="bg-white p-5 mb-4">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-3">Data Penyewa</p>
                <p class="text-base font-semibold text-(--color-text-primary)">{{ props.booking.user?.userProfile?.name ?? props.booking.user?.email ?? '-' }}</p>
                <p v-if="props.booking.user?.email" class="text-sm text-(--color-text-secondary)">{{ props.booking.user.email }}</p>
                <p v-if="props.booking.user?.phone" class="text-sm text-(--color-text-secondary)">{{ props.booking.user.phone }}</p>
            </div>

            <!-- Room info -->
            <div class="bg-white p-5 mb-4">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-3">Informasi Kamar</p>
                <p class="text-base font-semibold text-(--color-text-primary)">{{ props.booking.room?.kost?.name ?? '-' }}</p>
                <p class="text-sm text-(--color-text-secondary)">Kamar: {{ props.booking.room?.name ?? '-' }}</p>
                <p class="text-sm text-(--color-text-secondary) mt-1">Mulai: {{ formatDate(props.booking.start_date) }}</p>
                <p v-if="props.booking.end_date" class="text-sm text-(--color-text-secondary)">Selesai: {{ formatDate(props.booking.end_date) }}</p>
                <p v-if="props.booking.notes" class="text-sm text-(--color-text-secondary) mt-2">Catatan: {{ props.booking.notes }}</p>
            </div>

            <!-- Payments -->
            <div v-if="props.booking.payments?.length" class="bg-white p-5 mb-4">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-3">Riwayat Pembayaran</p>
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

            <!-- Actions for pending -->
            <div v-if="props.booking.status === 'pending'" class="bg-white p-5">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-4">Tindakan</p>

                <div class="flex gap-3 mb-3">
                    <Button @click="approve" :loading="approveForm.processing" color="green">Setujui</Button>
                    <Button variant="outline" @click="showRejectInput = !showRejectInput">Tolak</Button>
                </div>

                <div v-if="showRejectInput" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-(--color-text-primary) mb-1">Alasan Penolakan</label>
                        <textarea
                            v-model="rejectForm.cancellation_reason"
                            rows="3"
                            class="w-full px-3 py-2 text-sm border border-(--color-border) focus:outline-none focus:border-(--color-primary)"
                            placeholder="Masukkan alasan penolakan..."
                        />
                        <p v-if="rejectForm.errors.cancellation_reason" class="mt-1 text-xs text-(--color-primary)">{{ rejectForm.errors.cancellation_reason }}</p>
                    </div>
                    <Button variant="outline" @click="reject" :loading="rejectForm.processing">Konfirmasi Tolak</Button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

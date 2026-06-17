<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Button from '@/components/ui/Button.vue'
import Modal from '@/components/ui/Modal.vue'
import type { Booking } from '@/types/models'
import * as UserBookingController from '@/actions/App/Http/Controllers/User/BookingController'

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

function paymentStatusVariant(status: string): 'warning' | 'success' | 'error' | 'neutral' {
    const map: Record<string, 'warning' | 'success' | 'error' | 'neutral'> = {
        unpaid: 'warning', pending_verification: 'warning', paid: 'success', declined: 'error',
    }
    return map[status] ?? 'neutral'
}

function formatDate(date: string) {
    return new Intl.DateTimeFormat('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }).format(new Date(date))
}

function formatCurrency(amount: number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount)
}

const isCancellable = ['pending', 'approved'].includes(props.booking.status)
const showCancelModal = ref(false)
const cancelForm = useForm({ cancellation_reason: '' })

function doCancel() {
    cancelForm.patch(UserBookingController.cancel.url(props.booking.id), {
        onSuccess: () => {
            showCancelModal.value = false
        }
    })
}
</script>

<template>
    <DashboardLayout>
        <div class="max-w-2xl">
            <div class="flex items-center gap-3 mb-6">
                <h1 class="text-xl font-bold text-(--color-text-primary)">Detail Booking</h1>
                <Badge :variant="statusVariant(props.booking.status)" :label="statusLabel(props.booking.status)" />
            </div>

            <!-- Room & Kost info -->
            <div class="bg-white p-5 mb-4">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-2">Informasi Kamar</p>
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
                            <p class="text-sm text-(--color-text-primary)">
                                {{ formatDate(payment.period_start) }} – {{ formatDate(payment.period_end) }}
                            </p>
                            <p class="text-xs text-(--color-text-secondary)">{{ formatCurrency(payment.amount) }}</p>
                        </div>
                        <Badge :variant="paymentStatusVariant(payment.status)" :label="payment.status" />
                    </div>
                </div>
            </div>

            <!-- Cancel -->
            <div v-if="isCancellable" class="bg-white p-5 border border-(--color-border) rounded-2xl shadow-sm">
                <Button variant="outline" @click="showCancelModal = true">
                    Batalkan Booking
                </Button>
            </div>

            <!-- Cancel Confirmation Modal -->
            <Modal
                :open="showCancelModal"
                title="Batalkan Pengajuan Booking?"
                message="Tindakan ini tidak dapat dibatalkan. Kamar ini akan tersedia kembali untuk penyewa lain."
                confirm-label="Ya, Batalkan"
                confirm-variant="danger"
                :loading="cancelForm.processing"
                @confirm="doCancel"
                @cancel="showCancelModal = false"
            />
        </div>
    </DashboardLayout>
</template>

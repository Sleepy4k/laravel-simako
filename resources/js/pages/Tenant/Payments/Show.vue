<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Button from '@/components/ui/Button.vue'
import Modal from '@/components/ui/Modal.vue'
import type { Payment } from '@/types/models'
import * as TenantPaymentController from '@/actions/App/Http/Controllers/Tenant/PaymentController'

const props = defineProps<{
    payment: Payment
}>()

function statusVariant(status: string): 'warning' | 'success' | 'error' | 'neutral' {
    const map: Record<string, 'warning' | 'success' | 'error' | 'neutral'> = {
        unpaid: 'warning', pending_verification: 'warning', paid: 'success', declined: 'error',
    }
    return map[status] ?? 'neutral'
}

function statusLabel(status: string): string {
    const labels: Record<string, string> = {
        unpaid: 'Belum Bayar', pending_verification: 'Menunggu Verifikasi', paid: 'Lunas', declined: 'Ditolak',
    }
    return labels[status] ?? status
}

function formatCurrency(amount: number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount)
}

function formatDate(date: string) {
    return new Intl.DateTimeFormat('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }).format(new Date(date))
}

const showApproveModal = ref(false)
const showDeclineModal = ref(false)
const selectedProof = ref<string | null>(null)

const approveForm = useForm({})
const declineForm = useForm({ decline_notes: '' })

function approve() {
    approveForm.patch(TenantPaymentController.approve.url(props.payment.id), {
        onSuccess: () => {
            showApproveModal.value = false
        }
    })
}

function decline() {
    declineForm.patch(TenantPaymentController.decline.url(props.payment.id), {
        onSuccess: () => {
            showDeclineModal.value = false
        }
    })
}
</script>

<template>
    <DashboardLayout>
        <div class="max-w-2xl">
            <div class="flex items-center gap-3 mb-6">
                <h1 class="text-xl font-bold text-(--color-text-primary)">Verifikasi Pembayaran</h1>
                <Badge :variant="statusVariant(props.payment.status)" :label="statusLabel(props.payment.status)" />
            </div>

            <!-- Payment info -->
            <div class="bg-white p-5 mb-4 rounded-2xl border border-(--color-border) shadow-sm">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-(--color-text-secondary) mb-0.5">Penyewa</p>
                        <p class="font-medium text-(--color-text-primary)">{{ props.payment.booking?.user?.userProfile?.name ?? props.payment.booking?.user?.email ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-(--color-text-secondary) mb-0.5">Jumlah</p>
                        <p class="font-bold text-(--color-primary)">{{ formatCurrency(props.payment.amount) }}</p>
                    </div>
                    <div>
                        <p class="text-(--color-text-secondary) mb-0.5">Periode</p>
                        <p class="font-medium text-(--color-text-primary)">{{ formatDate(props.payment.period_start) }} – {{ formatDate(props.payment.period_end) }}</p>
                    </div>
                    <div>
                        <p class="text-(--color-text-secondary) mb-0.5">Jatuh Tempo</p>
                        <p class="font-medium text-(--color-text-primary)">{{ formatDate(props.payment.due_date) }}</p>
                    </div>
                </div>
            </div>

            <!-- Proof images -->
            <div v-if="props.payment.proofs?.length" class="bg-white p-5 mb-4 rounded-2xl border border-(--color-border) shadow-sm">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-3">Bukti Pembayaran ({{ props.payment.proofs.length }})</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div
                        v-for="proof in props.payment.proofs"
                        :key="proof.id"
                        class="cursor-pointer overflow-hidden rounded-xl border border-(--color-border) hover:opacity-85 transition-opacity"
                        @click="selectedProof = proof.path"
                    >
                        <img :src="proof.path" class="w-full h-28 object-cover" :alt="`Bukti ${proof.id}`" />
                    </div>
                </div>
            </div>

            <!-- Proof lightbox -->
            <div
                v-if="selectedProof"
                class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4"
                @click.self="selectedProof = null"
            >
                <img :src="selectedProof" class="max-w-full max-h-full object-contain rounded-xl" />
                <button class="absolute top-4 right-4 text-white text-xl" @click="selectedProof = null">✕</button>
            </div>

            <!-- Actions -->
            <div v-if="props.payment.status === 'pending_verification'" class="bg-white p-5 rounded-2xl border border-(--color-border) shadow-sm">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-4">Tindakan</p>

                <div class="flex gap-3">
                    <Button @click="showApproveModal = true">Setujui Pembayaran</Button>
                    <Button variant="outline" @click="showDeclineModal = true">Tolak</Button>
                </div>
            </div>

            <!-- Approve Confirmation Modal -->
            <Modal
                :open="showApproveModal"
                title="Setujui Pembayaran?"
                message="Apakah Anda yakin ingin menyetujui pembayaran ini? Booking akan diperbarui/diaktifkan secara otomatis."
                confirm-label="Ya, Setujui"
                confirm-variant="primary"
                :loading="approveForm.processing"
                @confirm="approve"
                @cancel="showApproveModal = false"
            />

            <!-- Decline Confirmation Modal -->
            <Modal
                :open="showDeclineModal"
                title="Tolak Pembayaran?"
                confirm-label="Ya, Tolak"
                confirm-variant="danger"
                :loading="declineForm.processing"
                @confirm="decline"
                @cancel="showDeclineModal = false"
            >
                <div class="mt-2 text-left">
                    <label class="block text-sm font-semibold text-(--color-text-primary) mb-1.5">Alasan Penolakan</label>
                    <textarea
                        v-model="declineForm.decline_notes"
                        rows="3"
                        class="w-full px-3.5 py-2.5 text-sm border border-(--color-border) rounded-xl focus:outline-none focus:ring-2 focus:ring-(--color-primary)/20 focus:border-(--color-primary) transition-all placeholder:text-(--color-text-secondary)/60"
                        placeholder="Contoh: Bukti tidak jelas, nominal tidak sesuai..."
                        required
                    />
                    <p v-if="declineForm.errors.decline_notes" class="mt-1.5 text-xs font-semibold text-(--color-primary)">{{ declineForm.errors.decline_notes }}</p>
                </div>
            </Modal>
        </div>
    </DashboardLayout>
</template>

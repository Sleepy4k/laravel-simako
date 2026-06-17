<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Button from '@/components/ui/Button.vue'
import type { Payment, BankAccount } from '@/types/models'

const props = defineProps<{
    payment: Payment
    bankAccount?: BankAccount
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

const proofForm = useForm({ proof: null as File | null })

function handleFileChange(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0] ?? null
    proofForm.proof = file
}

function submitProof() {
    proofForm.post(`/dashboard/payments/${props.payment.id}/proof`, { forceFormData: true })
}
</script>

<template>
    <DashboardLayout>
        <div class="max-w-xl">
            <div class="flex items-center gap-3 mb-6">
                <h1 class="text-xl font-bold text-(--color-text-primary)">Detail Pembayaran</h1>
                <Badge :variant="statusVariant(props.payment.status)" :label="statusLabel(props.payment.status)" />
            </div>

            <!-- Payment info -->
            <div class="bg-white p-5 mb-4">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-(--color-text-secondary) mb-0.5">Periode</p>
                        <p class="font-medium text-(--color-text-primary)">
                            {{ formatDate(props.payment.period_start) }} – {{ formatDate(props.payment.period_end) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-(--color-text-secondary) mb-0.5">Jumlah</p>
                        <p class="font-bold text-(--color-primary)">{{ formatCurrency(props.payment.amount) }}</p>
                    </div>
                    <div>
                        <p class="text-(--color-text-secondary) mb-0.5">Jatuh Tempo</p>
                        <p class="font-medium text-(--color-text-primary)">{{ formatDate(props.payment.due_date) }}</p>
                    </div>
                </div>

                <!-- Decline notes -->
                <div v-if="props.payment.status === 'declined' && props.payment.decline_notes" class="mt-4 p-3 bg-red-50">
                    <p class="text-xs font-semibold text-(--color-primary) mb-1">Alasan Penolakan</p>
                    <p class="text-sm text-(--color-text-primary)">{{ props.payment.decline_notes }}</p>
                </div>
            </div>

            <!-- Bank Account -->
            <div v-if="props.bankAccount" class="bg-white p-5 mb-4">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-3">Transfer ke Rekening</p>
                <p class="text-base font-semibold text-(--color-text-primary)">{{ props.bankAccount.bank_name }}</p>
                <p class="text-sm text-(--color-text-secondary)">{{ props.bankAccount.account_number }}</p>
                <p class="text-sm text-(--color-text-secondary)">a/n {{ props.bankAccount.account_holder }}</p>
            </div>

            <!-- Upload proof -->
            <div v-if="['unpaid', 'declined'].includes(props.payment.status)" class="bg-white p-5 mb-4">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-3">Upload Bukti Bayar</p>
                <form @submit.prevent="submitProof" class="space-y-3">
                    <input
                        type="file"
                        accept="image/*,.pdf"
                        class="block w-full text-sm text-(--color-text-secondary) file:mr-4 file:py-2 file:px-4 file:bg-(--color-primary) file:text-white file:text-sm file:font-medium file:border-0 file:cursor-pointer hover:file:bg-(--color-primary-hover)"
                        @change="handleFileChange"
                    />
                    <p v-if="proofForm.errors.proof" class="text-xs text-(--color-primary)">{{ proofForm.errors.proof }}</p>
                    <Button type="submit" size="sm" :loading="proofForm.processing" :disabled="!proofForm.proof">
                        Upload Bukti
                    </Button>
                </form>
            </div>

            <!-- Proof history -->
            <div v-if="props.payment.proofs?.length" class="bg-white p-5">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-3">Riwayat Bukti Pembayaran</p>
                <div class="space-y-2">
                    <a
                        v-for="proof in props.payment.proofs"
                        :key="proof.id"
                        :href="proof.path"
                        target="_blank"
                        class="flex items-center gap-2 text-sm text-(--color-secondary) hover:underline"
                    >
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Bukti {{ new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(proof.uploaded_at)) }}
                    </a>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import type { Payment } from '@/types/models'
import * as AdminPaymentController from '@/actions/App/Http/Controllers/Admin/PaymentController'

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
</script>

<template>
    <DashboardLayout>
        <div class="max-w-xl">
            <Link :href="AdminPaymentController.index.url()" class="text-sm text-(--color-text-secondary) hover:text-(--color-primary) mb-6 inline-block">← Kembali</Link>

            <div class="flex items-center gap-3 mb-6">
                <h1 class="text-xl font-bold text-(--color-text-primary)">Detail Pembayaran</h1>
                <Badge :variant="statusVariant(props.payment.status)" :label="statusLabel(props.payment.status)" />
            </div>

            <div class="bg-white p-5 mb-4">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-(--color-text-secondary) mb-0.5">Penyewa</p>
                        <p class="font-medium text-(--color-text-primary)">{{ props.payment.booking?.user?.userProfile?.name ?? props.payment.booking?.user?.email ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-(--color-text-secondary) mb-0.5">Kost / Kamar</p>
                        <p class="font-medium text-(--color-text-primary)">{{ props.payment.booking?.room?.kost?.name ?? '-' }} / {{ props.payment.booking?.room?.name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-(--color-text-secondary) mb-0.5">Jumlah</p>
                        <p class="font-bold text-(--color-primary)">{{ formatCurrency(props.payment.amount) }}</p>
                    </div>
                    <div>
                        <p class="text-(--color-text-secondary) mb-0.5">Jatuh Tempo</p>
                        <p class="font-medium text-(--color-text-primary)">{{ formatDate(props.payment.due_date) }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-(--color-text-secondary) mb-0.5">Periode</p>
                        <p class="font-medium text-(--color-text-primary)">{{ formatDate(props.payment.period_start) }} – {{ formatDate(props.payment.period_end) }}</p>
                    </div>
                </div>

                <div v-if="props.payment.decline_notes" class="mt-4 p-3 bg-red-50">
                    <p class="text-xs font-semibold text-(--color-primary) mb-1">Catatan Penolakan</p>
                    <p class="text-sm text-(--color-text-primary)">{{ props.payment.decline_notes }}</p>
                </div>
            </div>

            <div v-if="props.payment.proofs?.length" class="bg-white p-5">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-3">Bukti Pembayaran</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <a
                        v-for="proof in props.payment.proofs"
                        :key="proof.id"
                        :href="proof.path"
                        target="_blank"
                    >
                        <img :src="proof.path" class="w-full h-28 object-cover" :alt="`Bukti ${proof.id}`" />
                    </a>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Pagination from '@/components/ui/Pagination.vue'
import type { Payment, Paginated } from '@/types/models'
import * as AdminPaymentController from '@/actions/App/Http/Controllers/Admin/PaymentController'

const props = defineProps<{
    payments: Paginated<Payment>
    filters?: Record<string, string>
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
    return new Intl.DateTimeFormat('id-ID', { year: 'numeric', month: 'short', day: 'numeric' }).format(new Date(date))
}
</script>

<template>
    <Head title="Pembayaran" />
    <DashboardLayout>
        <div>
            <div class="mb-6">
                <h1 class="text-xl font-black text-slate-900">Semua Pembayaran</h1>
                <p class="text-sm text-slate-500 mt-0.5">{{ props.payments.total }} total pembayaran</p>
            </div>

            <div v-if="props.payments.data.length > 0" class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50">
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Penyewa</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Kamar / Kost</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Periode</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Jumlah</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs text-slate-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="payment in props.payments.data" :key="payment.id" class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ payment.booking?.user?.userProfile?.name ?? payment.booking?.user?.email ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <p class="font-medium text-slate-900">{{ payment.booking?.room?.name ?? '-' }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ payment.booking?.room?.kost?.name ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-500">{{ formatDate(payment.period_start) }}</td>
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ formatCurrency(payment.amount) }}</td>
                                <td class="px-5 py-4">
                                    <Badge :variant="statusVariant(payment.status)" :label="statusLabel(payment.status)" />
                                </td>
                                <td class="px-5 py-4">
                                    <Link
                                        :href="AdminPaymentController.show.url(payment.id)"
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
                <p class="text-sm font-semibold text-slate-700">Tidak ada data pembayaran</p>
            </div>

            <Pagination v-if="props.payments.last_page > 1" :links="props.payments.links" />
        </div>
    </DashboardLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Pagination from '@/components/ui/Pagination.vue'
import type { Payment, Paginated } from '@/types/models'

const props = defineProps<{
    payments: Paginated<Payment>
}>()

function statusVariant(status: string): 'warning' | 'success' | 'error' | 'neutral' {
    const map: Record<string, 'warning' | 'success' | 'error' | 'neutral'> = {
        unpaid: 'warning', pending_verification: 'warning', paid: 'success', declined: 'error',
    }
    return map[status] ?? 'neutral'
}

function statusLabel(status: string): string {
    const labels: Record<string, string> = {
        unpaid: 'Belum Bayar',
        pending_verification: 'Menunggu Verifikasi',
        paid: 'Lunas',
        declined: 'Ditolak',
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
    <DashboardLayout>
        <div>
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-6">Pembayaran</h1>

            <div v-if="props.payments.data.length > 0" class="bg-white overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-(--color-surface) text-(--color-text-secondary)">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium">Periode</th>
                            <th class="text-left px-4 py-3 font-medium">Jumlah</th>
                            <th class="text-left px-4 py-3 font-medium">Jatuh Tempo</th>
                            <th class="text-left px-4 py-3 font-medium">Status</th>
                            <th class="text-left px-4 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-(--color-border)">
                        <tr v-for="payment in props.payments.data" :key="payment.id">
                            <td class="px-4 py-3 text-(--color-text-secondary)">
                                {{ formatDate(payment.period_start) }} – {{ formatDate(payment.period_end) }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-(--color-text-primary)">{{ formatCurrency(payment.amount) }}</td>
                            <td class="px-4 py-3 text-(--color-text-secondary)">{{ formatDate(payment.due_date) }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="statusVariant(payment.status)" :label="statusLabel(payment.status)" />
                            </td>
                            <td class="px-4 py-3">
                                <Link
                                    :href="`/dashboard/payments/${payment.id}`"
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
                <p>Belum ada tagihan pembayaran.</p>
            </div>

            <Pagination v-if="props.payments.last_page > 1" :links="props.payments.links" />
        </div>
    </DashboardLayout>
</template>

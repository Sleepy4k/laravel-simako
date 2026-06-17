<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import StatCard from '@/components/dashboard/StatCard.vue'
import Badge from '@/components/ui/Badge.vue'
import type { Payment } from '@/types/models'

const props = defineProps<{
    totalEarnings: number
    monthlyEarnings: number
    recentPayments: Payment[]
}>()

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
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-6">Pendapatan</h1>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                <StatCard label="Total Pendapatan" :value="formatCurrency(props.totalEarnings)" color="green" />
                <StatCard label="Pendapatan Bulan Ini" :value="formatCurrency(props.monthlyEarnings)" color="blue" />
            </div>

            <div class="bg-white">
                <div class="px-5 py-4 border-b border-(--color-border)">
                    <h2 class="text-base font-semibold text-(--color-text-primary)">Pembayaran Terbaru</h2>
                </div>

                <div v-if="props.recentPayments.length > 0" class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-(--color-surface) text-(--color-text-secondary)">
                            <tr>
                                <th class="text-left px-4 py-3 font-medium">Penyewa</th>
                                <th class="text-left px-4 py-3 font-medium">Kamar</th>
                                <th class="text-left px-4 py-3 font-medium">Periode</th>
                                <th class="text-left px-4 py-3 font-medium">Jumlah</th>
                                <th class="text-left px-4 py-3 font-medium">Dibayar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-(--color-border)">
                            <tr v-for="payment in props.recentPayments" :key="payment.id">
                                <td class="px-4 py-3 text-(--color-text-primary)">
                                    {{ payment.booking?.user?.userProfile?.name ?? payment.booking?.user?.email ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-(--color-text-secondary)">{{ payment.booking?.room?.name ?? '-' }}</td>
                                <td class="px-4 py-3 text-(--color-text-secondary)">{{ formatDate(payment.period_start) }}</td>
                                <td class="px-4 py-3 font-semibold text-(--color-success)">{{ formatCurrency(payment.amount) }}</td>
                                <td class="px-4 py-3 text-(--color-text-secondary)">{{ payment.paid_at ? formatDate(payment.paid_at) : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="p-12 text-center text-(--color-text-secondary)">
                    <p>Belum ada pembayaran yang diterima.</p>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import StatCard from '@/components/dashboard/StatCard.vue'
import Badge from '@/components/ui/Badge.vue'
import type { Auth } from '@/types/auth'

const props = defineProps<{
    role: string
    stats: Record<string, number>
    recentBookings?: Array<Record<string, unknown>>
}>()

const page = usePage()
const auth = computed(() => page.props.auth as Auth)

function formatCurrency(amount: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount)
}

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
</script>

<template>
    <DashboardLayout>
        <div>
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-6">
                Selamat datang, {{ auth.user?.userProfile?.name ?? auth.user?.email }}
            </h1>

            <!-- Pengguna dashboard -->
            <template v-if="props.role === 'pengguna'">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                    <StatCard label="Booking Aktif" :value="props.stats.active_bookings ?? 0" color="blue" />
                    <StatCard label="Pembayaran Tertunda" :value="props.stats.pending_payments ?? 0" color="orange" />
                </div>

                <div class="bg-white p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-semibold text-(--color-text-primary)">Booking Terbaru</h2>
                        <Link href="/dashboard/bookings" class="text-sm text-(--color-primary) font-medium">Lihat semua</Link>
                    </div>
                    <p class="text-sm text-(--color-text-secondary)">Belum ada booking.</p>
                </div>
            </template>

            <!-- Tenant dashboard -->
            <template v-else-if="props.role === 'tenant'">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
                    <StatCard label="Total Kost" :value="props.stats.total_kosts ?? 0" />
                    <StatCard label="Booking Aktif" :value="props.stats.active_bookings ?? 0" color="blue" />
                    <StatCard label="Menunggu Verifikasi" :value="props.stats.pending_verifications ?? 0" color="orange" />
                    <StatCard label="Pendapatan Bulan Ini" :value="formatCurrency(props.stats.monthly_earnings ?? 0)" color="green" />
                </div>

                <div class="bg-white p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-semibold text-(--color-text-primary)">Permintaan Booking Terbaru</h2>
                        <Link href="/dashboard/tenant/bookings" class="text-sm text-(--color-primary) font-medium">Lihat semua</Link>
                    </div>
                    <p class="text-sm text-(--color-text-secondary)">Belum ada permintaan booking.</p>
                </div>
            </template>

            <!-- Admin dashboard -->
            <template v-else-if="props.role === 'admin'">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-8">
                    <StatCard label="Total Pengguna" :value="props.stats.total_users ?? 0" />
                    <StatCard label="Total Tenant" :value="props.stats.total_tenants ?? 0" />
                    <StatCard label="Kost Aktif" :value="props.stats.active_kosts ?? 0" color="green" />
                    <StatCard label="Total Revenue" :value="formatCurrency(props.stats.total_revenue ?? 0)" color="blue" />
                    <StatCard label="Menunggu Verifikasi" :value="props.stats.pending_verifications ?? 0" color="orange" />
                </div>
            </template>
        </div>
    </DashboardLayout>
</template>

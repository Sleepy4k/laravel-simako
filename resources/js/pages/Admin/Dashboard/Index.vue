<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import StatCard from '@/components/dashboard/StatCard.vue'

const props = defineProps<{
    stats: Record<string, number>
}>()

function formatCurrency(amount: number) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(amount)
}
</script>

<template>
    <DashboardLayout>
        <div>
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-6">Admin Dashboard</h1>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <StatCard label="Total Pengguna" :value="props.stats.total_users ?? 0" />
                <StatCard label="Total Tenant" :value="props.stats.total_tenants ?? 0" />
                <StatCard label="Menunggu Verifikasi" :value="props.stats.pending_verifications ?? 0" color="orange" />
                <StatCard label="Kost Aktif" :value="props.stats.active_kosts ?? 0" color="green" />
                <StatCard label="Total Booking" :value="props.stats.total_bookings ?? 0" color="blue" />
                <StatCard label="Total Revenue" :value="formatCurrency(props.stats.total_revenue ?? 0)" color="green" />
            </div>
        </div>
    </DashboardLayout>
</template>

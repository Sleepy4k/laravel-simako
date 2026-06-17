<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import StatCard from '@/components/dashboard/StatCard.vue'
import LineChart from '@/components/charts/LineChart.vue'
import BarChart from '@/components/charts/BarChart.vue'
import type { Auth } from '@/types/auth'
 
const props = defineProps<{
    role: string
    stats: Record<string, number>
    recentBookings?: Array<Record<string, any>>
    chartData?: { labels: string[]; values: number[] }
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
</script>
 
<template>
    <DashboardLayout>
        <div class="space-y-6">
            <h1 class="text-xl sm:text-2xl font-black text-(--color-text-primary)">
                Selamat datang, {{ auth.user?.userProfile?.name ?? auth.user?.email }}
            </h1>
 
            <!-- Pengguna dashboard -->
            <template v-if="props.role === 'pengguna'">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <StatCard label="Booking Aktif" :value="props.stats.active_bookings ?? 0" color="blue" />
                    <StatCard label="Pembayaran Tertunda" :value="props.stats.pending_payments ?? 0" color="orange" />
                </div>
 
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Expenses Chart -->
                    <div class="lg:col-span-2 bg-white p-6 border border-(--color-border) rounded-2xl shadow-sm">
                        <h2 class="text-sm font-bold text-(--color-text-primary) mb-6">Pengeluaran Bulanan</h2>
                        <div class="h-64">
                            <LineChart
                                :labels="props.chartData?.labels ?? []"
                                :values="props.chartData?.values ?? []"
                                label="Pengeluaran (Rp)"
                            />
                        </div>
                    </div>
 
                    <!-- Recent Bookings -->
                    <div class="bg-white p-6 border border-(--color-border) rounded-2xl shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-sm font-bold text-(--color-text-primary)">Booking Terbaru</h2>
                                <Link href="/dashboard/bookings" class="text-xs text-(--color-primary) font-semibold hover:underline">
                                    Lihat semua
                                </Link>
                            </div>
 
                            <div v-if="props.recentBookings && props.recentBookings.length > 0" class="space-y-3">
                                <div
                                    v-for="b in props.recentBookings"
                                    :key="b.id"
                                    class="p-3 border border-(--color-border) rounded-xl flex items-center justify-between"
                                >
                                    <div>
                                        <p class="text-xs font-bold text-(--color-text-primary)">{{ b.room?.kost?.name }}</p>
                                        <p class="text-[10px] text-(--color-text-secondary) mt-0.5">Kamar: {{ b.room?.name }}</p>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase" :class="b.status === 'active' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'">
                                        {{ b.status }}
                                    </span>
                                </div>
                            </div>
                            <p v-else class="text-xs text-(--color-text-secondary) py-4">Belum ada data booking.</p>
                        </div>
                    </div>
                </div>
            </template>
 
            <!-- Tenant dashboard -->
            <template v-else-if="props.role === 'tenant'">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <StatCard label="Total Kost" :value="props.stats.total_kosts ?? 0" />
                    <StatCard label="Booking Aktif" :value="props.stats.active_bookings ?? 0" color="blue" />
                    <StatCard label="Menunggu Verifikasi" :value="props.stats.pending_verifications ?? 0" color="orange" />
                    <StatCard label="Pendapatan Bulan Ini" :value="formatCurrency(props.stats.monthly_earnings ?? 0)" color="green" />
                </div>
 
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Earnings Chart -->
                    <div class="lg:col-span-2 bg-white p-6 border border-(--color-border) rounded-2xl shadow-sm">
                        <h2 class="text-sm font-bold text-(--color-text-primary) mb-6">Pendapatan Kost</h2>
                        <div class="h-64">
                            <BarChart
                                :labels="props.chartData?.labels ?? []"
                                :values="props.chartData?.values ?? []"
                                label="Pendapatan (Rp)"
                            />
                        </div>
                    </div>
 
                    <!-- Recent Booking Requests -->
                    <div class="bg-white p-6 border border-(--color-border) rounded-2xl shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-sm font-bold text-(--color-text-primary)">Permintaan Booking</h2>
                                <Link href="/dashboard/tenant/bookings" class="text-xs text-(--color-primary) font-semibold hover:underline">
                                    Lihat semua
                                </Link>
                            </div>
 
                            <div v-if="props.recentBookings && props.recentBookings.length > 0" class="space-y-3">
                                <div
                                    v-for="b in props.recentBookings"
                                    :key="b.id"
                                    class="p-3 border border-(--color-border) rounded-xl flex items-center justify-between"
                                >
                                    <div>
                                        <p class="text-xs font-bold text-(--color-text-primary)">{{ b.user?.userProfile?.name ?? b.user?.email }}</p>
                                        <p class="text-[10px] text-(--color-text-secondary) mt-0.5">{{ b.room?.kost?.name }} — {{ b.room?.name }}</p>
                                    </div>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase" :class="b.status === 'active' ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700'">
                                        {{ b.status }}
                                    </span>
                                </div>
                            </div>
                            <p v-else class="text-xs text-(--color-text-secondary) py-4">Belum ada pengajuan booking baru.</p>
                        </div>
                    </div>
                </div>
            </template>
 
            <!-- Admin dashboard -->
            <template v-else-if="props.role === 'admin'">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <StatCard label="Total Pengguna" :value="props.stats.total_users ?? 0" />
                    <StatCard label="Total Tenant" :value="props.stats.total_tenants ?? 0" />
                    <StatCard label="Kost Aktif" :value="props.stats.active_kosts ?? 0" color="green" />
                    <StatCard label="Total Revenue" :value="formatCurrency(props.stats.total_revenue ?? 0)" color="blue" />
                    <StatCard label="Menunggu Verifikasi" :value="props.stats.pending_verifications ?? 0" color="orange" />
                </div>
 
                <!-- Admin Earnings Chart -->
                <div class="bg-white p-6 border border-(--color-border) rounded-2xl shadow-sm">
                    <h2 class="text-sm font-bold text-(--color-text-primary) mb-6">Total Transaksi Platform</h2>
                    <div class="h-72">
                        <LineChart
                            :labels="props.chartData?.labels ?? []"
                            :values="props.chartData?.values ?? []"
                            label="Volume Transaksi (Rp)"
                        />
                    </div>
                </div>
            </template>
        </div>
    </DashboardLayout>
</template>

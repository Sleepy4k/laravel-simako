<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import type { Kost } from '@/types/models'

const props = defineProps<{
    kost: Kost
}>()

const typeVariant = computed((): 'blue' | 'pink' | 'purple' => {
    switch (props.kost.type) {
        case 'putra': return 'blue'
        case 'putri': return 'pink'
        default: return 'purple'
    }
})

const typeLabel = computed(() => {
    switch (props.kost.type) {
        case 'putra': return 'Putra'
        case 'putri': return 'Putri'
        default: return 'Campur'
    }
})

function formatCurrency(amount: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount)
}

const periodLabel: Record<string, string> = {
    monthly: 'Bulanan',
    quarterly: '3 Bulan',
    semi_annual: '6 Bulan',
    annual: 'Tahunan',
}

const groupedFacilities = computed(() => {
    if (!props.kost.facilities) return {}
    return props.kost.facilities.reduce<Record<string, typeof props.kost.facilities>>((acc, f) => {
        const cat = f.category?.name ?? 'Lainnya'
        if (!acc[cat]) acc[cat] = []
        acc[cat].push(f)
        return acc
    }, {})
})

const heroImage = computed(() => {
    if (props.kost.images?.length) return props.kost.images[0].path
    return props.kost.thumbnail
})
</script>

<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <!-- Image -->
            <div class="w-full bg-slate-100 overflow-hidden mb-8 rounded-2xl border border-slate-200 shadow-sm" style="aspect-ratio: 16/7">
                <img
                    v-if="heroImage"
                    :src="heroImage"
                    :alt="props.kost.name"
                    class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-(--color-text-secondary)">
                    Tidak ada foto
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left: Info -->
                <div class="lg:col-span-2">
                    <div class="flex items-start gap-3 mb-2">
                        <h1 class="text-2xl font-black text-(--color-text-primary) flex-1 leading-tight">{{ props.kost.name }}</h1>
                        <Badge :variant="typeVariant" :label="typeLabel" />
                    </div>

                    <p class="text-sm text-(--color-text-secondary) mb-4">
                        {{ [props.kost.address?.street, props.kost.address?.district, props.kost.address?.city, props.kost.address?.province].filter(Boolean).join(', ') }}
                    </p>

                    <p v-if="props.kost.description" class="text-sm text-(--color-text-primary) mb-6 leading-relaxed bg-slate-50 p-4 rounded-xl border border-slate-100">
                        {{ props.kost.description }}
                    </p>

                    <!-- Facilities -->
                    <div v-if="Object.keys(groupedFacilities).length" class="mb-6">
                        <h2 class="text-base font-bold text-(--color-text-primary) mb-4">Fasilitas</h2>
                        <div v-for="(items, category) in groupedFacilities" :key="category" class="mb-4">
                            <p class="text-xs font-bold text-(--color-text-secondary) uppercase tracking-wide mb-2.5">{{ category }}</p>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="f in items"
                                    :key="f.id"
                                    class="px-3.5 py-1.5 text-xs bg-(--color-surface) text-(--color-text-primary) rounded-xl border border-slate-100"
                                >
                                    {{ f.name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Rooms -->
                    <div v-if="props.kost.rooms?.length" class="mb-6">
                        <h2 class="text-base font-bold text-(--color-text-primary) mb-4">Kamar Tersedia</h2>
                        <div class="space-y-4">
                            <div
                                v-for="room in props.kost.rooms"
                                :key="room.id"
                                class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs hover:border-rose-100 transition-colors"
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-base font-bold text-(--color-text-primary)">{{ room.name }}</p>
                                        <p v-if="room.size_sqm" class="text-xs text-(--color-text-secondary) mt-0.5">Ukuran: {{ room.size_sqm }} m²</p>
                                    </div>
                                    <span
                                        class="text-[10px] px-2.5 py-1 rounded-lg uppercase tracking-wider font-bold"
                                        :class="room.is_available ? 'bg-green-50 text-green-700' : 'bg-red-50 text-(--color-primary)'"
                                    >
                                        {{ room.is_available ? 'Tersedia' : 'Terisi' }}
                                    </span>
                                </div>
                                <div v-if="room.prices?.length" class="mt-4 flex flex-wrap gap-2">
                                    <span
                                        v-for="price in room.prices"
                                        :key="price.id"
                                        class="text-xs bg-slate-50 border border-slate-100 px-3 py-1.5 rounded-xl text-slate-700"
                                    >
                                        {{ periodLabel[price.period] }}: <strong class="text-rose-600 font-bold ml-0.5">{{ formatCurrency(price.price) }}</strong>
                                    </span>
                                </div>
                                <div v-if="room.is_available" class="mt-4 pt-4 border-t border-slate-50">
                                    <Link
                                        :href="`/dashboard/bookings/create/${room.id}`"
                                        class="inline-flex items-center px-4.5 py-2.5 text-xs font-bold bg-(--color-primary) text-white hover:bg-(--color-primary-hover) rounded-xl transition-all shadow-xs"
                                    >
                                        Booking Kamar Ini
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews -->
                    <div v-if="props.kost.reviews?.length">
                        <h2 class="text-base font-bold text-(--color-text-primary) mb-4">Ulasan</h2>
                        <div class="space-y-4">
                            <div v-for="review in props.kost.reviews" :key="review.id" class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
                                <div class="flex items-center gap-2.5 mb-2">
                                    <span class="text-sm font-bold text-(--color-text-primary)">{{ review.user?.userProfile?.name ?? 'Pengguna' }}</span>
                                    <div class="flex gap-0.5">
                                        <span v-for="i in 5" :key="i" class="text-xs" :class="i <= review.rating ? 'text-amber-400' : 'text-slate-200'">★</span>
                                    </div>
                                </div>
                                <p v-if="review.comment" class="text-sm text-(--color-text-secondary) leading-relaxed">{{ review.comment }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white border border-(--color-border) p-6 rounded-2xl shadow-sm sticky top-24 space-y-4">
                        <div>
                            <p class="text-lg font-black text-(--color-text-primary)">{{ props.kost.available_rooms }} kamar tersedia</p>
                            <p class="text-xs text-(--color-text-secondary) mt-0.5">dari {{ props.kost.total_rooms }} total kamar di kost ini</p>
                        </div>
                        <div class="border-t border-slate-100 pt-4">
                            <Link href="/kosts" class="block text-center text-sm font-semibold text-(--color-text-secondary) hover:text-(--color-primary) transition-colors">
                                ← Kembali ke daftar kost
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

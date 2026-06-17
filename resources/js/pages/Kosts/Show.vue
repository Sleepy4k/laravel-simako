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
            <div class="w-full bg-gray-200 overflow-hidden mb-8" style="aspect-ratio: 16/7">
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
                        <h1 class="text-2xl font-bold text-(--color-text-primary) flex-1">{{ props.kost.name }}</h1>
                        <Badge :variant="typeVariant" :label="typeLabel" />
                    </div>

                    <p class="text-sm text-(--color-text-secondary) mb-4">
                        {{ [props.kost.address?.street, props.kost.address?.district, props.kost.address?.city, props.kost.address?.province].filter(Boolean).join(', ') }}
                    </p>

                    <p v-if="props.kost.description" class="text-sm text-(--color-text-primary) mb-6 leading-relaxed">
                        {{ props.kost.description }}
                    </p>

                    <!-- Facilities -->
                    <div v-if="Object.keys(groupedFacilities).length" class="mb-6">
                        <h2 class="text-base font-semibold text-(--color-text-primary) mb-3">Fasilitas</h2>
                        <div v-for="(items, category) in groupedFacilities" :key="category" class="mb-3">
                            <p class="text-xs font-semibold text-(--color-text-secondary) uppercase tracking-wide mb-2">{{ category }}</p>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="f in items"
                                    :key="f.id"
                                    class="px-3 py-1 text-xs bg-(--color-surface) text-(--color-text-primary)"
                                >
                                    {{ f.name }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Rooms -->
                    <div v-if="props.kost.rooms?.length" class="mb-6">
                        <h2 class="text-base font-semibold text-(--color-text-primary) mb-3">Kamar Tersedia</h2>
                        <div class="space-y-3">
                            <div
                                v-for="room in props.kost.rooms"
                                :key="room.id"
                                class="bg-(--color-surface) p-4"
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-semibold text-(--color-text-primary)">{{ room.name }}</p>
                                        <p v-if="room.size_sqm" class="text-xs text-(--color-text-secondary)">{{ room.size_sqm }} m²</p>
                                    </div>
                                    <span
                                        class="text-xs px-2 py-0.5"
                                        :class="room.is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-(--color-primary)'"
                                    >
                                        {{ room.is_available ? 'Tersedia' : 'Terisi' }}
                                    </span>
                                </div>
                                <div v-if="room.prices?.length" class="mt-2 flex flex-wrap gap-2">
                                    <span
                                        v-for="price in room.prices"
                                        :key="price.id"
                                        class="text-xs bg-white px-2 py-1"
                                    >
                                        {{ periodLabel[price.period] }}: <strong>{{ formatCurrency(price.price) }}</strong>
                                    </span>
                                </div>
                                <div v-if="room.is_available" class="mt-3">
                                    <Link
                                        :href="`/dashboard/bookings/create/${room.id}`"
                                        class="inline-flex items-center px-4 py-1.5 text-xs font-semibold bg-(--color-primary) text-white hover:bg-(--color-primary-hover) transition-colors"
                                    >
                                        Booking Kamar Ini
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reviews -->
                    <div v-if="props.kost.reviews?.length">
                        <h2 class="text-base font-semibold text-(--color-text-primary) mb-3">Ulasan</h2>
                        <div class="space-y-3">
                            <div v-for="review in props.kost.reviews" :key="review.id" class="bg-(--color-surface) p-4">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-sm font-medium text-(--color-text-primary)">{{ review.user?.userProfile?.name ?? 'Pengguna' }}</span>
                                    <div class="flex gap-0.5">
                                        <span v-for="i in 5" :key="i" class="text-xs" :class="i <= review.rating ? 'text-yellow-400' : 'text-gray-300'">★</span>
                                    </div>
                                </div>
                                <p v-if="review.comment" class="text-sm text-(--color-text-secondary)">{{ review.comment }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-(--color-surface) p-5 sticky top-24">
                        <p class="text-sm font-semibold text-(--color-text-primary) mb-1">{{ props.kost.available_rooms }} kamar tersedia</p>
                        <p class="text-xs text-(--color-text-secondary) mb-4">dari {{ props.kost.total_rooms }} total kamar</p>
                        <Link href="/kosts" class="block text-center text-sm text-(--color-text-secondary) hover:text-(--color-primary)">
                            ← Kembali ke daftar kost
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

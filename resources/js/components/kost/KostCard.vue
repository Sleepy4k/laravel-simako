<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import type { Kost } from '@/types/models'
import Badge from '@/components/ui/Badge.vue'
import * as PublicKostController from '@/actions/App/Http/Controllers/Public/KostController'

const props = defineProps<{
    kost: Kost
}>()

const lowestPrice = computed(() => {
    if (!props.kost.rooms?.length) return null
    const prices: number[] = []
    for (const room of props.kost.rooms) {
        if (room.prices?.length) {
            const monthly = room.prices.find((p) => p.period === 'monthly')
            if (monthly) prices.push(monthly.price)
        }
    }
    if (!prices.length) return null
    return Math.min(...prices)
})

const formattedPrice = computed(() => {
    if (lowestPrice.value === null) return null
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(lowestPrice.value)
})

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

const kostUrl = computed(() => PublicKostController.show.url(props.kost.slug))
</script>

<template>
    <Link :href="kostUrl" class="block group">
        <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 hover:border-slate-300 hover:shadow-lg transition-all duration-300">
            <!-- Thumbnail -->
            <div class="relative w-full overflow-hidden" style="padding-top: 60%">
                <img
                    v-if="props.kost.thumbnail"
                    :src="props.kost.thumbnail"
                    :alt="props.kost.name"
                    class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                />
                <div v-else class="absolute inset-0 bg-slate-100 flex items-center justify-center">
                    <svg class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    </svg>
                </div>
                <!-- Type badge overlay -->
                <div class="absolute top-3 left-3">
                    <Badge :variant="typeVariant" :label="typeLabel" />
                </div>
                <!-- Available rooms badge -->
                <div class="absolute top-3 right-3 bg-black/50 backdrop-blur-sm text-white text-xs font-semibold px-2 py-0.5 rounded-full">
                    {{ props.kost.available_rooms }} tersedia
                </div>
            </div>

            <!-- Content -->
            <div class="p-4">
                <h3 class="text-sm font-bold text-slate-900 line-clamp-2 group-hover:text-(--color-primary) transition-colors">
                    {{ props.kost.name }}
                </h3>

                <div class="flex items-center gap-1 mt-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <p class="text-xs text-slate-500 truncate">{{ props.kost.address?.city ?? '-' }}</p>
                </div>

                <div class="mt-3 pt-3 border-t border-slate-100">
                    <span v-if="formattedPrice" class="text-sm font-bold text-(--color-primary)">
                        {{ formattedPrice }}<span class="text-xs font-normal text-slate-400">/bln</span>
                    </span>
                    <span v-else class="text-sm text-slate-400">Harga belum tersedia</span>
                </div>
            </div>
        </div>
    </Link>
</template>

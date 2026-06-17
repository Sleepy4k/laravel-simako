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
        <div class="bg-white overflow-hidden">
            <!-- 16:9 thumbnail -->
            <div class="relative w-full" style="padding-top: 56.25%">
                <img
                    v-if="props.kost.thumbnail"
                    :src="props.kost.thumbnail"
                    :alt="props.kost.name"
                    class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                />
                <div v-else class="absolute inset-0 bg-gray-200 flex items-center justify-center">
                    <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    </svg>
                </div>
            </div>

            <!-- Content -->
            <div class="pt-3 pb-1">
                <div class="flex items-start justify-between gap-2">
                    <h3 class="text-sm font-semibold text-(--color-text-primary) line-clamp-2 flex-1">
                        {{ props.kost.name }}
                    </h3>
                    <Badge :variant="typeVariant" :label="typeLabel" />
                </div>

                <p class="text-xs text-(--color-text-secondary) mt-1">
                    {{ props.kost.address?.city ?? '-' }}
                </p>

                <div class="mt-2 flex items-center justify-between">
                    <div>
                        <span v-if="formattedPrice" class="text-sm font-bold text-(--color-primary)">
                            Mulai {{ formattedPrice }}/bln
                        </span>
                        <span v-else class="text-sm text-(--color-text-secondary)">Harga belum tersedia</span>
                    </div>
                    <span class="text-xs text-(--color-text-secondary)">
                        {{ props.kost.available_rooms }} kamar
                    </span>
                </div>
            </div>
        </div>
    </Link>
</template>

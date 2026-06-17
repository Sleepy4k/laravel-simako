<script setup lang="ts">
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import KostCard from '@/components/kost/KostCard.vue'
import Pagination from '@/components/ui/Pagination.vue'
import type { Kost, Paginated } from '@/types/models'

const props = defineProps<{
    kosts: Paginated<Kost>
    filters: { search?: string; type?: string }
}>()

const search = ref(props.filters.search ?? '')
const activeType = ref(props.filters.type ?? '')

const typeFilters = [
    { label: 'Semua', value: '' },
    { label: 'Putra', value: 'putra' },
    { label: 'Putri', value: 'putri' },
    { label: 'Campur', value: 'campur' },
]

function applyFilters() {
    router.visit('/kosts', {
        data: {
            search: search.value || undefined,
            type: activeType.value || undefined,
        },
        preserveState: true,
        replace: true,
    })
}

function setType(value: string) {
    activeType.value = value
    applyFilters()
}

let searchTimeout: ReturnType<typeof setTimeout>
watch(search, () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(applyFilters, 400)
})
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <h1 class="text-2xl font-bold text-(--color-text-primary) mb-6">Cari Kost</h1>

            <!-- Search -->
            <div class="mb-6">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama kost atau kota..."
                    class="w-full max-w-md px-4.5 py-2.5 text-sm bg-white border border-(--color-border) rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-(--color-primary) transition-all placeholder:text-slate-400"
                />
            </div>

            <!-- Filter chips -->
            <div class="flex gap-2.5 mb-8 flex-wrap">
                <button
                    v-for="f in typeFilters"
                    :key="f.value"
                    class="px-4 py-2 text-xs font-semibold uppercase tracking-wider transition-all rounded-xl cursor-pointer"
                    :class="activeType === f.value
                        ? 'bg-(--color-primary) text-white shadow-sm'
                        : 'bg-white text-(--color-text-secondary) border border-(--color-border) hover:border-(--color-primary) hover:text-(--color-primary)'"
                    @click="setType(f.value)"
                >
                    {{ f.label }}
                </button>
            </div>

            <!-- Grid -->
            <div v-if="props.kosts.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <KostCard v-for="kost in props.kosts.data" :key="kost.id" :kost="kost" />
            </div>

            <div v-else class="text-center py-20 text-(--color-text-secondary)">
                <p>Tidak ada kost yang ditemukan.</p>
            </div>

            <!-- Pagination -->
            <Pagination v-if="props.kosts.last_page > 1" :links="props.kosts.links" />
        </div>
    </AppLayout>
</template>

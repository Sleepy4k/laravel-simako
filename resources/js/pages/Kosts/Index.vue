<script setup lang="ts">
import { ref, watch } from 'vue'
import { router, Head } from '@inertiajs/vue3'
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
    <Head title="Cari Kost" />
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <!-- Page header -->
            <div class="mb-8">
                <h1 class="text-2xl font-black text-slate-900">Cari Kost</h1>
                <p class="text-sm text-slate-500 mt-1">{{ props.kosts.total }} kost tersedia</p>
            </div>

            <!-- Search + Filters -->
            <div class="flex flex-col sm:flex-row gap-4 mb-6">
                <div class="relative flex-1 max-w-md">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama kost atau kota..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-(--color-primary)/15 focus:border-(--color-primary) transition-all placeholder:text-slate-400"
                    />
                </div>

                <!-- Filter chips -->
                <div class="flex gap-2 flex-wrap">
                    <button
                        v-for="f in typeFilters"
                        :key="f.value"
                        class="px-4 py-2 text-xs font-semibold uppercase tracking-wider transition-all rounded-xl cursor-pointer"
                        :class="activeType === f.value
                            ? 'bg-(--color-primary) text-white shadow-sm'
                            : 'bg-white text-slate-600 border border-slate-200 hover:border-(--color-primary) hover:text-(--color-primary)'"
                        @click="setType(f.value)"
                    >
                        {{ f.label }}
                    </button>
                </div>
            </div>

            <!-- Grid -->
            <div v-if="props.kosts.data.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <KostCard v-for="kost in props.kosts.data" :key="kost.id" :kost="kost" />
            </div>

            <div v-else class="text-center py-24 bg-white rounded-3xl border border-slate-200 mt-4">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <p class="text-sm font-semibold text-slate-700">Tidak ada kost ditemukan</p>
                <p class="text-xs text-slate-400 mt-1">Coba ubah filter pencarian Anda</p>
            </div>

            <!-- Pagination -->
            <Pagination v-if="props.kosts.last_page > 1" :links="props.kosts.links" />
        </div>
    </AppLayout>
</template>

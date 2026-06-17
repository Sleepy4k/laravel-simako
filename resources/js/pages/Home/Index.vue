<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import KostCard from '@/components/kost/KostCard.vue'
import type { Kost } from '@/types/models'

const props = defineProps<{
    kosts: Kost[]
}>()

const search = ref('')

function handleSearch() {
    if (search.value.trim()) {
        router.visit('/kosts', { data: { search: search.value } })
    }
}
</script>

<template>
    <AppLayout>
        <div>
            <!-- Hero -->
            <section class="bg-gradient-to-br from-gray-900 via-gray-800 to-red-900 text-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
                    <h1 class="text-4xl sm:text-5xl font-black mb-4 leading-tight">
                        Temukan Kost <span class="text-(--color-primary)">Impianmu</span>
                    </h1>
                    <p class="text-lg text-gray-300 mb-10 max-w-xl mx-auto">
                        Platform terpercaya untuk mencari dan mengelola kost di seluruh Indonesia.
                    </p>
                    <div class="max-w-lg mx-auto flex gap-2">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari kost berdasarkan nama atau kota..."
                            class="flex-1 px-4 py-3 text-sm text-gray-900 bg-white focus:outline-none focus:ring-2 focus:ring-(--color-primary)"
                            @keyup.enter="handleSearch"
                        />
                        <button
                            class="bg-(--color-primary) hover:bg-(--color-primary-hover) text-white px-6 py-3 text-sm font-semibold transition-colors"
                            @click="handleSearch"
                        >
                            Cari
                        </button>
                    </div>
                </div>
            </section>

            <!-- Kost Terbaru -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <h2 class="text-2xl font-bold text-(--color-text-primary) mb-6">Kost Terbaru</h2>

                <div v-if="props.kosts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <KostCard v-for="kost in props.kosts" :key="kost.id" :kost="kost" />
                </div>

                <div v-else class="text-center py-16 text-(--color-text-secondary)">
                    <p>Belum ada kost yang tersedia.</p>
                </div>

                <div class="text-center mt-10">
                    <a
                        href="/kosts"
                        class="inline-flex items-center gap-2 text-sm font-semibold text-(--color-primary) hover:text-(--color-primary-hover) transition-colors"
                    >
                        Lihat semua kost
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

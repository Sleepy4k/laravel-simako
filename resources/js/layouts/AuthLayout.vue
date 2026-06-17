<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined)
</script>

<template>
    <div class="min-h-screen bg-(--color-surface) flex flex-col items-center justify-center px-4 py-12">
        <div class="w-full max-w-[448px]">
            <div class="text-center mb-8">
                <a href="/" class="inline-block">
                    <span class="text-3xl font-black text-(--color-primary) tracking-tight">Simako</span>
                </a>
                <p class="mt-2 text-sm text-(--color-text-secondary)">Platform Manajemen Kost Terpadu</p>
            </div>

            <div v-if="flash?.success" class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-(--color-success)">
                {{ flash.success }}
            </div>
            <div v-if="flash?.error" class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-(--color-primary)">
                {{ flash.error }}
            </div>

            <div class="bg-white px-8 py-10">
                <slot />
            </div>
        </div>
    </div>
</template>

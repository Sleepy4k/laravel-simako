<script setup lang="ts">
import { router } from '@inertiajs/vue3'

const props = defineProps<{
    links: { url: string | null; label: string; active: boolean }[]
}>()

function navigate(url: string | null) {
    if (url) {
        router.visit(url)
    }
}
</script>

<template>
    <div class="flex items-center justify-center gap-1 mt-6">
        <button
            v-for="link in props.links"
            :key="link.label"
            :disabled="!link.url"
            class="inline-flex items-center justify-center min-w-[36px] h-9 px-2.5 text-sm font-medium rounded-lg transition-all disabled:opacity-30 disabled:cursor-not-allowed"
            :class="link.active
                ? 'bg-(--color-primary) text-white shadow-sm'
                : 'text-(--color-text-secondary) hover:text-(--color-text-primary) hover:bg-(--color-surface)'"
            @click="navigate(link.url)"
            v-html="link.label"
        />
    </div>
</template>

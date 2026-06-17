<script setup lang="ts">
defineProps<{
    rounded?: 'sm' | 'md' | 'lg' | 'full'
}>()
</script>

<template>
    <div
        class="animate-pulse-skeleton bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200 bg-[length:200%_100%]"
        :class="{
            'rounded-sm': rounded === 'sm',
            'rounded-md': rounded === 'md',
            'rounded-lg': rounded === 'lg' || !rounded,
            'rounded-full': rounded === 'full',
        }"
    />
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
    src?: string | null
    name: string
    size?: 'sm' | 'md' | 'lg'
}>(), {
    size: 'md',
})

const initials = computed(() => {
    return props.name
        .split(' ')
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('')
})

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm': return 'w-8 h-8 text-xs'
        case 'lg': return 'w-14 h-14 text-lg'
        default: return 'w-10 h-10 text-sm'
    }
})
</script>

<template>
    <div class="rounded-full overflow-hidden flex-shrink-0 bg-(--color-surface)" :class="sizeClasses">
        <img
            v-if="props.src"
            :src="props.src"
            :alt="props.name"
            class="w-full h-full object-cover"
        />
        <div
            v-else
            class="w-full h-full flex items-center justify-center font-semibold text-(--color-text-secondary)"
        >
            {{ initials }}
        </div>
    </div>
</template>

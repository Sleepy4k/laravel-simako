<script setup lang="ts">
const props = withDefaults(defineProps<{
    variant?: 'success' | 'warning' | 'error' | 'neutral' | 'primary' | 'blue' | 'pink' | 'purple'
    label: string
}>(), {
    variant: 'neutral',
})
</script>

<template>
    <span
        class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full"
        :class="[
            props.variant === 'success' ? 'bg-green-100 text-green-800' : '',
            props.variant === 'warning' ? 'bg-orange-100 text-orange-800' : '',
            props.variant === 'error' ? 'bg-red-100 text-(--color-primary)' : '',
            props.variant === 'neutral' ? 'bg-gray-100 text-gray-600' : '',
            props.variant === 'primary' ? 'bg-red-100 text-(--color-primary)' : '',
            props.variant === 'blue' ? 'bg-blue-100 text-blue-800' : '',
            props.variant === 'pink' ? 'bg-pink-100 text-pink-800' : '',
            props.variant === 'purple' ? 'bg-purple-100 text-purple-800' : '',
        ]"
    >
        {{ props.label }}
    </span>
</template>

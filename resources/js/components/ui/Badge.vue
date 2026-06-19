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
        class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full tracking-wide"
        :class="[
            props.variant === 'success' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200/60' : '',
            props.variant === 'warning' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200/60' : '',
            props.variant === 'error' ? 'bg-red-50 text-red-700 ring-1 ring-red-200/60' : '',
            props.variant === 'neutral' ? 'bg-slate-100 text-slate-600 ring-1 ring-slate-200/60' : '',
            props.variant === 'primary' ? 'bg-rose-50 text-rose-700 ring-1 ring-rose-200/60' : '',
            props.variant === 'blue' ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-200/60' : '',
            props.variant === 'pink' ? 'bg-pink-50 text-pink-700 ring-1 ring-pink-200/60' : '',
            props.variant === 'purple' ? 'bg-violet-50 text-violet-700 ring-1 ring-violet-200/60' : '',
        ]"
    >
        {{ props.label }}
    </span>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
    variant?: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger'
    size?: 'sm' | 'md' | 'lg'
    disabled?: boolean
    loading?: boolean
    type?: 'button' | 'submit' | 'reset'
}>(), {
    variant: 'primary',
    size: 'md',
    type: 'button',
})

defineEmits<{
    click: [event: MouseEvent]
}>()
</script>

<template>
    <button
        :type="props.type"
        :disabled="props.disabled || props.loading"
        class="inline-flex items-center justify-center gap-2 font-semibold tracking-wide transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-(--color-primary) disabled:opacity-50 disabled:cursor-not-allowed rounded-xl cursor-pointer active:scale-[0.97]"
        :class="[
            props.size === 'sm' ? 'px-3 py-1.5 text-xs' : props.size === 'lg' ? 'px-6 py-3 text-base' : 'px-4 py-2.5 text-sm',
            props.variant === 'primary' ? 'bg-(--color-primary) hover:bg-(--color-primary-hover) text-white shadow-sm shadow-(--color-primary)/20' : '',
            props.variant === 'secondary' ? 'bg-slate-800 hover:bg-slate-700 text-white shadow-sm' : '',
            props.variant === 'outline' ? 'border border-(--color-primary) text-(--color-primary) bg-transparent hover:bg-red-50' : '',
            props.variant === 'ghost' ? 'bg-transparent text-(--color-text-secondary) hover:text-(--color-text-primary) hover:bg-(--color-surface)' : '',
            props.variant === 'danger' ? 'bg-red-600 hover:bg-red-700 text-white shadow-sm shadow-red-500/20' : '',
        ]"
        @click="$emit('click', $event)"
    >
        <svg v-if="props.loading" class="animate-spin h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <slot />
    </button>
</template>

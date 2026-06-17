<script setup lang="ts">
const props = withDefaults(defineProps<{
    variant?: 'primary' | 'secondary' | 'outline' | 'ghost'
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
        class="inline-flex items-center justify-center font-bold tracking-wide transition-all focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed rounded-xl cursor-pointer active:scale-[0.98] shadow-xs"
        :class="[
            props.size === 'sm' ? 'px-3 py-1.5 text-xs' : props.size === 'lg' ? 'px-6 py-3 text-base' : 'px-4 py-2 text-sm',
            props.variant === 'primary' ? 'bg-(--color-primary) hover:bg-(--color-primary-hover) text-white' : '',
            props.variant === 'secondary' ? 'bg-gray-800 hover:bg-gray-700 text-white' : '',
            props.variant === 'outline' ? 'border border-(--color-primary) text-(--color-primary) bg-transparent hover:bg-red-50' : '',
            props.variant === 'ghost' ? 'bg-transparent text-(--color-text-secondary) hover:text-(--color-text-primary)' : '',
        ]"
        @click="$emit('click', $event)"
    >
        <svg v-if="props.loading" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <slot />
    </button>
</template>

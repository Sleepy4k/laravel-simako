<script setup lang="ts">
const props = withDefaults(defineProps<{
    label: string
    value: string | number
    color?: 'red' | 'blue' | 'green' | 'orange'
    icon?: string
}>(), {
    color: undefined,
})

const valueColor: Record<string, string> = {
    red: 'text-(--color-primary)',
    blue: 'text-blue-600',
    green: 'text-emerald-600',
    orange: 'text-amber-600',
}

const bgColor: Record<string, string> = {
    red: 'bg-rose-50',
    blue: 'bg-blue-50',
    green: 'bg-emerald-50',
    orange: 'bg-amber-50',
}
</script>

<template>
    <div class="bg-white rounded-2xl border border-(--color-border) p-5 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-(--color-text-secondary) uppercase tracking-wider truncate">{{ props.label }}</p>
                <p
                    class="mt-2 text-2xl font-bold leading-none truncate"
                    :class="props.color ? valueColor[props.color] : 'text-(--color-text-primary)'"
                >
                    {{ props.value }}
                </p>
            </div>
            <div
                v-if="props.color"
                class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                :class="bgColor[props.color]"
            >
                <svg class="w-5 h-5" :class="valueColor[props.color]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
            <div v-else class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-slate-50">
                <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
const props = withDefaults(defineProps<{
    label: string
    value: string | number
    color?: 'red' | 'blue' | 'green' | 'orange'
}>(), {
    color: undefined,
})

const valueColor = {
    red: 'text-(--color-primary)',
    blue: 'text-(--color-secondary)',
    green: 'text-(--color-success)',
    orange: 'text-(--color-warning)',
}
</script>

<template>
    <div class="bg-white p-6">
        <p class="text-sm text-(--color-text-secondary) font-medium">{{ props.label }}</p>
        <p
            class="mt-2 text-3xl font-bold"
            :class="props.color ? valueColor[props.color] : 'text-(--color-text-primary)'"
        >
            {{ props.value }}
        </p>
    </div>
</template>

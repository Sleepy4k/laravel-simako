<script setup lang="ts">
const props = withDefaults(defineProps<{
    label?: string
    error?: string
    helper?: string
    type?: string
    placeholder?: string
    modelValue?: string | number
    required?: boolean
    disabled?: boolean
    name?: string
}>(), {
    type: 'text',
})

defineEmits<{
    'update:modelValue': [value: string]
}>()
</script>

<template>
    <div>
        <label v-if="props.label" class="block text-sm font-medium text-(--color-text-primary) mb-1">
            {{ props.label }}
            <span v-if="props.required" class="text-(--color-primary) ml-0.5">*</span>
        </label>
        <input
            :type="props.type"
            :placeholder="props.placeholder"
            :value="props.modelValue"
            :required="props.required"
            :disabled="props.disabled"
            :name="props.name"
            class="w-full px-3 py-2 text-sm bg-white border border-(--color-border) text-(--color-text-primary) placeholder:text-(--color-text-secondary) focus:outline-none focus:border-(--color-primary) transition-colors disabled:bg-(--color-surface) disabled:cursor-not-allowed"
            :class="props.error ? 'border-(--color-primary)' : ''"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />
        <p v-if="props.error" class="mt-1 text-xs text-(--color-primary)">{{ props.error }}</p>
        <p v-else-if="props.helper" class="mt-1 text-xs text-(--color-text-secondary)">{{ props.helper }}</p>
    </div>
</template>

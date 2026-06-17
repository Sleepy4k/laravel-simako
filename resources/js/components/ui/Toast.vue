<script setup lang="ts">
import { ref, watch, onUnmounted } from 'vue'

const props = defineProps<{
    show: boolean
    message: string
    type?: 'success' | 'error'
}>()

const emit = defineEmits<{
    close: []
}>()

const visible = ref(props.show)
let timer: ReturnType<typeof setTimeout>

watch(
    () => props.show,
    (val) => {
        visible.value = val
        if (val) {
            clearTimeout(timer)
            timer = setTimeout(() => emit('close'), 4000)
        }
    },
)

onUnmounted(() => clearTimeout(timer))
</script>

<template>
    <Transition
        enter-active-class="duration-300 ease-out"
        enter-from-class="opacity-0 translate-x-full"
        enter-to-class="opacity-100 translate-x-0"
        leave-active-class="duration-200 ease-in"
        leave-from-class="opacity-100 translate-x-0"
        leave-to-class="opacity-0 translate-x-full"
    >
        <div
            v-if="visible"
            class="flex items-start gap-3 p-4 pr-10 rounded-xl shadow-lg relative max-w-sm w-full"
            :class="type === 'error' ? 'bg-red-600 text-white' : 'bg-green-600 text-white'"
        >
            <!-- Icon -->
            <svg v-if="type === 'error'" class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <svg v-else class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>

            <p class="text-sm font-medium leading-snug">{{ message }}</p>

            <!-- Close -->
            <button
                class="absolute top-3 right-3 text-white/70 hover:text-white transition-colors"
                @click="emit('close')"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </Transition>
</template>

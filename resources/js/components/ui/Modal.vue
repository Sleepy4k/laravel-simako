<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    open: boolean
    title: string
    message?: string
    confirmLabel?: string
    confirmVariant?: 'danger' | 'primary'
    loading?: boolean
}>()

const emit = defineEmits<{
    confirm: []
    cancel: []
}>()

const confirmClass = computed(() =>
    props.confirmVariant === 'danger'
        ? 'bg-red-600 hover:bg-red-700 text-white'
        : 'bg-(--color-primary) hover:bg-(--color-primary-hover) text-white',
)
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="emit('cancel')"
            >
                <!-- Overlay -->
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" />

                <!-- Modal panel -->
                <Transition
                    enter-active-class="duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                    appear
                >
                    <div
                        v-if="open"
                        class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 mx-auto"
                    >
                        <!-- Icon area -->
                        <div
                            v-if="confirmVariant === 'danger'"
                            class="flex items-center justify-center w-12 h-12 rounded-full bg-red-50 mx-auto mb-4"
                        >
                            <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>

                        <h3 class="text-lg font-bold text-(--color-text-primary) text-center mb-2">
                            {{ title }}
                        </h3>

                        <p v-if="message" class="text-sm text-(--color-text-secondary) text-center mb-6">
                            {{ message }}
                        </p>

                        <slot />

                        <div class="flex gap-3 mt-6">
                            <button
                                type="button"
                                class="flex-1 px-4 py-2.5 text-sm font-semibold text-(--color-text-primary) bg-(--color-surface) hover:bg-(--color-surface-2) rounded-xl transition-colors"
                                :disabled="loading"
                                @click="emit('cancel')"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                class="flex-1 px-4 py-2.5 text-sm font-semibold rounded-xl transition-colors disabled:opacity-60"
                                :class="confirmClass"
                                :disabled="loading"
                                @click="emit('confirm')"
                            >
                                <span v-if="loading">Memproses...</span>
                                <span v-else>{{ confirmLabel ?? 'Konfirmasi' }}</span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>

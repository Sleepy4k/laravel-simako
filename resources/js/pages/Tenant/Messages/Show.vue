<script setup lang="ts">
import { ref, nextTick, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { MessageThread } from '@/types/models'

const props = defineProps<{
    thread: MessageThread
    currentUserId: number
}>()

const messagesEndRef = ref<HTMLDivElement>()
const form = useForm({ body: '' })

function sendMessage() {
    if (!form.body.trim()) return
    form.post(`/dashboard/tenant/messages/${props.thread.id}`, {
        onSuccess: () => {
            form.reset('body')
            nextTick(() => scrollToBottom())
        },
    })
}

function scrollToBottom() {
    messagesEndRef.value?.scrollIntoView({ behavior: 'smooth' })
}

function formatTime(date: string) {
    return new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', day: 'numeric', month: 'short' }).format(new Date(date))
}

onMounted(() => scrollToBottom())
</script>

<template>
    <DashboardLayout>
        <div class="max-w-2xl flex flex-col" style="height: calc(100vh - 10rem)">
            <!-- Header -->
            <div class="bg-white px-5 py-4 border-b border-(--color-border)">
                <p class="text-base font-semibold text-(--color-text-primary)">
                    {{ props.thread.booking?.user?.userProfile?.name ?? props.thread.booking?.user?.email ?? 'Penyewa' }}
                </p>
                <p class="text-xs text-(--color-text-secondary)">
                    {{ props.thread.booking?.room?.kost?.name }} - {{ props.thread.booking?.room?.name }}
                </p>
            </div>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-(--color-surface)">
                <div
                    v-for="message in props.thread.messages"
                    :key="message.id"
                    class="flex"
                    :class="message.user_id === props.currentUserId ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-[75%] px-4 py-2 text-sm"
                        :class="message.user_id === props.currentUserId
                            ? 'bg-(--color-primary) text-white'
                            : 'bg-white text-(--color-text-primary)'"
                    >
                        <p>{{ message.body }}</p>
                        <p class="text-xs mt-1 opacity-70">{{ formatTime(message.created_at) }}</p>
                    </div>
                </div>
                <div ref="messagesEndRef" />
            </div>

            <!-- Send form -->
            <div class="bg-white border-t border-(--color-border) p-4">
                <form @submit.prevent="sendMessage" class="flex gap-2">
                    <input
                        v-model="form.body"
                        type="text"
                        placeholder="Ketik pesan..."
                        class="flex-1 px-3 py-2 text-sm border border-(--color-border) focus:outline-none focus:border-(--color-primary)"
                    />
                    <button
                        type="submit"
                        :disabled="form.processing || !form.body.trim()"
                        class="px-4 py-2 bg-(--color-primary) hover:bg-(--color-primary-hover) text-white text-sm font-semibold disabled:opacity-50 transition-colors"
                    >
                        Kirim
                    </button>
                </form>
            </div>
        </div>
    </DashboardLayout>
</template>

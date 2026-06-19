<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import type { MessageThread } from '@/types/models'

const props = defineProps<{
    threads: MessageThread[]
}>()

function lastMessage(thread: MessageThread) {
    const messages = thread.messages
    if (!messages?.length) return 'Belum ada pesan'
    return messages[messages.length - 1].body
}

function hasUnread(thread: MessageThread) {
    return thread.messages?.some((m) => !m.read_at) ?? false
}
</script>

<template>
    <DashboardLayout>
        <div>
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-6">Pesan</h1>

            <div v-if="props.threads.length > 0" class="bg-white divide-y divide-(--color-border)">
                <Link
                    v-for="thread in props.threads"
                    :key="thread.id"
                    :href="`/dashboard/messages/${thread.id}`"
                    class="flex items-start gap-4 p-4 hover:bg-(--color-surface) transition-colors"
                >
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="text-sm font-semibold text-(--color-text-primary) truncate">
                                {{ thread.kost?.name ?? thread.booking?.room?.kost?.name ?? 'Kost' }}
                            </p>
                            <span v-if="hasUnread(thread)" class="w-2 h-2 rounded-full bg-(--color-primary) flex-shrink-0" />
                        </div>
                        <p class="text-sm text-(--color-text-secondary) truncate">{{ lastMessage(thread) }}</p>
                    </div>
                </Link>
            </div>

            <div v-else class="bg-white p-12 text-center text-(--color-text-secondary)">
                <p>Belum ada percakapan.</p>
            </div>
        </div>
    </DashboardLayout>
</template>

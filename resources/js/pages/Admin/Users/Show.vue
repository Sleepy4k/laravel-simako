<script setup lang="ts">
import { ref } from 'vue'
import { Link, useForm, Head } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Button from '@/components/ui/Button.vue'
import Modal from '@/components/ui/Modal.vue'
import type { User, Booking } from '@/types/models'
import * as AdminUserController from '@/actions/App/Http/Controllers/Admin/UserController'

const props = defineProps<{
    user: User & { bookings?: Booking[] }
}>()

function statusVariant(status: string): 'warning' | 'blue' | 'success' | 'neutral' | 'error' {
    const map: Record<string, 'warning' | 'blue' | 'success' | 'neutral' | 'error'> = {
        pending: 'warning', approved: 'blue', active: 'success', ended: 'neutral', cancelled: 'error',
    }
    return map[status] ?? 'neutral'
}

function statusLabel(status: string): string {
    const labels: Record<string, string> = {
        pending: 'Menunggu', approved: 'Disetujui', active: 'Aktif', ended: 'Selesai', cancelled: 'Dibatalkan',
    }
    return labels[status] ?? status
}

function formatDate(date: string) {
    return new Intl.DateTimeFormat('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }).format(new Date(date))
}

const showSuspendModal = ref(false)
const showActivateModal = ref(false)

const suspendForm = useForm({ is_active: false })
const activateForm = useForm({ is_active: true })

function suspendUser() {
    suspendForm.patch(AdminUserController.update.url(props.user.id), {
        onSuccess: () => { showSuspendModal.value = false },
    })
}

function activateUser() {
    activateForm.patch(AdminUserController.update.url(props.user.id), {
        onSuccess: () => { showActivateModal.value = false },
    })
}
</script>

<template>
    <Head title="Detail Pengguna" />
    <DashboardLayout>
        <div class="max-w-2xl">
            <div class="flex items-center gap-3 mb-6">
                <Link :href="AdminUserController.index.url()" class="text-sm text-(--color-text-secondary) hover:text-(--color-primary)">← Kembali</Link>
            </div>

            <!-- User info -->
            <div class="bg-white p-5 mb-4">
                <div class="flex items-start justify-between mb-4">
                    <h1 class="text-xl font-bold text-(--color-text-primary)">{{ props.user.userProfile?.name ?? '-' }}</h1>
                    <Badge :variant="props.user.is_active ? 'success' : 'neutral'" :label="props.user.is_active ? 'Aktif' : 'Nonaktif'" />
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-(--color-text-secondary)">Email</p>
                        <p class="font-medium text-(--color-text-primary)">{{ props.user.email }}</p>
                    </div>
                    <div>
                        <p class="text-(--color-text-secondary)">Nomor HP</p>
                        <p class="font-medium text-(--color-text-primary)">{{ props.user.phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-(--color-text-secondary)">Jenis Kelamin</p>
                        <p class="font-medium text-(--color-text-primary)">{{ props.user.userProfile?.gender === 'male' ? 'Laki-laki' : props.user.userProfile?.gender === 'female' ? 'Perempuan' : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-(--color-text-secondary)">Tanggal Lahir</p>
                        <p class="font-medium text-(--color-text-primary)">{{ props.user.userProfile?.birth_date ? formatDate(props.user.userProfile.birth_date) : '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white p-5 mb-4">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-4">Tindakan Admin</p>
                <div class="flex flex-wrap gap-3">
                    <Button
                        v-if="props.user.is_active"
                        variant="outline"
                        size="sm"
                        @click="showSuspendModal = true"
                    >
                        Tangguhkan Akun
                    </Button>
                    <Button
                        v-else
                        size="sm"
                        @click="showActivateModal = true"
                    >
                        Aktifkan Akun
                    </Button>
                </div>
            </div>

            <!-- Booking history -->
            <div class="bg-white p-5">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-3">Riwayat Booking</p>
                <div v-if="props.user.bookings?.length" class="space-y-2">
                    <div
                        v-for="booking in props.user.bookings"
                        :key="booking.id"
                        class="flex items-center justify-between py-2 border-b border-(--color-border) last:border-0"
                    >
                        <div>
                            <p class="text-sm text-(--color-text-primary)">{{ booking.room?.kost?.name ?? '-' }} - {{ booking.room?.name ?? '-' }}</p>
                            <p class="text-xs text-(--color-text-secondary)">{{ formatDate(booking.start_date) }}</p>
                        </div>
                        <Badge :variant="statusVariant(booking.status)" :label="statusLabel(booking.status)" />
                    </div>
                </div>
                <p v-else class="text-sm text-(--color-text-secondary)">Belum ada booking.</p>
            </div>
        </div>

        <Modal
            :open="showSuspendModal"
            title="Tangguhkan Akun Pengguna?"
            message="Pengguna tidak akan bisa login setelah ditangguhkan."
            confirm-label="Ya, Tangguhkan"
            confirm-variant="danger"
            :loading="suspendForm.processing"
            @confirm="suspendUser"
            @cancel="showSuspendModal = false"
        />

        <Modal
            :open="showActivateModal"
            title="Aktifkan Akun Pengguna?"
            message="Pengguna akan bisa kembali login setelah diaktifkan."
            confirm-label="Ya, Aktifkan"
            confirm-variant="primary"
            :loading="activateForm.processing"
            @confirm="activateUser"
            @cancel="showActivateModal = false"
        />
    </DashboardLayout>
</template>

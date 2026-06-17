<script setup lang="ts">
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Button from '@/components/ui/Button.vue'
import Modal from '@/components/ui/Modal.vue'
import type { User } from '@/types/models'
import * as AdminTenantController from '@/actions/App/Http/Controllers/Admin/TenantController'

const props = defineProps<{
    tenant: User
}>()

const showVerifyModal = ref(false)
const showSuspendModal = ref(false)

const verifyForm = useForm({})
const suspendForm = useForm({ suspension_reason: '' })

function verify() {
    verifyForm.patch(AdminTenantController.verify.url(props.tenant.id), {
        onSuccess: () => {
            showVerifyModal.value = false
        }
    })
}

function suspend() {
    suspendForm.patch(AdminTenantController.suspend.url(props.tenant.id), {
        onSuccess: () => {
            showSuspendModal.value = false
        }
    })
}

function formatDate(date: string) {
    return new Intl.DateTimeFormat('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }).format(new Date(date))
}
</script>

<template>
    <DashboardLayout>
        <div class="max-w-2xl">
            <Link :href="AdminTenantController.index.url()" class="text-sm font-semibold text-(--color-text-secondary) hover:text-(--color-primary) mb-6 inline-block">← Kembali</Link>

            <!-- Tenant info -->
            <div class="bg-white p-5 mb-4 rounded-2xl border border-(--color-border) shadow-sm">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h1 class="text-xl font-bold text-(--color-text-primary)">{{ props.tenant.userProfile?.name ?? '-' }}</h1>
                        <p class="text-sm text-(--color-text-secondary) mt-0.5">{{ props.tenant.tenantProfile?.business_name ?? '' }}</p>
                    </div>
                    <div class="flex gap-2">
                        <Badge
                            :variant="props.tenant.tenantProfile?.is_verified ? 'success' : 'warning'"
                            :label="props.tenant.tenantProfile?.is_verified ? 'Terverifikasi' : 'Belum Verifikasi'"
                        />
                        <Badge
                            v-if="props.tenant.tenantProfile?.is_suspended"
                            variant="error"
                            label="Ditangguhkan"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm border-t border-(--color-border) pt-4 mt-4">
                    <div>
                        <p class="text-(--color-text-secondary) mb-0.5">Email</p>
                        <p class="font-medium text-(--color-text-primary)">{{ props.tenant.email }}</p>
                    </div>
                    <div>
                        <p class="text-(--color-text-secondary) mb-0.5">Nomor HP</p>
                        <p class="font-medium text-(--color-text-primary)">{{ props.tenant.phone ?? '-' }}</p>
                    </div>
                    <div v-if="props.tenant.tenantProfile?.verified_at">
                        <p class="text-(--color-text-secondary) mb-0.5">Diverifikasi</p>
                        <p class="font-medium text-(--color-text-primary)">{{ formatDate(props.tenant.tenantProfile.verified_at) }}</p>
                    </div>
                    <div v-if="props.tenant.tenantProfile?.suspension_reason" class="col-span-2">
                        <p class="text-(--color-text-secondary) mb-0.5">Alasan Penangguhan</p>
                        <p class="font-medium text-(--color-text-primary) bg-red-50 text-red-700 p-3 rounded-xl border border-red-100">{{ props.tenant.tenantProfile.suspension_reason }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white p-5 rounded-2xl border border-(--color-border) shadow-sm">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-4">Tindakan Admin</p>

                <div class="flex flex-wrap gap-3">
                    <Button
                        v-if="!props.tenant.tenantProfile?.is_verified"
                        @click="showVerifyModal = true"
                        size="sm"
                    >
                        Verifikasi Tenant
                    </Button>

                    <Button
                        v-if="!props.tenant.tenantProfile?.is_suspended"
                        variant="outline"
                        size="sm"
                        @click="showSuspendModal = true"
                    >
                        Tangguhkan Akun
                    </Button>
                </div>
            </div>

            <!-- Verify Confirmation Modal -->
            <Modal
                :open="showVerifyModal"
                title="Verifikasi Akun Tenant?"
                message="Apakah Anda yakin ingin menyetujui dan memverifikasi akun tenant ini? Mereka akan diizinkan untuk mengaktifkan kost dan menerima booking."
                confirm-label="Ya, Verifikasi"
                confirm-variant="primary"
                :loading="verifyForm.processing"
                @confirm="verify"
                @cancel="showVerifyModal = false"
            />

            <!-- Suspend Confirmation Modal -->
            <Modal
                :open="showSuspendModal"
                title="Tangguhkan Akun Tenant?"
                confirm-label="Ya, Tangguhkan"
                confirm-variant="danger"
                :loading="suspendForm.processing"
                @confirm="suspend"
                @cancel="showSuspendModal = false"
            >
                <div class="mt-2 text-left">
                    <label class="block text-sm font-semibold text-(--color-text-primary) mb-1.5">Alasan Penangguhan</label>
                    <textarea
                        v-model="suspendForm.suspension_reason"
                        rows="3"
                        class="w-full px-3.5 py-2.5 text-sm border border-(--color-border) rounded-xl focus:outline-none focus:ring-2 focus:ring-(--color-primary)/20 focus:border-(--color-primary) transition-all placeholder:text-(--color-text-secondary)/60"
                        placeholder="Masukkan alasan penangguhan..."
                        required
                    />
                    <p v-if="suspendForm.errors.suspension_reason" class="mt-1.5 text-xs font-semibold text-(--color-primary)">{{ suspendForm.errors.suspension_reason }}</p>
                </div>
            </Modal>
        </div>
    </DashboardLayout>
</template>

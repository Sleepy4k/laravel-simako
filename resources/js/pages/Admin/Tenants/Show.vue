<script setup lang="ts">
import { ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Button from '@/components/ui/Button.vue'
import type { User } from '@/types/models'
import * as AdminTenantController from '@/actions/App/Http/Controllers/Admin/TenantController'

const props = defineProps<{
    tenant: User
}>()

const showSuspendInput = ref(false)

const verifyForm = useForm({})
const suspendForm = useForm({ suspension_reason: '' })

function verify() {
    verifyForm.patch(AdminTenantController.verify.url(props.tenant.id))
}

function suspend() {
    suspendForm.patch(AdminTenantController.suspend.url(props.tenant.id))
}

function formatDate(date: string) {
    return new Intl.DateTimeFormat('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }).format(new Date(date))
}
</script>

<template>
    <DashboardLayout>
        <div class="max-w-2xl">
            <Link :href="AdminTenantController.index.url()" class="text-sm text-(--color-text-secondary) hover:text-(--color-primary) mb-6 inline-block">← Kembali</Link>

            <!-- Tenant info -->
            <div class="bg-white p-5 mb-4">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h1 class="text-xl font-bold text-(--color-text-primary)">{{ props.tenant.userProfile?.name ?? '-' }}</h1>
                        <p class="text-sm text-(--color-text-secondary)">{{ props.tenant.tenantProfile?.business_name ?? '' }}</p>
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

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-(--color-text-secondary)">Email</p>
                        <p class="font-medium text-(--color-text-primary)">{{ props.tenant.email }}</p>
                    </div>
                    <div>
                        <p class="text-(--color-text-secondary)">Nomor HP</p>
                        <p class="font-medium text-(--color-text-primary)">{{ props.tenant.phone ?? '-' }}</p>
                    </div>
                    <div v-if="props.tenant.tenantProfile?.verified_at">
                        <p class="text-(--color-text-secondary)">Diverifikasi</p>
                        <p class="font-medium text-(--color-text-primary)">{{ formatDate(props.tenant.tenantProfile.verified_at) }}</p>
                    </div>
                    <div v-if="props.tenant.tenantProfile?.suspension_reason">
                        <p class="text-(--color-text-secondary)">Alasan Penangguhan</p>
                        <p class="font-medium text-(--color-text-primary)">{{ props.tenant.tenantProfile.suspension_reason }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white p-5">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-4">Tindakan Admin</p>

                <div class="flex flex-wrap gap-3 mb-3">
                    <Button
                        v-if="!props.tenant.tenantProfile?.is_verified"
                        @click="verify"
                        :loading="verifyForm.processing"
                        size="sm"
                    >
                        Verifikasi Tenant
                    </Button>

                    <Button
                        v-if="!props.tenant.tenantProfile?.is_suspended"
                        variant="outline"
                        size="sm"
                        @click="showSuspendInput = !showSuspendInput"
                    >
                        Tangguhkan Akun
                    </Button>
                </div>

                <div v-if="showSuspendInput" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-(--color-text-primary) mb-1">Alasan Penangguhan</label>
                        <textarea
                            v-model="suspendForm.suspension_reason"
                            rows="3"
                            class="w-full px-3 py-2 text-sm border border-(--color-border) focus:outline-none focus:border-(--color-primary)"
                            placeholder="Masukkan alasan penangguhan..."
                        />
                        <p v-if="suspendForm.errors.suspension_reason" class="mt-1 text-xs text-(--color-primary)">{{ suspendForm.errors.suspension_reason }}</p>
                    </div>
                    <Button variant="outline" size="sm" @click="suspend" :loading="suspendForm.processing">
                        Konfirmasi Tangguhkan
                    </Button>
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>

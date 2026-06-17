<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import Avatar from '@/components/ui/Avatar.vue'
import type { AuthUser } from '@/types/auth'
import type { UserProfile } from '@/types/models'

const props = defineProps<{
    user: AuthUser & { userProfile: UserProfile }
}>()

const form = useForm({
    _method: 'PATCH',
    name: props.user.userProfile?.name ?? '',
    gender: props.user.userProfile?.gender ?? '',
    birth_date: props.user.userProfile?.birth_date ?? '',
    avatar: null as File | null,
    id_card_image: null as File | null,
})

function handleAvatarChange(event: Event) {
    form.avatar = (event.target as HTMLInputElement).files?.[0] ?? null
}

function handleIdCardChange(event: Event) {
    form.id_card_image = (event.target as HTMLInputElement).files?.[0] ?? null
}

function submit() {
    form.post('/dashboard/profile', { forceFormData: true })
}
</script>

<template>
    <DashboardLayout>
        <div class="max-w-xl">
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-6">Profil Saya</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Avatar section -->
                <div class="bg-white p-5">
                    <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-4">Foto Profil</p>
                    <div class="flex items-center gap-4">
                        <Avatar
                            :src="props.user.userProfile?.avatar"
                            :name="props.user.userProfile?.name ?? props.user.email"
                            size="lg"
                        />
                        <div>
                            <input
                                type="file"
                                accept="image/*"
                                class="block text-sm text-(--color-text-secondary) file:mr-3 file:py-1.5 file:px-3 file:bg-(--color-surface) file:text-sm file:border-0 file:cursor-pointer"
                                @change="handleAvatarChange"
                            />
                            <p v-if="form.errors.avatar" class="mt-1 text-xs text-(--color-primary)">{{ form.errors.avatar }}</p>
                        </div>
                    </div>
                </div>

                <!-- Profile info -->
                <div class="bg-white p-5 space-y-4">
                    <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide">Data Diri</p>

                    <Input v-model="form.name" label="Nama Lengkap" :error="form.errors.name" required />

                    <div>
                        <p class="text-xs text-(--color-text-secondary) mb-1">Email</p>
                        <p class="text-sm text-(--color-text-primary)">{{ props.user.email }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-(--color-text-secondary) mb-1">Nomor HP</p>
                        <p class="text-sm text-(--color-text-primary)">{{ props.user.phone ?? '-' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-(--color-text-primary) mb-1">Jenis Kelamin</label>
                        <select
                            v-model="form.gender"
                            class="w-full px-3 py-2 text-sm bg-white border border-(--color-border) focus:outline-none focus:border-(--color-primary)"
                        >
                            <option value="">Pilih jenis kelamin</option>
                            <option value="male">Laki-laki</option>
                            <option value="female">Perempuan</option>
                        </select>
                        <p v-if="form.errors.gender" class="mt-1 text-xs text-(--color-primary)">{{ form.errors.gender }}</p>
                    </div>

                    <Input v-model="form.birth_date" type="date" label="Tanggal Lahir" :error="form.errors.birth_date" />
                </div>

                <!-- ID Card -->
                <div class="bg-white p-5">
                    <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-3">KTP / Identitas</p>
                    <div v-if="props.user.userProfile?.id_card_image" class="mb-3">
                        <img :src="props.user.userProfile.id_card_image" alt="KTP" class="max-h-40 object-contain" />
                    </div>
                    <input
                        type="file"
                        accept="image/*"
                        class="block text-sm text-(--color-text-secondary) file:mr-3 file:py-1.5 file:px-3 file:bg-(--color-surface) file:text-sm file:border-0 file:cursor-pointer"
                        @change="handleIdCardChange"
                    />
                    <p v-if="form.errors.id_card_image" class="mt-1 text-xs text-(--color-primary)">{{ form.errors.id_card_image }}</p>
                </div>

                <Button type="submit" :loading="form.processing">Simpan Perubahan</Button>
            </form>
        </div>
    </DashboardLayout>
</template>

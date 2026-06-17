<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import AuthLayout from '@/layouts/AuthLayout.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import * as RegisterController from '@/actions/App/Http/Controllers/Auth/RegisterController'

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    business_name: '',
    bank_name: '',
    account_number: '',
    account_holder: '',
})

function submit() {
    form.post(RegisterController.storeTenant.url())
}
</script>

<template>
    <AuthLayout>
        <div>
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-6">Daftar sebagai Pemilik Kost</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="border-b border-(--color-border) pb-4 mb-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-(--color-text-secondary) mb-3">Data Pribadi</p>

                    <div class="space-y-4">
                        <Input v-model="form.name" label="Nama Lengkap" placeholder="Nama lengkap" :error="form.errors.name" required />
                        <Input v-model="form.email" type="email" label="Email" placeholder="email@contoh.com" :error="form.errors.email" required />
                        <Input v-model="form.phone" label="Nomor HP" placeholder="08xxxxxxxxxx" :error="form.errors.phone" required />
                        <Input v-model="form.password" type="password" label="Kata Sandi" placeholder="Minimal 8 karakter" :error="form.errors.password" required />
                        <Input v-model="form.password_confirmation" type="password" label="Konfirmasi Kata Sandi" placeholder="Ulangi kata sandi" :error="form.errors.password_confirmation" required />
                    </div>
                </div>

                <div class="border-b border-(--color-border) pb-4 mb-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-(--color-text-secondary) mb-3">Data Usaha</p>
                    <Input v-model="form.business_name" label="Nama Usaha" placeholder="Nama usaha kost" :error="form.errors.business_name" required />
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-(--color-text-secondary) mb-3">Rekening Bank</p>
                    <div class="space-y-4">
                        <Input v-model="form.bank_name" label="Nama Bank" placeholder="BCA, BRI, Mandiri, dll." :error="form.errors.bank_name" required />
                        <Input v-model="form.account_number" label="Nomor Rekening" placeholder="Nomor rekening bank" :error="form.errors.account_number" required />
                        <Input v-model="form.account_holder" label="Atas Nama" placeholder="Nama sesuai rekening" :error="form.errors.account_holder" required />
                    </div>
                </div>

                <Button type="submit" class="w-full" :loading="form.processing">
                    Daftar sebagai Pemilik Kost
                </Button>
            </form>

            <div class="mt-4 text-center text-sm text-(--color-text-secondary)">
                Sudah punya akun?
                <a href="/login" class="font-semibold text-(--color-primary)">Masuk</a>
            </div>
        </div>
    </AuthLayout>
</template>

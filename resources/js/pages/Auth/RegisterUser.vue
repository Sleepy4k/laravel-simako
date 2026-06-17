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
    gender: '',
    birth_date: '',
})

function submit() {
    form.post(RegisterController.storeUser.url())
}
</script>

<template>
    <AuthLayout>
        <div>
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-1">Daftar sebagai Pengguna</h1>
            <p class="text-xs text-(--color-text-secondary) mb-6">
                Email ATAU Nomor HP (salah satu wajib diisi).
            </p>

            <form @submit.prevent="submit" class="space-y-4">
                <Input
                    v-model="form.name"
                    label="Nama Lengkap"
                    placeholder="Masukkan nama lengkap"
                    :error="form.errors.name"
                    required
                />

                <Input
                    v-model="form.email"
                    type="email"
                    label="Email (opsional)"
                    placeholder="email@contoh.com"
                    :error="form.errors.email"
                />

                <Input
                    v-model="form.phone"
                    label="Nomor HP (opsional)"
                    placeholder="08xxxxxxxxxx"
                    :error="form.errors.phone"
                />

                <Input
                    v-model="form.password"
                    type="password"
                    label="Kata Sandi"
                    placeholder="Minimal 8 karakter"
                    :error="form.errors.password"
                    required
                />

                <Input
                    v-model="form.password_confirmation"
                    type="password"
                    label="Konfirmasi Kata Sandi"
                    placeholder="Ulangi kata sandi"
                    :error="form.errors.password_confirmation"
                    required
                />

                <div>
                    <label class="block text-sm font-medium text-(--color-text-primary) mb-1">Jenis Kelamin</label>
                    <select
                        v-model="form.gender"
                        class="w-full px-3 py-2 text-sm bg-white border border-(--color-border) text-(--color-text-primary) focus:outline-none focus:border-(--color-primary)"
                    >
                        <option value="">Pilih jenis kelamin</option>
                        <option value="male">Laki-laki</option>
                        <option value="female">Perempuan</option>
                    </select>
                    <p v-if="form.errors.gender" class="mt-1 text-xs text-(--color-primary)">{{ form.errors.gender }}</p>
                </div>

                <Input
                    v-model="form.birth_date"
                    type="date"
                    label="Tanggal Lahir"
                    :error="form.errors.birth_date"
                />

                <Button
                    type="submit"
                    class="w-full"
                    :loading="form.processing"
                >
                    Daftar
                </Button>
            </form>

            <div class="mt-4 text-center text-sm text-(--color-text-secondary)">
                Sudah punya akun?
                <a href="/login" class="font-semibold text-(--color-primary)">Masuk</a>
            </div>
        </div>
    </AuthLayout>
</template>

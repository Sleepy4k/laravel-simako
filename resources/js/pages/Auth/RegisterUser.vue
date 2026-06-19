<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Head, Link } from '@inertiajs/vue3'
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
    <Head title="Daftar Penyewa" />
    <AuthLayout>
        <div>
            <h1 class="text-xl font-bold text-slate-900 mb-1">Daftar sebagai Penyewa</h1>
            <p class="text-xs text-slate-500 mb-6">
                Email atau Nomor HP - salah satu wajib diisi.
            </p>

            <form @submit.prevent="submit" class="space-y-4">
                <Input
                    v-model="form.name"
                    label="Nama Lengkap"
                    placeholder="Masukkan nama lengkap"
                    :error="form.errors.name"
                    required
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-(--color-text-primary) mb-1">Jenis Kelamin</label>
                        <select
                            v-model="form.gender"
                            class="w-full px-3.5 py-2.5 text-sm bg-white border border-(--color-border) rounded-xl text-(--color-text-primary) focus:outline-none focus:ring-2 focus:ring-(--color-primary)/15 focus:border-(--color-primary) transition-all"
                        >
                            <option value="">Pilih jenis kelamin</option>
                            <option value="male">Laki-laki</option>
                            <option value="female">Perempuan</option>
                        </select>
                        <p v-if="form.errors.gender" class="mt-1.5 text-xs text-red-600 font-medium">{{ form.errors.gender }}</p>
                    </div>

                    <Input
                        v-model="form.birth_date"
                        type="date"
                        label="Tanggal Lahir"
                        :error="form.errors.birth_date"
                    />
                </div>

                <Button
                    type="submit"
                    class="w-full mt-2"
                    :loading="form.processing"
                >
                    Daftar
                </Button>
            </form>

            <div class="mt-4 text-center text-sm text-slate-500">
                Sudah punya akun?
                <Link href="/login" class="font-semibold text-(--color-primary)">Masuk</Link>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Head, Link } from '@inertiajs/vue3'
import AuthLayout from '@/layouts/AuthLayout.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import * as LoginController from '@/actions/App/Http/Controllers/Auth/LoginController'

defineProps<{
    errors?: { login?: string; password?: string }
}>()

const form = useForm({
    login: '',
    password: '',
})

function submit() {
    form.post(LoginController.store.url())
}
</script>

<template>
    <Head title="Masuk" />
    <AuthLayout>
        <div>
            <h1 class="text-xl font-bold text-slate-900 mb-1">Selamat datang kembali</h1>
            <p class="text-sm text-slate-500 mb-6">Masuk ke akun Simako Anda</p>

            <form @submit.prevent="submit" class="space-y-4">
                <Input
                    v-model="form.login"
                    label="Email atau Nomor HP"
                    placeholder="email@contoh.com atau 08xxxxxxxxxx"
                    :error="form.errors.login"
                    required
                />

                <Input
                    v-model="form.password"
                    type="password"
                    label="Kata Sandi"
                    placeholder="Masukkan kata sandi"
                    :error="form.errors.password"
                    required
                />

                <Button
                    type="submit"
                    class="w-full"
                    :loading="form.processing"
                >
                    Masuk
                </Button>
            </form>

            <div class="mt-6 text-center text-sm text-slate-500">
                Belum punya akun?
                <Link href="/register" class="font-semibold text-(--color-primary) hover:text-(--color-primary-hover)">Daftar sekarang</Link>
            </div>
        </div>
    </AuthLayout>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
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
    <AuthLayout>
        <div>
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-6">Masuk ke Simako</h1>

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

            <div class="mt-6 text-center text-sm text-(--color-text-secondary)">
                Belum punya akun?
                <a href="/register" class="font-semibold text-(--color-primary) hover:text-(--color-primary-hover)">Daftar sekarang</a>
            </div>
        </div>
    </AuthLayout>
</template>

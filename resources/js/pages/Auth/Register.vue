<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import AuthLayout from '@/layouts/AuthLayout.vue'
import * as RegisterController from '@/actions/App/Http/Controllers/Auth/RegisterController'
</script>

<template>
    <AuthLayout>
        <div>
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-2">Daftar ke Simako</h1>
            <p class="text-sm text-(--color-text-secondary) mb-8">Pilih tipe akun yang ingin Anda buat.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Pengguna -->
                <Link
                    :href="RegisterController.showUserForm.url()"
                    class="block p-6 bg-(--color-surface) hover:bg-red-50 hover:border-(--color-primary) border-2 border-transparent transition-all text-center group"
                >
                    <div class="w-12 h-12 bg-white flex items-center justify-center mx-auto mb-4 group-hover:bg-(--color-primary) transition-colors">
                        <svg class="w-6 h-6 text-(--color-text-secondary) group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-(--color-text-primary) mb-1">Saya Pengguna</h2>
                    <p class="text-xs text-(--color-text-secondary)">Mencari dan booking kost</p>
                </Link>

                <!-- Pemilik Kost -->
                <Link
                    :href="RegisterController.showTenantForm.url()"
                    class="block p-6 bg-(--color-surface) hover:bg-red-50 hover:border-(--color-primary) border-2 border-transparent transition-all text-center group"
                >
                    <div class="w-12 h-12 bg-white flex items-center justify-center mx-auto mb-4 group-hover:bg-(--color-primary) transition-colors">
                        <svg class="w-6 h-6 text-(--color-text-secondary) group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                    </div>
                    <h2 class="text-base font-bold text-(--color-text-primary) mb-1">Saya Pemilik Kost</h2>
                    <p class="text-xs text-(--color-text-secondary)">Kelola kost dan terima booking</p>
                </Link>
            </div>

            <div class="mt-6 text-center text-sm text-(--color-text-secondary)">
                Sudah punya akun?
                <a href="/login" class="font-semibold text-(--color-primary) hover:text-(--color-primary-hover)">Masuk</a>
            </div>
        </div>
    </AuthLayout>
</template>

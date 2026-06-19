<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AuthLayout from '@/layouts/AuthLayout.vue'
import * as RegisterController from '@/actions/App/Http/Controllers/Auth/RegisterController'
</script>

<template>
    <Head title="Daftar" />
    <AuthLayout>
        <div>
            <h1 class="text-xl font-bold text-slate-900 mb-1">Buat akun baru</h1>
            <p class="text-sm text-slate-500 mb-8">Pilih tipe akun yang ingin Anda buat</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Pengguna -->
                <Link
                    :href="RegisterController.showUserForm.url()"
                    class="block p-5 bg-slate-50 hover:bg-rose-50 hover:border-rose-300 border-2 border-slate-200 rounded-2xl transition-all text-center group cursor-pointer"
                >
                    <div class="w-12 h-12 bg-white flex items-center justify-center mx-auto mb-4 group-hover:bg-(--color-primary) transition-colors rounded-xl border border-slate-200 shadow-sm">
                        <svg class="w-6 h-6 text-slate-500 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h2 class="text-sm font-bold text-slate-900 mb-1">Saya Penyewa</h2>
                    <p class="text-xs text-slate-500">Mencari dan booking kost</p>
                </Link>

                <!-- Pemilik Kost -->
                <Link
                    :href="RegisterController.showTenantForm.url()"
                    class="block p-5 bg-slate-50 hover:bg-rose-50 hover:border-rose-300 border-2 border-slate-200 rounded-2xl transition-all text-center group cursor-pointer"
                >
                    <div class="w-12 h-12 bg-white flex items-center justify-center mx-auto mb-4 group-hover:bg-(--color-primary) transition-colors rounded-xl border border-slate-200 shadow-sm">
                        <svg class="w-6 h-6 text-slate-500 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        </svg>
                    </div>
                    <h2 class="text-sm font-bold text-slate-900 mb-1">Saya Pemilik Kost</h2>
                    <p class="text-xs text-slate-500">Kelola kost dan terima booking</p>
                </Link>
            </div>

            <div class="mt-6 text-center text-sm text-slate-500">
                Sudah punya akun?
                <Link href="/login" class="font-semibold text-(--color-primary) hover:text-(--color-primary-hover)">Masuk</Link>
            </div>
        </div>
    </AuthLayout>
</template>

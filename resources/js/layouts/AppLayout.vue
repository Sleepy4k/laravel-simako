<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import type { Auth } from '@/types/auth'

const page = usePage()
const auth = computed(() => page.props.auth as Auth)
const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined)
</script>

<template>
    <div class="min-h-screen flex flex-col bg-white">
        <!-- Sticky Header -->
        <header class="sticky top-0 z-50 bg-white border-b border-(--color-border)">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <Link href="/" class="flex items-center">
                        <span class="text-2xl font-black text-(--color-primary) tracking-tight">Simako</span>
                    </Link>

                    <!-- Nav -->
                    <nav class="hidden md:flex items-center gap-8">
                        <Link href="/" class="text-sm font-medium text-(--color-text-primary) hover:text-(--color-primary) transition-colors">
                            Beranda
                        </Link>
                        <Link href="/kosts" class="text-sm font-medium text-(--color-text-primary) hover:text-(--color-primary) transition-colors">
                            Cari Kost
                        </Link>
                    </nav>

                    <!-- Auth buttons -->
                    <div class="flex items-center gap-3">
                        <template v-if="auth.user">
                            <Link
                                href="/dashboard"
                                class="text-sm font-medium text-(--color-text-primary) hover:text-(--color-primary) transition-colors"
                            >
                                Dashboard
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                href="/login"
                                class="text-sm font-medium text-(--color-text-primary) hover:text-(--color-primary) transition-colors px-4 py-2"
                            >
                                Masuk
                            </Link>
                            <Link
                                href="/register"
                                class="text-sm font-semibold bg-(--color-primary) hover:bg-(--color-primary-hover) text-white px-4 py-2 transition-colors"
                            >
                                Daftar
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash messages -->
        <div v-if="flash?.success || flash?.error" class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-4">
            <div v-if="flash?.success" class="rounded-lg bg-green-50 px-4 py-3 text-sm text-(--color-success)">
                {{ flash.success }}
            </div>
            <div v-if="flash?.error" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-(--color-primary)">
                {{ flash.error }}
            </div>
        </div>

        <!-- Main content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <span class="text-xl font-black text-(--color-primary)">Simako</span>
                <p class="mt-2 text-sm text-gray-400">
                    &copy; {{ new Date().getFullYear() }} Simako. Platform Manajemen Kost Terpadu.
                </p>
            </div>
        </footer>
    </div>
</template>

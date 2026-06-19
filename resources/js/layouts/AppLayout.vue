<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { computed, ref, onMounted, onUnmounted, watch } from 'vue'
import type { Auth } from '@/types/auth'
import Toast from '@/components/ui/Toast.vue'

const page = usePage()
const auth = computed(() => page.props.auth as Auth)
const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined)

const mobileMenuOpen = ref(false)
const scrolled = ref(false)

const showSuccessToast = ref(false)
const showErrorToast = ref(false)

function onScroll() {
    if (typeof window !== 'undefined') {
        scrolled.value = window.scrollY > 10
    }
}

onMounted(() => {
    if (typeof window !== 'undefined') {
        window.addEventListener('scroll', onScroll, { passive: true })
    }
})

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('scroll', onScroll)
    }
})

watch(() => flash.value?.success, (val) => { if (val) showSuccessToast.value = true }, { immediate: true })
watch(() => flash.value?.error, (val) => { if (val) showErrorToast.value = true }, { immediate: true })

const currentYear = new Date().getFullYear()
</script>

<template>
    <div class="min-h-screen flex flex-col bg-white">
        <!-- Toast notifications -->
        <div class="fixed top-4 right-4 z-[60] flex flex-col gap-2">
            <Toast
                :show="showSuccessToast"
                :message="flash?.success ?? ''"
                type="success"
                @close="showSuccessToast = false"
            />
            <Toast
                :show="showErrorToast"
                :message="flash?.error ?? ''"
                type="error"
                @close="showErrorToast = false"
            />
        </div>

        <!-- Header -->
        <header
            class="sticky top-0 z-50 transition-all duration-200"
            :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-sm' : 'bg-white border-b border-(--color-border)'"
        >
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <!-- Logo -->
                    <Link href="/" class="flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-(--color-primary) rounded-lg flex items-center justify-center shadow-sm shadow-(--color-primary)/30">
                            <span class="text-white font-black text-sm">S</span>
                        </div>
                        <span class="text-xl font-black text-slate-900 tracking-tight">Simako</span>
                    </Link>

                    <!-- Desktop Nav -->
                    <nav class="hidden md:flex items-center gap-1">
                        <Link
                            href="/"
                            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                            :class="page.url === '/' ? 'text-(--color-primary) bg-rose-50' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50'"
                        >
                            Beranda
                        </Link>
                        <Link
                            href="/kosts"
                            class="px-4 py-2 text-sm font-medium rounded-lg transition-colors"
                            :class="page.url.startsWith('/kosts') ? 'text-(--color-primary) bg-rose-50' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50'"
                        >
                            Cari Kost
                        </Link>
                    </nav>

                    <!-- Auth buttons -->
                    <div class="flex items-center gap-2">
                        <template v-if="auth.user">
                            <Link
                                href="/dashboard"
                                class="hidden sm:flex items-center gap-2 px-4 py-2 text-sm font-semibold text-(--color-primary) bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                                Dashboard
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                href="/login"
                                class="hidden sm:block px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 rounded-lg transition-colors"
                            >
                                Masuk
                            </Link>
                            <Link
                                href="/register"
                                class="px-4 py-2 text-sm font-semibold bg-(--color-primary) hover:bg-(--color-primary-hover) text-white rounded-lg transition-colors shadow-sm shadow-(--color-primary)/20"
                            >
                                Daftar
                            </Link>
                        </template>

                        <!-- Mobile menu button -->
                        <button
                            class="md:hidden p-2 text-slate-500 hover:text-slate-900 rounded-lg transition-colors"
                            @click="mobileMenuOpen = !mobileMenuOpen"
                        >
                            <svg v-if="!mobileMenuOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <Transition
                    enter-active-class="duration-150 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="duration-100 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <div v-if="mobileMenuOpen" class="md:hidden pb-4 border-t border-slate-100 mt-2 pt-4">
                        <nav class="flex flex-col gap-1">
                            <Link href="/" class="px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Beranda</Link>
                            <Link href="/kosts" class="px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Cari Kost</Link>
                            <template v-if="auth.user">
                                <Link href="/dashboard" class="px-3 py-2 text-sm font-semibold text-(--color-primary) hover:bg-rose-50 rounded-lg">Dashboard</Link>
                            </template>
                            <template v-else>
                                <Link href="/login" class="px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 rounded-lg">Masuk</Link>
                                <Link href="/register" class="px-3 py-2 text-sm font-semibold text-(--color-primary) hover:bg-rose-50 rounded-lg">Daftar</Link>
                            </template>
                        </nav>
                    </div>
                </Transition>
            </div>
        </header>

        <!-- Main content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-slate-900 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Top footer -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 py-14">
                    <!-- Col 1: Brand -->
                    <div class="lg:col-span-2">
                        <div class="flex items-center gap-2.5 mb-4">
                            <div class="w-8 h-8 bg-(--color-primary) rounded-lg flex items-center justify-center">
                                <span class="text-white font-black text-sm">S</span>
                            </div>
                            <span class="text-xl font-black tracking-tight">Simako</span>
                        </div>
                        <p class="text-sm text-slate-400 leading-relaxed mb-6 max-w-xs">
                            Platform manajemen kost terpadu untuk penyewa dan pemilik kost di seluruh Indonesia.
                        </p>
                        <!-- Social links -->
                        <div class="flex items-center gap-3">
                            <a href="#" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-white/10 flex items-center justify-center transition-colors" aria-label="Instagram">
                                <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                            <a href="#" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-white/10 flex items-center justify-center transition-colors" aria-label="Twitter/X">
                                <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </a>
                            <a href="#" class="w-9 h-9 rounded-lg bg-white/5 hover:bg-white/10 flex items-center justify-center transition-colors" aria-label="WhatsApp">
                                <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Col 2: Navigation -->
                    <div>
                        <h3 class="text-xs font-bold text-slate-300 mb-4 uppercase tracking-wider">Navigasi</h3>
                        <ul class="space-y-3">
                            <li><Link href="/" class="text-sm text-slate-400 hover:text-white transition-colors">Beranda</Link></li>
                            <li><Link href="/kosts" class="text-sm text-slate-400 hover:text-white transition-colors">Cari Kost</Link></li>
                            <li><Link href="/kosts?type=putra" class="text-sm text-slate-400 hover:text-white transition-colors">Kost Putra</Link></li>
                            <li><Link href="/kosts?type=putri" class="text-sm text-slate-400 hover:text-white transition-colors">Kost Putri</Link></li>
                            <li><Link href="/kosts?type=campur" class="text-sm text-slate-400 hover:text-white transition-colors">Kost Campur</Link></li>
                        </ul>
                    </div>

                    <!-- Col 3: Help -->
                    <div>
                        <h3 class="text-xs font-bold text-slate-300 mb-4 uppercase tracking-wider">Bantuan</h3>
                        <ul class="space-y-3">
                            <li><Link href="/contact" class="text-sm text-slate-400 hover:text-white transition-colors">Hubungi Kami</Link></li>
                            <li><Link href="/help" class="text-sm text-slate-400 hover:text-white transition-colors">Pusat Bantuan</Link></li>
                            <li><Link href="/terms" class="text-sm text-slate-400 hover:text-white transition-colors">Syarat & Ketentuan</Link></li>
                            <li><Link href="/privacy" class="text-sm text-slate-400 hover:text-white transition-colors">Kebijakan Privasi</Link></li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom bar -->
                <div class="border-t border-white/10 py-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-sm text-slate-500">
                        &copy; {{ currentYear }} Simako. Semua hak dilindungi.
                    </p>
                    <p class="text-sm text-slate-500">
                        Dibuat dengan ❤️ di Indonesia
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>

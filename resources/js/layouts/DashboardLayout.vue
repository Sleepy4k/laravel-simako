<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed, ref, onMounted } from 'vue'
import type { Auth } from '@/types/auth'
import Avatar from '@/components/ui/Avatar.vue'
import Modal from '@/components/ui/Modal.vue'
import Toast from '@/components/ui/Toast.vue'

const page = usePage()
const auth = computed(() => page.props.auth as Auth)
const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined)
const sidebarOpen = ref(false)
const userMenuOpen = ref(false)
const showLogoutModal = ref(false)

const showSuccessToast = ref(false)
const showErrorToast = ref(false)

onMounted(() => {
    if (flash.value?.success) showSuccessToast.value = true
    if (flash.value?.error) showErrorToast.value = true
})

const roleName = computed(() => auth.value.user?.role?.name ?? 'pengguna')

interface NavItem {
    label: string
    href: string
    icon: string
    exactMatch?: boolean
}

const navItems = computed((): NavItem[] => {
    switch (roleName.value) {
        case 'admin':
            return [
                { label: 'Dashboard', href: '/dashboard', icon: 'grid', exactMatch: true },
                { label: 'Pengguna', href: '/dashboard/admin/users', icon: 'users' },
                { label: 'Tenant', href: '/dashboard/admin/tenants', icon: 'building' },
                { label: 'Kost', href: '/dashboard/admin/kosts', icon: 'home' },
                { label: 'Booking', href: '/dashboard/admin/bookings', icon: 'calendar' },
                { label: 'Pembayaran', href: '/dashboard/admin/payments', icon: 'credit-card' },
            ]
        case 'tenant':
            return [
                { label: 'Dashboard', href: '/dashboard', icon: 'grid', exactMatch: true },
                { label: 'Kost Saya', href: '/dashboard/kosts', icon: 'home' },
                { label: 'Booking Masuk', href: '/dashboard/tenant/bookings', icon: 'calendar' },
                { label: 'Pembayaran', href: '/dashboard/tenant/payments', icon: 'credit-card' },
                { label: 'Pesan', href: '/dashboard/tenant/messages', icon: 'chat' },
                { label: 'Rekening Bank', href: '/dashboard/bank-accounts', icon: 'bank' },
                { label: 'Pendapatan', href: '/dashboard/earnings', icon: 'chart' },
            ]
        default:
            return [
                { label: 'Dashboard', href: '/dashboard', icon: 'grid', exactMatch: true },
                { label: 'Booking Saya', href: '/dashboard/bookings', icon: 'calendar' },
                { label: 'Pembayaran', href: '/dashboard/payments', icon: 'credit-card' },
                { label: 'Pesan', href: '/dashboard/messages', icon: 'chat' },
                { label: 'Profil', href: '/dashboard/profile', icon: 'user' },
            ]
    }
})

const roleLabel = computed(() => {
    switch (roleName.value) {
        case 'admin': return 'Administrator'
        case 'tenant': return 'Pemilik Kost'
        default: return 'Pengguna'
    }
})

function isActive(item: NavItem): boolean {
    if (item.exactMatch) return page.url === item.href
    return page.url.startsWith(item.href)
}

function doLogout() {
    router.post('/logout')
}

const ICONS: Record<string, string> = {
    grid: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z',
    home: 'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z M9 22V12h6v10',
    users: 'M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2 M23 21v-2a4 4 0 00-3-3.87 M16 3.13a4 4 0 010 7.75',
    building: 'M3 21h18 M5 21V7l8-4v18 M19 21V11l-6-4 M9 9h1v1H9z M13 9h1v1h-1z M9 13h1v1H9z M13 13h1v1h-1z M9 17h1v1H9z M13 17h1v1h-1z',
    calendar: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    'credit-card': 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
    chat: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    bank: 'M3 21h18 M3 10h18 M5 6l7-3 7 3 M4 10v11 M20 10v11 M8 10v11 M12 10v11 M16 10v11',
    chart: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    user: 'M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2 M12 11a4 4 0 100-8 4 4 0 000 8z',
}
</script>

<template>
    <div class="min-h-screen bg-(--color-surface) flex">
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

        <!-- Logout Modal -->
        <Modal
            :open="showLogoutModal"
            title="Keluar dari Simako?"
            message="Sesi Anda akan diakhiri. Anda bisa masuk kembali kapan saja."
            confirm-label="Ya, Keluar"
            confirm-variant="danger"
            @confirm="doLogout"
            @cancel="showLogoutModal = false"
        />

        <!-- Sidebar overlay (mobile) -->
        <Transition
            enter-active-class="duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-20 bg-black/50 backdrop-blur-sm lg:hidden"
                @click="sidebarOpen = false"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-30 w-64 bg-white flex flex-col transition-transform duration-300 ease-out lg:translate-x-0 lg:static lg:flex border-r border-(--color-border)"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Logo -->
            <div class="flex items-center h-16 px-5 border-b border-(--color-border)">
                <Link href="/" class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-(--color-primary) rounded-lg flex items-center justify-center">
                        <span class="text-white font-black text-sm">S</span>
                    </div>
                    <span class="text-lg font-black text-(--color-text-primary) tracking-tight">Simako</span>
                </Link>
            </div>

            <!-- Nav items -->
            <nav class="flex-1 overflow-y-auto py-4 px-3">
                <p class="px-3 mb-2 text-[10px] font-bold uppercase tracking-widest text-(--color-text-muted)">
                    Menu
                </p>
                <ul class="space-y-0.5">
                    <li v-for="item in navItems" :key="item.href">
                        <Link
                            :href="item.href"
                            class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150"
                            :class="isActive(item)
                                ? 'text-(--color-primary) bg-(--color-primary-light) font-semibold'
                                : 'text-(--color-text-secondary) hover:text-(--color-text-primary) hover:bg-(--color-surface)'"
                        >
                            <!-- Icon -->
                            <svg
                                class="w-4.5 h-4.5 shrink-0"
                                :class="isActive(item) ? 'text-(--color-primary)' : 'text-(--color-text-muted)'"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.75"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" :d="ICONS[item.icon]" />
                            </svg>
                            {{ item.label }}

                            <!-- Active indicator -->
                            <span
                                v-if="isActive(item)"
                                class="ml-auto w-1.5 h-1.5 rounded-full bg-(--color-primary)"
                            />
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- User card at bottom -->
            <div class="p-4 border-t border-(--color-border)">
                <div class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-(--color-surface) transition-colors cursor-pointer" @click="userMenuOpen = !userMenuOpen">
                    <Avatar
                        :src="auth.user?.userProfile?.avatar ?? null"
                        :name="auth.user?.userProfile?.name ?? auth.user?.email ?? 'U'"
                        size="sm"
                    />
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-(--color-text-primary) truncate">
                            {{ auth.user?.userProfile?.name ?? auth.user?.email }}
                        </p>
                        <p class="text-xs text-(--color-text-muted)">{{ roleLabel }}</p>
                    </div>
                    <svg class="w-4 h-4 text-(--color-text-muted) transition-transform duration-200" :class="userMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

                <!-- User dropdown -->
                <Transition
                    enter-active-class="duration-150 ease-out"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="duration-100 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-1"
                >
                    <div v-if="userMenuOpen" class="mt-2 px-2 py-1 bg-(--color-surface) rounded-xl">
                        <Link
                            href="/dashboard/profile"
                            class="flex items-center gap-2 px-3 py-2 text-sm text-(--color-text-secondary) hover:text-(--color-text-primary) rounded-lg transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Edit Profil
                        </Link>
                        <button
                            class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            @click="showLogoutModal = true; userMenuOpen = false"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Keluar
                        </button>
                    </div>
                </Transition>
            </div>
        </aside>

        <!-- Main content area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="bg-white border-b border-(--color-border) h-16 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-10">
                <!-- Mobile menu button -->
                <button
                    class="lg:hidden p-2 text-(--color-text-secondary) hover:text-(--color-text-primary) rounded-lg transition-colors"
                    @click="sidebarOpen = !sidebarOpen"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Breadcrumb / Page title slot -->
                <div class="flex-1 lg:flex-none px-2 lg:px-0">
                    <slot name="header" />
                </div>

                <!-- Right side -->
                <div class="flex items-center gap-3">
                    <!-- Notification bell (static) -->
                    <button class="relative p-2 text-(--color-text-secondary) hover:text-(--color-text-primary) rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>

                    <!-- Desktop user info -->
                    <div class="hidden lg:flex items-center gap-2.5">
                        <Avatar
                            :src="auth.user?.userProfile?.avatar ?? null"
                            :name="auth.user?.userProfile?.name ?? auth.user?.email ?? 'U'"
                            size="sm"
                        />
                        <div>
                            <p class="text-sm font-semibold text-(--color-text-primary) leading-tight">
                                {{ auth.user?.userProfile?.name ?? auth.user?.email }}
                            </p>
                            <p class="text-xs text-(--color-text-muted) leading-tight">{{ roleLabel }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>

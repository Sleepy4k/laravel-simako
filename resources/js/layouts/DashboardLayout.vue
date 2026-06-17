<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import type { Auth } from '@/types/auth'
import Avatar from '@/components/ui/Avatar.vue'

const page = usePage()
const auth = computed(() => page.props.auth as Auth)
const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined)
const sidebarOpen = ref(false)

const roleName = computed(() => auth.value.user?.role?.name ?? 'pengguna')

interface NavItem {
    label: string
    href: string
}

const navItems = computed((): NavItem[] => {
    switch (roleName.value) {
        case 'admin':
            return [
                { label: 'Dashboard', href: '/dashboard/admin' },
                { label: 'Pengguna', href: '/dashboard/admin/users' },
                { label: 'Tenant', href: '/dashboard/admin/tenants' },
                { label: 'Kost', href: '/dashboard/admin/kosts' },
                { label: 'Booking', href: '/dashboard/admin/bookings' },
                { label: 'Pembayaran', href: '/dashboard/admin/payments' },
            ]
        case 'tenant':
            return [
                { label: 'Dashboard', href: '/dashboard' },
                { label: 'Kost Saya', href: '/dashboard/kosts' },
                { label: 'Booking Masuk', href: '/dashboard/tenant/bookings' },
                { label: 'Pembayaran', href: '/dashboard/tenant/payments' },
                { label: 'Pesan', href: '/dashboard/tenant/messages' },
                { label: 'Rekening Bank', href: '/dashboard/tenant/bank-accounts' },
                { label: 'Pendapatan', href: '/dashboard/tenant/earnings' },
            ]
        default:
            return [
                { label: 'Dashboard', href: '/dashboard' },
                { label: 'Booking Saya', href: '/dashboard/bookings' },
                { label: 'Pembayaran', href: '/dashboard/payments' },
                { label: 'Pesan', href: '/dashboard/messages' },
            ]
    }
})

const roleLabel = computed(() => {
    switch (roleName.value) {
        case 'admin': return 'Admin'
        case 'tenant': return 'Pemilik Kost'
        default: return 'Pengguna'
    }
})

function logout() {
    router.post('/logout')
}
</script>

<template>
    <div class="min-h-screen bg-(--color-surface) flex">
        <!-- Sidebar overlay (mobile) -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-20 bg-black/50 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-30 w-64 bg-white flex flex-col transition-transform duration-200 lg:translate-x-0 lg:static lg:flex"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Logo -->
            <div class="flex items-center h-16 px-6 border-b border-(--color-border)">
                <Link href="/" class="text-2xl font-black text-(--color-primary) tracking-tight">
                    Simako
                </Link>
            </div>

            <!-- Role badge -->
            <div class="px-4 py-3 border-b border-(--color-border)">
                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium bg-(--color-surface) text-(--color-text-secondary)">
                    {{ roleLabel }}
                </span>
            </div>

            <!-- Nav items -->
            <nav class="flex-1 overflow-y-auto py-4 px-3">
                <ul class="space-y-1">
                    <li v-for="item in navItems" :key="item.href">
                        <Link
                            :href="item.href"
                            class="flex items-center px-3 py-2 text-sm font-medium rounded transition-colors"
                            :class="page.url.startsWith(item.href) && (item.href !== '/dashboard' || page.url === '/dashboard')
                                ? 'bg-(--color-primary) text-white'
                                : 'text-(--color-text-primary) hover:bg-(--color-surface)'"
                        >
                            {{ item.label }}
                        </Link>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main content area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="bg-white border-b border-(--color-border) h-16 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-10">
                <!-- Mobile menu button -->
                <button
                    class="lg:hidden p-2 text-(--color-text-secondary) hover:text-(--color-text-primary)"
                    @click="sidebarOpen = !sidebarOpen"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div class="flex-1 lg:flex-none" />

                <!-- User info -->
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <Avatar
                            :src="auth.user?.userProfile?.avatar ?? null"
                            :name="auth.user?.userProfile?.name ?? auth.user?.email ?? 'U'"
                            size="sm"
                        />
                        <span class="hidden sm:block text-sm font-medium text-(--color-text-primary)">
                            {{ auth.user?.userProfile?.name ?? auth.user?.email }}
                        </span>
                    </div>
                    <button
                        class="text-sm text-(--color-text-secondary) hover:text-(--color-primary) transition-colors"
                        @click="logout"
                    >
                        Keluar
                    </button>
                </div>
            </header>

            <!-- Flash messages -->
            <div v-if="flash?.success || flash?.error" class="px-4 sm:px-6 pt-4">
                <div v-if="flash?.success" class="rounded-lg bg-green-50 px-4 py-3 text-sm text-(--color-success) mb-3">
                    {{ flash.success }}
                </div>
                <div v-if="flash?.error" class="rounded-lg bg-red-50 px-4 py-3 text-sm text-(--color-primary) mb-3">
                    {{ flash.error }}
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1 p-4 sm:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

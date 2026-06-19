<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined)
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-rose-50/30 flex flex-col items-center justify-center px-4 py-12">
        <!-- Flash -->
        <div v-if="flash?.success" class="w-full max-w-[448px] mb-4 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700 font-medium">
            {{ flash.success }}
        </div>
        <div v-if="flash?.error" class="w-full max-w-[448px] mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-600 font-medium">
            {{ flash.error }}
        </div>

        <div class="w-full max-w-[448px]">
            <!-- Brand -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-flex items-center gap-2 group">
                    <div class="w-9 h-9 bg-(--color-primary) rounded-xl flex items-center justify-center shadow-sm shadow-(--color-primary)/30">
                        <span class="text-white font-black text-sm">S</span>
                    </div>
                    <span class="text-2xl font-black text-slate-900 tracking-tight">Simako</span>
                </Link>
                <p class="mt-2 text-sm text-slate-500">Platform Manajemen Kost Terpadu</p>
            </div>

            <div class="bg-white px-8 py-10 rounded-3xl border border-slate-200 shadow-sm shadow-slate-900/5">
                <slot />
            </div>
        </div>
    </div>
</template>

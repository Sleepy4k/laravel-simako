<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import KostCard from '@/components/kost/KostCard.vue'
import type { Kost } from '@/types/models'

const props = defineProps<{
    featuredKosts: Kost[]
}>()

const search = ref('')
const activeFaq = ref<number | null>(null)

function handleSearch() {
    if (search.value.trim()) {
        router.visit('/kosts', { data: { search: search.value } })
    }
}

function toggleFaq(index: number) {
    activeFaq.value = activeFaq.value === index ? null : index
}

const faqs = [
    {
        q: 'Bagaimana cara menyewa kost di Simako?',
        a: 'Pilih kost yang Anda inginkan, tentukan kamar, dan klik tombol booking. Masukkan tanggal mulai sewa lalu ajukan ke pemilik. Setelah disetujui, bayar tagihan Anda melalui menu Pembayaran di dashboard.'
    },
    {
        q: 'Apakah semua kost di Simako terverifikasi?',
        a: 'Simako memverifikasi identitas pemilik kost (KTP dan profil bisnis) serta memverifikasi ulasan dari penyewa riil untuk menjamin kenyamanan Anda.'
    },
    {
        q: 'Bagaimana sistem pembayaran sewa kost?',
        a: 'Penyewa melakukan transfer langsung ke rekening bank pemilik kost yang terdaftar di sistem. Bukti bayar diunggah ke sistem dan diverifikasi langsung oleh pemilik.'
    }
]

const tips = [
    {
        title: 'Tips Memilih Kost Dekat Kampus yang Nyaman',
        desc: 'Cari tahu jarak jalan kaki, akses internet, dan fasilitas air bersih sebelum memutuskan menyewa.',
        date: '15 Juni 2026',
        category: 'Tips Sewa'
    },
    {
        title: 'Mengelola Pengeluaran Bulanan bagi Anak Kost',
        desc: 'Simak panduan menyusun anggaran makan, kuota, sewa kost, dan menabung agar tidak boros.',
        date: '10 Juni 2026',
        category: 'Gaya Hidup'
    },
    {
        title: 'Hak & Kewajiban Penyewa yang Wajib Diketahui',
        desc: 'Ketahui batasan jam malam, aturan tamu, serta biaya tambahan seperti listrik agar tidak salah paham.',
        date: '05 Juni 2026',
        category: 'Aturan Kost'
    }
]
</script>

<template>
    <AppLayout>
        <div class="space-y-20 pb-20 bg-slate-50/50">
            <!-- Hero Section -->
            <section class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-rose-950 text-white overflow-hidden py-24 sm:py-32">
                <div class="absolute inset-0 opacity-15">
                    <div class="absolute top-10 left-10 w-96 h-96 bg-rose-500 rounded-full blur-3xl" />
                    <div class="absolute bottom-10 right-10 w-96 h-96 bg-indigo-500 rounded-full blur-3xl" />
                </div>
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-8">
                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold bg-white/10 rounded-full backdrop-blur-md text-rose-300">
                        ⚡ Booking Kost Mudah & Terpercaya
                    </span>
                    <h1 class="text-4xl sm:text-6xl font-black tracking-tight leading-[1.15] max-w-4xl mx-auto">
                        Temukan Hunian Kost <br class="hidden sm:inline" />
                        <span class="text-rose-500">Nyaman</span> & Lebih <span class="text-rose-400">Praktis</span>
                    </h1>
                    <p class="text-base sm:text-lg text-slate-300 max-w-xl mx-auto leading-relaxed">
                        Platform terintegrasi untuk mencari, menyewa, dan mengelola pembayaran kost secara transparan di seluruh Indonesia.
                    </p>
                    
                    <!-- Search Box -->
                    <div class="max-w-xl mx-auto flex flex-col sm:flex-row gap-2 bg-white/5 p-2 rounded-2xl border border-white/10 backdrop-blur-md">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari kost berdasarkan nama kota atau area..."
                            class="flex-1 px-4 py-3 text-sm text-slate-950 bg-white rounded-xl focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all placeholder:text-slate-400"
                            @keyup.enter="handleSearch"
                        />
                        <button
                            class="bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 text-sm font-bold rounded-xl transition-all shadow-md hover:shadow-rose-600/20 cursor-pointer"
                            @click="handleSearch"
                        >
                            Cari Sekarang
                        </button>
                    </div>
                </div>
            </section>

            <!-- Featured Kost Section -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
                    <div>
                        <span class="text-xs font-bold text-rose-600 uppercase tracking-widest">Rekomendasi</span>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">Kost Terbaru</h2>
                    </div>
                    <Link
                        href="/kosts"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-600 hover:text-rose-700 transition-colors"
                    >
                        Lihat Semua Kost
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>

                <div v-if="props.featuredKosts?.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <KostCard v-for="kost in props.featuredKosts" :key="kost.id" :kost="kost" />
                </div>

                <div v-else class="text-center py-20 bg-white border border-slate-200 rounded-3xl">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <p class="text-sm font-semibold text-slate-900">Belum ada kost yang tersedia</p>
                    <p class="text-xs text-slate-500 mt-1">Coba cari area lain atau hubungi dukungan.</p>
                </div>
            </section>

            <!-- Key Features / Benefits -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white border border-slate-200 rounded-3xl p-8 sm:p-12 shadow-sm grid grid-cols-1 md:grid-cols-3 gap-8 sm:gap-12">
                    <div class="space-y-3">
                        <div class="w-10 h-10 bg-rose-50 text-rose-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">100% Terverifikasi</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Kami melakukan seleksi ketat dan validasi data pemilik kost untuk memastikan keakuratan informasi hunian.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Pembayaran Terintegrasi</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Upload bukti pembayaran langsung melalui dashboard penyewa dan pantau konfirmasi tagihan bulanan Anda.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Pesan Langsung</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Hubungi pemilik kost secara privat untuk menanyakan detail kamar melalui fitur percakapan internal kami.
                        </p>
                    </div>
                </div>
            </section>

            <!-- How it Works Section -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-xl mx-auto mb-12">
                    <span class="text-xs font-bold text-rose-600 uppercase tracking-widest">Alur Proses</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">Cara Kerja Simako</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="bg-white border border-slate-200 p-6 rounded-2xl text-center relative shadow-sm hover:shadow-md transition-all">
                        <div class="w-8 h-8 rounded-full bg-rose-600 text-white font-bold text-sm flex items-center justify-center mx-auto mb-4">1</div>
                        <h3 class="text-sm font-bold text-slate-900 mb-1">Cari Kamar</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">Temukan area kost yang cocok lewat filter pencarian kami.</p>
                    </div>

                    <div class="bg-white border border-slate-200 p-6 rounded-2xl text-center relative shadow-sm hover:shadow-md transition-all">
                        <div class="w-8 h-8 rounded-full bg-rose-600 text-white font-bold text-sm flex items-center justify-center mx-auto mb-4">2</div>
                        <h3 class="text-sm font-bold text-slate-900 mb-1">Booking & Konfirmasi</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">Pilih periode sewa, tentukan tanggal mulai, dan kirim booking.</p>
                    </div>

                    <div class="bg-white border border-slate-200 p-6 rounded-2xl text-center relative shadow-sm hover:shadow-md transition-all">
                        <div class="w-8 h-8 rounded-full bg-rose-600 text-white font-bold text-sm flex items-center justify-center mx-auto mb-4">3</div>
                        <h3 class="text-sm font-bold text-slate-900 mb-1">Bayar & Upload</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">Lakukan transfer bank lalu upload bukti bayar di dashboard.</p>
                    </div>

                    <div class="bg-white border border-slate-200 p-6 rounded-2xl text-center relative shadow-sm hover:shadow-md transition-all">
                        <div class="w-8 h-8 rounded-full bg-rose-600 text-white font-bold text-sm flex items-center justify-center mx-auto mb-4">4</div>
                        <h3 class="text-sm font-bold text-slate-900 mb-1">Masuk Kost</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">Selamat! Anda bisa langsung masuk ke kost impian sesuai tanggal mulai.</p>
                    </div>
                </div>
            </section>

            <!-- FAQ Section -->
            <section class="max-w-3xl mx-auto px-4 sm:px-6">
                <div class="text-center mb-10">
                    <span class="text-xs font-bold text-rose-600 uppercase tracking-widest">Tanya Jawab</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">FAQ Terpopuler</h2>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="(faq, idx) in faqs"
                        :key="idx"
                        class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm"
                    >
                        <button
                            class="w-full px-5 py-4 flex items-center justify-between text-left font-bold text-sm text-slate-900 hover:bg-slate-50/50"
                            @click="toggleFaq(idx)"
                        >
                            <span>{{ faq.q }}</span>
                            <svg
                                class="w-4 h-4 text-slate-400 transition-transform duration-200"
                                :class="activeFaq === idx ? 'rotate-180' : ''"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div v-if="activeFaq === idx" class="px-5 pb-5 pt-3 border-t border-slate-100">
                            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                                {{ faq.a }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tips & Articles Section -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-xl mx-auto mb-12">
                    <span class="text-xs font-bold text-rose-600 uppercase tracking-widest">Informasi</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">Tips & Panduan Kost</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="(tip, idx) in tips"
                        :key="idx"
                        class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between p-6"
                    >
                        <div>
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-rose-600 bg-rose-50 px-2.5 py-1 rounded-md mb-4">
                                {{ tip.category }}
                            </span>
                            <h3 class="text-base font-bold text-slate-900 mb-2 leading-snug">
                                {{ tip.title }}
                            </h3>
                            <p class="text-xs text-slate-500 leading-relaxed mb-4">
                                {{ tip.desc }}
                            </p>
                        </div>
                        <div class="flex items-center justify-between pt-4 border-t border-slate-100 text-[10px] text-slate-400 font-semibold">
                            <span>{{ tip.date }}</span>
                            <span class="text-rose-600 cursor-pointer hover:underline">Baca Selengkapnya →</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-rose-950 text-white rounded-3xl p-8 sm:p-14 text-center space-y-6 relative overflow-hidden shadow-lg">
                    <div class="absolute inset-0 opacity-10 pointer-events-none">
                        <div class="absolute -top-10 -left-10 w-64 h-64 bg-rose-500 rounded-full blur-2xl" />
                        <div class="absolute -bottom-10 -right-10 w-64 h-64 bg-indigo-500 rounded-full blur-2xl" />
                    </div>
                    <div class="relative max-w-2xl mx-auto space-y-4">
                        <h2 class="text-2xl sm:text-4xl font-black leading-tight">Mulai Kelola Kost Anda Lebih Profesional</h2>
                        <p class="text-sm sm:text-base text-slate-300">
                            Daftarkan akun sebagai Pemilik Kost untuk mengelola kamar, booking penyewa, dan memverifikasi pembayaran bulanan secara instan dalam satu platform.
                        </p>
                        <div class="pt-4 flex flex-col sm:flex-row justify-center gap-3">
                            <Link
                                href="/register/tenant"
                                class="px-6 py-3 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition-all shadow-md hover:shadow-rose-600/10 cursor-pointer"
                            >
                                Daftar Pemilik Kost
                            </Link>
                            <Link
                                href="/register/user"
                                class="px-6 py-3 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-xl border border-white/10 transition-all cursor-pointer"
                            >
                                Daftar Penyewa
                            </Link>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

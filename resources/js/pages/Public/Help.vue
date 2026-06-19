<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface FAQItem {
    id: number
    category: string
    question: string
    answer: string
}

const searchQuery = ref('')
const activeCategory = ref('all')
const openFaqId = ref<number | null>(null)

const categories = [
    { value: 'all', label: 'Semua Kategori' },
    { value: 'booking', label: 'Sewa & Booking' },
    { value: 'payment', label: 'Pembayaran' },
    { value: 'account', label: 'Akun & Profil' },
]

const faqs: FAQItem[] = [
    {
        id: 1,
        category: 'booking',
        question: 'Bagaimana cara melakukan booking kamar kost?',
        answer: 'Cari kost yang Anda inginkan di halaman "Cari Kost", pilih kamar yang tersedia, lalu klik tombol "Booking Kamar Ini". Masukkan tanggal mulai sewa, pilih periode pembayaran, isi catatan opsional, dan kirim booking. Pemilik kost akan meninjau pengajuan sewa Anda.',
    },
    {
        id: 2,
        category: 'booking',
        question: 'Apakah saya bisa membatalkan pengajuan booking?',
        answer: 'Ya, Anda dapat membatalkan pengajuan booking selama statusnya masih "menunggu persetujuan" atau "disetujui" (belum aktif). Buka "Booking Saya" di dashboard, pilih booking terkait, dan klik "Batalkan Booking".',
    },
    {
        id: 3,
        category: 'payment',
        question: 'Bagaimana cara membayar sewa kamar?',
        answer: 'Setelah pengajuan booking disetujui oleh pemilik kost, tagihan pembayaran akan muncul di menu "Pembayaran" di dashboard. Transfer pembayaran sesuai nominal ke rekening bank pemilik kost yang tertera, lalu upload bukti transfer pada detail pembayaran tersebut.',
    },
    {
        id: 4,
        category: 'payment',
        question: 'Berapa lama proses verifikasi bukti pembayaran?',
        answer: 'Proses verifikasi dilakukan langsung secara manual oleh pemilik kost. Biasanya memakan waktu kurang dari 24 jam. Jika status belum berubah setelah 24 jam, Anda dapat menghubungi pemilik kost via pesan chat di dashboard.',
    },
    {
        id: 5,
        category: 'account',
        question: 'Bagaimana cara mengubah profil dan foto profil?',
        answer: 'Masuk ke dashboard Anda, lalu klik menu "Profil" di sidebar (atau klik kartu profil Anda di bawah kiri lalu pilih "Edit Profil"). Anda dapat memperbarui nama lengkap, jenis kelamin, tanggal lahir, foto profil (avatar), serta mengunggah foto KTP sebagai syarat verifikasi diri.',
    },
    {
        id: 6,
        category: 'booking',
        question: 'Kapan sewa kost saya dianggap aktif?',
        answer: 'Sewa kost Anda akan otomatis aktif jika bukti pembayaran untuk tagihan pertama telah disetujui/diverifikasi oleh pemilik kost dan tanggal mulai sewa telah tercapai.',
    },
]

const filteredFaqs = computed(() => {
    return faqs.filter((faq) => {
        const matchesCategory = activeCategory.value === 'all' || faq.category === activeCategory.value
        const matchesSearch = faq.question.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                              faq.answer.toLowerCase().includes(searchQuery.value.toLowerCase())
        return matchesCategory && matchesSearch
    })
})

function toggleFaq(id: number) {
    if (openFaqId.value === id) {
        openFaqId.value = null
    } else {
        openFaqId.value = id
    }
}
</script>

<template>
    <Head title="Pusat Bantuan" />
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-3xl sm:text-4xl font-black text-(--color-text-primary) tracking-tight mb-4">
                    Pusat <span class="text-(--color-primary)">Bantuan</span>
                </h1>
                <p class="text-base text-(--color-text-secondary) max-w-xl mx-auto mb-8">
                    Temukan jawaban atas pertanyaan Anda seputar penggunaan aplikasi Simako.
                </p>

                <!-- Search Bar -->
                <div class="max-w-lg mx-auto relative">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari bantuan atau pertanyaan..."
                        class="w-full pl-10 pr-4 py-3 text-sm text-(--color-text-primary) bg-white border border-(--color-border) rounded-2xl focus:outline-none focus:border-(--color-primary) focus:ring-1 focus:ring-(--color-primary) transition-all duration-200"
                    />
                    <div class="absolute left-3.5 top-3.5 text-(--color-text-muted)">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Categories Tabs -->
            <div class="flex flex-wrap justify-center gap-2 mb-10">
                <button
                    v-for="cat in categories"
                    :key="cat.value"
                    @click="activeCategory = cat.value; openFaqId = null"
                    class="px-4 py-2 text-xs font-semibold rounded-xl transition-all duration-200"
                    :class="activeCategory === cat.value
                        ? 'bg-(--color-primary) text-white'
                        : 'bg-white border border-(--color-border) text-(--color-text-secondary) hover:bg-slate-50'"
                >
                    {{ cat.label }}
                </button>
            </div>

            <!-- FAQ List Accordion -->
            <div class="space-y-4">
                <div v-if="filteredFaqs.length > 0" class="space-y-3">
                    <div
                        v-for="faq in filteredFaqs"
                        :key="faq.id"
                        class="bg-white border border-(--color-border) rounded-2xl overflow-hidden shadow-sm transition-all duration-200"
                    >
                        <button
                            @click="toggleFaq(faq.id)"
                            class="w-full px-6 py-4 flex items-center justify-between text-left font-bold text-sm text-(--color-text-primary) hover:bg-slate-50/50 focus:outline-none"
                        >
                            <span>{{ faq.question }}</span>
                            <svg
                                class="w-4 h-4 text-(--color-text-muted) transition-transform duration-200"
                                :class="openFaqId === faq.id ? 'rotate-180' : ''"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <Transition
                            enter-active-class="transition-all duration-200 ease-out"
                            enter-from-class="max-h-0 opacity-0"
                            enter-to-class="max-h-[300px] opacity-100"
                            leave-active-class="transition-all duration-150 ease-in"
                            leave-from-class="max-h-[300px] opacity-100"
                            leave-to-class="max-h-0 opacity-0"
                        >
                            <div v-if="openFaqId === faq.id" class="px-6 pb-5 border-t border-(--color-border) pt-4">
                                <p class="text-sm text-(--color-text-secondary) leading-relaxed">
                                    {{ faq.answer }}
                                </p>
                            </div>
                        </Transition>
                    </div>
                </div>

                <div v-else class="text-center py-16 bg-white border border-(--color-border) rounded-2xl">
                    <svg class="w-12 h-12 text-(--color-text-muted) mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-semibold text-(--color-text-primary) mb-1">Pertanyaan tidak ditemukan</p>
                    <p class="text-xs text-(--color-text-secondary)">Coba masukkan kata kunci yang berbeda atau pilih kategori lain.</p>
                </div>
            </div>

            <!-- Footer Help CTA -->
            <div class="mt-16 bg-gradient-to-br from-slate-900 to-red-950 text-white rounded-3xl p-8 sm:p-12 text-center shadow-lg">
                <h3 class="text-xl sm:text-2xl font-bold mb-3">Tidak menemukan jawaban Anda?</h3>
                <p class="text-sm text-gray-300 max-w-lg mx-auto mb-6">
                    Hubungi tim customer service kami untuk membantu Anda secara langsung.
                </p>
                <a
                    href="/contact"
                    class="inline-flex items-center justify-center px-6 py-3 bg-(--color-primary) hover:bg-(--color-primary-hover) text-white text-xs font-bold rounded-xl shadow-md transition-all duration-200"
                >
                    Hubungi Kami Sekarang
                </a>
            </div>
        </div>
    </AppLayout>
</template>

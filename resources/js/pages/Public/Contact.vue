<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
})

const submitted = ref(false)

function submit() {
    form.processing = true
    setTimeout(() => {
        form.processing = false
        submitted.value = true
        form.reset()
    }, 1000)
}
</script>

<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <!-- Header -->
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h1 class="text-3xl sm:text-4xl font-black text-(--color-text-primary) tracking-tight mb-4">
                    Hubungi <span class="text-(--color-primary)">Kami</span>
                </h1>
                <p class="text-base text-(--color-text-secondary) leading-relaxed">
                    Punya pertanyaan tentang Simako? Tim kami siap membantu Anda kapan saja. Hubungi kami lewat form di bawah atau kontak resmi kami.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                <!-- Contact Info Cards -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white border border-(--color-border) p-6 rounded-2xl shadow-sm transition-smooth hover:shadow-md">
                        <div class="w-10 h-10 bg-(--color-primary-light) text-(--color-primary) rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-(--color-text-primary) mb-1">Telepon & WhatsApp</h3>
                        <p class="text-sm text-(--color-text-secondary) mb-2">Hubungi CS kami di jam kerja (08.00 - 17.00 WIB).</p>
                        <a href="https://wa.me/6281200000000" class="text-sm font-semibold text-(--color-primary) hover:underline">+62 812-0000-0000</a>
                    </div>

                    <div class="bg-white border border-(--color-border) p-6 rounded-2xl shadow-sm transition-smooth hover:shadow-md">
                        <div class="w-10 h-10 bg-(--color-primary-light) text-(--color-primary) rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-(--color-text-primary) mb-1">Email Dukungan</h3>
                        <p class="text-sm text-(--color-text-secondary) mb-2">Kirim pertanyaan umum maupun kemitraan via email.</p>
                        <a href="mailto:support@simako.com" class="text-sm font-semibold text-(--color-primary) hover:underline">support@simako.com</a>
                    </div>

                    <div class="bg-white border border-(--color-border) p-6 rounded-2xl shadow-sm transition-smooth hover:shadow-md">
                        <div class="w-10 h-10 bg-(--color-primary-light) text-(--color-primary) rounded-xl flex items-center justify-center mb-4">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-(--color-text-primary) mb-1">Kantor Utama</h3>
                        <p class="text-sm text-(--color-text-secondary) leading-relaxed">
                            Jl. Telekomunikasi No. 1, Terusan Buah Batu, Bandung, Jawa Barat 40257
                        </p>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="lg:col-span-2">
                    <div class="bg-white border border-(--color-border) p-8 rounded-2xl shadow-sm">
                        <h2 class="text-lg font-bold text-(--color-text-primary) mb-6">Kirim Pesan Langsung</h2>

                        <div v-if="submitted" class="p-6 bg-green-50 border border-green-200 rounded-2xl text-center">
                            <svg class="w-12 h-12 text-(--color-success) mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-base font-bold text-(--color-text-primary) mb-1">Pesan Terkirim!</h3>
                            <p class="text-sm text-(--color-text-secondary) mb-4">Terima kasih telah menghubungi kami. Kami akan merespons dalam waktu 24 jam.</p>
                            <Button size="sm" @click="submitted = false">Kirim Pesan Baru</Button>
                        </div>

                        <form v-else @submit.prevent="submit" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <Input v-model="form.name" label="Nama Lengkap" placeholder="Masukkan nama Anda" required />
                                <Input v-model="form.email" type="email" label="Email" placeholder="email@contoh.com" required />
                            </div>
                            <Input v-model="form.subject" label="Subjek / Topik" placeholder="Contoh: Masalah Pembayaran, Tanya Kemitraan" required />
                            <div>
                                <label class="block text-sm font-semibold text-(--color-text-primary) mb-1.5">Pesan Anda</label>
                                <textarea
                                    v-model="form.message"
                                    rows="5"
                                    required
                                    placeholder="Tuliskan pesan Anda di sini secara detail..."
                                    class="w-full px-3.5 py-2.5 text-sm bg-white border border-(--color-border) rounded-xl text-(--color-text-primary) focus:outline-none focus:border-(--color-primary) focus:ring-1 focus:ring-(--color-primary) transition-all duration-200"
                                />
                            </div>
                            <div class="pt-2">
                                <Button type="submit" :loading="form.processing" class="w-full sm:w-auto">Kirim Pesan</Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

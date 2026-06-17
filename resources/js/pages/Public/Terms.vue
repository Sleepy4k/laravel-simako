<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <h1 class="text-3xl font-black text-(--color-text-primary) tracking-tight mb-8">
                Syarat & <span class="text-(--color-primary)">Ketentuan</span>
            </h1>

            <div class="bg-white border border-(--color-border) rounded-2xl p-6 sm:p-8 shadow-sm space-y-6 text-sm text-(--color-text-secondary) leading-relaxed">
                <p>Selamat datang di Simako! Halaman ini menjelaskan syarat dan ketentuan yang mengatur penggunaan Anda atas platform pencarian dan pengelolaan kost kami.</p>

                <section class="space-y-2">
                    <h2 class="text-base font-bold text-(--color-text-primary)">1. Ketentuan Umum</h2>
                    <p>Dengan mengakses atau menggunakan layanan Simako, Anda menyatakan setuju untuk terikat oleh Syarat & Ketentuan ini. Jika Anda tidak menyetujui bagian mana pun dari ketentuan ini, Anda tidak diperkenankan menggunakan platform kami.</p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-bold text-(--color-text-primary)">2. Akun Pengguna</h2>
                    <p>Untuk menggunakan fitur tertentu seperti melakukan sewa kamar atau mengelola kost, Anda diharuskan mendaftar dan memverifikasi identitas Anda. Anda wajib menjaga kerahasiaan kata sandi Anda dan bertanggung jawab penuh atas segala aktivitas yang terjadi di bawah akun Anda.</p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-bold text-(--color-text-primary)">3. Ketentuan bagi Penyewa</h2>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Penyewa wajib memberikan informasi diri yang benar saat mendaftar dan mengajukan sewa kost.</li>
                        <li>Booking kamar kost yang diajukan bersifat permohonan sewa dan menunggu persetujuan dari pemilik kost.</li>
                        <li>Penyewa wajib membayar biaya sewa sesuai nominal yang tertera pada invoice sebelum batas jatuh tempo pembayaran.</li>
                    </ul>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-bold text-(--color-text-primary)">4. Ketentuan bagi Pemilik Kost</h2>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Pemilik kost bertanggung jawab penuh atas keakuratan deskripsi, fasilitas, harga, dan ketersediaan kamar kost yang mereka publikasikan.</li>
                        <li>Pemilik kost wajib meninjau pembayaran sewa yang dikirimkan oleh penyewa secara jujur dan tepat waktu.</li>
                    </ul>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-bold text-(--color-text-primary)">5. Kebijakan Pembatalan</h2>
                    <p>Pembatalan booking oleh penyewa dapat dilakukan sebelum pembayaran diverifikasi. Refund biaya sewa mengikuti kebijakan pemilik kost masing-masing, dan Simako tidak bertanggung jawab atas sengketa pengembalian dana di luar sistem pembayaran platform.</p>
                </section>

                <p class="text-xs text-(--color-text-muted) pt-4 border-t border-(--color-border)">Terakhir diperbarui: 18 Juni 2026</p>
            </div>
        </div>
    </AppLayout>
</template>

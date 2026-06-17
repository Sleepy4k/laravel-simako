<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
</script>

<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <h1 class="text-3xl font-black text-(--color-text-primary) tracking-tight mb-8">
                Kebijakan <span class="text-(--color-primary)">Privasi</span>
            </h1>

            <div class="bg-white border border-(--color-border) rounded-2xl p-6 sm:p-8 shadow-sm space-y-6 text-sm text-(--color-text-secondary) leading-relaxed">
                <p>Simako berkomitmen melindungi data pribadi Anda. Kebijakan Privasi ini menjelaskan bagaimana kami mengumpulkan, menggunakan, menyimpan, dan membagikan informasi pribadi Anda saat menggunakan platform kami.</p>

                <section class="space-y-2">
                    <h2 class="text-base font-bold text-(--color-text-primary)">1. Informasi yang Kami Kumpulkan</h2>
                    <p>Kami mengumpulkan data Anda untuk menunjang transaksi dan keaktifan layanan sewa:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li><strong>Data Pendaftaran Akun</strong>: Alamat email, nomor HP, jenis kelamin, dan kata sandi Anda.</li>
                        <li><strong>Data Verifikasi Diri</strong>: Nama lengkap, nomor KTP, foto profil, dan dokumen identitas (KTP) untuk pemilik kost dan penyewa guna mencegah penipuan.</li>
                        <li><strong>Data Transaksi</strong>: Rekening bank pemilik kost dan bukti pembayaran sewa dari penyewa.</li>
                    </ul>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-bold text-(--color-text-primary)">2. Cara Kami Menggunakan Informasi Anda</h2>
                    <p>Informasi Anda digunakan untuk:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Memproses booking dan administrasi sewa kost antara pemilik dan penyewa.</li>
                        <li>Memverifikasi identitas pengguna untuk menjaga keamanan transaksi.</li>
                        <li>Menghubungi Anda terkait transaksi, pemberitahuan tagihan, dan pembaruan sistem.</li>
                    </ul>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-bold text-(--color-text-primary)">3. Pembagian Informasi</h2>
                    <p>Kami tidak menjual informasi pribadi Anda kepada pihak ketiga. Kami hanya membagikan detail kontak dasar (nama, nomor HP) antara penyewa dan pemilik kost yang memiliki booking aktif untuk mempermudah komunikasi.</p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-base font-bold text-(--color-text-primary)">4. Penyimpanan dan Keamanan Data</h2>
                    <p>Kami menerapkan prosedur keamanan fisik, elektronik, dan manajerial yang wajar untuk mengamankan data Anda dari akses tanpa izin, kehilangan, atau penyalahgunaan. File bukti bayar dan dokumen identitas dienkripsi dan disimpan di server penyimpanan aman.</p>
                </section>

                <p class="text-xs text-(--color-text-muted) pt-4 border-t border-(--color-border)">Terakhir diperbarui: 18 Juni 2026</p>
            </div>
        </div>
    </AppLayout>
</template>

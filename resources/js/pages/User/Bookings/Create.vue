<script setup lang="ts">
import { useForm, router, Head } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import type { Room, Kost } from '@/types/models'
import * as UserBookingController from '@/actions/App/Http/Controllers/User/BookingController'

const props = defineProps<{
    room: Room
    kost: Kost
}>()

const form = useForm({
    room_id: props.room.id,
    room_price_id: props.room.prices?.[0]?.id ?? null as number | null,
    start_date: '',
    notes: '',
})

function formatCurrency(amount: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount)
}

const periodLabel: Record<string, string> = {
    monthly: 'Bulanan',
    quarterly: '3 Bulan',
    semi_annual: '6 Bulan',
    annual: 'Tahunan',
}

function goBack() { router.visit('/dashboard/bookings') }

function submit() {
    form.post(UserBookingController.store.url())
}
</script>

<template>
    <Head title="Ajukan Booking" />
    <DashboardLayout>
        <div class="max-w-xl">
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-6">Booking Kamar</h1>
 
            <!-- Room info -->
            <div class="bg-(--color-surface) p-5 border border-(--color-border) rounded-2xl mb-6 shadow-sm">
                <p class="text-[10px] font-bold text-(--color-text-muted) uppercase tracking-wider mb-1.5">Kost</p>
                <p class="text-base font-bold text-(--color-text-primary)">{{ props.kost?.name ?? props.room.kost?.name ?? '-' }}</p>
                <p class="text-sm text-(--color-text-secondary) mt-1.5 font-medium">Kamar: {{ props.room.name }}</p>
                <p v-if="props.room.size_sqm" class="text-sm text-(--color-text-secondary) mt-0.5">Ukuran: {{ props.room.size_sqm }} m²</p>
            </div>
 
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Price selection -->
                <div v-if="props.room.prices?.length">
                    <label class="block text-sm font-semibold text-(--color-text-primary) mb-3">Pilih Periode Sewa</label>
                    <div class="space-y-2.5">
                        <label
                            v-for="price in props.room.prices"
                            :key="price.id"
                            class="flex items-center gap-3 p-3.5 border rounded-2xl cursor-pointer transition-all duration-200"
                            :class="form.room_price_id === price.id
                                ? 'border-(--color-primary) bg-red-50/50 shadow-sm'
                                : 'border-(--color-border) hover:border-(--color-primary) hover:bg-slate-50'"
                        >
                            <input
                                type="radio"
                                :value="price.id"
                                v-model="form.room_price_id"
                                class="accent-(--color-primary) w-4 h-4"
                            />
                            <span class="flex-1 text-sm font-medium text-(--color-text-secondary)">{{ periodLabel[price.period] }}</span>
                            <span class="text-sm font-bold text-(--color-primary)">{{ formatCurrency(price.price) }}</span>
                        </label>
                    </div>
                    <p v-if="form.errors.room_price_id" class="mt-1.5 text-xs text-(--color-primary)">{{ form.errors.room_price_id }}</p>
                </div>
 
                <Input
                    v-model="form.start_date"
                    type="date"
                    label="Tanggal Mulai"
                    :error="form.errors.start_date"
                    required
                />
 
                <div>
                    <label class="block text-sm font-medium text-(--color-text-primary) mb-1.5">Catatan (opsional)</label>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        placeholder="Catatan untuk pemilik kost..."
                        class="w-full px-3.5 py-2.5 text-sm bg-white border border-(--color-border) rounded-xl text-(--color-text-primary) focus:outline-none focus:border-(--color-primary) focus:ring-1 focus:ring-(--color-primary) transition-all duration-200"
                    />
                </div>
 
                <div class="flex gap-3 pt-2">
                    <Button type="submit" :loading="form.processing">Kirim Booking</Button>
                    <Button variant="ghost" type="button" @click="goBack">Batal</Button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

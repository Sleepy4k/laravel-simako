<script setup lang="ts">
import { computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import type { Facility } from '@/types/models'
import * as TenantRoomController from '@/actions/App/Http/Controllers/Tenant/RoomController'

interface KostInfo {
    id: number
    name: string
    slug: string
}

const props = defineProps<{
    kost: KostInfo
    facilities: Facility[]
}>()

const periodOptions = [
    { value: 'monthly', label: 'Bulanan' },
    { value: 'quarterly', label: '3 Bulan' },
    { value: 'semi_annual', label: '6 Bulan' },
    { value: 'annual', label: 'Tahunan' },
]

const form = useForm({
    name: '',
    floor: '' as string | number,
    size_sqm: '' as string | number,
    is_available: true,
    facility_ids: [] as number[],
    prices: periodOptions.map((p) => ({
        period: p.value,
        price: '' as string | number,
        deposit: '' as string | number,
        enabled: false,
    })),
})

const groupedFacilities = computed(() => {
    return props.facilities.reduce<Record<string, Facility[]>>((acc, f) => {
        const cat = f.category?.name ?? 'Lainnya'
        if (!acc[cat]) acc[cat] = []
        acc[cat].push(f)
        return acc
    }, {})
})

function toggleFacility(id: number) {
    const idx = form.facility_ids.indexOf(id)
    if (idx === -1) {
        form.facility_ids.push(id)
    } else {
        form.facility_ids.splice(idx, 1)
    }
}

function goBack() { router.visit('/dashboard/kosts/' + props.kost.slug + '/rooms') }

function submit() {
    form.post(TenantRoomController.store.url(props.kost.id))
}
</script>

<template>
    <DashboardLayout>
        <div class="max-w-2xl">
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-1">Tambah Kamar</h1>
            <p class="text-sm text-(--color-text-secondary) mb-6">Kost: {{ props.kost.name }}</p>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Room info -->
                <div class="bg-white p-5 space-y-4">
                    <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide">Informasi Kamar</p>
                    <Input v-model="form.name" label="Nama Kamar" placeholder="Contoh: Kamar A1" :error="form.errors.name" required />
                    <div class="grid grid-cols-2 gap-4">
                        <Input v-model="form.floor" type="number" label="Lantai" placeholder="1" :error="form.errors.floor" />
                        <Input v-model="form.size_sqm" type="number" label="Ukuran (m²)" placeholder="12" :error="form.errors.size_sqm" />
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="form.is_available" class="accent-(--color-primary)" />
                        <span class="text-sm text-(--color-text-primary)">Kamar tersedia</span>
                    </label>
                </div>

                <!-- Prices -->
                <div class="bg-white p-5">
                    <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-4">Harga Sewa</p>
                    <div class="space-y-3">
                        <div
                            v-for="(price, idx) in form.prices"
                            :key="price.period"
                            class="border border-(--color-border) p-3"
                        >
                            <label class="flex items-center gap-2 cursor-pointer mb-3">
                                <input type="checkbox" v-model="form.prices[idx].enabled" class="accent-(--color-primary)" />
                                <span class="text-sm font-medium text-(--color-text-primary)">{{ periodOptions[idx].label }}</span>
                            </label>
                            <div v-if="form.prices[idx].enabled" class="grid grid-cols-2 gap-3">
                                <Input v-model="form.prices[idx].price" type="number" label="Harga (Rp)" placeholder="500000" />
                                <Input v-model="form.prices[idx].deposit" type="number" label="Deposit (Rp)" placeholder="0" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Facilities -->
                <div v-if="props.facilities.length > 0" class="bg-white p-5">
                    <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-4">Fasilitas Kamar</p>
                    <div v-for="(items, category) in groupedFacilities" :key="category" class="mb-4">
                        <p class="text-xs font-semibold text-(--color-text-secondary) mb-2">{{ category }}</p>
                        <div class="flex flex-wrap gap-2">
                            <label
                                v-for="f in items"
                                :key="f.id"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs cursor-pointer border transition-colors"
                                :class="form.facility_ids.includes(f.id)
                                    ? 'border-(--color-primary) bg-red-50 text-(--color-primary)'
                                    : 'border-(--color-border) text-(--color-text-secondary)'"
                            >
                                <input type="checkbox" :checked="form.facility_ids.includes(f.id)" class="sr-only" @change="toggleFacility(f.id)" />
                                {{ f.name }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <Button type="submit" :loading="form.processing">Simpan Kamar</Button>
                    <Button variant="ghost" type="button" @click="goBack">Batal</Button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

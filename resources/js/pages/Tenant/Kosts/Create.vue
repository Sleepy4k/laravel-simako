<script setup lang="ts">
import { computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import type { Facility } from '@/types/models'
import * as TenantKostController from '@/actions/App/Http/Controllers/Tenant/KostController'

const props = defineProps<{
    facilities: Facility[]
}>()

const form = useForm({
    name: '',
    description: '',
    type: 'putra' as 'putra' | 'putri' | 'campur',
    street: '',
    district: '',
    city: '',
    province: '',
    postal_code: '',
    facility_ids: [] as number[],
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

function goBack() { router.visit('/dashboard/kosts') }

function submit() {
    form.post(TenantKostController.store.url())
}
</script>

<template>
    <DashboardLayout>
        <div class="max-w-2xl">
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-6">Tambah Kost Baru</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic info -->
                <div class="bg-white p-5 space-y-4">
                    <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide">Informasi Kost</p>

                    <Input v-model="form.name" label="Nama Kost" placeholder="Contoh: Kost Putra Bahagia" :error="form.errors.name" required />

                    <div>
                        <label class="block text-sm font-medium text-(--color-text-primary) mb-1">Deskripsi</label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            placeholder="Deskripsi kost..."
                            class="w-full px-3 py-2 text-sm bg-white border border-(--color-border) focus:outline-none focus:border-(--color-primary)"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-(--color-text-primary) mb-2">Tipe Kost</label>
                        <div class="flex gap-4">
                            <label v-for="opt in [{ value: 'putra', label: 'Putra' }, { value: 'putri', label: 'Putri' }, { value: 'campur', label: 'Campur' }]" :key="opt.value" class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" :value="opt.value" v-model="form.type" class="accent-(--color-primary)" />
                                <span class="text-sm text-(--color-text-primary)">{{ opt.label }}</span>
                            </label>
                        </div>
                        <p v-if="form.errors.type" class="mt-1 text-xs text-(--color-primary)">{{ form.errors.type }}</p>
                    </div>
                </div>

                <!-- Address -->
                <div class="bg-white p-5 space-y-4">
                    <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide">Alamat</p>
                    <Input v-model="form.street" label="Jalan" placeholder="Nama jalan dan nomor" :error="form.errors.street" />
                    <div class="grid grid-cols-2 gap-4">
                        <Input v-model="form.district" label="Kecamatan" :error="form.errors.district" />
                        <Input v-model="form.city" label="Kota" :error="form.errors.city" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <Input v-model="form.province" label="Provinsi" :error="form.errors.province" required />
                        <Input v-model="form.postal_code" label="Kode Pos" :error="form.errors.postal_code" />
                    </div>
                </div>

                <!-- Facilities -->
                <div v-if="props.facilities.length > 0" class="bg-white p-5">
                    <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-4">Fasilitas</p>
                    <div v-for="(items, category) in groupedFacilities" :key="category" class="mb-4">
                        <p class="text-xs font-semibold text-(--color-text-secondary) mb-2">{{ category }}</p>
                        <div class="flex flex-wrap gap-2">
                            <label
                                v-for="f in items"
                                :key="f.id"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs cursor-pointer border transition-colors"
                                :class="form.facility_ids.includes(f.id)
                                    ? 'border-(--color-primary) bg-red-50 text-(--color-primary)'
                                    : 'border-(--color-border) text-(--color-text-secondary) hover:border-(--color-primary)'"
                            >
                                <input type="checkbox" :checked="form.facility_ids.includes(f.id)" class="sr-only" @change="toggleFacility(f.id)" />
                                {{ f.name }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <Button type="submit" :loading="form.processing">Simpan Kost</Button>
                    <Button variant="ghost" type="button" @click="goBack">Batal</Button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

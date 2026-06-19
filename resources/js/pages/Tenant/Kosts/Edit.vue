<script setup lang="ts">
import { computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import type { Kost, Facility } from '@/types/models'
import * as TenantKostController from '@/actions/App/Http/Controllers/Tenant/KostController'

const props = defineProps<{
    kost: Kost
    facilities: Facility[]
}>()

const form = useForm({
    name: props.kost.name,
    description: props.kost.description ?? '',
    type: props.kost.type,
    status: props.kost.status,
    street: props.kost.address?.street ?? '',
    district: props.kost.address?.district ?? '',
    city: props.kost.address?.city ?? '',
    province: props.kost.address?.province ?? '',
    postal_code: props.kost.address?.postal_code ?? '',
    facility_ids: props.kost.facilities?.map((f) => f.id) ?? [],
    thumbnail: null as File | null,
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

function handleThumbnailChange(event: Event) {
    form.thumbnail = (event.target as HTMLInputElement).files?.[0] ?? null
}

function goBack() { router.visit('/dashboard/kosts') }

function submit() {
    form.patch(TenantKostController.update.url(props.kost.id))
}
</script>

<template>
    <DashboardLayout>
        <div class="max-w-2xl">
            <h1 class="text-xl font-bold text-(--color-text-primary) mb-6">Edit Kost</h1>

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic info -->
                <div class="bg-white p-5 space-y-4">
                    <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide">Informasi Kost</p>

                    <Input v-model="form.name" label="Nama Kost" :error="form.errors.name" required />

                    <div>
                        <label class="block text-sm font-medium text-(--color-text-primary) mb-1">Deskripsi</label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            class="w-full px-3 py-2 text-sm bg-white border border-(--color-border) focus:outline-none focus:border-(--color-primary)"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-(--color-text-primary) mb-2">Tipe Kost</label>
                        <div class="flex gap-4">
                            <label v-for="opt in [{ value: 'putra', label: 'Putra' }, { value: 'putri', label: 'Putri' }, { value: 'campur', label: 'Campur' }]" :key="opt.value" class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" :value="opt.value" v-model="form.type" class="accent-(--color-primary)" />
                                <span class="text-sm">{{ opt.label }}</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-(--color-text-primary) mb-1">Status</label>
                        <select
                            v-model="form.status"
                            class="w-full px-3 py-2 text-sm bg-white border border-(--color-border) focus:outline-none focus:border-(--color-primary)"
                        >
                            <option value="draft">Draft</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                        <p v-if="form.errors.status" class="mt-1 text-xs text-(--color-primary)">{{ form.errors.status }}</p>
                    </div>
                </div>

                <!-- Address -->
                <div class="bg-white p-5 space-y-4">
                    <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide">Alamat</p>
                    <Input v-model="form.street" label="Jalan" :error="form.errors.street" />
                    <div class="grid grid-cols-2 gap-4">
                        <Input v-model="form.district" label="Kecamatan" />
                        <Input v-model="form.city" label="Kota" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <Input v-model="form.province" label="Provinsi" required />
                        <Input v-model="form.postal_code" label="Kode Pos" />
                    </div>
                </div>

                <!-- Thumbnail -->
                <div class="bg-white p-5">
                    <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-3">Foto Utama (Thumbnail)</p>
                    <div v-if="props.kost.thumbnail" class="mb-3">
                        <img :src="props.kost.thumbnail" alt="Thumbnail" class="h-32 object-cover" />
                    </div>
                    <input
                        type="file"
                        accept="image/*"
                        class="block text-sm text-(--color-text-secondary) file:mr-3 file:py-1.5 file:px-3 file:bg-(--color-surface) file:text-sm file:border-0 file:cursor-pointer"
                        @change="handleThumbnailChange"
                    />
                    <p class="mt-1 text-xs text-(--color-text-secondary)">Upload foto baru untuk mengganti yang lama.</p>
                    <p v-if="form.errors.thumbnail" class="mt-1 text-xs text-(--color-primary)">{{ form.errors.thumbnail }}</p>
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
                    <Button type="submit" :loading="form.processing">Simpan Perubahan</Button>
                    <Button variant="ghost" type="button" @click="goBack">Batal</Button>
                </div>
            </form>
        </div>
    </DashboardLayout>
</template>

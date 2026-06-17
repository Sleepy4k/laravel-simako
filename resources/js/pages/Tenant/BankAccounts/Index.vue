<script setup lang="ts">
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Modal from '@/components/ui/Modal.vue'
import type { BankAccount } from '@/types/models'
import * as BankAccountController from '@/actions/App/Http/Controllers/Tenant/BankAccountController'

const props = defineProps<{
    bankAccounts: BankAccount[]
}>()

const showAddForm = ref(false)
const editingId = ref<number | null>(null)

const addForm = useForm({
    bank_name: '',
    account_number: '',
    account_holder: '',
    is_primary: false,
})

function createEditForm(account: BankAccount) {
    return useForm({
        bank_name: account.bank_name,
        account_number: account.account_number,
        account_holder: account.account_holder,
        is_primary: account.is_primary,
    })
}

const editForm = ref(useForm({ bank_name: '', account_number: '', account_holder: '', is_primary: false }))

function startEdit(account: BankAccount) {
    editingId.value = account.id
    editForm.value = createEditForm(account)
}

function submitAdd() {
    addForm.post(BankAccountController.store.url(), {
        onSuccess: () => {
            addForm.reset()
            showAddForm.value = false
        },
    })
}

function submitEdit(id: number) {
    editForm.value.patch(BankAccountController.update.url(id), {
        onSuccess: () => {
            editingId.value = null
        },
    })
}

const showDeleteModal = ref(false)
const accountToDelete = ref<number | null>(null)
const deleting = ref(false)

function confirmDelete(id: number) {
    accountToDelete.value = id
    showDeleteModal.value = true
}

function doDelete() {
    if (accountToDelete.value !== null) {
        deleting.value = true
        router.delete(BankAccountController.destroy.url(accountToDelete.value), {
            onSuccess: () => {
                showDeleteModal.value = false
                accountToDelete.value = null
                deleting.value = false
            },
            onError: () => {
                deleting.value = false
            }
        })
    }
}
</script>

<template>
    <DashboardLayout>
        <div class="max-w-2xl">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-xl font-bold text-(--color-text-primary)">Rekening Bank</h1>
                <Button size="sm" @click="showAddForm = !showAddForm">+ Tambah Rekening</Button>
            </div>

            <!-- Add form -->
            <div v-if="showAddForm" class="bg-white p-5 mb-4">
                <p class="text-xs text-(--color-text-secondary) uppercase tracking-wide mb-4">Rekening Baru</p>
                <form @submit.prevent="submitAdd" class="space-y-4">
                    <Input v-model="addForm.bank_name" label="Nama Bank" placeholder="BCA, BRI, Mandiri..." :error="addForm.errors.bank_name" required />
                    <Input v-model="addForm.account_number" label="Nomor Rekening" :error="addForm.errors.account_number" required />
                    <Input v-model="addForm.account_holder" label="Atas Nama" :error="addForm.errors.account_holder" required />
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="addForm.is_primary" class="accent-(--color-primary)" />
                        <span class="text-sm">Jadikan rekening utama</span>
                    </label>
                    <div class="flex gap-2">
                        <Button type="submit" size="sm" :loading="addForm.processing">Simpan</Button>
                        <Button type="button" variant="ghost" size="sm" @click="showAddForm = false">Batal</Button>
                    </div>
                </form>
            </div>

            <!-- Accounts list -->
            <div v-if="props.bankAccounts.length > 0" class="space-y-3">
                <div v-for="account in props.bankAccounts" :key="account.id" class="bg-white p-5">
                    <template v-if="editingId === account.id">
                        <form @submit.prevent="submitEdit(account.id)" class="space-y-3">
                            <Input v-model="editForm.bank_name" label="Nama Bank" required />
                            <Input v-model="editForm.account_number" label="Nomor Rekening" required />
                            <Input v-model="editForm.account_holder" label="Atas Nama" required />
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="editForm.is_primary" class="accent-(--color-primary)" />
                                <span class="text-sm">Rekening utama</span>
                            </label>
                            <div class="flex gap-2">
                                <Button type="submit" size="sm" :loading="editForm.processing">Simpan</Button>
                                <Button type="button" variant="ghost" size="sm" @click="editingId = null">Batal</Button>
                            </div>
                        </form>
                    </template>

                    <template v-else>
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="text-base font-semibold text-(--color-text-primary)">{{ account.bank_name }}</p>
                                    <Badge v-if="account.is_primary" variant="primary" label="Utama" />
                                </div>
                                <p class="text-sm text-(--color-text-secondary)">{{ account.account_number }}</p>
                                <p class="text-sm text-(--color-text-secondary)">a/n {{ account.account_holder }}</p>
                            </div>
                            <div class="flex gap-3">
                                <button class="text-sm text-(--color-secondary) hover:underline cursor-pointer" @click="startEdit(account)">Edit</button>
                                <button class="text-sm text-(--color-primary) hover:underline cursor-pointer" @click="confirmDelete(account.id)">Hapus</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div v-else-if="!showAddForm" class="bg-white p-12 border border-(--color-border) rounded-2xl text-center text-(--color-text-secondary)">
                <p>Belum ada rekening bank.</p>
            </div>

            <!-- Delete Confirmation Modal -->
            <Modal
                :open="showDeleteModal"
                title="Hapus Rekening Bank?"
                message="Tindakan ini tidak dapat dibatalkan. Rekening ini tidak akan digunakan lagi sebagai metode pembayaran sewa."
                confirm-label="Ya, Hapus"
                confirm-variant="danger"
                :loading="deleting"
                @confirm="doDelete"
                @cancel="showDeleteModal = false; accountToDelete = null"
            />
        </div>
    </DashboardLayout>
</template>

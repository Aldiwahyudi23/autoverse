<template>
    <AppLayout title="Detail Penarikan">
        <Head title="Detail Penarikan" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Detail Pengajuan Penarikan</h3>
                    </div>
                    <div :class="statusBadgeClass" class="px-3 py-1 rounded-full text-sm font-medium">
                        {{ statusOptions[withdrawal.status] || withdrawal.status }}
                    </div>
                </div>
            </div>

            <!-- Alert -->
            <div v-if="$page.props.flash.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ $page.props.flash.error }}
            </div>

            <!-- Informasi Pengaju -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Pengaju</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nama</label>
                            <p class="mt-1 text-sm text-gray-900">{{ withdrawal.user?.name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Email</label>
                            <p class="mt-1 text-sm text-gray-900">{{ withdrawal.user?.email }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal Pengajuan</label>
                            <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(withdrawal.requested_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Informasi Pembayaran -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Pembayaran</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Metode Pembayaran</label>
                            <p class="mt-1 text-sm text-gray-900">{{ paymentMethods[withdrawal.payment_method] }}</p>
                        </div>
                        <div v-if="withdrawal.account_number">
                            <label class="text-sm font-medium text-gray-500">Nomor Rekening</label>
                            <p class="mt-1 text-sm text-gray-900">{{ withdrawal.account_number }}</p>
                        </div>
                        <div v-if="withdrawal.account_name">
                            <label class="text-sm font-medium text-gray-500">Nama Pemilik</label>
                            <p class="mt-1 text-sm text-gray-900">{{ withdrawal.account_name }}</p>
                        </div>
                        <div v-if="withdrawal.bank_name">
                            <label class="text-sm font-medium text-gray-500">Nama Bank</label>
                            <p class="mt-1 text-sm text-gray-900">{{ withdrawal.bank_name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribusi yang Dipilih -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Distribusi yang Dipilih</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kode Transaksi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Deskripsi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jumlah
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="distribution in withdrawal.transaction_distributions" :key="distribution.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <!-- Invoice -->
                                        <div>{{ distribution.transaction.invoice_number }}</div>
                                        
                                        <!-- Plate number (lebih kecil & redup) -->
                                        <div class="text-xs text-gray-500">
                                            {{ distribution.transaction.inspection.plate_number }} 
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ distribution.transaction.inspection.car_name }}
                                        </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ distribution.role_type || 'Distribusi Pendapatan' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rp {{ formatNumber(distribution.amount) }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                    Total:
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    Rp {{ formatNumber(withdrawal.total_amount) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Form Konfirmasi -->
            <div v-if="withdrawal.status === 'pending' || withdrawal.status === 'processing'" class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi Penarikan</h3>
                
                <!-- Tab untuk Approve/Reject -->
                <div class="mb-6">
                    <nav class="flex space-x-4">
                        <button @click="activeTab = 'approve'" 
                                :class="activeTab === 'approve' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="border-b-2 px-1 pb-4 text-sm font-medium">
                            Setujui
                        </button>
                        <button @click="activeTab = 'reject'" 
                                :class="activeTab === 'reject' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="border-b-2 px-1 pb-4 text-sm font-medium">
                            Tolak
                        </button>
                    </nav>
                </div>

                <!-- Form Approve -->
                <form v-if="activeTab === 'approve'" @submit.prevent="submitApprove" class="space-y-6">
                    <!-- Bukti Transfer (jika metode transfer) -->
                    <div v-if="withdrawal.payment_method === 'transfer'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Upload Bukti Transfer *
                        </label>
                        <input type="file" 
                               @change="handleFileUpload"
                               accept=".jpg,.jpeg,.png,.pdf"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-sm text-gray-500">
                            Upload bukti transfer dalam format JPG, PNG, atau PDF (maks. 2MB)
                        </p>
                        <p v-if="errors.proof_file" class="mt-1 text-sm text-red-600">
                            {{ errors.proof_file }}
                        </p>
                    </div>

                    <!-- Biaya Admin -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Biaya Admin
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="number" 
                                   v-model="approveForm.admin_fee"
                                   @input="calculateFinalAmount"
                                   min="0"
                                   step="1000"
                                   class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            Kosongkan jika tidak ada biaya admin
                        </p>
                    </div>

                    <!-- Perhitungan -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-gray-600">Total Pengajuan:</span>
                            <span class="text-sm font-medium">Rp {{ formatNumber(withdrawal.total_amount) }}</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-gray-600">Biaya Admin:</span>
                            <span class="text-sm font-medium text-red-600">- Rp {{ formatNumber(approveForm.admin_fee || 0) }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-200">
                            <span class="text-sm font-medium text-gray-900">Dana yang Diterima:</span>
                            <span class="text-sm font-bold text-green-600">Rp {{ formatNumber(finalAmount) }}</span>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Catatan (Opsional)
                        </label>
                        <textarea v-model="approveForm.notes" 
                                  rows="3"
                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        <p class="mt-1 text-sm text-gray-500">
                            Berikan catatan tambahan jika diperlukan
                        </p>
                    </div>

                    <!-- Button Approve -->
                    <div class="flex justify-end">
                        <button type="submit" 
                                :disabled="isSubmitting || (withdrawal.payment_method === 'transfer' && !approveForm.proof_file)"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="isSubmitting">Menyetujui...</span>
                            <span v-else>Setujui Penarikan</span>
                        </button>
                    </div>
                </form>

                <!-- Form Reject -->
                <form v-if="activeTab === 'reject'" @submit.prevent="submitReject" class="space-y-6">
                    <!-- Alasan Penolakan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Alasan Penolakan *
                        </label>
                        <textarea v-model="rejectForm.rejection_reason" 
                                  rows="4"
                                  required
                                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
                        <p class="mt-1 text-sm text-gray-500">
                            Jelaskan alasan penolakan pengajuan ini
                        </p>
                        <p v-if="errors.rejection_reason" class="mt-1 text-sm text-red-600">
                            {{ errors.rejection_reason }}
                        </p>
                    </div>

                    <!-- Button Reject -->
                    <div class="flex justify-end">
                        <button type="submit" 
                                :disabled="isSubmitting"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="isSubmitting">Menolak...</span>
                            <span v-else>Tolak Pengajuan</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info jika sudah diproses -->
            <div v-else class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Status Penarikan</h3>
                <div class="space-y-4">
                    <div v-if="withdrawal.processed_at">
                        <label class="text-sm font-medium text-gray-500">Tanggal Diproses</label>
                        <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(withdrawal.processed_at) }}</p>
                    </div>
                    <div v-if="withdrawal.processor">
                        <label class="text-sm font-medium text-gray-500">Diproses Oleh</label>
                        <p class="mt-1 text-sm text-gray-900">{{ withdrawal.processor?.name }}</p>
                    </div>
                    <div v-if="withdrawal.admin_fee > 0">
                        <label class="text-sm font-medium text-gray-500">Biaya Admin</label>
                        <p class="mt-1 text-sm text-gray-900">Rp {{ formatNumber(withdrawal.admin_fee) }}</p>
                    </div>
                    <div v-if="withdrawal.final_amount">
                        <label class="text-sm font-medium text-gray-500">Dana yang Diterima</label>
                        <p class="mt-1 text-sm font-gray-900">Rp {{ formatNumber(withdrawal.final_amount) }}</p>
                    </div>
                    <div v-if="withdrawal.rejection_reason">
                        <label class="text-sm font-medium text-gray-500 text-red-600">Alasan Penolakan</label>
                        <p class="mt-1 text-sm text-gray-900">{{ withdrawal.rejection_reason }}</p>
                    </div>
                    <div v-if="withdrawal.notes">
                        <label class="text-sm font-medium text-gray-500">Catatan</label>
                        <p class="mt-1 text-sm text-gray-900">{{ withdrawal.notes }}</p>
                    </div>
                    <div v-if="withdrawal.file_path">
                        <label class="text-sm font-medium text-gray-500">Bukti Transfer</label>
                        <div class="mt-2">
                            <a :href="route('withdrawal.download-proof', withdrawal.id)" 
                               target="_blank"
                               class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Download Bukti
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    withdrawal: {
        type: Object,
        required: true
    },
    paymentMethods: {
        type: Object,
        required: true
    }
})

const activeTab = ref('approve')
const isSubmitting = ref(false)
const finalAmount = ref(props.withdrawal.total_amount)

const statusOptions = {
    pending: 'Pending',
    approved: 'Disetujui',
    processing: 'Diproses',
    completed: 'Selesai',
    rejected: 'Ditolak'
}

const statusBadgeClass = computed(() => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-blue-100 text-blue-800',
        processing: 'bg-indigo-100 text-indigo-800',
        completed: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800'
    }
    return classes[props.withdrawal.status] || 'bg-gray-100 text-gray-800'
})

const approveForm = useForm({
    admin_fee: 0,
    notes: '',
    proof_file: null
})

const rejectForm = useForm({
    rejection_reason: ''
})

const errors = computed(() => {
    return activeTab.value === 'approve' ? approveForm.errors : rejectForm.errors
})

const handleFileUpload = (event) => {
    approveForm.proof_file = event.target.files[0]
}

const calculateFinalAmount = () => {
    const adminFee = parseFloat(approveForm.admin_fee) || 0
    finalAmount.value = props.withdrawal.total_amount - adminFee
}

const submitApprove = () => {
    approveForm.post(route('withdrawal.approve', props.withdrawal.id), {
        preserveScroll: true,
        onStart: () => isSubmitting.value = true,
        onFinish: () => isSubmitting.value = false,
        onSuccess: () => router.reload()
    })
}

const submitReject = () => {
    rejectForm.post(route('withdrawal.reject', props.withdrawal.id), {
        preserveScroll: true,
        onStart: () => isSubmitting.value = true,
        onFinish: () => isSubmitting.value = false,
        onSuccess: () => router.reload()
    })
}

// Helper functions
const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num || 0)
}

const formatDateTime = (date) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>
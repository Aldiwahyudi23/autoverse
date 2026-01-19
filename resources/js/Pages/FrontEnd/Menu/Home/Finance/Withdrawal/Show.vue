<template>
    <AppLayout title="Detail Penarikan">
        <Head title="Detail Penarikan" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Detail Penarikan Dana</h3>
                    </div>
                    <div :class="statusBadgeClass" 
                         class="px-4 py-2 rounded-full text-sm font-semibold inline-flex items-center justify-center self-start sm:self-center">
                        <span class="mr-2">{{ getStatusIcon(withdrawal.status) }}</span>
                        {{ getStatusText(withdrawal.status) }}
                    </div>
                </div>
            </div>

            <!-- Alert Status -->
            <div v-if="withdrawal.status === 'approved'" class="mb-6" 
                 :class="is_owner ? 'bg-green-50 border-l-4 border-green-500' : 'bg-yellow-50 border-l-4 border-yellow-500'">
                <div class="flex p-4">
                    <div class="flex-shrink-0">
                        <svg v-if="is_owner" class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <svg v-else class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p v-if="is_owner" class="text-sm font-medium text-green-800">
                            Dana penarikan sudah disetujui dan sedang dalam proses pengiriman
                        </p>
                        <p v-else class="text-sm font-medium text-yellow-800">
                            Dana penarikan sudah disetujui dan sedang dalam proses pengiriman
                        </p>
                        <div class="mt-2">
                            <!-- Tombol untuk pemilik data -->
                            <button v-if="is_owner"
                                    @click="confirmComplete" 
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                Konfirmasi Dana Diterima
                            </button>
                            
                            <!-- Tombol untuk user lain (disabled) -->
                            <button v-else
                                    disabled
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-500 bg-gray-100 cursor-not-allowed">
                                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                </svg>
                                Menunggu Diterima
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert untuk status lainnya -->
            <div v-if="withdrawal.status === 'pending'" class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800">
                            Pengajuan penarikan {{ is_owner ? 'Anda' : withdrawal.user?.name }} sedang menunggu konfirmasi admin
                        </p>
                    </div>
                </div>
            </div>

            <div v-if="withdrawal.status === 'rejected'" class="mb-6 bg-red-50 border-l-4 border-red-500 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">
                            Pengajuan penarikan {{ is_owner ? 'Anda' : withdrawal.user?.name }} ditolak
                        </p>
                    </div>
                </div>
            </div>

            <!-- Informasi Utama -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Card 1: Informasi Penarikan -->
                <div class="bg-white shadow rounded-lg p-6 lg:col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Penarikan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Jumlah Penarikan</label>
                            <p class="mt-1 text-2xl font-bold text-blue-600">
                                Rp {{ formatNumber(withdrawal.total_amount) }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Biaya Admin</label>
                            <p class="mt-1 text-xl font-medium" :class="withdrawal.admin_fee > 0 ? 'text-red-600' : 'text-green-600'">
                                {{ withdrawal.admin_fee > 0 ? 'Rp ' + formatNumber(withdrawal.admin_fee) : 'Gratis' }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Dana Diterima</label>
                            <p class="mt-1 text-2xl font-bold text-green-600">
                                Rp {{ formatNumber(withdrawal.final_amount || withdrawal.total_amount) }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal Pengajuan</label>
                            <p class="mt-1 text-sm font-medium text-gray-900">
                                {{ formatDateTime(withdrawal.requested_at) }}
                            </p>
                        </div>
                        <div v-if="withdrawal.processed_at">
                            <label class="text-sm font-medium text-gray-500">Tanggal Diproses</label>
                            <p class="mt-1 text-sm font-medium text-gray-900">
                                {{ formatDateTime(withdrawal.processed_at) }}
                            </p>
                        </div>
                        <div v-if="withdrawal.processor">
                            <label class="text-sm font-medium text-gray-500">Diproses Oleh</label>
                            <p class="mt-1 text-sm font-medium text-gray-900">
                                {{ withdrawal.processor?.name }}
                            </p>
                        </div>
                        <!-- Informasi User (hanya untuk admin) -->
                        <div v-if="!is_owner && withdrawal.user">
                            <label class="text-sm font-medium text-gray-500">Pemilik Penarikan</label>
                            <p class="mt-1 text-sm font-medium text-gray-900">
                                {{ withdrawal.user?.name }}
                            </p>
                            <p class="text-xs text-gray-500">{{ withdrawal.user?.email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Informasi Pembayaran -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Pembayaran</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Metode</label>
                            <p class="mt-1 text-sm font-medium text-gray-900 flex items-center">
                                <span :class="getPaymentMethodBadgeClass(withdrawal.payment_method)" 
                                      class="px-2 py-1 rounded-md text-xs mr-2">
                                    {{ getPaymentMethodText(withdrawal.payment_method) }}
                                </span>
                            </p>
                        </div>
                        
                        <div v-if="withdrawal.payment_method === 'transfer'">
                            <div v-if="withdrawal.bank_name">
                                <label class="text-sm font-medium text-gray-500">Bank/E-Wallet</label>
                                <p class="mt-1 text-sm font-medium text-gray-900">{{ withdrawal.bank_name }}</p>
                            </div>
                            <div v-if="withdrawal.account_number">
                                <label class="text-sm font-medium text-gray-500">Nomor</label>
                                <p class="mt-1 text-sm font-medium text-gray-900 font-mono">
                                    {{ formatAccountNumber(withdrawal.account_number) }}
                                </p>
                            </div>
                            <div v-if="withdrawal.account_name">
                                <label class="text-sm font-medium text-gray-500">Atas Nama</label>
                                <p class="mt-1 text-sm font-medium text-gray-900">{{ withdrawal.account_name }}</p>
                            </div>
                        </div>
                        
                        <div v-else>
                            <p class="text-sm text-gray-600 mt-2">
                                Ambil dana tunai di lokasi sesuai dengan jadwal yang sudah ditentukan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catatan dan Bukti -->
            <div v-if="withdrawal.notes || withdrawal.rejection_reason || withdrawal.file_path" 
                 class="bg-white shadow rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Tambahan</h3>
                <div class="space-y-4">
                    <!-- Catatan -->
                    <div v-if="withdrawal.notes">
                        <label class="text-sm font-medium text-gray-500">Catatan</label>
                        <div class="mt-1 p-3 bg-blue-50 rounded-lg">
                            <p class="text-sm text-gray-700">{{ withdrawal.notes }}</p>
                        </div>
                    </div>

                    <!-- Alasan Penolakan -->
                    <div v-if="withdrawal.rejection_reason">
                        <label class="text-sm font-medium text-red-600">Alasan Penolakan</label>
                        <div class="mt-1 p-3 bg-red-50 rounded-lg">
                            <p class="text-sm text-red-700">{{ withdrawal.rejection_reason }}</p>
                        </div>
                    </div>

                    <!-- Bukti Transfer -->
                    <div v-if="withdrawal.file_path">
                        <label class="text-sm font-medium text-gray-500">Bukti Transfer</label>
                        <div class="mt-2">
                            <!-- Preview Gambar -->
                            <div v-if="isImageFile(withdrawal.file_path)" 
                                 class="border rounded-lg p-4 bg-gray-50">
                                <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                                    <div class="flex-shrink-0">
                                        <img :src="getProofImageUrl(withdrawal.file_path)" 
                                             alt="Bukti Transfer"
                                             class="h-48 w-48 object-contain rounded-lg border border-gray-200"
                                             @error="imageError">
                                        <p class="mt-2 text-xs text-gray-500 text-center">
                                            Klik gambar untuk melihat ukuran penuh
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview PDF -->
                            <div v-else class="border rounded-lg p-4 bg-gray-50">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-12 w-12 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">File Bukti Transfer (PDF)</p>
                                        <div class="mt-2">
                                            <a :href="route('withdrawal.download-proof', withdrawal.id)" 
                                               target="_blank"
                                               class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                <svg class="h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                                Download Bukti Transfer
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribusi Pendapatan -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Detail Distribusi Pendapatan</h3>
                    <div class="mt-2 sm:mt-0">
                        <span class="text-sm text-gray-600">
                            Total {{ withdrawal.transaction_distributions?.length || 0 }} distribusi
                        </span>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Transaksi
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Deskripsi
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jumlah
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(distribution, index) in withdrawal.transaction_distributions" :key="distribution.id">
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ index + 1 }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ distribution.transaction?.invoice_number || 'N/A' }}
                                    </div>
                                    <div v-if="distribution.transaction?.inspection" class="text-xs text-gray-500 mt-1">
                                        <div>{{ distribution.transaction.inspection.plate_number }}</div>
                                        <div>{{ distribution.transaction.inspection.car_name }}</div>
                                        <div class="mt-1">
                                            <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">
                                                {{ formatDate(distribution.created_at) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ distribution.role_type || 'Distribusi Pendapatan' }}
                                    </div>
                                    <div v-if="distribution.transaction?.description" 
                                         class="text-xs text-gray-500 mt-1">
                                        {{ distribution.transaction.description }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        Rp {{ formatNumber(distribution.amount) }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-right text-sm font-medium text-gray-900">
                                    Total Penarikan:
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-lg font-bold text-blue-600">
                                    Rp {{ formatNumber(withdrawal.total_amount) }}
                                </td>
                            </tr>
                            <tr v-if="withdrawal.admin_fee > 0">
                                <td colspan="3" class="px-4 py-4 text-right text-sm font-medium text-gray-900">
                                    Biaya Admin:
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                                    - Rp {{ formatNumber(withdrawal.admin_fee) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-right text-sm font-medium text-gray-900">
                                    Dana Diterima:
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-lg font-bold text-green-600">
                                    Rp {{ formatNumber(withdrawal.final_amount || withdrawal.total_amount) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 justify-end">
                <Link :href="route('withdrawals.history')" 
                      class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    Kembali ke Riwayat
                </Link>
                
                <!-- Tombol Konfirmasi Dana Diterima -->
                <template v-if="withdrawal.status === 'approved'">
                    <!-- Tombol aktif untuk pemilik data -->
                    <button v-if="is_owner"
                            @click="confirmComplete"
                            class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        Konfirmasi Dana Diterima
                    </button>
                    
                    <!-- Tombol disabled untuk user lain -->
                    <button v-else
                            disabled
                            class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-500 bg-gray-100 cursor-not-allowed">
                        <svg class="h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                        Menunggu Diterima
                    </button>
                </template>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    withdrawal: {
        type: Object,
        required: true
    },
    paymentMethods: {
        type: Object,
        required: true
    },
    is_owner: {
        type: Boolean,
        required: true
    },
    current_user_id: {
        type: Number,
        required: true
    }
})

// Computed property untuk status badge
const statusBadgeClass = computed(() => {
    return getStatusBadgeClass(props.withdrawal.status)
})

// Helper functions
const getStatusText = (status) => {
    const texts = {
        pending: 'Menunggu Konfirmasi',
        approved: 'Dana Dikirim',
        processing: 'Sedang Diproses',
        completed: 'Selesai',
        rejected: 'Ditolak'
    }
    return texts[status] || status
}

const getStatusIcon = (status) => {
    const icons = {
        pending: '⏳',
        approved: '📤',
        processing: '⚙️',
        completed: '✅',
        rejected: '❌'
    }
    return icons[status] || '❓'
}

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
        approved: 'bg-green-100 text-green-800 border border-green-200',
        processing: 'bg-indigo-100 text-indigo-800 border border-indigo-200',
        completed: 'bg-blue-100 text-blue-800 border border-blue-200',
        rejected: 'bg-red-100 text-red-800 border border-red-200'
    }
    return classes[status] || 'bg-gray-100 text-gray-800 border border-gray-200'
}

const getPaymentMethodText = (method) => {
    const methods = {
        transfer: 'Transfer',
        cash: 'Tunai'
    }
    return methods[method] || method
}

const getPaymentMethodBadgeClass = (method) => {
    const classes = {
        transfer: 'bg-blue-100 text-blue-800',
        cash: 'bg-yellow-100 text-yellow-800'
    }
    return classes[method] || 'bg-gray-100 text-gray-800'
}

const formatAccountNumber = (accountNumber) => {
    if (!accountNumber) return ''
    // Format dengan spasi setiap 4 digit
    return accountNumber.replace(/(.{4})/g, '$1 ').trim()
}

const isImageFile = (filePath) => {
    if (!filePath) return false
    const imageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp']
    return imageExtensions.some(ext => filePath.toLowerCase().endsWith(ext))
}

const getProofImageUrl = (filePath) => {
    // Sesuaikan dengan storage configuration Anda
    return `/${filePath}`
}

const imageError = (event) => {
    // Fallback jika gambar tidak bisa dimuat - gunakan placeholder
    if (!event.target.src.includes('default-proof')) {
        event.target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjRjNGNEY2Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5QTBBRSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkdhbWJhciBUaWRhayBEaXRlbXVrYW48L3RleHQ+PC9zdmc+'
    }
}

const confirmComplete = () => {
    if (confirm('Apakah Anda yakin telah menerima dana penarikan ini? Dana akan ditandai sebagai selesai dan tidak dapat dikembalikan.')) {
        router.post(route('withdrawal.complete', props.withdrawal.id), {
            preserveScroll: true,
            onSuccess: () => router.reload()
        })
    }
}

const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num || 0)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    })
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

<style scoped>
/* Responsive table styles */
@media (max-width: 640px) {
    table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
}

/* Image preview styles */
img {
    max-width: 100%;
    height: auto;
}

/* Animation for status badge */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
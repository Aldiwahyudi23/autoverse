<template>
    <AppLayout title="Ajukan Penarikan">
        <Head title="Ajukan Penarikan" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Ajukan Penarikan Dana</h1>
                <p class="text-gray-600 mt-2">Pilih distribusi pendapatan yang ingin ditarik</p>
            </div>

            <!-- Alert -->
            <div v-if="$page.props.flash.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ $page.props.flash.error }}
            </div>

            <!-- Section: Sedang Diproses -->
            <div v-if="processing.length > 0" class="mb-8">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Penarikan Sedang Diproses</h2>
                                <p class="text-sm text-gray-600">Anda memiliki pengajuan penarikan yang sedang diproses</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                            {{ processing.length }} sedang diproses
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div v-for="withdrawal in processing" :key="withdrawal.id" 
                             class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200">
                            <div class="flex flex-col md:flex-row md:items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <div class="flex items-center">
                                            <span class="animate-ping absolute h-2 w-2 rounded-full bg-blue-400 opacity-75"></span>
                                            <span class="relative h-2 w-2 rounded-full bg-blue-500"></span>
                                            <span class="ml-3 text-sm font-medium text-gray-900">
                                                Penarikan
                                            </span>
                                        </div>
                                        <span class="ml-4 px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded">
                                            {{ getStatusText(withdrawal.status) }}
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                                        <div>
                                            <span class="text-gray-500">Jumlah:</span>
                                            <span class="ml-2 font-medium text-gray-900">
                                                Rp {{ formatNumber(withdrawal.total_amount) }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Metode:</span>
                                            <span class="ml-2 font-medium text-gray-900">
                                                {{ getPaymentMethodText(withdrawal.payment_method) }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Pengajuan:</span>
                                            <span class="ml-2 font-medium text-gray-900">
                                                {{ formatDate(withdrawal.requested_at) }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div v-if="withdrawal.notes" class="mt-2">
                                        <p class="text-xs text-gray-600">
                                            <span class="font-medium">Catatan:</span> {{ withdrawal.notes }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="mt-4 md:mt-0 md:ml-4">
                                    <Link v-if="withdrawal.status === 'pending' || withdrawal.status === 'processing'"
                                    :href="route('withdrawal.show', withdrawal.id)"
                                          class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                        </svg>
                                        Lihat Detail
                                    </Link>

                                    <button v-if="withdrawal.status === 'approved'"
                                            @click="confirmComplete(withdrawal)"
                                            class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <svg class="h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        Konfirmasi Dana Diterima
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="mt-4">
                                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                    <span>Proses Penarikan</span>
                                    <span>{{ getProgressPercentage(withdrawal.status) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div :class="getProgressBarClass(withdrawal.status)" 
                                         :style="{ width: getProgressPercentage(withdrawal.status) + '%' }"
                                         class="h-2 rounded-full transition-all duration-500"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span>Pengajuan</span>
                                    <span>Diproses</span>
                                    <span>Disetujui</span>
                                    <span>Selesai</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 text-sm text-blue-600">
                        <p>ⓘ Penarikan yang sedang diproses tidak dapat diedit atau dibatalkan.</p>
                    </div>
                </div>
            </div>

            <!-- Section: Pilih Distribusi Baru -->
            <div class="bg-white shadow rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Ajukan Penarikan Baru</h2>
                    <span v-if="distributions.length > 0" class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                        {{ distributions.length }} distribusi tersedia
                    </span>
                </div>
                
                <div v-if="distributions.length === 0" class="text-center py-8 text-gray-500">
                    <svg class="h-12 w-12 mx-auto text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada distribusi yang tersedia</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Semua distribusi pendapatan Anda sudah diajukan atau sudah ditarik.
                    </p>
                </div>
                
                <div v-else>
                    <!-- Info distribusi -->
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-gray-400 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <p class="text-sm text-gray-600">
                                Pilih distribusi pendapatan yang ingin ditarik. Anda dapat memilih satu atau lebih distribusi.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Tabel Distribusi -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                                        <input type="checkbox" 
                                               @change="selectAll" 
                                               :checked="selectedDistributions.length === distributions.length && distributions.length > 0"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
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
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="distribution in distributions" :key="distribution.id" 
                                    class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <input type="checkbox" 
                                               :value="distribution.id" 
                                               v-model="selectedDistributions"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <!-- <td class="px-4 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ distribution.transaction?.invoice_number || 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <div>{{ distribution.transaction?.inspection?.plate_number || '-' }}</div>
                                            <div>{{ distribution.transaction?.inspection?.car_name || '-' }}</div>
                                        </div>
                                    </td> -->
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
                                    <td class="px-4 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{ distribution.role_type || 'Distribusi Pendapatan' }}
                                        </div>
                                        <div v-if="distribution.transaction?.description" 
                                             class="text-xs text-gray-500 mt-1 truncate max-w-xs">
                                            {{ distribution.transaction.description }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            Rp {{ formatNumber(distribution.amount) }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(distribution.created_at) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Summary Distribusi -->
                    <div v-if="selectedDistributions.length > 0" class="mt-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 bg-blue-50 rounded-lg">
                            <div>
                                <p class="text-sm text-blue-800">
                                    <span class="font-medium">{{ selectedDistributions.length }}</span> distribusi terpilih
                                </p>
                                <p class="text-xs text-blue-600 mt-1">
                                    Total dana yang akan ditarik:
                                </p>
                            </div>
                            <div class="mt-2 sm:mt-0 text-right">
                                <div class="text-2xl font-bold text-blue-600">
                                    Rp {{ formatNumber(calculatedTotal) }}
                                </div>
                                <p class="text-xs text-blue-500">
                                    Sebelum biaya admin
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section: Informasi Pembayaran -->
            <div v-if="selectedDistributions.length > 0" class="bg-white shadow rounded-lg p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Informasi Pembayaran</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Metode Pembayaran -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Metode Pembayaran
                            <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.payment_method" 
                                @change="onPaymentMethodChange"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Metode</option>
                            <option value="transfer">Transfer Bank/E-Wallet</option>
                            <option value="cash">Tunai</option>
                        </select>
                        <p v-if="errors.payment_method" class="mt-1 text-sm text-red-600">
                            {{ errors.payment_method }}
                        </p>
                    </div>

                    <!-- Bank / E-Wallet -->
                    <div v-if="form.payment_method === 'transfer'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Bank / E-Wallet <span class="text-red-500">*</span>
                        </label>
                        <select v-model="form.bank_name" 
                                @change="onBankChange"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Pilih Bank/E-Wallet</option>
                            <option value="BRI">Bank BRI</option>
                            <option value="BCA">Bank BCA</option>
                            <option value="MANDIRI">Bank Mandiri</option>
                            <option value="BNI">Bank BNI</option>
                            <option value="Lainnya">Bank Lainnya</option>
                            <option value="OVO">OVO</option>
                            <option value="DANA">DANA</option>
                            <option value="GoPay">GoPay</option>
                        </select>
                        <p v-if="errors.bank_name" class="mt-1 text-sm text-red-600">
                            {{ errors.bank_name }}
                        </p>
                    </div>

                    <!-- Nomor Rekening / E-Wallet -->
                    <div v-if="form.payment_method === 'transfer' && form.bank_name">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ accountNumberLabel }}
                        </label>
                        <input type="text" 
                               v-model="form.account_number"
                               :placeholder="accountNumberPlaceholder"
                               @input="formatAccountNumber"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">
                            {{ accountNumberHint }}
                        </p>
                        <p v-if="errors.account_number" class="mt-1 text-sm text-red-600">
                            {{ errors.account_number }}
                        </p>
                    </div>

                    <!-- Nama Pemilik -->
                    <div v-if="form.payment_method === 'transfer'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Atas Nama <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               v-model="form.account_name"
                               :placeholder="userName"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-sm text-gray-500">
                            Masukan nama yang sesuai Atas nama di rek: {{ userName }}
                        </p>
                        <p v-if="errors.account_name" class="mt-1 text-sm text-red-600">
                            {{ errors.account_name }}
                        </p>
                    </div>
                </div>

                <!-- Informasi untuk tunai -->
                <div v-if="form.payment_method === 'cash'" class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <InformationCircleIcon class="h-5 w-5 text-blue-400" />
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">Informasi Penarikan Tunai</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>Untuk penarikan tunai, Anda akan menerima dana langsung di lokasi setelah pengajuan disetujui.</p>
                                <p class="mt-1">Silahkan hubungi admin untuk pengambilan dana.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4">
                <Link :href="route('dashboard')"
                      class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Batal
                </Link>
                <button @click="submitForm"
                        :disabled="isSubmitting || selectedDistributions.length === 0 || !isFormValid"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span v-if="isSubmitting">Mengirim...</span>
                    <span v-else>Ajukan Penarikan</span>
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm, usePage, router  } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { InformationCircleIcon } from '@heroicons/vue/24/outline'

const page = usePage()
const props = defineProps({
    distributions: {
        type: Array,
        required: true
    },
    processing: {
        type: Array,
        required: true
    }
})

const selectedDistributions = ref([])
const isSubmitting = ref(false)

const form = useForm({
    selected_distributions: [],
    payment_method: '',
    bank_name: '',
    account_number: '',
    account_name: ''
})

const errors = computed(() => form.errors)

// Hitung total dari distribusi yang dipilih
const calculatedTotal = computed(() => {
    return props.distributions
        .filter(d => selectedDistributions.value.includes(d.id))
        .reduce((sum, d) => sum + parseFloat(d.amount), 0)
})

// Validasi form
const isFormValid = computed(() => {
    if (selectedDistributions.value.length === 0) return false
    if (!form.payment_method) return false
    
    if (form.payment_method === 'transfer') {
        if (!form.bank_name) return false
        if (!form.account_number) return false
        if (!isAccountNumberValid.value) return false
    }
    
    return true
})

// Validasi nomor rekening/e-wallet
const isAccountNumberValid = computed(() => {
    if (!form.account_number || !form.bank_name) return true
    
    const accountNumber = form.account_number.replace(/\s/g, '')
    
    // Untuk e-wallet
    if (['OVO', 'DANA', 'GoPay'].includes(form.bank_name)) {
        // Validasi nomor HP (08xxxxxxxxxx)
        const phoneRegex = /^08[1-9][0-9]{7,11}$/
        return phoneRegex.test(accountNumber) && accountNumber.length >= 10 && accountNumber.length <= 13
    }
    
    // Untuk bank
    switch (form.bank_name) {
        case 'BRI':
            return /^\d{10,16}$/.test(accountNumber)
        case 'BCA':
            return /^\d{10,12}$/.test(accountNumber)
        case 'MANDIRI':
            return /^\d{10,15}$/.test(accountNumber)
        case 'BNI':
            return /^\d{10,12}$/.test(accountNumber)
        default:
            return accountNumber.length >= 5 && accountNumber.length <= 20
    }
})

// Label untuk input nomor
const accountNumberLabel = computed(() => {
    if (['OVO', 'DANA', 'GoPay'].includes(form.bank_name)) {
        return 'Nomor Handphone *'
    }
    return 'Nomor Rekening *'
})

// Placeholder untuk input nomor
const accountNumberPlaceholder = computed(() => {
    if (['OVO', 'DANA', 'GoPay'].includes(form.bank_name)) {
        return '08xxxxxxxxxx'
    }
    
    switch (form.bank_name) {
        case 'BRI': return 'Contoh: 1234567890123456'
        case 'BCA': return 'Contoh: 1234567890'
        case 'MANDIRI': return 'Contoh: 123456789012345'
        case 'BNI': return 'Contoh: 1234567890'
        default: return 'Masukkan nomor rekening'
    }
})

// Hint untuk input nomor
const accountNumberHint = computed(() => {
    if (['OVO', 'DANA', 'GoPay'].includes(form.bank_name)) {
        return 'Masukkan nomor HP yang terdaftar di ' + form.bank_name
    }
    
    switch (form.bank_name) {
        case 'BRI': return 'Masukkan 10-16 digit nomor rekening BRI'
        case 'BCA': return 'Masukkan 10-12 digit nomor rekening BCA'
        case 'MANDIRI': return 'Masukkan 10-15 digit nomor rekening Mandiri'
        case 'BNI': return 'Masukkan 10-12 digit nomor rekening BNI'
        default: return 'Masukkan nomor rekening bank'
    }
})

// Nama user yang login
const userName = computed(() => {
    return page.props.auth.user.name
})

// Helper functions untuk processing data
const getStatusText = (status) => {
    const texts = {
        pending: 'Pending',
        approved: 'Disetujui',
        processing: 'Diproses',
        completed: 'Selesai',
        rejected: 'Ditolak'
    }
    return texts[status] || status
}

const getPaymentMethodText = (method) => {
    const methods = {
        transfer: 'Transfer',
        cash: 'Tunai'
    }
    return methods[method] || method
}

const getProgressPercentage = (status) => {
    switch (status) {
        case 'pending': return 25
        case 'processing': return 50
        case 'approved': return 75
        case 'completed': return 100
        default: return 0
    }
}

const getProgressBarClass = (status) => {
    switch (status) {
        case 'pending': return 'bg-yellow-500'
        case 'processing': return 'bg-blue-500'
        case 'approved': return 'bg-indigo-500'
        case 'completed': return 'bg-green-500'
        default: return 'bg-gray-500'
    }
}

const selectAll = (event) => {
    if (event.target.checked) {
        selectedDistributions.value = props.distributions.map(d => d.id)
    } else {
        selectedDistributions.value = []
    }
}

const onPaymentMethodChange = () => {
    if (form.payment_method === 'cash') {
        // Reset field untuk tunai
        form.bank_name = ''
        form.account_number = ''
        form.account_name = ''
    } else if (form.payment_method === 'transfer') {
        // Set nama default untuk transfer
        if (!form.account_name) {
            form.account_name = userName.value
        }
    }
}

const onBankChange = () => {
    // Reset nomor rekening saat ganti bank
    form.account_number = ''
    
    // Set placeholder berdasarkan bank/e-wallet
    if (['OVO', 'DANA', 'GoPay'].includes(form.bank_name)) {
        // Set nama default untuk e-wallet
        if (!form.account_name) {
            form.account_name = userName.value
        }
    }
}

const formatAccountNumber = () => {
    if (!form.account_number || !form.bank_name) return
    
    // Hapus semua karakter non-digit
    let cleaned = form.account_number.replace(/\D/g, '')
    
    // Batasi panjang maksimal
    let maxLength = 20
    if (['OVO', 'DANA', 'GoPay'].includes(form.bank_name)) {
        maxLength = 13
    }
    cleaned = cleaned.substring(0, maxLength)
    
    // Format untuk tampilan (tambah spasi setiap 4 digit untuk bank)
    if (!['OVO', 'DANA', 'GoPay'].includes(form.bank_name)) {
        form.account_number = cleaned.replace(/(\d{4})(?=\d)/g, '$1 ')
    } else {
        form.account_number = cleaned
    }
}

const submitForm = () => {
    if (selectedDistributions.value.length === 0) {
        alert('Pilih minimal satu distribusi')
        return
    }

    if (!isAccountNumberValid.value) {
        alert('Nomor rekening/e-wallet tidak valid. Silahkan periksa kembali.')
        return
    }

    // Bersihkan spasi dari nomor rekening sebelum dikirim
    const cleanForm = {
        ...form,
        account_number: form.account_number ? form.account_number.replace(/\s/g, '') : '',
        selected_distributions: selectedDistributions.value,
        account_name: form.account_name || userName.value
    }

    cleanForm.post(route('withdrawal.store'), {
        preserveScroll: true,
        onStart: () => isSubmitting.value = true,
        onFinish: () => isSubmitting.value = false,
        onSuccess: () => {
            form.reset()
            selectedDistributions.value = []
        }
    })
}

const confirmComplete = (withdrawal) => {
    if (confirm('Apakah Anda yakin telah menerima dana penarikan ini? Dana akan ditandai sebagai selesai dan tidak dapat dikembalikan.')) {
        router.post(route('withdrawal.complete', withdrawal.id), {
            preserveScroll: true,
            onSuccess: () => router.reload()
        })
    }
}

// Helper functions
const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    })
}
</script>

<style scoped>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Animation untuk progress bar */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-ping {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Responsive table */
@media (max-width: 640px) {
    .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
    }
}
</style>
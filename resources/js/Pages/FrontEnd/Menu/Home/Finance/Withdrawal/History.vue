<template>
    <AppLayout title="Riwayat Penarikan">
        <Head title="Riwayat Penarikan" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Riwayat Penarikan Dana</h1>
                <p class="text-gray-600 mt-2">Daftar semua pengajuan penarikan {{ is_admin_or_coordinator ? 'sistem' : 'Anda' }}</p>
            </div>

            <!-- Filter Section -->
            <div class="mb-6 bg-white shadow rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <!-- Filter Tahun -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tahun
                        </label>
                        <select v-model="selectedYear" 
                                @change="applyFilters"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua Tahun</option>
                            <option v-for="year in availableYears" :key="year" :value="year">
                                {{ year }}
                            </option>
                        </select>
                    </div>

                    <!-- Filter Bulan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Bulan
                        </label>
                        <select v-model="selectedMonth" 
                                @change="applyFilters"
                                :disabled="!selectedYear"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua Bulan</option>
                            <option v-for="(month, index) in monthOptions" 
                                    :key="index" 
                                    :value="index + 1"
                                    :disabled="!isMonthAvailable(index + 1)">
                                {{ month }}
                            </option>
                        </select>
                    </div>

                    <!-- Filter Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Status
                        </label>
                        <select v-model="selectedStatus" 
                                @change="applyFilters"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Dikirim</option>
                            <option value="processing">Diproses</option>
                            <option value="completed">Selesai</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>

                    <!-- Filter Region/Wilayah (Hanya untuk Admin/Coordinator) -->
                    <div v-if="is_admin_or_coordinator">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Wilayah
                        </label>
                        <select v-model="selectedRegion" 
                                @change="onRegionChange"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua Wilayah</option>
                            <option value="all">Semua Wilayah</option>
                            <option v-for="region in regions" :key="region.id" :value="region.id">
                                {{ region.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Filter User (Hanya untuk Admin/Coordinator) -->
                    <div v-if="is_admin_or_coordinator">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            User
                        </label>
                        <select v-model="selectedUser" 
                                @change="applyFilters"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Semua User</option>
                            <option v-for="user in all_users" :key="user.id" :value="user.id">
                                {{ user.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Reset Filter Button -->
                <div v-if="isFiltered" class="mt-4 flex justify-end">
                    <button @click="resetFilters"
                            class="px-3 py-1.5 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Reset Filter
                    </button>
                </div>
            </div>

            <!-- Info Summary -->
            <div v-if="filteredWithdrawals.length > 0" class="mb-4 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <!-- Total Withdrawals -->
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                            <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Penarikan</p>
                            <p class="text-lg font-semibold text-gray-900">{{ filteredWithdrawals.length }}</p>
                        </div>
                    </div>
                </div>

                <!-- Total Amount -->
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                            <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Dana</p>
                            <p class="text-lg font-semibold text-gray-900">Rp {{ formatNumber(totalAmount) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Success Rate -->
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-md p-3">
                            <svg class="h-6 w-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Berhasil</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ completedCount }} dari {{ filteredWithdrawals.length }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pending Count -->
                <div class="bg-white shadow rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                            <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Pending</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ pendingCount }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div v-if="filteredWithdrawals.length === 0" class="text-center py-12 text-gray-500">
                    <template v-if="isFiltered">
                        <svg class="h-12 w-12 mx-auto text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ditemukan</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Tidak ada data penarikan dengan filter yang dipilih
                        </p>
                        <button @click="resetFilters" 
                                class="mt-4 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Tampilkan semua
                        </button>
                    </template>
                    <template v-else>
                        <svg class="h-12 w-12 mx-auto text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada riwayat penarikan</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ is_admin_or_coordinator ? 'Belum ada data penarikan di sistem' : 'Anda belum melakukan pengajuan penarikan.' }}
                        </p>
                    </template>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <!-- Kolom User hanya untuk Admin/Coordinator -->
                                <th v-if="is_admin_or_coordinator" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Biaya Admin
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Diterima
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Metode
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="withdrawal in filteredWithdrawals" :key="withdrawal.id">
                                <!-- Kolom User hanya untuk Admin/Coordinator -->
                                <td v-if="is_admin_or_coordinator" class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-blue-600 font-medium">
                                                    {{ withdrawal.user?.name?.charAt(0) || 'U' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ withdrawal.user?.name || 'Unknown User' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ withdrawal.user?.email || '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <button @click="goToWithdrawalDetail(withdrawal.id)"
                                                :class="`${getStatusBadgeClass(withdrawal.status)} px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full hover:opacity-80 transition-opacity cursor-pointer`">
                                            {{ getStatusText(withdrawal.status) }}
                                        </button>
                                        <div v-if="withdrawal.status === 'approved'" class="ml-2">
                                            <span class="animate-ping absolute h-2 w-2 rounded-full bg-green-400 opacity-75"></span>
                                            <span class="relative h-2 w-2 rounded-full bg-green-500"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rp {{ formatNumber(withdrawal.total_amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ withdrawal.admin_fee > 0 ? 'Rp ' + formatNumber(withdrawal.admin_fee) : 'Gratis' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ withdrawal.final_amount ? 'Rp ' + formatNumber(withdrawal.final_amount) : 'Rp ' + formatNumber(withdrawal.total_amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ getPaymentMethodText(withdrawal.payment_method) }}
                                    </div>
                                    <div v-if="withdrawal.bank_name" class="text-xs text-gray-500">
                                        {{ withdrawal.bank_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ formatDate(withdrawal.requested_at) }}</div>
                                    <div class="text-xs text-gray-400">
                                        {{ formatTime(withdrawal.requested_at) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <!-- Tombol Konfirmasi Diterima -->
                                    <template v-if="withdrawal.status === 'approved'">
                                        <button v-if="withdrawal.user_id === current_user_id"
                                                @click="confirmComplete(withdrawal)"
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                                            <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Konfirmasi Diterima
                                        </button>
                                        <button v-else
                                                disabled
                                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-500 bg-gray-100 cursor-not-allowed">
                                            <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                            </svg>
                                            Menunggu Diterima
                                        </button>
                                    </template>
                                    <span v-else class="text-gray-400 text-xs">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Table Footer -->
                    <div v-if="filteredWithdrawals.length > 0" class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                Menampilkan <span class="font-medium">{{ filteredWithdrawals.length }}</span> data
                                <span v-if="isFiltered">
                                    (difilter)
                                </span>
                            </div>
                            <div class="text-sm text-gray-600">
                                Total: <span class="font-bold text-blue-600">Rp {{ formatNumber(totalAmount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    withdrawals: {
        type: Array,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    is_admin_or_coordinator: {
        type: Boolean,
        default: false
    },
    regions: {
        type: Array,
        default: () => []
    },
    all_users: {
        type: Array,
        default: () => []
    },
    available_years: {
        type: Array,
        default: () => []
    },
    current_user_id: {
        type: Number,
        required: true
    }
})

// Filter states
const selectedYear = ref(props.filters.year || '')
const selectedMonth = ref(props.filters.month || '')
const selectedStatus = ref(props.filters.status || '')
const selectedRegion = ref(props.filters.region_id || '')
const selectedUser = ref(props.filters.user_id || '')

// Month options
const monthOptions = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]

// Check if month is available for selected year
const isMonthAvailable = (month) => {
    if (!selectedYear.value) return true
    
    return props.withdrawals.some(withdrawal => {
        const date = new Date(withdrawal.requested_at)
        return date.getFullYear() === parseInt(selectedYear.value) && 
               date.getMonth() + 1 === month
    })
}

// Filter data
const filteredWithdrawals = computed(() => {
    let result = [...props.withdrawals]

    // Filter by year
    if (selectedYear.value) {
        const year = parseInt(selectedYear.value)
        result = result.filter(withdrawal => {
            const withdrawalYear = new Date(withdrawal.requested_at).getFullYear()
            return withdrawalYear === year
        })
    }

    // Filter by month
    if (selectedMonth.value) {
        const month = parseInt(selectedMonth.value)
        result = result.filter(withdrawal => {
            const withdrawalMonth = new Date(withdrawal.requested_at).getMonth() + 1
            return withdrawalMonth === month
        })
    }

    // Filter by status
    if (selectedStatus.value) {
        result = result.filter(withdrawal => withdrawal.status === selectedStatus.value)
    }

    // Filter by user (only for admin/coordinator)
    if (props.is_admin_or_coordinator && selectedUser.value) {
        result = result.filter(withdrawal => withdrawal.user_id == selectedUser.value)
    }

    // Sort by date (newest first)
    return result.sort((a, b) => new Date(b.requested_at) - new Date(a.requested_at))
})

// Calculate total amount
const totalAmount = computed(() => {
    return filteredWithdrawals.value.reduce((sum, withdrawal) => 
        sum + parseFloat(withdrawal.total_amount), 0
    )
})

// Count completed withdrawals
const completedCount = computed(() => {
    return filteredWithdrawals.value.filter(w => w.status === 'completed').length
})

// Count pending withdrawals
const pendingCount = computed(() => {
    return filteredWithdrawals.value.filter(w => w.status === 'pending').length
})

// Check if any filter is active
const isFiltered = computed(() => {
    const baseFilters = selectedYear.value || selectedMonth.value || selectedStatus.value
    if (!props.is_admin_or_coordinator) return baseFilters
    return baseFilters || selectedRegion.value || selectedUser.value
})

// Apply filters
const applyFilters = () => {
    const filters = {}
    
    if (selectedYear.value) filters.year = selectedYear.value
    if (selectedMonth.value) filters.month = selectedMonth.value
    if (selectedStatus.value) filters.status = selectedStatus.value
    
    // Only add admin filters if user is admin/coordinator
    if (props.is_admin_or_coordinator) {
        if (selectedRegion.value) filters.region_id = selectedRegion.value
        if (selectedUser.value) filters.user_id = selectedUser.value
    }
    
    router.get(route('withdrawals.history'), filters, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}

// Handle region change
const onRegionChange = () => {
    // Reset user when region changes
    selectedUser.value = ''
    applyFilters()
}

// Reset all filters
const resetFilters = () => {
    selectedYear.value = ''
    selectedMonth.value = ''
    selectedStatus.value = ''
    
    if (props.is_admin_or_coordinator) {
        selectedRegion.value = ''
        selectedUser.value = ''
    }
    
    router.get(route('withdrawals.history'), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}

// Status text mapping
const getStatusText = (status) => {
    const texts = {
        pending: 'Pending',
        approved: 'Dikirim',
        processing: 'Diproses',
        completed: 'Selesai',
        rejected: 'Ditolak'
    }
    return texts[status] || status
}

// Status badge classes
const getStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800 border border-green-200',
        processing: 'bg-indigo-100 text-indigo-800',
        completed: 'bg-blue-100 text-blue-800',
        rejected: 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

// Payment method text
const getPaymentMethodText = (method) => {
    const methods = {
        transfer: 'Transfer',
        cash: 'Tunai'
    }
    return methods[method] || method
}

const goToWithdrawalDetail = (id) => {
    router.visit(route('withdrawal.show', id))
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
    return new Intl.NumberFormat('id-ID').format(num || 0)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    })
}

const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>
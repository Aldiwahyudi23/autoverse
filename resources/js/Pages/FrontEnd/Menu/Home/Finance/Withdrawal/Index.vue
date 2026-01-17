<template>
    <AppLayout title="Konfirmasi Penarikan">
        <Head title="Konfirmasi Penarikan" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Konfirmasi Pengajuan Penarikan</h1>
                <p class="text-gray-600 mt-2">Daftar pengajuan penarikan yang menunggu konfirmasi</p>
            </div>

            <!-- Alert -->
            <div v-if="$page.props.flash.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ $page.props.flash.success }}
            </div>

            <!-- Search Section -->
            <div class="mb-6 bg-white shadow rounded-lg p-4">
                <div class="max-w-md">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Cari berdasarkan nama user
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" 
                               v-model="searchName"
                               @input="handleSearch"
                               placeholder="Cari nama user..."
                               class="pl-10 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        
                        <!-- Clear search button -->
                        <button v-if="searchName" 
                                @click="clearSearch"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <svg class="h-5 w-5 text-gray-400 hover:text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">
                        Ketik nama user untuk mencari pengajuan penarikan
                    </p>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <!-- Table Info -->
                <div v-if="searchName" class="px-6 py-4 bg-blue-50 border-b border-blue-100">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                            <p class="text-sm text-blue-700">
                                Hasil pencarian untuk: "<span class="font-semibold">{{ searchName }}</span>"
                                <span class="ml-2 text-blue-600">
                                    ({{ filteredWithdrawals.length }} hasil ditemukan)
                                </span>
                            </p>
                        </div>
                        <button @click="clearSearch" 
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Tampilkan semua
                        </button>
                    </div>
                </div>

                <div v-if="filteredWithdrawals.length === 0" class="text-center py-12 text-gray-500">
                    <template v-if="searchName">
                        <svg class="h-12 w-12 mx-auto text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ditemukan</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Tidak ada pengajuan untuk user dengan nama "<span class="font-medium">{{ searchName }}</span>"
                        </p>
                        <button @click="clearSearch" 
                                class="mt-4 inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Tampilkan semua pengajuan
                        </button>
                    </template>
                    <template v-else>
                        <svg class="h-12 w-12 mx-auto text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada pengajuan</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Tidak ada pengajuan penarikan yang menunggu konfirmasi.
                        </p>
                    </template>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Metode
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal Pengajuan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="withdrawal in filteredWithdrawals" :key="withdrawal.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-blue-600 font-medium text-sm">
                                                    {{ getInitials(withdrawal.user?.name) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ withdrawal.user?.name }}</div>
                                            <div class="text-sm text-gray-500">{{ withdrawal.user?.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rp {{ formatNumber(withdrawal.total_amount) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ paymentMethods[withdrawal.payment_method] || withdrawal.payment_method }}
                                    </div>
                                    <div v-if="withdrawal.bank_name" class="text-xs text-gray-500 mt-1">
                                        {{ withdrawal.bank_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDateTime(withdrawal.requested_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <Link :href="route('withdrawal.process', withdrawal.id)"
                                          class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                        </svg>
                                        Proses
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Search result summary -->
                    <div v-if="searchName" class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                        <p class="text-sm text-gray-600 text-center">
                            Menampilkan <span class="font-medium">{{ filteredWithdrawals.length }}</span> dari 
                            <span class="font-medium">{{ withdrawals.length }}</span> total pengajuan
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    withdrawals: {
        type: Array,
        required: true
    },
    paymentMethods: {
        type: Object,
        required: true
    }
})

// State untuk pencarian
const searchName = ref('')

// Filter data berdasarkan pencarian nama (client-side)
const filteredWithdrawals = computed(() => {
    if (!searchName.value.trim()) {
        return props.withdrawals
    }
    
    const searchTerm = searchName.value.toLowerCase().trim()
    
    return props.withdrawals.filter(withdrawal => {
        // Cari di nama user
        const userName = withdrawal.user?.name?.toLowerCase() || ''
        if (userName.includes(searchTerm)) {
            return true
        }
        
        // Cari di email user
        const userEmail = withdrawal.user?.email?.toLowerCase() || ''
        if (userEmail.includes(searchTerm)) {
            return true
        }
        
        return false
    })
})

// Handle search input dengan debounce
const handleSearch = debounce(() => {
    // Untuk client-side filtering, tidak perlu kirim request ke server
    // Jika ingin server-side filtering, gunakan kode berikut:
    // router.get(route('withdrawals.index'), { search: searchName.value }, {
    //     preserveState: true,
    //     preserveScroll: true
    // })
}, 300)

// Clear search
const clearSearch = () => {
    searchName.value = ''
}

// Helper functions
const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num || 0)
}

const formatDateTime = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getInitials = (name) => {
    if (!name) return '?'
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2)
}
</script>

<style scoped>
/* Optional: Tambahkan animasi untuk search results */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CalendarDaysIcon, ClipboardDocumentListIcon, MagnifyingGlassIcon, XMarkIcon, FunnelIcon, UserIcon } from '@heroicons/vue/24/outline';
import { CarIcon } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    tasks: Array,
    encryptedIds: Object,
    filters: Object,
    availableMonths: Object,
    currentMonth: Number,
    currentYear: Number,
    userRole: String
});

// Mapping status ke label bahasa Indonesia
const statusLabel = (status) => {
   switch (status) {
        case 'draft': return 'Dibuat';
        case 'in_progress': return 'Dalam Proses';
        case 'pending': return 'Menunggu';
        case 'pending_review': return 'Menunggu Review';
        case 'approved': return 'Disetujui';
        case 'rejected': return 'Ditolak';
        case 'revision': return 'Revisi';
        case 'completed': return 'Selesai';
        case 'cancelled': return 'Dibatalkan';
        default: return status;
    }
};

// Status badge color
const getStatusColor = (status) => {
    switch (status) {
        case 'approved': 
        case 'completed': return 'bg-green-100 text-green-800';
        case 'rejected': 
        case 'cancelled': return 'bg-red-100 text-red-800';
        case 'pending_review': return 'bg-purple-100 text-purple-800';
        case 'revision': return 'bg-orange-100 text-orange-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

// State untuk filter dan pencarian
const showSearch = ref(false);
const searchTerm = ref(props.filters.search);
const selectedMonth = ref(props.filters.month);
const selectedYear = ref(props.filters.year);

// Daftar bulan dalam Bahasa Indonesia
const months = [
    { value: 1, name: 'Jan' },
    { value: 2, name: 'Feb' },
    { value: 3, name: 'Mar' },
    { value: 4, name: 'Apr' },
    { value: 5, name: 'Mei' },
    { value: 6, name: 'Jun' },
    { value: 7, name: 'Jul' },
    { value: 8, name: 'Ags' },
    { value: 9, name: 'Sep' },
    { value: 10, name: 'Okt' },
    { value: 11, name: 'Nov' },
    { value: 12, name: 'Des' }
];

// Tahun yang tersedia
const availableYears = computed(() => {
    return Object.keys(props.availableMonths || {}).sort((a, b) => b - a);
});

// Label tombol berdasarkan role
const getButtonLabel = () => {
    if (props.userRole === 'quality_control') {
        return 'Review Detail';
    }
    return 'Lihat Detail';
};

// Filter data berdasarkan bulan dan tahun
const filterByMonthYear = () => {
    router.get(route('inspections.history'), {
        month: selectedMonth.value,
        year: selectedYear.value,
        search: '' // Reset search saat filter aktif
    }, {
        preserveState: true,
        replace: true
    });
};

// Pencarian dengan debounce
let searchTimeout = null;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('inspections.history'), {
            search: searchTerm.value,
            // Tidak mengirim month/year saat searching
        }, {
            preserveState: true,
            replace: true
        });
    }, 300);
};

// Aktifkan mode pencarian
const activateSearch = () => {
    showSearch.value = true;
    searchTerm.value = '';
    // Reset filter saat masuk mode pencarian
    router.get(route('inspections.history'), {
        search: ''
    }, {
        preserveState: true,
        replace: true
    });
};

// Nonaktifkan mode pencarian dan kembali ke filter
const deactivateSearch = () => {
    showSearch.value = false;
    searchTerm.value = '';
    // Kembali ke filter bulan/tahun saat ini
    router.get(route('inspections.history'), {
        month: selectedMonth.value,
        year: selectedYear.value,
        search: ''
    }, {
        preserveState: true,
        replace: true
    });
};

// Watch perubahan filter bulan/tahun
watch([selectedMonth, selectedYear], () => {
    if (!showSearch.value) {
        filterByMonthYear();
    }
});

// Format phone number for WhatsApp
const formatPhoneForWhatsApp = (phone) => {
    if (!phone) return '';
    let cleanPhone = phone.replace(/\D/g, '');
    if (cleanPhone.startsWith('0')) {
        cleanPhone = '62' + cleanPhone.substring(1);
    }
    if (!cleanPhone.startsWith('62')) {
        cleanPhone = '62' + cleanPhone;
    }
    return cleanPhone;
};

// Generate WhatsApp message
const generateWhatsAppMessage = (task) => {
    if (!task) return '';
    
    const date = new Date(task.inspection_date);
    const formattedDate = date.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    const carInfo = task.car 
        ? `${task.car.brand.name} ${task.car.model.name} ${task.car.type.name}`
        : task.car_name;
    
    return `Halo, ada update untuk inspeksi:\n\n📅 *${formattedDate}*\n🚗 *${carInfo}*\n📝 *${task.plate_number}*\n\nStatus saat ini: ${statusLabel(task.status)}\n\nMohon dicek kembali. Terima kasih!`;
};
</script>

<template>
    <AppLayout>
        <Head title="Riwayat Inspeksi" />
        <div class="w-full px-4 sm:px-6 lg:px-6 py-6">
            <!-- Header dengan role info -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                <h3 class="text-xl md:text-3xl font-bold text-gray-900 text-center sm:text-left">
                    {{ 
                        userRole === 'admin_plant' ? 'Riwayat Semua Inspeksi' :
                        userRole === 'quality_control' ? 'Riwayat Review & Revisi' :
                        'Riwayat Inspeksi Saya'
                    }}
                </h3>
            </div>

            <!-- Controls Container -->
            <div class="bg-white rounded-lg shadow-md p-3 mb-6">
                <!-- Mode Pencarian -->
                <div v-if="showSearch" class="flex items-center justify-between">
                    <div class="relative flex-1">
                        <input
                            v-model="searchTerm"
                            @input="handleSearch"
                            type="text"
                            :placeholder="userRole === 'inspector' ? 'Cari berdasarkan plat atau nama mobil...' : 'Cari berdasarkan plat, nama mobil, atau inspector...'"
                            class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            autofocus
                        />
                        <button
                            @click="deactivateSearch"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        >
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <!-- Mode Filter -->
                <div v-else class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 flex-1">
                        <FunnelIcon class="h-4 w-4 text-gray-500 flex-shrink-0" />
                        <select 
                            v-model="selectedMonth" 
                            class="px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm"
                        >
                            <option v-for="month in months" :key="month.value" :value="month.value">
                                {{ month.name }}
                            </option>
                        </select>
                        
                        <select 
                            v-model="selectedYear" 
                            class="px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 text-sm"
                        >
                            <option v-for="year in availableYears" :key="year" :value="year">
                                {{ year }}
                            </option>
                        </select>
                    </div>

                    <button
                        @click="activateSearch"
                        class="p-1 text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <MagnifyingGlassIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Info Filter Aktif -->
            <div v-if="!showSearch && searchTerm" class="bg-blue-50 border border-blue-200 rounded-md p-3 mb-4">
                <p class="text-sm text-blue-800 flex items-center gap-2">
                    <MagnifyingGlassIcon class="h-4 w-4" />
                    Menampilkan hasil pencarian: "{{ searchTerm }}"
                    <button @click="deactivateSearch" class="text-blue-600 hover:text-blue-800 ml-2">
                        × Hapus pencarian
                    </button>
                </p>
            </div>

            <!-- Jika ada tasks -->
            <div
                v-if="tasks.length > 0"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"
            >
                <div
                    v-for="task in tasks"
                    :key="task.id"
                    class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 hover:shadow-lg transition"
                >
                    <!-- Status Badge -->
                    <div class="px-4 py-2 border-b">
                        <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(task.status)]">
                            {{ statusLabel(task.status).toUpperCase() }}
                        </span>
                    </div>

                    <!-- Info Inspector (untuk admin plant dan QC) -->
                    <div v-if="(userRole === 'admin_plant' || userRole === 'quality_control') && task.user" 
                         class="px-4 py-3 bg-gray-50 border-b">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <UserIcon class="h-4 w-4 text-gray-500 mr-2" />
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Inspector</p>
                                    <p class="text-sm text-gray-600">{{ task.user.name }}</p>
                                </div>
                            </div>
                            
                            <!-- WhatsApp untuk admin plant -->
                            <a
                                v-if="userRole === 'admin_plant' && task.user.phone"
                                :href="`https://wa.me/${formatPhoneForWhatsApp(task.user.phone)}?text=${encodeURIComponent(generateWhatsAppMessage(task))}`"
                                target="_blank"
                                class="text-green-600 hover:text-green-800 transition"
                                title="Kirim pesan WhatsApp ke inspector"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.465 3.488"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    <!-- Jadwal + Link Log -->
                    <div class="p-4 flex justify-between items-start">
                        <div>
                            <div class="flex items-center mb-1">
                                <CalendarDaysIcon class="h-5 w-5 text-blue-500 mr-2" />
                                <span class="text-sm font-medium text-gray-600">Jadwal</span>
                            </div>
                            <p class="text-sm font-semibold text-blue-700 ml-7 -mt-1">
                                {{ new Date(task.inspection_date).toLocaleDateString('id-ID', {
                                    weekday: 'short', year: 'numeric', month: 'short', day: 'numeric',
                                    hour: '2-digit', minute: '2-digit'
                                }) }}
                            </p>
                        </div>

                        <!-- Link ke Log -->
                        <Link
                            :href="route('inspection.log', { inspection: encryptedIds[task.id] })"
                            class="text-xs font-semibold text-indigo-600 hover:underline"
                        >
                            Lihat Log
                        </Link>
                    </div>

                    <!-- Mobil -->
                    <div  class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                        <div class="flex items-center">
                            <CarIcon class="h-5 w-5 text-gray-500 mr-2" />
                            <div class="text-sm font-medium text-gray-800">
                               <div v-if="task.car">
                                    {{ `${task.car.brand.name} ${task.car.model.name} ${task.car.type.name} ${(task.car.cc / 1000).toFixed(1)} ${task.car.transmission} ${task.car.year}` }}
                                    <span class="text-gray-600">({{ task.car.fuel_type }})</span>
                                </div>
                                <div v-else>
                                    {{ task.car_name }}
                                </div>
                            </div>
                        </div>
                        <!-- Nomor Plat Mobil -->
                        <div class="flex items-center mt-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 mr-2">NO POLISI:</span>
                            <span class="text-sm font-bold text-gray-900">{{ task.plate_number }}</span>
                        </div>
                    </div>

                    <!-- Status & Catatan -->
                    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100">
                        <div class="flex items-center mb-1">
                        <ClipboardDocumentListIcon class="h-5 w-5 text-green-500 mr-2" />
                        <span class="text-sm font-medium text-gray-600">Status</span>
                        </div>
                        <p class="text-sm font-semibold text-gray-800 ml-7 -mt-1">
                        {{ statusLabel(task.status) }}
                        </p>
                    </div>

                    <!-- Tombol detail -->
                    <div class="p-4">
                         <Link
                            :href="route('inspections.review', { id: encryptedIds[task.id] })"
                            :class="[
                                'inline-flex items-center justify-center w-full px-3 py-2 font-medium rounded-md text-sm transition-colors',
                                userRole === 'quality_control'
                                    ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white hover:from-purple-700 hover:to-pink-700'
                                    : 'bg-gradient-to-r from-indigo-700 to-sky-600 text-white hover:from-indigo-800 hover:to-sky-700'
                            ]"
                        >
                            {{ getButtonLabel() }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Jika tidak ada tasks -->
            <div v-else class="text-center py-8 bg-white rounded-lg shadow-md">
                <p class="text-gray-500">
                    {{ searchTerm ? 'Tidak ditemukan hasil pencarian' : 'Belum ada riwayat inspeksi untuk periode ini' }}
                </p>
            </div>

            <!-- Tombol kembali -->
            <div class="text-center mt-6">
                <Link
                    :href="route('job.index')"
                    class="inline-block px-4 py-2 text-sm font-medium text-indigo-600 border border-indigo-600 rounded-md hover:bg-blue-50"
                >
                    Kembali ke Tugas
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-bounce {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}
</style>
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CardCustomer from '@/Components/Detail/CardCustomer.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { CalendarDaysIcon, ArrowRightIcon, PlusIcon, XMarkIcon, MagnifyingGlassIcon, ArrowPathIcon, UserIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import { CarIcon } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    tasks: Array,
    encryptedIds: Object,
    userRole: String,
    userId: Number,
    inspectors: Array,
    filters: Object
});

// Modal states
const showModal = ref(false);
const showCancelModal = ref(false);
const showTransferModal = ref(false);
const showSearch = ref(false);
const selectedTask = ref(null);
const cancelReason = ref('');
const transferReason = ref('');
const selectedInspector = ref('');
const searchTerm = ref(props.filters.search);

// Collapsible states
const expandedTasks = ref(new Set());

// Forms
const startForm = useForm({});
const cancelForm = useForm({ reason: '' });
const transferForm = useForm({
    new_inspector_id: '',
    transfer_reason: ''
});

// Search dengan debounce
let searchTimeout = null;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('job.index'), {
            search: searchTerm.value,
        }, {
            preserveState: true,
            replace: true
        });
    }, 300);
};

// Toggle search mode
const toggleSearch = () => {
    if (showSearch.value) {
        // Jika sedang search mode, clear search
        searchTerm.value = '';
        router.get(route('job.index'), {
            search: ''
        }, {
            preserveState: true,
            replace: true
        });
    }
    showSearch.value = !showSearch.value;
};

// Open modals
const openModal = (task) => {
    selectedTask.value = task;

    // Untuk admin_plann, tombol langsung redirect ke review page
    if (isAdminPlann.value) {
        router.get(route('inspections.review', { id: props.encryptedIds[task.id] }));
        return;
    }

    // Untuk QC: jika status pending_review, redirect ke review; jika tidak, tampilkan modal
    if (isQualityControl.value) {
        if (task.status === 'pending_review') {
            router.get(route('inspections.review', { id: props.encryptedIds[task.id] }));
            return;
        }
        // Untuk status lain, tampilkan modal konfirmasi
        showModal.value = true;
        return;
    }

    // Untuk inspector biasa, coordinator, dan admin biasa, tampilkan modal konfirmasi
    showModal.value = true;
};

const openCancelModal = (task) => {
    selectedTask.value = task;
    showCancelModal.value = true;
};

const openTransferModal = (task) => {
    selectedTask.value = task;
    showTransferModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    showCancelModal.value = false;
    showTransferModal.value = false;
    cancelReason.value = '';
    transferReason.value = '';
    selectedInspector.value = '';
};

// Submit functions
const submitCancel = () => {
    if (!selectedTask.value) return;

    cancelForm.reason = cancelReason.value;
    cancelForm.post(route('inspections.cancel', {
        inspection: props.encryptedIds[selectedTask.value.id]
    }), {
        onSuccess: () => {
            closeModal();
            cancelReason.value = '';
        }
    });
};

const submitStartInspection = () => {
    if (!selectedTask.value) return;

    startForm.get(route('inspections.start', {
        inspection: props.encryptedIds[selectedTask.value.id]
    }), {
        onFinish: () => {
            closeModal();
        }
    });
};

const submitTransfer = () => {
    if (!selectedTask.value) return;

    transferForm.new_inspector_id = selectedInspector.value;
    transferForm.transfer_reason = transferReason.value;
    
    transferForm.post(route('inspections.transfer', {
        inspection: props.encryptedIds[selectedTask.value.id]
    }), {
        onSuccess: () => {
            closeModal();
            transferReason.value = '';
            selectedInspector.value = '';
        }
    });
};

// Computed properties untuk role checking
const isAdminPlann = computed(() => props.userRole === 'admin_plann');
const isInspector = computed(() => props.userRole === 'inspector');
const isQualityControl = computed(() => props.userRole === 'quality_control');
const isInspectorCoordinator = computed(() => props.userRole === 'coordinator');

// Cek apakah user adalah admin biasa (bukan admin_plann)
const isRegularAdmin = computed(() => props.userRole === 'Admin');

// Cek apakah role yang diizinkan untuk melihat warning data hilang
const canSeeDataWarnings = computed(() => {
    return ['Admin', 'coordinator', 'admin_plann', 'admin_region'].includes(props.userRole);
});

// Cek apakah tombol disabled untuk inspector dan coordinator
const isButtonDisabled = (task) => {
    // Admin biasa bisa mengakses semua
    if (isRegularAdmin.value) return false;
    
    // Admin_plann tidak perlu tombol action
    if (isAdminPlann.value) return false;
    
    // QC bisa mengakses semua review
    if (isQualityControl.value) return false;
    
    // Inspector coordinator bisa mengakses semua (tidak ada batasan)
    if (isInspectorCoordinator.value) return false;
    
    // Untuk inspector biasa: cek jika ada task aktif selain task saat ini
    if (isInspector.value) {
        const hasActiveTask = props.tasks.some(t => 
            (t.status === 'in_progress' || t.status === 'revision') && t.id !== task.id
        );
        return hasActiveTask && !(task.status === 'in_progress' || task.status === 'revision');
    }
    
    return false;
};

// Label tombol berdasarkan role dan status
const getButtonLabel = (task) => {
    if (isAdminPlann.value) {
        return 'Lihat Detail';
    }

    if (isQualityControl.value || isRegularAdmin.value || isInspectorCoordinator.value) {
        // QC, Admin biasa, dan Inspector coordinator bisa melakukan semua action seperti inspector
        if (task.status === 'draft') return 'Mulai Inspeksi';
        if (task.status === 'in_progress') return 'Lanjutkan Inspeksi';
        if (task.status === 'pending_review') return 'Periksa Laporan';
        if (task.status === 'revision') return 'Lanjutkan Revisi';
        if (task.status === 'pending') return 'Lanjutkan Inspeksi';
        return 'Detail';
    }

    // Inspector (default)
    if (task.status === 'draft') return 'Mulai Inspeksi';
    if (task.status === 'in_progress') return 'Lanjutkan Inspeksi';
    if (task.status === 'pending_review') return 'Periksa Laporan';
    if (task.status === 'revision') return 'Lanjutkan Revisi';
    if (task.status === 'pending') return 'Lanjutkan Inspeksi';
    return 'Detail';
};

const getButtonProses = (task) => {
    if (isAdminPlann.value || isQualityControl.value) {
        return 'Memuat...';
    }
    
    if (task.status === 'draft') return 'Memuat Halaman Inspeksi...';
    if (task.status === 'in_progress') return 'Membuka Halaman Inspeksi...';
    if (task.status === 'pending_review') return 'Memuat Halaman Review...';
    if (task.status === 'revision') return 'Memuat Halaman Revisi...';
    if (task.status === 'pending') return 'Memuat Halaman Inspeksi...';
    return 'Memuat...';
};

// Generate WhatsApp message untuk inspector
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
        ? `${task.car.brand.name} ${task.car.model.name} ${task.car.type.name} ${(task.car.cc / 1000).toFixed(1)}L ${task.car.transmission} ${task.car.year}`
        : task.car_name;
    
    return `Halo, ada jadwal inspeksi baru:\n\n📅 *${formattedDate}*\n🚗 *${carInfo}*\n📝 *${task.plate_number}*\n\nTolong di bantu untuk melakukan inspeksi sesuai jadwal. Terima kasih!`;
};

// Format phone number for WhatsApp
const formatPhoneForWhatsApp = (numberPhone) => {
    if (!numberPhone) return '';
    // Remove non-numeric characters
    let cleanPhone = numberPhone.replace(/\D/g, '');
    // Remove leading 0 if present
    if (cleanPhone.startsWith('0')) {
        cleanPhone = '62' + cleanPhone.substring(1);
    }
    // Ensure it starts with 62
    if (!cleanPhone.startsWith('62')) {
        cleanPhone = '62' + cleanPhone;
    }
    return cleanPhone;
};

// Cek apakah bisa transfer (hanya admin plant untuk status draft)
const canTransfer = (task) => {
    return isAdminPlann.value && task.status === 'draft';
};

// Cek apakah show tombol batal (untuk inspector, coordinator, admin, admin_plann, dan quality_control)
const showCancelButton = (task) => {
    const canCancelRoles = ['inspector', 'coordinator', 'Admin', 'admin_plann', 'quality_control'];
    return canCancelRoles.includes(props.userRole) &&
           ['draft', 'in_progress', 'pending', 'revision'].includes(task.status);
};

// Status badge color
const getStatusColor = (status) => {
    switch (status) {
        case 'draft': return 'bg-gray-100 text-gray-800';
        case 'in_progress': return 'bg-blue-100 text-blue-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'pending_review': return 'bg-purple-100 text-purple-800';
        case 'revision': return 'bg-orange-100 text-orange-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

// Cek apakah bisa melakukan inspeksi (semua role kecuali admin_plann)
const canInspect = computed(() => {
    return !isAdminPlann.value;
});

// Cek apakah tombol utama perlu modal
const needsModal = (task) => {
    // Admin_plann dan QC tidak perlu modal
    if (isAdminPlann.value || isQualityControl.value) return false;
    
    // Inspector, coordinator, dan admin biasa perlu modal untuk konfirmasi
    return ['inspector', 'coordinator', 'Admin'].includes(props.userRole);
};

// Get seller dari customer
const getSeller = computed(() => {
  if (props.tasks.customer && props.tasks.customer.sellers) {
    return props.tasks.customer.sellers.find(seller => seller.tasks_id === props.tasks.id) || 
           props.tasks.customer.sellers[0] || 
           null;
  }
  return null;
});

// Toggle expand/collapse for task details
const toggleTaskExpansion = (taskId) => {
    if (expandedTasks.value.has(taskId)) {
        expandedTasks.value.delete(taskId);
    } else {
        expandedTasks.value.add(taskId);
    }
};

// Check if task is expanded
const isTaskExpanded = (taskId) => {
    return expandedTasks.value.has(taskId);
};

// Watch search term
watch(searchTerm, () => {
    handleSearch();
});

// Get warning message for missing data
const getWarningMessage = (task) => {
    const noCustomer = !task.customer;
    const noTransaction = !task.transaction;
    if (noCustomer && noTransaction) {
        return 'Belum ada data cust dan transaksi';
    } else if (noCustomer) {
        return 'Belum ada data cust';
    } else if (noTransaction) {
        return 'Belum ada transaksi';
    }
    return '';
};
</script>

<template>
    <AppLayout>
        <Head title="Tugas" />
        <div class="w-full px-4 sm:px-6 lg:px-6 py-6">
            <!-- Header dengan role info -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                <h3 class="text-xl md:text-3xl font-bold text-gray-900 text-center sm:text-left">
                    {{ 
                        isAdminPlann ? 'Monitoring Tugas Inspeksi' :
                        isQualityControl ? 'Review & Revisi Inspeksi' :
                        'Tugas Inspeksi'
                    }}
                    <span v-if="isInspector" class="text-lg text-gray-600 block sm:inline sm:ml-2">- Saya</span>
                    <span v-if="isInspectorCoordinator" class="text-lg text-gray-600 block sm:inline sm:ml-2">- Coordinator</span>
                    <span v-if="isRegularAdmin" class="text-lg text-gray-600 block sm:inline sm:ml-2">- Admin</span>
                </h3>
                
                <!-- Search untuk admin plant dan QC -->
                <div v-if="isAdminPlann || isQualityControl" 
                     class="flex items-center gap-2">
                    <div v-if="showSearch" class="relative flex-1">
                        <input
                            v-model="searchTerm"
                            type="text"
                            placeholder="Cari plat/nama mobil/inspector..."
                            class="w-full px-4 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            autofocus
                        />
                        <button
                            @click="toggleSearch"
                            class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                        >
                            <XMarkIcon class="h-5 w-5" />
                        </button>
                    </div>
                    <button
                        @click="toggleSearch"
                        class="p-2 text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <MagnifyingGlassIcon class="h-5 w-5" />
                    </button>
                </div>
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
                    <div class="px-4 py-2 border-b flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(task.status)]">
                                {{ task.status.replace('_', ' ').toUpperCase() }}
                            </span>

                            <!-- Warning untuk data customer/transaksi hilang -->
                            <Link
                                v-if="canSeeDataWarnings && (!task.customer || !task.transaction)"
                                :href="route('inspections.review', { id: encryptedIds[task.id] })"
                                class="cursor-pointer"
                            >
                                <ExclamationTriangleIcon
                                    class="h-5 w-5 text-yellow-500"
                                    :title="getWarningMessage(task)"
                                />
                            </Link>
                        </div>
                    </div>

                    <!-- Info Inspector (untuk admin plant dan QC) -->
                    <div v-if="(isAdminPlann || isQualityControl) && task.user" 
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
                                v-if="isAdminPlann && task.user.numberPhone"
                                :href="`https://wa.me/${formatPhoneForWhatsApp(task.user.numberPhone)}?text=${encodeURIComponent(generateWhatsAppMessage(task))}`"
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
                    <div
                        @click="toggleTaskExpansion(task.id)"
                        class="px-4 py-3 bg-gray-50 border-t border-gray-100 cursor-pointer hover:bg-gray-100 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center flex-1">
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
                            <!-- Expand/Collapse Icon -->
                            <svg
                                :class="['h-5 w-5 text-gray-400 transition-transform', isTaskExpanded(task.id) ? 'rotate-180' : '']"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>

                        <!-- Nomor Plat Mobil -->
                        <div class="flex items-center mt-2">
                            <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 mr-2">NO POLISI: </span>
                            <span class="text-sm font-bold text-gray-900">{{ task.plate_number }}</span>
                        </div>
                    </div>

                    <!-- Collapsible Content -->
                    <div v-show="isTaskExpanded(task.id)" class="transition-all duration-300 ease-in-out">
                        <!-- Informasi Customer & Transaksi -->
                        <div class="px-4 py-3 bg-white border-t border-gray-100">
                            <!-- Data Customer -->
                            <CardCustomer
                                :customer="task.customer"
                                :seller="task.customer?.sellers?.find(s => s.inspection_id === task.id) ||  null"
                                :inspection="task"
                                :userRole="props.userRole"
                            />

                            <!-- Data Transaksi -->
                            <div
                                v-if="task.transaction"
                                class="p-2 rounded"
                                :class="{
                                'bg-green-50 border border-green-200': task?.transaction.status === 'paid',
                                'bg-yellow-50 border border-yellow-200': task?.transaction?.status === 'pending'
                                }"
                            >
                                <p class="text-xs font-medium text-gray-500">Pembayaran</p>
                                <div class="grid grid-cols-2 gap-1 text-sm">
                                <span>Status:</span>
                                <span :class="{
                                    'text-green-600 font-semibold': task?.transaction.status === 'paid',
                                    'text-yellow-600 font-semibold': task?.transaction?.status === 'pending'
                                }">
                                    {{ task?.transaction.status === 'paid' ? 'Lunas' : 'Menunggu' }}
                                </span>
                                </div>
                            </div>
                            <div
                                v-else
                                class="p-2 bg-yellow-50 border border-yellow-200 rounded"
                            >
                                <p class="text-xs text-yellow-700">Data transaksi belum tersedia</p>
                            </div>
                        </div>

                        <!-- Buttons Container -->
                        <div class="p-4 flex space-x-2">
                            <!-- Tombol Batal (untuk inspector, coordinator, dan admin biasa) -->
                            <button
                                v-if="showCancelButton(task)"
                                @click.stop="openCancelModal(task)"
                                type="button"
                                class="px-3 py-2 bg-gray-200 text-gray-700 font-medium rounded-md text-sm hover:bg-gray-300 transition-colors"
                            >
                                Batal
                            </button>

                            <!-- Tombol Utama -->
                            <button
                                @click="openModal(task)"
                                :disabled="isButtonDisabled(task)"
                                :class="[
                                    'flex-1 inline-flex items-center justify-center px-3 py-2 font-medium rounded-md text-sm transition-colors',
                                    isAdminPlann
                                        ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700'
                                        : isQualityControl
                                            ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white hover:from-purple-700 hover:to-pink-700'
                                            : !isButtonDisabled(task)
                                                ? 'bg-gradient-to-r from-indigo-700 to-sky-600 text-white hover:from-indigo-800 hover:to-sky-700'
                                                : 'bg-gray-300 text-gray-600 cursor-not-allowed'
                                ]"
                            >
                                {{ getButtonLabel(task) }}
                                <ArrowRightIcon v-if="!isAdminPlann && !isQualityControl && !isButtonDisabled(task)" class="ml-2 h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Jika tidak ada tasks -->
            <div v-else class="text-center py-8">
                <p class="text-gray-500">
                    {{ 
                        isAdminPlann ? 'Tidak ada tugas inspeksi yang perlu dimonitor.' :
                        isQualityControl ? 'Tidak ada laporan yang perlu direview.' :
                        'Tidak ada tugas inspeksi yang tersedia.'
                    }}
                </p>
            </div>

            <!-- Link tambahan untuk melihat inspeksi lainnya -->
            <div class="text-center mt-6">
                <Link
                    :href="route('inspections.history')"
                    class="inline-block px-4 py-2 text-sm font-medium text-indigo-600 border border-indigo-600 rounded-md hover:bg-blue-50"
                >
                    Lihat Riwayat Inspeksi
                </Link>
            </div>
        </div>

        <!-- Tombol Mengambang untuk Membuat Inspeksi Baru (hanya admin_plann) -->
        <Link
            v-if="isAdminPlann"
            :href="route('inspections.create.new')"
            class="fixed bottom-24 right-6 z-40 p-4 bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white rounded-full transition-colors duration-200 animate-bounce"
            title="Buat Inspeksi Baru"
        >
            <PlusIcon class="h-6 w-6" />
        </Link>

        <!-- Modal Konfirmasi Mulai/Lanjutkan Inspeksi (untuk inspector, coordinator, admin biasa, dan QC) -->
        <div
            v-if="showModal && (isInspector || isInspectorCoordinator || isRegularAdmin || isQualityControl)"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50"
        >
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Konfirmasi</h3>

                    <!-- Info Mobil -->
                    <div  class="flex items-start mb-4">
                        <CarIcon class="h-8 w-8 text-blue-500 mr-3 mt-1" />
                        <div>
                            <div v-if="selectedTask?.car">
                                <p class="font-medium text-gray-800">
                                    {{ `${selectedTask.car.brand.name} ${selectedTask.car.model.name} ${selectedTask.car.type.name}` }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ selectedTask.car.cc }} • {{ selectedTask.car.transmission }} •
                                    {{ selectedTask.car.year }}
                                    <span class="text-gray-500">({{ selectedTask.car.fuel_type }})</span>
                                </p>
                            </div>
                            <div v-else class="text-sm font-medium text-gray-800">
                                {{ selectedTask?.car_name }}
                            </div>
                            <!-- Nomor Plat Mobil di Modal -->
                            <div class="flex items-center mt-2">
                                <span class="text-sm font-semibold uppercase tracking-wide text-gray-500 mr-2">no polisi:</span>
                                <span class="text-base font-bold text-gray-900">{{ selectedTask?.plate_number }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Pesan konfirmasi -->
                    <p class="text-gray-600 mb-6">
                        Anda yakin ingin {{ getButtonLabel(selectedTask).toLowerCase() }}
                        inspeksi ini
                        <span v-if="selectedTask?.category" class="font-semibold text-gray-800">
                            berdasarkan Type Inspek: {{ selectedTask.category.name }}
                        </span>?
                    </p>

                    <!-- Tombol -->
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeModal"
                            type="button"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Batal
                        </button>
                        <!-- Tombol "Mulai Inspeksi" yang diperbarui -->
                        <button
                            @click="submitStartInspection"
                            type="button"
                            :disabled="startForm.processing"
                            class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white border border-transparent rounded-md text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="startForm.processing">{{ getButtonProses(selectedTask) }}</span>
                            <span v-else>{{ getButtonLabel(selectedTask) }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Transfer Tugas (hanya admin plant) -->
        <div
            v-if="showTransferModal && isAdminPlann"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50"
        >
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Alihkan Tugas Inspeksi</h3>
                        <button
                            @click="closeModal"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <XMarkIcon class="h-6 w-6" />
                        </button>
                    </div>

                    <!-- Info Tugas -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-start">
                            <CarIcon class="h-6 w-6 text-gray-500 mr-3 mt-0.5" />
                            <div>
                                <p class="font-medium text-gray-800">{{ selectedTask?.plate_number }}</p>
                                <p class="text-sm text-gray-600">{{ selectedTask?.car_name }}</p>
                                <p class="text-sm text-gray-500 mt-1">
                                    Inspector saat ini: 
                                    <span class="font-medium">{{ selectedTask?.user?.name || 'Belum ditugaskan' }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Transfer -->
                    <div class="space-y-4">
                        <div>
                            <label for="inspector" class="block text-sm font-medium text-gray-700 mb-1">
                                Pilih Inspector Baru *
                            </label>
                            <select
                                id="inspector"
                                v-model="selectedInspector"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                            >
                                <option value="">-- Pilih Inspector --</option>
                                <option v-for="inspector in inspectors" :key="inspector.id" :value="inspector.id">
                                    {{ inspector.name }} ({{ inspector.email }})
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="transferReason" class="block text-sm font-medium text-gray-700 mb-1">
                                Alasan Pengalihan *
                            </label>
                            <textarea
                                id="transferReason"
                                v-model="transferReason"
                                rows="3"
                                required
                                placeholder="Masukkan alasan mengapa tugas ini dialihkan..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                            ></textarea>
                            <p v-if="transferForm.errors.transfer_reason" class="text-red-500 text-sm mt-1">
                                {{ transferForm.errors.transfer_reason }}
                            </p>
                        </div>
                    </div>

                    <!-- Tombol -->
                    <div class="flex justify-end space-x-3 mt-6">
                        <button
                            @click="closeModal"
                            type="button"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Batal
                        </button>
                        <button
                            @click="submitTransfer"
                            :disabled="!selectedInspector || !transferReason.trim() || transferForm.processing"
                            class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white border border-transparent rounded-md text-sm font-medium hover:from-green-700 hover:to-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="transferForm.processing">
                                <ArrowPathIcon class="h-4 w-4 animate-spin inline mr-2" />
                                Mengalihkan...
                            </span>
                            <span v-else>Alihkan Tugas</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Batalkan Inspeksi -->
        <div
            v-if="showCancelModal && (isInspector || isInspectorCoordinator || isRegularAdmin || isAdminPlann || isQualityControl)"
            class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50"
        >
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Batalkan Inspeksi</h3>
                        <button
                            @click="closeModal"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <XMarkIcon class="h-6 w-6" />
                        </button>
                    </div>

                    <!-- Info Mobil -->
                    <div class="flex items-start mb-4">
                        <CarIcon class="h-8 w-8 text-red-500 mr-3 mt-1" />
                        <div>
                            <div v-if="selectedTask?.car">
                                <p class="font-medium text-gray-800">
                                    {{ `${selectedTask.car.brand.name} ${selectedTask.car.model.name} ${selectedTask.car.type.name}` }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    {{ selectedTask.car.cc }} • {{ selectedTask.car.transmission }} •
                                    {{ selectedTask.car.year }}
                                    <span class="text-gray-500">({{ selectedTask.car.fuel_type }})</span>
                                </p>
                            </div>
                            <div v-else class="text-sm font-medium text-gray-800">
                                {{ selectedTask?.car_name }}
                            </div>
                            <!-- Nomor Plat Mobil -->
                            <div class="flex items-center mt-2">
                                <span class="text-sm font-semibold uppercase tracking-wide text-gray-500 mr-2">no polisi:</span>
                                <span class="text-base font-bold text-gray-900">{{ selectedTask?.plate_number }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Form Alasan -->
                    <div class="mb-6">
                        <label for="cancelReason" class="block text-sm font-medium text-gray-700 mb-2">
                            Alasan Pembatalan *
                        </label>
                        <textarea
                            id="cancelReason"
                            v-model="cancelReason"
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Masukkan alasan pembatalan inspeksi..."
                            required
                        ></textarea>
                        <p v-if="cancelForm.errors.reason" class="text-red-500 text-sm mt-1">
                            {{ cancelForm.errors.reason }}
                        </p>
                    </div>

                    <!-- Tombol -->
                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeModal"
                            type="button"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Tutup
                        </button>
                        <button
                            @click="submitCancel"
                            :disabled="!cancelReason.trim() || cancelForm.processing"
                            class="px-4 py-2 bg-red-600 text-white border border-transparent rounded-md text-sm font-medium hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="cancelForm.processing">Memproses...</span>
                            <span v-else>Batalkan Inspeksi</span>
                        </button>
                    </div>
                </div>
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
<template>
    <AppLayout title="Dashboard Koordinator">
        <Head title="Dashboard Koordinator" />
        <!-- Kontainer utama untuk konten halaman -->
        <div class="flex-1 overflow-y-auto">
            <!-- Header -->
            <header class="bg-white shadow-md p-4 md:p-6 flex items-center justify-between">
                <h1 class="text-xl font-bold text-gray-900">Dashboard Koordinator</h1>
                <div class="flex items-center space-x-4 text-sm text-gray-600">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Wilayah: {{ region.name }}</span>
                </div>
            </header>

            <main class="w-full py-4 sm:px-4 lg:px-4">
                <!-- Bagian Filter -->
                <div class="px-4 sm:px-0 mb-4">
                    <div class="bg-white overflow-hidden shadow-lg rounded-2xl">
                        <!-- Header Filter dengan Toggle -->
                        <div class="flex items-center justify-between p-4 border-b border-gray-200">
                            <div class="flex items-center space-x-2">
                                <svg class="h-5 w-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900">Filter Inspeksi</h3>
                            </div>
                            <div class="flex items-center space-x-3">
                                <!-- Reset Button -->
                                <button
                                    @click="resetFilters"
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200"
                                    :disabled="loading"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                    </svg>
                                    Reset
                                </button>
                                
                                <!-- Toggle Button -->
                                <button
                                    @click="filterExpanded = !filterExpanded"
                                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200"
                                >
                                    <span class="mr-1">{{ filterExpanded ? 'Tutup' : 'Buka' }}</span>
                                    <svg 
                                        class="w-4 h-4 transition-transform duration-200" 
                                        :class="{ 'rotate-180': filterExpanded }" 
                                        fill="currentColor" 
                                        viewBox="0 0 20 20"
                                    >
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Kontainer Filter (Expandable) -->
                        <div v-show="filterExpanded" class="p-4 border-b border-gray-200">
                            <!-- Kontainer untuk Filter -->
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-4">
                                <!-- Filter Region (Admin only) -->
                                <div v-if="isAdmin">
                                    <label for="region" class="block text-sm font-medium text-gray-700 mb-1">Region</label>
                                    <select 
                                        id="region"
                                        v-model="filters.region_id"
                                        @change="handleRegionChange"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="all">Semua Region</option>
                                        <option v-for="reg in allRegions" :key="reg.id" :value="reg.id">
                                            {{ reg.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Filter User (Admin only) -->
                                <div v-if="isAdmin">
                                    <label for="user" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                                    <select 
                                        id="user"
                                        v-model="filters.user_id"
                                        @change="updateFilters"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">Semua User</option>
                                        <option v-for="teamUser in regionUsers" :key="teamUser.id" :value="teamUser.id">
                                            {{ teamUser.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Filter Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select 
                                        id="status" 
                                        v-model="filters.status"
                                        @change="updateFilters"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">Semua Status</option>
                                        <option value="draft">Dibuat</option>
                                        <option value="in_progress">Dalam Proses</option>
                                        <option value="pending">Tertunda</option>
                                        <option value="pending_review">Menunggu Tinjauan</option>
                                        <option value="approved">Disetujui</option>
                                        <option value="rejected">Ditolak</option>
                                        <option value="revision">Revisi</option>
                                        <option value="completed">Selesai</option>
                                        <option value="cancelled">Dibatalkan</option>
                                    </select>
                                </div>

                                <!-- Filter Tahun -->
                                <div>
                                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                                    <select 
                                        id="year" 
                                        v-model="filters.year"
                                        @change="handleYearChange"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">Semua Tahun</option>
                                        <option v-for="year in availableYears" :key="year" :value="year">
                                            {{ year }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Filter Bulan -->
                                <div>
                                    <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                                    <select 
                                        id="month" 
                                        v-model="filters.month"
                                        @change="updateFilters"
                                        :disabled="!filters.year"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="">Semua Bulan</option>
                                        <option v-for="month in availableMonths" :key="month.value" :value="month.value">
                                            {{ month.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Input Pencarian dan Button Apply -->
                            <div class="flex flex-col md:flex-row md:items-end gap-4">
                                <div class="flex-1">
                                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                                    <input 
                                        type="text" 
                                        id="search" 
                                        v-model="filters.search"
                                        @input="debouncedUpdateFilters"
                                        placeholder="Cari berdasarkan mobil, plat nomor, atau inspektor..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    />
                                </div>
                                
                                <!-- Apply Filter Button
                                <div>
                                    <button
                                        @click="applyFilters"
                                        :disabled="loading"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                                    >
                                        <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg v-else class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                                        </svg>
                                        {{ loading ? 'Memproses...' : 'Terapkan Filter' }}
                                    </button>
                                </div> -->
                            </div>
                            
                            <!-- Filter Active Tags -->
                            <div v-if="activeFilterCount > 0" class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex items-center">
                                    <span class="text-sm text-gray-500 mr-2">Filter aktif:</span>
                                    <div class="flex flex-wrap gap-2">
                                        <!-- Status Filter Tag -->
                                        <span v-if="filters.status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Status: {{ getStatusLabel(filters.status) }}
                                            <button @click="removeFilter('status')" class="ml-1 text-blue-600 hover:text-blue-800">
                                                ×
                                            </button>
                                        </span>
                                        
                                        <!-- Year Filter Tag -->
                                        <span v-if="filters.year" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Tahun: {{ filters.year }}
                                            <button @click="removeFilter('year')" class="ml-1 text-green-600 hover:text-green-800">
                                                ×
                                            </button>
                                        </span>
                                        
                                        <!-- Month Filter Tag -->
                                        <span v-if="filters.month" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Bulan: {{ getMonthLabel(filters.month) }}
                                            <button @click="removeFilter('month')" class="ml-1 text-purple-600 hover:text-purple-800">
                                                ×
                                            </button>
                                        </span>
                                        
                                        <!-- Region Filter Tag -->
                                        <span v-if="filters.region_id && filters.region_id !== 'all'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            Region: {{ getRegionLabel(filters.region_id) }}
                                            <button @click="removeFilter('region_id')" class="ml-1 text-indigo-600 hover:text-indigo-800">
                                                ×
                                            </button>
                                        </span>
                                        
                                        <!-- User Filter Tag -->
                                        <span v-if="filters.user_id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            User: {{ getUserLabel(filters.user_id) }}
                                            <button @click="removeFilter('user_id')" class="ml-1 text-amber-600 hover:text-amber-800">
                                                ×
                                            </button>
                                        </span>
                                        
                                        <!-- Search Filter Tag -->
                                        <span v-if="filters.search" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Pencarian: "{{ filters.search }}"
                                            <button @click="removeFilter('search')" class="ml-1 text-red-600 hover:text-red-800">
                                                ×
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gambaran Umum Statistik -->
                <div class="px-4 sm:px-0 mb-4">
                    <div class="flex flex-col gap-4">
                        <!-- Kartu Total Inspeksi (Lebar Penuh) -->
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                            <dt class="text-sm font-medium opacity-80">Total Inspeksi</dt>
                            <dd class="text-3xl font-semibold">{{ stats.total }}</dd>
                        </div>
                        
                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-4 lg:grid-cols-5 gap-2">
                            <!-- Dibuat -->
                            <div class="bg-yellow-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Dibuat</dt>
                                <dd class="text-2xl font-semibold text-yellow-600">{{ stats.draft }}</dd>
                            </div>

                            <!-- Dalam Proses -->
                            <div class="bg-blue-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Dalam Proses</dt>
                                <dd class="text-2xl font-semibold text-blue-600">{{ stats.in_progress }}</dd>
                            </div>

                            <!-- Tertunda -->
                            <div class="bg-amber-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Tertunda</dt>
                                <dd class="text-2xl font-semibold text-amber-600">{{ stats.pending }}</dd>
                            </div>

                            <!-- Ditinjau -->
                            <div class="bg-indigo-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Ditinjau</dt>
                                <dd class="text-2xl font-semibold text-indigo-600">{{ stats.pending_review }}</dd>
                            </div>

                            <!-- Disetujui -->
                            <div class="bg-green-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Disetujui</dt>
                                <dd class="text-2xl font-semibold text-green-600">{{ stats.approved }}</dd>
                            </div>

                            <!-- Ditolak -->
                            <div class="bg-red-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Ditolak</dt>
                                <dd class="text-2xl font-semibold text-red-600">{{ stats.rejected }}</dd>
                            </div>

                            <!-- Revisi -->
                            <div class="bg-purple-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Revisi</dt>
                                <dd class="text-2xl font-semibold text-purple-600">{{ stats.revision }}</dd>
                            </div>

                            <!-- Dibatalkan -->
                            <div class="bg-gray-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Dibatalkan</dt>
                                <dd class="text-2xl font-semibold text-gray-600">{{ stats.cancelled }}</dd>
                            </div>

                            <!-- Selesai -->
                            <div class="bg-emerald-100 p-2 rounded-2xl shadow-lg text-center transition-transform transform hover:scale-105 duration-300">
                                <dt class="text-sm font-medium text-gray-700 truncate">Selesai</dt>
                                <dd class="text-2xl font-semibold text-emerald-600">{{ stats.completed }}</dd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Inspeksi -->
                <div class="px-4 sm:px-0">
                    <div class="bg-white shadow-lg overflow-hidden rounded-2xl">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                            Detail Mobil
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                            Inspektur
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                            Tanggal Inspeksi
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                            Status Inspection
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                            Status Pembayaran
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">
                                            Tindakan
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="inspections.data && inspections.data.length === 0">
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <p class="mt-2">Tidak ada data inspeksi yang ditemukan</p>
                                            <button 
                                                v-if="activeFilterCount > 0"
                                                @click="resetFilters"
                                                class="mt-2 inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200"
                                            >
                                                Reset filter untuk melihat semua data
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-for="inspection in inspections.data" :key="inspection.id">
                                        <td class="px-4 py-4">
                                            <div class="min-w-0">
                                                <div class="text-sm font-medium text-gray-900 truncate">{{ inspection.car_name }}</div>
                                                <div class="text-sm text-gray-500">{{ inspection.plate_number }}</div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="text-sm text-gray-900">{{ inspection.user?.name }}</div>
                                            <div class="text-sm text-gray-500 truncate max-w-xs">{{ inspection.user?.email }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ formatDate(inspection.inspection_date) }}</div>
                                            <div class="text-sm text-gray-500">{{ formatTime(inspection.inspection_date) }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span :class="`px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass(inspection.status)}`">
                                                {{ formatStatus(inspection.status) ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span 
                                                :class="`px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass(inspection.transaction?.status ?? 'unpaid')}`"
                                            >
                                                {{ inspection.transaction?.status ? formatStatus(inspection.transaction.status) : 'Belum Ada Transaksi' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex flex-col sm:flex-row gap-2">
                                                <Link 
                                                    :href="route('inspections.review', { id: encryptedIds[inspection.id] })" 
                                                    class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200 text-xs sm:text-sm"
                                                >
                                                    Lihat
                                                </Link>

                                                <Link 
                                                    v-if="inspection.status === 'draft' || inspection.user_id == null" 
                                                    :href="route('coordinator.inspections.show', { id: encryptedIds[inspection.id] })"
                                                    class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-white bg-green-600 hover:bg-green-700 transition-colors duration-200 text-xs sm:text-sm"
                                                >
                                                    Tugaskan
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="bg-white px-4 py-3 border-t border-gray-200">
                            <!-- Mobile Pagination -->
                            <div v-if="inspections.links && inspections.links.length > 3" class="flex items-center justify-between sm:hidden">
                                <button
                                    @click="updatePage(inspections.current_page - 1)"
                                    :disabled="!inspections.prev_page_url"
                                    :class="['relative inline-flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors',
                                        inspections.prev_page_url 
                                            ? 'text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 cursor-pointer' 
                                            : 'text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed'
                                    ]"
                                >
                                    <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    Sebelumnya
                                </button>
                                
                                <span class="text-sm text-gray-700">
                                    Hal. {{ inspections.current_page }} dari {{ inspections.last_page }}
                                </span>
                                
                                <button
                                    @click="updatePage(inspections.current_page + 1)"
                                    :disabled="!inspections.next_page_url"
                                    :class="['relative inline-flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors',
                                        inspections.next_page_url 
                                            ? 'text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 cursor-pointer' 
                                            : 'text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed'
                                    ]"
                                >
                                    Selanjutnya
                                    <svg class="w-5 h-5 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Desktop Pagination -->
                            <div v-if="inspections.links && inspections.links.length > 3" class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Menampilkan
                                        <span class="font-medium">{{ inspections.from || 0 }}</span>
                                        sampai
                                        <span class="font-medium">{{ inspections.to || 0 }}</span>
                                        dari
                                        <span class="font-medium">{{ inspections.total || 0 }}</span>
                                        hasil
                                    </p>
                                </div>
                                
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                        <!-- Previous Page -->
                                        <button
                                            @click="updatePage(inspections.current_page - 1)"
                                            :disabled="!inspections.prev_page_url"
                                            :class="['relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 text-sm font-medium transition-colors',
                                                !inspections.prev_page_url 
                                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                                    : 'bg-white text-gray-500 hover:bg-gray-50 cursor-pointer'
                                            ]"
                                        >
                                            <span class="sr-only">Sebelumnya</span>
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        
                                        <!-- Page Numbers -->
                                        <template v-for="(link, index) in inspections.links" :key="index">
                                            <button
                                                v-if="!['&laquo; Previous', 'Next &raquo;', '...'].includes(link.label)"
                                                @click="updatePage(parseInt(link.label))"
                                                :disabled="link.active"
                                                :class="[
                                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                                    link.active 
                                                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600 cursor-default'
                                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 cursor-pointer'
                                                ]"
                                            >
                                                {{ link.label }}
                                            </button>
                                            <span
                                                v-else-if="link.label === '...'"
                                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                                            >
                                                {{ link.label }}
                                            </span>
                                        </template>
                                        
                                        <!-- Next Page -->
                                        <button
                                            @click="updatePage(inspections.current_page + 1)"
                                            :disabled="!inspections.next_page_url"
                                            :class="['relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 text-sm font-medium transition-colors',
                                                !inspections.next_page_url 
                                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                                    : 'bg-white text-gray-500 hover:bg-gray-50 cursor-pointer'
                                            ]"
                                        >
                                            <span class="sr-only">Selanjutnya</span>
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch, reactive, computed, onMounted } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';

// Get props from Inertia
const props = defineProps({
    inspections: Object,
    encryptedIds: Object,
    filters: Object,
    stats: Object,
    region: Object,
    allRegions: Array,
    isAdmin: Boolean,
    availableYears: Array,
    availableMonths: Array,
});

// State
const filterExpanded = ref(true); // Default terbuka
const regionUsers = ref([]);
const loading = ref(false);

// Filters
const filters = reactive({
    status: props.filters.status || '',
    year: props.filters.year || '',
    month: props.filters.month || '',
    search: props.filters.search || '',
    region_id: props.filters.region_id || 'all',
    user_id: props.filters.user_id || '',
});

// Computed Properties
const activeFilterCount = computed(() => {
    let count = 0;
    if (filters.status && filters.status !== '') count++;
    if (filters.year && filters.year !== '') count++;
    if (filters.month && filters.month !== '') count++;
    if (filters.region_id && filters.region_id !== 'all') count++;
    if (filters.user_id && filters.user_id !== '') count++;
    if (filters.search && filters.search !== '') count++;
    return count;
});

// Helper functions for labels
const getStatusLabel = (status) => {
    const statusMap = {
        'draft': 'Dibuat',
        'in_progress': 'Dalam Proses',
        'pending': 'Tertunda',
        'pending_review': 'Menunggu Tinjauan',
        'approved': 'Disetujui',
        'rejected': 'Ditolak',
        'revision': 'Revisi',
        'completed': 'Selesai',
        'cancelled': 'Dibatalkan',
    };
    return statusMap[status] || status;
};

const getMonthLabel = (monthNumber) => {
    const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    return months[parseInt(monthNumber) - 1] || `Bulan ${monthNumber}`;
};

const getRegionLabel = (regionId) => {
    const region = props.allRegions.find(r => r.id == regionId);
    return region ? region.name : `Region ${regionId}`;
};

const getUserLabel = (userId) => {
    const user = regionUsers.value.find(u => u.id == userId);
    return user ? user.name : `User ${userId}`;
};

// Handle region change
const handleRegionChange = async () => {
    if (props.isAdmin) {
        loading.value = true;
        try {
            if (!filters.region_id || filters.region_id === "all") {
                const response = await axios.get(route('api.users.all'));
                regionUsers.value = response.data;
            } else {
                const response = await axios.get(route('api.region.users', { id: filters.region_id }));
                regionUsers.value = response.data;
            }
            // Reset user filter when region changes
            filters.user_id = '';
        } catch (error) {
            console.error('Error fetching users:', error);
            regionUsers.value = [];
        } finally {
            loading.value = false;
        }
    }
    updateFilters();
};

// Handle year change - update months
const handleYearChange = () => {
    // Reset month when year changes
    filters.month = '';
    updateFilters();
};

// Remove specific filter
const removeFilter = (filterName) => {
    if (filterName === 'region_id') {
        filters[filterName] = 'all';
        // Reset user when region changes
        filters.user_id = '';
        handleRegionChange();
    } else if (filterName === 'year') {
        filters[filterName] = '';
        // Reset month when year is cleared
        filters.month = '';
        updateFilters();
    } else {
        filters[filterName] = '';
        updateFilters();
    }
};

// Reset all filters
const resetFilters = () => {
    filters.status = '';
    filters.year = '';
    filters.month = '';
    filters.search = '';
    filters.region_id = 'all';
    filters.user_id = '';
    
    // Reset region users
    if (props.isAdmin) {
        handleRegionChange();
    } else {
        updateFilters();
    }
};

// Apply filters manually
const applyFilters = () => {
    updateFilters();
};

// Debounced search function
const debouncedUpdateFilters = debounce(() => {
    updateFilters();
}, 500);

// Update filters and reload page
const updateFilters = () => {
    const params = { ...filters };
    
    // Clean up empty values
    Object.keys(params).forEach(key => {
        if (params[key] === '' || params[key] === 'all') {
            delete params[key];
        }
    });
    
    // Reset to page 1 when filters change
    params.page = 1;
    
    router.get(route('coordinator.inspections.index'), params, {
        preserveState: true,
        replace: true,
        onStart: () => {
            loading.value = true;
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

// Helper function untuk update page
const updatePage = (page) => {
    if (page < 1 || page > props.inspections.last_page) return;

    const params = { ...filters, page };

    // Clean up empty values
    Object.keys(params).forEach(key => {
        if (params[key] === '' || params[key] === 'all') {
            delete params[key];
        }
    });

    router.get(route('coordinator.inspections.index'), params, {
        preserveState: true,
        replace: true,
        onStart: () => {
            loading.value = true;
        },
        onFinish: () => {
            loading.value = false;
        }
    });
};

// Format date
const formatDate = (dateString) => {
    if (!dateString) return '-';
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

// Format time
const formatTime = (dateString) => {
    if (!dateString) return '-';
    const options = { hour: '2-digit', minute: '2-digit' };
    return new Date(dateString).toLocaleTimeString('id-ID', options);
};

// Format status
const formatStatus = (status) => {
    const statusMap = {
        // Inspection Status
        'draft': 'Dibuat',
        'in_progress': 'Dalam Proses',
        'pending': 'Tertunda',
        'pending_review': 'Menunggu Tinjauan',
        'approved': 'Disetujui',
        'rejected': 'Ditolak',
        'revision': 'Revisi',
        'completed': 'Selesai',
        'cancelled': 'Dibatalkan',
        
        // Transaction Status
        'unpaid': 'Belum Bayar',
        'paid': 'Sudah Dibayar',
        'failed': 'Gagal',
        'refunded': 'Dikembalikan',
        'expired': 'Kadaluarsa',
        'released': 'Diberikan',
    };
    return statusMap[status] || status;
};

// Status class
const statusClass = (status) => {
    const classMap = {
        // Inspection Status
        'draft': 'bg-slate-400 text-white',
        'in_progress': 'bg-blue-600 text-white',
        'pending': 'bg-amber-500 text-white',
        'pending_review': 'bg-indigo-500 text-white',
        'approved': 'bg-emerald-500 text-white',
        'rejected': 'bg-red-500 text-white',
        'revision': 'bg-orange-500 text-white',
        'completed': 'bg-emerald-500 text-white',
        'cancelled': 'bg-red-500 text-white',
        
        // Transaction Status
        'unpaid': 'bg-gray-500 text-white',
        'failed': 'bg-red-500 text-white',
        'expired': 'bg-red-500 text-white',
        'paid': 'bg-emerald-500 text-white',
        'refunded': 'bg-orange-500 text-white',
        'released': 'bg-green-500 text-white',
    };
    return classMap[status] || 'bg-gray-100 text-gray-800';
};

// Initialize on mounted
onMounted(() => {
    // Initialize region users if admin
    if (props.isAdmin) {
        handleRegionChange();
    }
});
</script>

<style scoped>
.rotate-180 {
    transform: rotate(180deg);
}
</style>
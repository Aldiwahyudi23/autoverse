<template>
  <div class="p-6 bg-white rounded-lg shadow-md">
    <!-- Alert Messages -->
    <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
      <div class="flex">
        <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        <span>{{ $page.props.flash.success }}</span>
      </div>
    </div>

    <div v-if="$page.props.flash.error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
      <div class="flex">
        <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
        </svg>
        <span>{{ $page.props.flash.error }}</span>
      </div>
    </div>

    <!-- Konten Laporan -->
    <div v-if="hasDataOrImages">
      <div class="header flex flex-col gap-5 mb-6 mt-6">
        <div class="flex items-center gap-5">
          <!-- Gambar Utama -->
          <img
            v-if="coverImage && imageExists(coverImage.image_path)"
            :src="getImageUrl(coverImage.image_path)"
            alt="Foto Utama"
            class="w-40 h-40 object-cover rounded-lg border border-gray-300 cursor-pointer hover:opacity-90 transition-opacity"
            @click="openImageModal(getImageUrl(coverImage.image_path))"
          >
          <div v-else class="w-40 h-40 border border-gray-300 rounded-lg flex items-center justify-center bg-gray-100 text-gray-400">
            <span>Gambar tidak tersedia</span>
          </div>
          <div class="mt-8">
            <h2 class="text-3xl font-bold m-0 text-gray-800">{{ inspection.car_name }}</h2>
          </div>
        </div>

        <!-- Tabel Informasi Mobil -->
        <div v-if="inspection.car_id" class="car-info">
          <table class="w-full border-collapse border border-gray-300 rounded-lg overflow-hidden">
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold w-1/3 text-gray-700">ID Order</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.order_code }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold w-1/3 text-gray-700">Nomor Polisi</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.plate_number }}</td>
            </tr>
            <tr>
              <td class="p-2 border border-gray-300 font-bold text-gray-700">Merek</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.brand?.name }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold text-gray-700">Model</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.model?.name }}</td>
            </tr>
            <tr>
              <td class="p-2 border border-gray-300 font-bold text-gray-700">Tipe</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.type?.name }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold text-gray-700">CC</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.cc }}</td>
            </tr>
            <tr>
              <td class="p-2 border border-gray-300 font-bold text-gray-700">Bahan Bakar</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.fuel_type }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold text-gray-700">Transmisi</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.transmission }}</td>
            </tr>
            <tr>
              <td class="p-2 border border-gray-300 font-bold text-gray-700">Periode Model</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.production_period || '-' }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold text-gray-700">Tahun Pembuatan</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.year }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold text-gray-700">Warna</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.color }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold text-gray-700">No Rangka</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.noka }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold text-gray-700">No Mesin</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.nosin }}</td>
            </tr>
            <tr class="bg-gray-50">
              <td class="p-2 border border-gray-300 font-bold text-gray-700">KM</td>
              <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.km }}</td>
            </tr>
          </table>
        </div>

       <!-- Tabel Status Banjir dan Tabrak -->
<table v-if="inspection.settings" style="width: 100%; margin: 15px 0; border-collapse: collapse;">
  <tr>
    <!-- Kolom Kiri: Status Banjir -->
    <td style="width: 50%; padding: 6px; vertical-align: top;">
      <div style="text-align: center;">
        <img v-if="inspection.settings.conclusion.flooded === 'yes'" src="/images/icons/banjir.png" alt="Banjir" style="width: 80px; height: 80px; display: block; margin: 0 auto;">
        <img v-else src="/images/icons/aman.png" alt="Aman" style="width: 80px; height: 80px; display: block; margin: 0 auto;">
        
        <p v-if="inspection.settings.conclusion.flooded === 'yes'" style="font-weight: bold; font-size: 15px; color: #dc3545; margin-top: 8px; margin-bottom: 0;">
          Bekas Banjir
        </p>
        <p v-else style="font-weight: bold; font-size: 15px; color: #28a745; margin-top: 8px; margin-bottom: 0;">
          Bebas Banjir
        </p>
      </div>
    </td>
    
    <!-- Kolom Kanan: Status Tabrak -->
    <td style="width: 50%; padding: 6px; vertical-align: top;">
      <div style="text-align: center;">
        <template v-if="inspection.settings.conclusion.collision === 'yes'">
          <img :src="collisionImage" alt="Tabrak" style="width: 80px; height: 80px; display: block; margin: 0 auto;">
          <p :style="{ fontWeight: 'bold', fontSize: '15px', color: collisionColor, marginTop: '8px', marginBottom: '0' }">
            {{ collisionText }}
          </p>
        </template>
        <template v-else>
          <img src="/images/icons/aman.png" alt="Aman" style="width: 80px; height: 80px; display: block; margin: 0 auto;">
          <p style="font-weight: bold; font-size: 15px; color: #28a745; margin-top: 8px; margin-bottom: 0;">
            Bebas Tabrak
          </p>
        </template>
      </div>
    </td>
  </tr>
</table>

        <!-- Kesimpulan Inspeksi -->
        <div v-if="inspection.notes" class="conclusion p-4 bg-gray-50 border-l-4 border-gray-800 rounded-lg">
          <h3 class="text-lg font-bold mb-2 text-gray-800">Kesimpulan Inspeksi:</h3>
          <p class="m-0 text-gray-600">
            <div v-html="inspection.notes || '-'"></div>
          </p>
        </div>
      </div>

      <h2 class="text-2xl font-bold border-b-2 border-gray-800 pb-2 mb-6">Hasil Inspeksi</h2>

      <!-- Loop untuk setiap komponen inspeksi -->
      <div 
        v-for="group in groupedPoints" 
        :key="group.component.id"
        :class="['section mb-6', group.component.name === 'Foto Kendaraan' ? 'photo-component' : '']">

        <!-- Judul Komponen -->
        <div class="component-title bg-gray-100 px-3 py-2 border-l-4 border-gray-800 font-bold rounded-l-lg">
          {{ group.component.name || 'Tanpa Komponen' }}
        </div>

        <!-- Bagian Foto Kendaraan -->
        <div v-if="group.component.name === 'Foto Kendaraan'" class="images flex flex-wrap gap-2 mt-4">
          <div v-for="point in group.points" :key="point.id">
            <div v-for="(img, imgIndex) in point.inspection_point?.images" :key="img.id" class="image-container">
              <img
                v-if="imageExists(img.image_path)"
                :src="getImageUrl(img.image_path)"
                alt="Foto Kendaraan"
                class="w-28 h-28 object-cover border border-gray-300 rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                @click="openImageModal(getImageUrl(img.image_path))"
              >
              <div v-else class="w-28 h-28 border border-gray-300 rounded-lg flex items-center justify-center bg-gray-100 text-xs text-gray-400">
                <span>Gambar tidak ditemukan</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Bagian Poin Inspeksi non-foto -->
        <template v-else>
          <div v-for="point in group.points" :key="point.id" class="point ml-4 py-3 border-b border-gray-100">
            <template v-if="hasResult(point) || hasImage(point)">
              <!-- Nama Poin Inspeksi -->
              <span class="point-name inline-block min-w-[160px] font-bold align-top text-gray-700">
                {{ point.inspection_point?.name || '-' }}
              </span>
              
              <div class="point-content inline-block w-[calc(100%-70px)] align-top">
                <template v-if="hasResult(point)">
                  <!-- Badge Status (mendukung array dan string) -->
                  <div v-if="shouldShowStatusBadge(point)" class="status-badges-container">
                    <span
                      v-for="status in getStatusArray(point)"
                      :key="status"
                      :class="['status-badge', getStatusClass(status)]"
                    >
                      {{ status.trim() }}
                    </span>
                    <!-- Button to add repair estimation -->
                    <button
                      v-if="canEditEstimations && hasBadStatus(point) && group.component.name !== 'Dokumen'"
                      @click="openEstimationModal(point)"
                      class="ml-2 bg-orange-500 text-white px-2 py-1 rounded text-xs hover:bg-orange-600 transition-colors inline-flex items-center"
                      title="Tambah Estimasi Perbaikan"
                    >
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                      </svg>
                    </button>
                  </div>

                  <!-- Catatan (Note) -->
                  <div v-if="shouldShowNote(point)" class="point-note italic text-gray-600 my-1">
                    {{ formatNote(point) }}
                  </div>
                </template>
              </div>

              <!-- Gambar Poin Inspeksi -->
              <div v-if="shouldShowImages(point)" class="inspection-images flex flex-wrap gap-2 mt-4">
                <div v-for="(img, imgIndex) in point.inspection_point?.images" :key="img.id" class="image-container">
                  <img
                    v-if="imageExists(img.image_path)"
                    :src="getImageUrl(img.image_path)"
                    alt="image"
                    class="w-20 h-20 object-cover border border-gray-300 rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                    @click="openImageModal(getImageUrl(img.image_path))"
                  >
                  <div v-else class="w-20 h-20 border border-gray-300 rounded-lg flex items-center justify-center bg-gray-100 text-xs text-gray-400">
                    <span>Gambar tidak ditemukan</span>
                  </div>
                </div>
              </div>

              <!-- Catatan Textarea -->
              <div v-if="shouldShowTextarea(point) && hasNote(point)" class="textarea-note italic text-gray-600 my-2">
                {{ point.inspection_point?.results[0].note }}
              </div>
            </template>
          </div>
        </template>
      </div>

      <!-- Bagian Estimasi Perbaikan (Component Terpisah) -->
      <RepairEstimationSection
        :estimations="repairEstimations"
        :inspection-id="inspection.id"
        :encrypted-ids="encryptedIds"
        :can-edit="canEditEstimations"
        @update:estimations="updateEstimations"
        @update:totalCost="updateTotalCost"
      />

       <!-- Tombol Persetujuan Laporan -->
    <div v-if="canApproveReport" class="mt-2 mb-6">
      <button 
        @click="showConfirmationModal = true"
        class="bg-blue-600 text-white px-4 py-2 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
      >
        Setujui Laporan
      </button>
      <p class="text-sm text-gray-500 mt-2 italic">
        Catatan: Halaman ini hanya simulasi dan mungkin berbeda dengan tampilan file PDF. Silakan periksa lebih detail untuk memastikan tidak ada kesalahan. Jika sudah yakin, silakan setujui untuk proses selanjutnya.
      </p>
    </div>

    </div>
    
    <!-- Tampilan Jika Data Tidak Ada -->
    <div v-else class="flex flex-col items-center justify-center h-96 text-center">
      <p class="text-lg font-semibold text-gray-700">
        Data sudah tidak ada, karena sudah dijadikan PDF.
      </p>
      <a 
        :href="route('inspections.download.approved.pdf', encryptedIds)"
        class="mt-4 inline-block bg-blue-600 text-white px-6 py-3 rounded-lg shadow-md hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
      >
        Download PDF
      </a>
    </div>

    <!-- Modal Konfirmasi Setujui Laporan -->
    <div v-if="showConfirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl w-96 max-w-sm">
        <h3 class="text-lg font-bold mb-4">Konfirmasi Laporan</h3>
        <p class="text-gray-700 mb-6">
          Apakah Anda yakin semua data sudah sesuai? Setelah disetujui, laporan akan diproses menjadi file PDF.
        </p>
        <div class="flex justify-end space-x-4">
          <button @click="showConfirmationModal = false" class="bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400">
            Batal
          </button>
          <button
            v-if="!inspection.file"
            @click="approveReport"
            class="bg-sky-600 text-white px-4 py-2 rounded-lg hover:bg-sky-700 transition-colors duration-200"
            :disabled="isLoading"
          >
            <span v-if="isLoading">Membuat file PDF...</span>
            <span v-else>Setujui Laporan</span>
          </button>
          <button v-else class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors duration-200 cursor-not-allowed">
            <span>Laporan sudah di buat</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Full Screen Image Viewer Modal - SEDERHANA -->
    <div v-if="showImageModal"
         class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50"
         @click="closeImageModal">

      <div class="relative w-full h-full flex items-center justify-center p-4">

        <!-- Close Button -->
        <button
          @click="closeImageModal"
          class="absolute top-4 right-4 z-10 bg-black bg-opacity-50 text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-70 transition-all"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Gambar Besar -->
        <img
          v-if="currentImageUrl"
          :src="currentImageUrl"
          alt="Full size image"
          class="max-w-[90vw] max-h-[90vh] w-auto h-auto object-contain"
          @click.stop
        />

        <div v-else class="text-white text-lg">
          Gambar tidak dapat dimuat
        </div>

      </div>
    </div>

    <!-- Repair Estimation Modal -->
    <RepairEstimationModal
      :showModal="showEstimationModal"
      :estimationData="selectedEstimationData"
      :inspectionId="inspection.id"
      :encryptedIds="encryptedIds"
      @close="closeEstimationModal"
      @saved="handleEstimationSaved"
    />

    <!-- Scroll to Top/Bottom Buttons -->
    <div v-if="showScrollButtons" class="fixed bottom-6 right-6 flex flex-col gap-2 z-40">
      <!-- Scroll to Top Button - Show when scrolling up -->
      <button
        v-if="scrollDirection === 'up'"
        @click="scrollToTop"
        class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-full shadow-lg transition-all duration-300 transform hover:scale-110"
        title="Scroll ke atas"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
      </button>

      <!-- Scroll to Bottom Button - Show when scrolling down -->
      <button
        v-if="scrollDirection === 'down'"
        @click="scrollToBottom"
        class="bg-green-600 hover:bg-green-700 text-white p-3 rounded-full shadow-lg transition-all duration-300 transform hover:scale-110"
        title="Scroll ke bawah"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V4" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from "axios"
import RepairEstimationSection from '@/Components/InspectionFormLocal/RepairEstimationSection.vue'
import RepairEstimationModal from '@/Components/InspectionFormLocal/RepairEstimationModal.vue'

const props = defineProps({
  inspection: {
    type: Object,
    required: true
  },
  menu_points: {
    type: Array,
    default: () => []
  },
  coverImage: {
    type: Object,
    default: null
  },
  encryptedIds: Object,
  repairEstimations: {
    type: Array,
    default: () => []
  },
  totalRepairCost: {
    type: Number,
    default: 0
  }
});

const page = usePage()
const showConfirmationModal = ref(false)
const isLoading = ref(false)
const repairEstimations = ref(props.repairEstimations)
const totalRepairCost = ref(props.totalRepairCost)

// Repair estimation modal state
const showEstimationModal = ref(false)
const selectedEstimationData = ref(null)

// Scroll buttons state
const showScrollButtons = ref(false)
const scrollDirection = ref('') // 'up' or 'down'
const lastScrollY = ref(0)
const idleTimer = ref(null)

// Role checking
const userRoles = computed(() => page.props.global?.has_roles || [])
const canViewRepairEstimation = computed(() => {
  return userRoles.value.includes('quality_control') || userRoles.value.includes('qc')
})
const canEditEstimations = computed(() => {
  return (userRoles.value.includes('quality_control') || userRoles.value.includes('qc')) && inspection.value.status === 'pending_review'
})
const canApproveReport = computed(() => {
  return (userRoles.value.includes('quality_control') || userRoles.value.includes('qc')) && inspection.value.status === 'pending_review'
})

// Image viewer modal state - SEDERHANA
const showImageModal = ref(false)
const currentImageUrl = ref('')

// Image modal functions - SEDERHANA
const openImageModal = (imageUrl) => {
  currentImageUrl.value = imageUrl
  showImageModal.value = true
}

const closeImageModal = () => {
  showImageModal.value = false
  currentImageUrl.value = ''
}

// bikin reactive data dari props
const inspection = ref(props.inspection)
const menuPoints = ref(props.menu_points)
const coverImage = ref(props.coverImage)

// polling API detail
const fetchInspection = async () => {
  if (inspection.value.status === 'in_progress') {
    try {
      const response = await axios.get(route("inspections.detail", props.encryptedIds))
      inspection.value = response.data.inspection
      menuPoints.value = response.data.menu_points
      coverImage.value = response.data.coverImage
      repairEstimations.value = response.data.repairEstimations || []
      totalRepairCost.value = response.data.totalRepairCost || 0
    } catch (err) {
      console.error("Polling gagal:", err)
    }
  }
}

onMounted(() => {
  setInterval(fetchInspection, 5000) // polling tiap 5 detik

  // Scroll event listener for showing/hiding scroll buttons
  const handleScroll = () => {
    const currentScrollY = window.scrollY

    // Detect scroll direction
    if (currentScrollY > lastScrollY.value) {
      scrollDirection.value = 'down'
    } else if (currentScrollY < lastScrollY.value) {
      scrollDirection.value = 'up'
    }

    lastScrollY.value = currentScrollY

    // Show buttons only if scrolled past threshold and during scroll activity
    if (currentScrollY > 200) {
      showScrollButtons.value = true

      // Clear existing timer
      if (idleTimer.value) {
        clearTimeout(idleTimer.value)
      }

      // Set timer to hide buttons after 2 seconds of inactivity
      idleTimer.value = setTimeout(() => {
        showScrollButtons.value = false
      }, 2000)
    } else {
      showScrollButtons.value = false
    }
  }

  window.addEventListener('scroll', handleScroll)

  // Cleanup on unmount
  return () => {
    window.removeEventListener('scroll', handleScroll)
    if (idleTimer.value) {
      clearTimeout(idleTimer.value)
    }
  }
})

const updateEstimations = (newEstimations) => {
  repairEstimations.value = newEstimations
}

const updateTotalCost = (newTotal) => {
  totalRepairCost.value = newTotal
}

// Function to open estimation modal with pre-filled part name`
const openEstimationModal = (point) => {
  selectedEstimationData.value = {
    part_name: point.inspection_point?.name || '',
    repair_description: '',
    urgency: 'segera',
    status: 'perlu',
    estimated_cost: 0,
    notes: ''
  }
  showEstimationModal.value = true
}

// Function to close estimation modal
const closeEstimationModal = () => {
  showEstimationModal.value = false
  selectedEstimationData.value = null
}

// Function to handle saved estimation
const handleEstimationSaved = (estimation) => {
  // Add the new estimation to the repairEstimations array
  repairEstimations.value.push(estimation)
  closeEstimationModal()
}

// Scroll functions
const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  })
}

const scrollToBottom = () => {
  window.scrollTo({
    top: document.documentElement.scrollHeight,
    behavior: 'smooth'
  })
}

const groupedPoints = computed(() => {
  if (!menuPoints.value) return []

  // Group berdasarkan komponen
  const map = new Map()
  menuPoints.value.forEach(point => {
    const comp = point.inspection_point?.component
    const compId = comp?.id || 0
    if (!map.has(compId)) {
      map.set(compId, {
        component: comp || { id: 0, name: 'Tanpa Komponen', order: 9999 },
        points: []
      })
    }
    map.get(compId).points.push(point)
  })

  // Sort komponen berdasar order → fallback created_at
  const groups = Array.from(map.values()).sort((a, b) => {
    if (a.component.order === b.component.order) {
      return new Date(a.component.created_at) - new Date(b.component.created_at)
    }
    return (a.component.order || 9999) - (b.component.order || 9999)
  })

  // Sort point di dalam komponen berdasar order
  groups.forEach(group => {
    group.points.sort((a, b) => {
      if (a.order === b.order) {
        return new Date(a.created_at) - new Date(b.created_at)
      }
      return (a.order || 9999) - (b.order || 9999)
    })
  })

  return groups
})

const approveReport = () => {
  isLoading.value = true
  setTimeout(() => {
    window.location.href = route('inspections.download.pdf', props.encryptedIds)
    isLoading.value = false
    showConfirmationModal.value = false
  }, 2000)
}

const hasDataOrImages = computed(() => {
  const hasContent = props.menu_points.some(point => hasResult(point) || hasImage(point))
  const hasCoverImage = props.coverImage && props.coverImage.image_path
  return hasContent || hasCoverImage
})

// Helper functions
const imageExists = (imagePath) => imagePath && imagePath.length > 0
const getImageUrl = (imagePath) => `/${imagePath}`

// Mengakses data hasil dan gambar dari 'inspection_point' dengan aman
const hasResult = (point) => point.inspection_point?.results && point.inspection_point.results.length > 0
const hasImage = (point) => point.inspection_point?.images && point.inspection_point.images.length > 0
const hasNote = (point) => hasResult(point) && point.inspection_point.results[0].note

// FUNGSI BARU: Mendapatkan status dalam bentuk array (menangani string dan array)
const getStatusArray = (point) => {
  if (!hasResult(point)) return []
  
  const status = point.inspection_point?.results[0]?.status
  
  // Jika status adalah string kosong atau null
  if (!status) return []
  
  // Jika status sudah berupa array
  if (Array.isArray(status)) {
    return status
  }
  
  // Jika status adalah string dengan koma, split menjadi array
  if (typeof status === 'string' && status.includes(',')) {
    return status.split(',').map(s => s.trim()).filter(s => s.length > 0)
  }
  
  // Jika status adalah string biasa (tanpa koma)
  return [status]
}

const shouldShowStatusBadge = (point) => {
  const inputType = point.input_type || ''
  const statusArray = getStatusArray(point)
  return ['radio', 'imageTOradio'].includes(inputType) && statusArray.length > 0
}

const shouldShowNote = (point) => {
  const inputType = point.input_type || ''
  return ['text', 'number', 'date', 'textarea'].includes(inputType) && hasNote(point)
}

const shouldShowImages = (point) => {
  const inputType = point.input_type || ''
  const settings = point.settings || {}
  const result = point.inspection_point?.results[0] || {}

  // Untuk tipe image, selalu tampilkan gambar jika ada
  if (inputType === 'image' && hasImage(point)) {
    return true
  }

  // Untuk tipe imageTOradio, selalu tampilkan gambar jika ada (karena dasarnya sudah muncul)
  if (inputType === 'imageTOradio' && hasImage(point)) {
    return true
  }

  // Untuk tipe radio, hanya tampilkan jika show_image_upload aktif untuk status yang dipilih
  if (inputType === 'radio') {
    const statusArray = getStatusArray(point)

    // Periksa setiap status dalam array
    for (const status of statusArray) {
      const selectedOption = settings.radios?.find(radio => radio.value === status) || {}
      const showImageUpload = selectedOption.settings?.show_image_upload || false

      if (showImageUpload && hasImage(point)) {
        return true
      }
    }
  }

  return false
}

const shouldShowTextarea = (point) => {
  const settings = point.settings || {}
  const result = point.inspection_point?.results[0] || {}
  const statusArray = getStatusArray(point)
  
  // Periksa setiap status dalam array
  for (const status of statusArray) {
    const selectedOption = settings.radios?.find(radio => radio.value === status) || {}
    if (selectedOption.settings?.show_textarea) {
      return true
    }
  }
  
  return false
}

const getStatusClass = (status) => {
  if (!status) return 'status-warning'

  const statusStr = String(status).toLowerCase().trim()

  if (['normal', 'ada', 'baik', 'good', 'ok'].includes(statusStr)) {
    return 'status-good'
  } else if (['tidak normal', 'tidak ada', 'rusak', 'bad', 'not ok','rusak','repaired'].includes(statusStr)) {
    return 'status-bad'
  }
  return 'status-warning'
}

const hasBadStatus = (point) => {
  const statusArray = getStatusArray(point)
  return statusArray.some(status => {
    const statusStr = String(status).toLowerCase().trim()
    return !['normal', 'ada', 'baik', 'good', 'ok'].includes(statusStr)
  })
}

const formatNote = (point) => {
  const inputType = point.input_type || ''
  const result = point.inspection_point?.results[0] || {}
  const settings = point.settings || {}

  if (inputType === 'account' && result.note) {
    const cleanedNote = result.note.replace(/[^\d.-]/g, '')
    const value = parseFloat(cleanedNote)
    
  if (isNaN(value)) {
    return result.note
  }

  const symbol = settings.currency_symbol || 'Rp'
    const formatter = new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 2
    })

    const formattedValue = formatter.format(value).replace('Rp', symbol)
    return formattedValue
  }

  return result.note
}

// Computed properties for collision status
const collisionImage = computed(() => {
  if (!inspection.value.settings || inspection.value.settings.conclusion.collision !== 'yes') return '/images/icons/aman.png'
  const severity = inspection.value.settings.conclusion.collision_severity
  if (severity === 'moderate') return '/images/icons/beruntun.png'
  if (severity === 'heavy') return '/images/icons/berat.png'
  return '/images/icons/ringan.png'
})

const collisionText = computed(() => {
  if (!inspection.value.settings || inspection.value.settings.conclusion.collision !== 'yes') return 'Bebas Tabrak'
  const severity = inspection.value.settings.conclusion.collision_severity
  if (severity === 'moderate') return 'Tabrak Beruntun'
  if (severity === 'heavy') return 'Tabrak Berat'
  return 'Tabrak Ringan'
})

const collisionColor = computed(() => {
  if (!inspection.value.settings || inspection.value.settings.conclusion.collision !== 'yes') return '#28a745'
  const severity = inspection.value.settings.conclusion.collision_severity
  if (severity === 'moderate') return '#fd7e14'
  if (severity === 'heavy') return '#dc3545'
  return '#ffc107'
})
</script>

<style scoped>
/* Ukuran font dasar lebih kecil */
* {
  font-size: 13px; 
  line-height: 1.4;
}

.point-name {
  min-width: 140px;
  font-size: 13px;
}

.point-content {
  width: calc(100% - 150px);
  font-size: 12px;
  line-height: 1.4;
}

/* Container untuk multiple badges */
.status-badges-container {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-bottom: 6px;
}

.status-badge {
  display: inline-block;
  padding: 2px 6px;
  border-radius: 9999px;
  font-size: 11px;
  white-space: nowrap;
}
.status-good {
  background-color: #d4edda;
  color: #155724;
}
.status-bad {
  background-color: #f8d7da;
  color: #721c24;
}
.status-warning {
  background-color: #fff3cd;
  color: #856404;
}

.photo-component .point {
  display: none;
}

.conclusion {
  margin-top: 15px;
  padding: 12px;
  background-color: #f9f9f9;
  border-left: 3px solid #333;
  border-radius: 4px;
  font-size: 13px;
  line-height: 1.5;
}

.textarea-note {
  margin: 4px 0;
  font-style: italic;
  color: #555;
  font-size: 12px;
}

.car-info table {
  margin-top: 8px;
  border-collapse: collapse;
  width: 100%;
  font-size: 12px;
}
.car-info td {
  padding: 3px 5px;
  vertical-align: top;
  border: 1px solid #ddd;
}
.car-info td:first-child {
  width: 28%;
  font-weight: bold;
  font-size: 12px;
}

.component-title {
  font-weight: bold;
  font-size: 13px;
  margin-top: 12px;
  background-color: #f5f5f5;
  padding: 4px 8px;
  border-left: 3px solid #333;
}

.point {
  margin-left: 12px;
  margin-bottom: 8px;
  padding: 4px 0;
  border-bottom: 1px dotted #eee;
  font-size: 12px;
}

/* Ukuran gambar lebih kecil agar seimbang */
img {
  max-width: 80px;
  max-height: 80px;
}
.photo-component img {
  max-width: 100px;
  max-height: 100px;
}

/* Modal gambar fullscreen */
.fixed.inset-0.bg-black {
  background-color: rgba(0, 0, 0, 0.95) !important;
}

img.max-w-\[90vw\].max-h-\[90vh\] {
  object-fit: contain;
  width: auto;
  height: auto;
  max-width: 90vw;
  max-height: 90vh;
  border: 2px solid white;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
}

/* Hover effect untuk gambar kecil */
img.cursor-pointer:hover {
  opacity: 0.9;
  transform: scale(1.02);
  transition: all 0.2s ease;
}
</style>
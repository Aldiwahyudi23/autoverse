<template>
  <div class="mt-8 pt-6 border-t border-gray-300">
    <!-- Header dan Tombol Tambah -->
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold text-gray-800">Estimasi Perbaikan</h2>
      <button 
        v-if="canEdit"
        @click="openAddModal"
        class="bg-green-600 text-white px-4 py-2 rounded-lg shadow hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors duration-200 flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Estimasi
      </button>
    </div>

    <!-- Tabel Estimasi -->
    <div v-if="estimations.length > 0" class="overflow-x-auto">
      <table class="w-full border-collapse border border-gray-300 rounded-lg overflow-hidden">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-3 border border-gray-300 text-left font-bold text-gray-700">No</th>
            <th class="p-3 border border-gray-300 text-left font-bold text-gray-700">Part/Komponen</th>
            <th class="p-3 border border-gray-300 text-left font-bold text-gray-700">Deskripsi Perbaikan</th>
            <th class="p-3 border border-gray-300 text-left font-bold text-gray-700">Urgensi & Status</th>
            <th class="p-3 border border-gray-300 text-left font-bold text-gray-700">Estimasi Biaya</th>
            <th v-if="canEdit" class="p-3 border border-gray-300 text-left font-bold text-gray-700">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(estimation, index) in estimations" :key="estimation.id"
              :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
            <td class="p-3 border border-gray-300">{{ index + 1 }}</td>
            <td class="p-3 border border-gray-300 font-medium">{{ estimation.part_name }}</td>
            <td class="p-3 border border-gray-300">{{ estimation.repair_description }}</td>
            <td class="p-3 border border-gray-300">
              <div class="flex flex-col gap-1">
                <span :class="getUrgencyBadgeClass(estimation.urgency)">
                  {{ getUrgencyText(estimation.urgency) }}
                </span>
                <span :class="getStatusBadgeClass(estimation.status)">
                  {{ getStatusText(estimation.status) }}
                </span>
              </div>
            </td>
            <td class="p-3 border border-gray-300 font-bold text-right">
              {{ formatCurrency(estimation.estimated_cost) }}
            </td>
            <td v-if="canEdit" class="p-3 border border-gray-300">
              <div class="flex gap-2">
                <button 
                  @click="openEditModal(estimation)"
                  class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-sm"
                >
                  Edit
                </button>
                <button 
                  @click="openDeleteModal(estimation)"
                  class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm"
                >
                  Hapus
                </button>
              </div>
            </td>
          </tr>
        </tbody>
        <tfoot class="bg-gray-100">
          <tr>
            <td colspan="4" class="p-3 border border-gray-300 text-right font-bold">
              Total Estimasi:
            </td>
            <td :colspan="canEdit ? 2 : 1" 
                class="p-3 border border-gray-300 font-bold text-right text-green-700">
              {{ formatCurrency(totalCost) }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
    
    <!-- Kosong State -->
    <div v-else class="text-center py-8 border border-dashed border-gray-300 rounded-lg">
      <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <p class="text-gray-500">Belum ada estimasi perbaikan.</p>
      <button 
        v-if="canEdit"
        @click="openAddModal"
        class="mt-3 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700"
      >
        Tambah Estimasi Pertama
      </button>
    </div>

    <!-- Modal CRUD -->
    <RepairEstimationModal
      v-if="showModal"
      :show-modal="showModal"
      :estimation-data="selectedEstimation"
      :inspection-id="inspectionId"
      :encrypted-ids="encryptedIds"
      @close="closeModal"
      @saved="handleEstimationSaved"
      @deleted="handleEstimationDeleted"
      @error="handleEstimationError"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import RepairEstimationModal from '@/Components/InspectionFormLocal/RepairEstimationModal.vue'

const props = defineProps({
  estimations: {
    type: Array,
    default: () => []
  },
  inspectionId: {
    type: [String, Number],
    required: true
  },
  encryptedIds: {
    type: String,
    required: true
  },
  canEdit: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:estimations', 'update:totalCost'])

// State
const showModal = ref(false)
const selectedEstimation = ref(null)

// Computed
const totalCost = computed(() => {
  return props.estimations.reduce((sum, e) => sum + parseFloat(e.estimated_cost || 0), 0)
})

// Emit total cost changes
watch(totalCost, (newValue) => {
  emit('update:totalCost', newValue)
})

// Methods
const formatCurrency = (amount) => {
  if (!amount) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

const getUrgencyText = (urgency) => {
  return urgency === 'segera' ? 'Segera' : 'Jangka Panjang'
}

const getStatusText = (status) => {
  const statusMap = {
    'perlu': 'Perlu',
    'disarankan': 'Disarankan',
    'opsional': 'Opsional'
  }
  return statusMap[status] || status
}

const getUrgencyBadgeClass = (urgency) => {
  const classes = {
    'segera': 'bg-red-100 text-red-800',
    'jangka_panjang': 'bg-yellow-100 text-yellow-800'
  }
  return `inline-block px-2 py-1 rounded-full text-xs font-medium ${classes[urgency] || 'bg-gray-100 text-gray-800'}`
}

const getStatusBadgeClass = (status) => {
  const classes = {
    'perlu': 'bg-red-100 text-red-800',
    'disarankan': 'bg-yellow-100 text-yellow-800',
    'opsional': 'bg-blue-100 text-blue-800'
  }
  return `inline-block px-2 py-1 rounded-full text-xs font-medium ${classes[status] || 'bg-gray-100 text-gray-800'}`
}

const openAddModal = () => {
  selectedEstimation.value = null
  showModal.value = true
}

const openEditModal = (estimation) => {
  selectedEstimation.value = estimation
  showModal.value = true
}

const openDeleteModal = (estimation) => {
  selectedEstimation.value = estimation
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedEstimation.value = null
}

const handleEstimationSaved = (data) => {
  // Update local array immediately
  const existingIndex = props.estimations.findIndex(e => e.id === data.id)
  if (existingIndex !== -1) {
    // Update existing estimation
    const newEstimations = [...props.estimations]
    newEstimations[existingIndex] = data
    emit('update:estimations', newEstimations)
  } else {
    // Add new estimation
    emit('update:estimations', [...props.estimations, data])
  }
  closeModal()
}

const handleEstimationDeleted = (id) => {
  const newEstimations = props.estimations.filter(e => e.id !== id)
  emit('update:estimations', newEstimations)
  closeModal()
}

const handleEstimationError = (tempId) => {
  // Remove temporary estimation if API call failed
  const newEstimations = props.estimations.filter(e => e.id !== tempId)
  emit('update:estimations', newEstimations)
}
</script>
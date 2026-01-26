<template>
  <!-- Modal Tambah/Edit Estimasi -->
  <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
      <!-- Modal Header -->
      <div class="flex items-center justify-between p-4 border-b">
        <h3 class="text-lg font-bold">
          {{ isEditMode ? 'Edit Estimasi Perbaikan' : 'Tambah Estimasi Perbaikan' }}
        </h3>
        <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Modal Body -->
      <form @submit.prevent="saveEstimation">
        <div class="p-4 max-h-[60vh] overflow-y-auto">
          <div class="space-y-4">
            <!-- Part Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Nama Part/Komponen <span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                v-model="form.part_name"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Contoh: Radiator, Rem Depan, dll"
              >
            </div>

            <!-- Repair Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Deskripsi Perbaikan <span class="text-red-500">*</span>
              </label>
              <textarea
                v-model="form.repair_description"
                required
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Jelaskan jenis perbaikan yang diperlukan"
              ></textarea>
            </div>

            <!-- Urgency and Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Urgensi <span class="text-red-500">*</span>
                </label>
                <div class="space-y-2">
                  <label class="flex items-center">
                    <input
                      type="radio"
                      v-model="form.urgency"
                      value="segera"
                      required
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                    >
                    <span class="ml-2 text-sm text-gray-700">Segera</span>
                  </label>
                  <label class="flex items-center">
                    <input
                      type="radio"
                      v-model="form.urgency"
                      value="jangka_panjang"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                    >
                    <span class="ml-2 text-sm text-gray-700">Jangka Panjang</span>
                  </label>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Status <span class="text-red-500">*</span>
                </label>
                <div class="space-y-2">
                  <label class="flex items-center">
                    <input
                      type="radio"
                      v-model="form.status"
                      value="perlu"
                      required
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                    >
                    <span class="ml-2 text-sm text-gray-700">Perlu</span>
                  </label>
                  <label class="flex items-center">
                    <input
                      type="radio"
                      v-model="form.status"
                      value="disarankan"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                    >
                    <span class="ml-2 text-sm text-gray-700">Disarankan</span>
                  </label>
                  <label class="flex items-center">
                    <input
                      type="radio"
                      v-model="form.status"
                      value="opsional"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                    >
                    <span class="ml-2 text-sm text-gray-700">Opsional</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Estimated Cost -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Estimasi Biaya (Rp) <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500">Rp</span>
                </div>
                <input
                  type="text"
                  v-model="formattedCost"
                  @input="handleCostInput"
                  @blur="handleCostBlur"
                  required
                  class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="0"
                >
              </div>
              <p class="text-xs text-gray-500 mt-1">
                Masukkan angka tanpa titik atau koma
              </p>
            </div>

            <!-- Notes -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Catatan Tambahan
              </label>
              <textarea
                v-model="form.notes"
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Catatan khusus atau informasi tambahan"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end space-x-3 px-4 py-4 border-t">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500"
            >
              Batal
            </button>
            <button
              v-if="isEditMode"
              type="button"
              @click="confirmDelete"
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
              :disabled="isLoading"
            >
              Hapus
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
              :disabled="isLoading"
            >
              <span v-if="isLoading">
                <svg class="animate-spin h-5 w-5 inline-block" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Menyimpan...
              </span>
              <span v-else>
                {{ isEditMode ? 'Update' : 'Simpan' }}
              </span>
            </button>
          </div>
        </form>
    </div>
  </div>

  <!-- Konfirmasi Hapus -->
  <div v-if="showDeleteConfirm" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-96 mx-4">
      <div class="p-6">
        <h3 class="text-lg font-bold mb-4">Konfirmasi Hapus</h3>
        <p class="text-gray-700 mb-6">
          Apakah Anda yakin ingin menghapus estimasi perbaikan untuk "<strong>{{ form.part_name }}</strong>"?
        </p>
        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteConfirm = false"
            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
          >
            Batal
          </button>
          <button
            @click="deleteEstimation"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
            :disabled="isLoading"
          >
            <span v-if="isLoading">Menghapus...</span>
            <span v-else>Ya, Hapus</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, defineEmits, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  showModal: Boolean,
  estimationData: Object,
  inspectionId: [String, Number],
  encryptedIds: String
})

const emit = defineEmits(['close', 'saved', 'deleted'])

const isLoading = ref(false)
const showDeleteConfirm = ref(false)
const isEditMode = computed(() => !!props.estimationData?.id)

// Form data
const form = reactive({
  part_name: '',
  repair_description: '',
  urgency: 'segera',
  status: 'perlu',
  estimated_cost: 0,
  notes: ''
})

// Formatted cost for display
const formattedCost = computed({
  get: () => {
    if (!form.estimated_cost) return ''
    return new Intl.NumberFormat('id-ID').format(form.estimated_cost)
  },
  set: (value) => {
    // Remove all non-numeric characters
    const numericValue = value.replace(/[^\d]/g, '')
    form.estimated_cost = numericValue ? parseInt(numericValue, 10) : 0
  }
})

// Reset form
const resetForm = () => {
  form.part_name = ''
  form.repair_description = ''
  form.urgency = 'segera'
  form.status = 'perlu'
  form.estimated_cost = 0
  form.notes = ''
}

// Format currency
const formatCurrency = (amount) => {
  if (!amount) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount)
}

// Handle cost input
const handleCostInput = (event) => {
  const value = event.target.value
  // Remove all non-numeric characters and format
  const numericValue = value.replace(/[^\d]/g, '')
  if (numericValue) {
    form.estimated_cost = parseInt(numericValue, 10)
    // Update the input value with formatted number
    event.target.value = new Intl.NumberFormat('id-ID').format(form.estimated_cost)
  } else {
    form.estimated_cost = 0
    event.target.value = ''
  }
}

// Handle cost blur (format when user finishes typing)
const handleCostBlur = (event) => {
  if (form.estimated_cost > 0) {
    event.target.value = new Intl.NumberFormat('id-ID').format(form.estimated_cost)
  }
}

// Watch for estimationData changes to populate form
watch(() => props.estimationData, (newData) => {
  if (newData) {
    Object.assign(form, newData)
  }
}, { immediate: true })

// Save estimation
const saveEstimation = async () => {
  let tempId = null

  // Optimistic update for both new and edit items
  if (!isEditMode.value) {
    tempId = 'temp-' + Date.now()
    emit('saved', { ...form, id: tempId })
  } else {
    // For edits, emit immediately with existing ID
    emit('saved', { ...form, id: props.estimationData.id })
  }

  try {
    isLoading.value = true

    const url = isEditMode.value
      ? route('repair-estimations.update', {
          inspection: props.inspectionId,
          repair_estimation: props.estimationData.id
        })
      : route('repair-estimations.store', props.inspectionId)

    const method = isEditMode.value ? 'put' : 'post'

    const response = await axios[method](url, form)

    emit('saved', response.data)
    closeModal()

  } catch (error) {
    if (tempId) {
      emit('error', tempId)
    }
    console.error('Error saving estimation:', error)
    alert('Gagal menyimpan estimasi. Silakan coba lagi.')
  } finally {
    isLoading.value = false
  }
}

// Confirm delete
const confirmDelete = () => {
  showDeleteConfirm.value = true
}

// Delete estimation
const deleteEstimation = async () => {
  try {
    isLoading.value = true
    
    await axios.delete(route('repair-estimations.destroy', { 
      inspection: props.inspectionId, 
      repair_estimation: props.estimationData.id 
    }))
    
    emit('deleted', props.estimationData.id)
    showDeleteConfirm.value = false
    closeModal()
    
  } catch (error) {
    console.error('Error deleting estimation:', error)
    alert('Gagal menghapus estimasi. Silakan coba lagi.')
  } finally {
    isLoading.value = false
  }
}

// Close modal
const closeModal = () => {
  resetForm()
  emit('close')
}

// Watch for showModal to handle opening with data
watch(() => props.showModal, (isVisible) => {
  if (isVisible && props.estimationData) {
    // When modal opens with estimation data, populate the form
    Object.assign(form, props.estimationData)
  } else if (isVisible && !props.estimationData) {
    // When modal opens without data (new estimation), reset form
    resetForm()
  }
})
</script>
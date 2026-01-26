<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 transition-opacity" aria-hidden="true">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>

      <!-- Modal -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
              <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Tambah Damage
              </h3>
              <div class="mt-4">
                <form @submit.prevent="submitDamage">
                  <div class="space-y-4">
                    <!-- Damage Type Selection -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Jenis Damage
                      </label>
                      <select 
                        v-model="damageData.type"
                        required
                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      >
                        <option value="" disabled>Pilih jenis damage</option>
                        <option value="crack">Retak</option>
                        <option value="corrosion">Korosi</option>
                        <option value="leak">Kebocoran</option>
                        <option value="wear">Keausan</option>
                        <option value="other">Lainnya</option>
                      </select>
                    </div>

                    <!-- Location -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Lokasi
                      </label>
                      <input 
                        v-model="damageData.location"
                        type="text"
                        required
                        placeholder="Contoh: Pipa inlet, bagian atas"
                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      />
                    </div>

                    <!-- Severity -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tingkat Keparahan
                      </label>
                      <div class="flex space-x-4">
                        <label v-for="level in severityLevels" :key="level.value" class="inline-flex items-center">
                          <input 
                            v-model="damageData.severity"
                            type="radio"
                            :value="level.value"
                            class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
                          />
                          <span :class="['ml-2 text-sm', getSeverityColor(level.value)]">
                            {{ level.label }}
                          </span>
                        </label>
                      </div>
                    </div>

                    <!-- Description -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Deskripsi
                      </label>
                      <textarea 
                        v-model="damageData.description"
                        rows="3"
                        placeholder="Deskripsikan damage secara detail..."
                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      ></textarea>
                    </div>

                    <!-- Image Upload -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">
                        Foto Damage
                      </label>
                      <input 
                        type="file"
                        @change="handleDamageImage"
                        accept="image/*"
                        multiple
                        class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      />
                      <p class="mt-1 text-xs text-gray-500">
                        Upload foto untuk dokumentasi (maks. 5MB per file)
                      </p>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button
            @click="submitDamage"
            :disabled="isSubmitting"
            type="button"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Tambah Damage
          </button>
          <button
            @click="$emit('close')"
            type="button"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Batal
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
  inspectionId: Number,
  currentMenuId: Number
});

const emit = defineEmits(['close', 'damage-added']);

// State
const damageData = ref({
  type: '',
  location: '',
  severity: 'low',
  description: '',
  images: []
});

const isSubmitting = ref(false);

const severityLevels = [
  { value: 'low', label: 'Rendah' },
  { value: 'medium', label: 'Sedang' },
  { value: 'high', label: 'Tinggi' },
  { value: 'critical', label: 'Kritis' }
];

// Methods
const getSeverityColor = (severity) => {
  const colors = {
    low: 'text-green-600',
    medium: 'text-yellow-600',
    high: 'text-orange-600',
    critical: 'text-red-600'
  };
  return colors[severity] || 'text-gray-600';
};

const handleDamageImage = (event) => {
  damageData.value.images = Array.from(event.target.files);
};

const submitDamage = async () => {
  isSubmitting.value = true;
  
  try {
    const formData = new FormData();
    formData.append('inspection_id', props.inspectionId);
    formData.append('menu_id', props.currentMenuId);
    formData.append('type', damageData.value.type);
    formData.append('location', damageData.value.location);
    formData.append('severity', damageData.value.severity);
    formData.append('description', damageData.value.description);
    
    damageData.value.images.forEach((image, index) => {
      formData.append(`images[${index}]`, image);
    });
    
    const response = await axios.post('/api/form-inspection/damage', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    if (response.data.success) {
      emit('damage-added', response.data.damage);
    }
  } catch (error) {
    console.error('Error adding damage:', error);
    alert('Gagal menambahkan damage');
  } finally {
    isSubmitting.value = false;
  }
};
</script>
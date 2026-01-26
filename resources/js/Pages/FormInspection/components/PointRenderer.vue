<template>
  <div class="point-renderer">
    <!-- Point Header -->
    <div class="flex justify-between items-start mb-4">
      <div>
        <h4 class="text-lg font-semibold text-gray-900">
          {{ point.inspectionPoint.name }}
          <span v-if="point.isRequired" class="ml-2 text-red-500">*</span>
        </h4>
        <p v-if="point.inspectionPoint.description" class="mt-1 text-sm text-gray-600">
          {{ point.inspectionPoint.description }}
        </p>
      </div>
      
      <div class="flex items-center space-x-2">
        <!-- Status Badge -->
        <span 
          :class="[
            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
            getStatusClass()
          ]"
        >
          {{ getStatusText() }}
        </span>
        
        <!-- Menu Notes -->
        <button 
          v-if="point.inspectionPoint.notes"
          @click="showNotes = !showNotes"
          class="text-gray-400 hover:text-gray-600"
          title="Lihat catatan"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Notes Popup -->
    <div v-if="showNotes && point.inspectionPoint.notes" class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
      <div class="flex">
        <svg class="w-5 h-5 text-blue-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <p class="text-sm text-blue-800">{{ point.inspectionPoint.notes }}</p>
        </div>
      </div>
    </div>
    
    <!-- Input Component -->
    <div class="mb-4">
      <component
        :is="getInputComponent()"
        :point="point"
        :value="localValue"
        @input="handleInput"
        @blur="handleBlur"
        @focus="handleFocus"
      />
    </div>
    
    <!-- Validation Errors -->
    <div v-if="validationErrors.length" class="mb-3">
      <div v-for="(error, index) in validationErrors" :key="index" class="flex items-start text-sm text-red-600 mt-1">
        <svg class="w-4 h-4 mr-1 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        <span>{{ error }}</span>
      </div>
    </div>
    
    <!-- Additional Options -->
    <div v-if="point.showTextarea || point.showImageUpload" class="mt-4 pt-4 border-t border-gray-200">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Textarea for Notes -->
        <div v-if="point.showTextarea" class="col-span-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Catatan Tambahan
          </label>
          <textarea
            v-model="localNotes"
            @input="handleNotesChange"
            :rows="point.uiConfig.rows || 3"
            :placeholder="point.uiConfig.notePlaceholder"
            class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
          ></textarea>
        </div>
        
        <!-- Image Upload -->
        <div v-if="point.showImageUpload" class="col-span-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Upload Gambar
          </label>
          <div class="mt-1 flex items-center space-x-3">
            <input
              type="file"
              ref="fileInput"
              @change="handleFileChange"
              accept="image/*"
              multiple
              class="hidden"
            />
            <button
              @click="$refs.fileInput.click()"
              class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Pilih Gambar
            </button>
            
            <div v-if="selectedFiles.length" class="text-sm text-gray-500">
              {{ selectedFiles.length }} file dipilih
            </div>
          </div>
          
          <!-- Preview Images -->
          <div v-if="imagePreviews.length" class="mt-3 grid grid-cols-3 gap-2">
            <div v-for="(preview, index) in imagePreviews" :key="index" class="relative">
              <img :src="preview" class="h-20 w-full object-cover rounded-lg" />
              <button
                @click="removeImage(index)"
                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600"
              >
                ×
              </button>
            </div>
          </div>
          
          <!-- Existing Images -->
          <div v-if="existingImages.length" class="mt-3">
            <p class="text-sm text-gray-600 mb-2">Gambar yang sudah diupload:</p>
            <div class="grid grid-cols-3 gap-2">
              <div v-for="image in existingImages" :key="image.id" class="relative">
                <img :src="image.url" class="h-20 w-full object-cover rounded-lg" />
                <a :href="image.url" target="_blank" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 hover:bg-opacity-50 transition-all rounded-lg">
                  <svg class="w-6 h-6 text-white opacity-0 hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="mt-4 pt-4 border-t border-gray-200 flex justify-end space-x-3">
      <!-- Reset Button -->
      <button
        v-if="hasUnsavedChanges"
        @click="resetChanges"
        type="button"
        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
      >
        Reset
      </button>
      
      <!-- Validate Button -->
      <button
        @click="triggerValidation"
        :disabled="isValidating"
        type="button"
        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <svg v-if="isValidating" class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-700" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <svg v-else class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        Validasi
      </button>
      
      <!-- Save Button -->
      <button
        @click="saveData"
        :disabled="!hasUnsavedChanges || isSaving"
        type="button"
        :class="[
          'inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500',
          hasUnsavedChanges && !isSaving
            ? 'bg-blue-600 hover:bg-blue-700'
            : 'bg-gray-400 cursor-not-allowed'
        ]"
      >
        <svg v-if="isSaving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <svg v-else class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, defineAsyncComponent } from 'vue';
import axios from 'axios';

// Async components untuk input types
const TextInput = defineAsyncComponent(() => import('./inputs/TextInput.vue'));
const NumberInput = defineAsyncComponent(() => import('./inputs/NumberInput.vue'));
const SelectInput = defineAsyncComponent(() => import('./inputs/SelectInput.vue'));
const RadioGroup = defineAsyncComponent(() => import('./inputs/RadioGroup.vue'));
const CheckboxGroup = defineAsyncComponent(() => import('./inputs/CheckboxGroup.vue'));
const TextareaInput = defineAsyncComponent(() => import('./inputs/TextareaInput.vue'));
const DatePicker = defineAsyncComponent(() => import('./inputs/DatePicker.vue'));

const props = defineProps({
  point: Object,
  inspectionId: Number
});

const emit = defineEmits(['value-changed', 'save', 'validation']);

// Reactive State
const localValue = ref(props.point.currentValue || '');
const localNotes = ref('');
const showNotes = ref(false);
const isSaving = ref(false);
const isValidating = ref(false);
const validationErrors = ref([]);
const selectedFiles = ref([]);
const imagePreviews = ref([]);
const existingImages = ref([]);

// Computed
const hasUnsavedChanges = computed(() => {
  return localValue.value !== props.point.currentValue || 
         selectedFiles.value.length > 0 ||
         (localNotes.value && props.point.existingDataId);
});

// Methods
const getInputComponent = () => {
  const components = {
    'text': TextInput,
    'number': NumberInput,
    'select': SelectInput,
    'radio': RadioGroup,
    'checkbox': CheckboxGroup,
    'textarea': TextareaInput,
    'date': DatePicker
  };
  return components[props.point.inputType] || TextInput;
};

const handleInput = (value) => {
  localValue.value = value;
  emit('value-changed', {
    pointId: props.point.id,
    value: value
  });
  
  // Real-time validation jika diaktifkan
  triggerValidation();
};

const handleBlur = () => {
  // Auto-save pada blur jika ada perubahan
  if (hasUnsavedChanges.value) {
    saveData();
  }
};

const handleFocus = () => {
  // Kosongkan error saat focus
  if (validationErrors.value.length > 0) {
    validationErrors.value = [];
    emit('validation', { pointId: props.point.id, errors: [] });
  }
};

const handleNotesChange = () => {
  // Handle notes change
};

const handleFileChange = (event) => {
  const files = Array.from(event.target.files);
  selectedFiles.value = [...selectedFiles.value, ...files];
  
  // Generate previews
  files.forEach(file => {
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreviews.value.push(e.target.result);
    };
    reader.readAsDataURL(file);
  });
};

const removeImage = (index) => {
  selectedFiles.value.splice(index, 1);
  imagePreviews.value.splice(index, 1);
};

const resetChanges = () => {
  localValue.value = props.point.currentValue || '';
  selectedFiles.value = [];
  imagePreviews.value = [];
  validationErrors.value = [];
  emit('validation', { pointId: props.point.id, errors: [] });
};

const triggerValidation = async () => {
  if (!props.point.validationRules) return;
  
  isValidating.value = true;
  
  try {
    const response = await axios.post(`/api/form-inspection/validate/${props.point.id}`, {
      value: localValue.value,
      is_partial: true,
      inspection_id: props.inspectionId
    });
    
    validationErrors.value = response.data.errors || [];
    emit('validation', { 
      pointId: props.point.id, 
      errors: validationErrors.value 
    });
    
    // Handle triggers
    if (response.data.triggers && response.data.triggers.length > 0) {
      handleTriggers(response.data.triggers);
    }
    
  } catch (error) {
    console.error('Validation error:', error);
  } finally {
    isValidating.value = false;
  }
};

const saveData = async () => {
  if (!hasUnsavedChanges.value) return;
  
  isSaving.value = true;
  
  try {
    const formData = new FormData();
    formData.append('point_id', props.point.id);
    formData.append('value', localValue.value);
    
    if (localNotes.value) {
      formData.append('notes', localNotes.value);
    }
    
    // Append files
    selectedFiles.value.forEach(file => {
      formData.append('images[]', file);
    });
    
    const response = await axios.post(`/api/form-inspection/${props.inspectionId}/save`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    if (response.data.success) {
      // Reset states
      selectedFiles.value = [];
      imagePreviews.value = [];
      validationErrors.value = [];
      
      emit('save', props.point.id);
      emit('validation', { pointId: props.point.id, errors: [] });
      
      // Update existing images
      if (response.data.images) {
        existingImages.value = [...existingImages.value, ...response.data.images];
      }
    }
  } catch (error) {
    console.error('Save error:', error);
    if (error.response?.data?.errors) {
      validationErrors.value = error.response.data.errors;
      emit('validation', { 
        pointId: props.point.id, 
        errors: validationErrors.value 
      });
    }
  } finally {
    isSaving.value = false;
  }
};

const getStatusClass = () => {
  if (validationErrors.value.length > 0) {
    return 'bg-red-100 text-red-800';
  }
  
  if (hasUnsavedChanges.value) {
    return 'bg-yellow-100 text-yellow-800';
  }
  
  if (props.point.existingDataId) {
    return 'bg-green-100 text-green-800';
  }
  
  return 'bg-gray-100 text-gray-800';
};

const getStatusText = () => {
  if (validationErrors.value.length > 0) {
    return 'Error';
  }
  
  if (hasUnsavedChanges.value) {
    return 'Belum Disimpan';
  }
  
  if (props.point.existingDataId) {
    return 'Tersimpan';
  }
  
  return 'Belum Diisi';
};

const handleTriggers = (triggers) => {
  // Handle triggers dari validasi
  console.log('Triggers received:', triggers);
};

// Watch for point changes
watch(() => props.point.currentValue, (newValue) => {
  if (localValue.value !== newValue && !hasUnsavedChanges.value) {
    localValue.value = newValue || '';
  }
});
</script>
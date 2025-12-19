<template>
  <div class="bg-gray-50 shadow-lg rounded-xl overflow-hidden border border-gray-100">

    <div class="bg-indigo-200 px-6 py-2 border-b flex items-center justify-between">
      <h3 class="text-xl font-semibold text-indigo-700">
        {{ category.name }}
      </h3>

      <button
        v-if="hasHiddenPoints"
        @click="showHidden = !showHidden"
        class="text-sm text-indigo-700 hover:underline focus:outline-none"
      >
        {{ showHidden ? 'Sembunyikan Point Yang Tidak Perlu' : 'Tampilkan Point Tersembunyi' }}
      </button>
    </div>

    <div class="p-4 space-y-4"> 
      <!-- Sync indicator -->
      <div v-if="isSyncing" class="flex items-center justify-center text-sm text-gray-500 mb-4">
        <svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Menyimpan...
      </div>

      <div v-for="menuPoint in filteredPoints" :key="menuPoint.id" 
        class="space-y-2 pb-2 border-b border-gray-100 last:border-0 last:pb-0" >
        <div class="flex items-start justify-between">
          <label class="block text-sm font-medium text-gray-700">
            {{ menuPoint.inspection_point?.name }}
            <span v-if="menuPoint.settings?.is_required" class="text-red-500">*</span>
          </label>
          <span 
            v-if="isPointComplete(menuPoint)"
            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800"
          >
            ✓
          </span>
        </div>
        
        <!-- PERBAIKAN: Ganti v-model dengan :model-value dan @update:modelValue -->
        <input-text
          v-if="menuPoint.input_type === 'text'"
          :model-value="getResultValue(menuPoint.inspection_point?.id, 'note')"
          :required="menuPoint.settings?.is_required"
          :min-length="menuPoint.settings?.min_length"
          :max-length="menuPoint.settings?.max_length"
          :allowSpace="menuPoint.settings?.allow_space"
          :textTransform="menuPoint.settings?.text_transform"
          :placeholder="menuPoint.settings?.placeholder || 'Masukan text'"
          @update:modelValue="updateResult(menuPoint.inspection_point?.id, $event, 'note')"
          @save="saveResult(menuPoint.inspection_point?.id)"
        />
        
        <input-number
          v-if="menuPoint.input_type === 'number'"
          :model-value="getResultValue(menuPoint.inspection_point?.id, 'note')"
          :required="menuPoint.settings?.is_required"
          :min="menuPoint.settings?.min"
          :max="menuPoint.settings?.max"
          :step="menuPoint.settings?.step || 1"
          :placeholder="menuPoint.settings?.placeholder || 'Masukan number'"
          @update:modelValue="updateResult(menuPoint.inspection_point?.id, $event, 'note')"
          @save="saveResult(menuPoint.inspection_point?.id)"
        />

        <input-account
          v-if="menuPoint.input_type === 'account'"
          :model-value="getResultValue(menuPoint.inspection_point?.id, 'note')"
          :required="menuPoint.settings?.is_required"
          :placeholder="menuPoint.settings?.placeholder || 'Masukkan nilai'"
          :point-id="menuPoint.inspection_point?.id"
          :settings="menuPoint.settings"
          @update:modelValue="updateResult(menuPoint.inspection_point?.id, $event, 'note')"
          @save="saveResult(menuPoint.inspection_point?.id)"
        />
        
        <input-date
          v-if="menuPoint.input_type === 'date'"
          :model-value="getResultValue(menuPoint.inspection_point?.id, 'note')"
          :required="menuPoint.settings?.is_required"
          :min-date="menuPoint.settings?.min_date"
          :max-date="menuPoint.settings?.max_date"
          @update:modelValue="updateResult(menuPoint.inspection_point?.id, $event, 'note')"
          @save="saveResult(menuPoint.inspection_point?.id)"
        />
        
        <input-textarea
          v-if="menuPoint.input_type === 'textarea'"
          :model-value="getResultValue(menuPoint.inspection_point?.id, 'note')"
          :required="menuPoint.settings?.is_required"
          :min-length="menuPoint.settings?.min_length"
          :max-length="menuPoint.settings?.max_length"
          :placeholder="menuPoint.settings?.placeholder || 'Masukkan teks di sini'"
          :settings="menuPoint.settings"
          @update:modelValue="updateResult(menuPoint.inspection_point?.id, $event, 'note')"
          @save="saveResult(menuPoint.inspection_point?.id)"
        />

        <input-radio
          v-if="menuPoint.input_type === 'radio'"
          :model-value="getResultValue(menuPoint.inspection_point?.id, 'status')"
          :notes="getResultValue(menuPoint.inspection_point?.id, 'note')"
          :images="getImagesValue(menuPoint.inspection_point?.id)"
          :required="menuPoint.settings?.is_required"
          :point-id="menuPoint.inspection_point?.id"
          :point="menuPoint.inspection_point"
          :inspection-id="inspectionId" 
          :settings="menuPoint.settings"
          :point-name="menuPoint.inspection_point?.name"
          :selected-point="menuPoint.inspection_point ?? null"
          :options="menuPoint.settings?.radios || defaultRadioOptions"
          @update:modelValue="updateResult(menuPoint.inspection_point?.id, $event, 'status')"
          @update:notes="val => updateResult(menuPoint.inspection_point?.id, val, 'note')"
          @update:images="val => updateImages(menuPoint.inspection_point?.id, val)"
          @save="saveResult(menuPoint.inspection_point?.id)"
          @hapus="HapusPoint(menuPoint.inspection_point?.id)"
        />

        <InputImageToRadio
          v-if="menuPoint.input_type === 'imageTOradio'"
          :model-value="getResultValue(menuPoint.inspection_point?.id, 'status')"
          :notes="getResultValue(menuPoint.inspection_point?.id, 'note')"
          :images="getImagesValue(menuPoint.inspection_point?.id)"
          :required="menuPoint.settings?.is_required"
          :point-id="menuPoint.inspection_point?.id"
          :inspection-id="inspectionId" 
          :settings="menuPoint.settings"
          :point-name="menuPoint.inspection_point?.name"
          :point="menuPoint"
          :selected-point="menuPoint.inspection_point ?? null"
          :options="menuPoint.settings?.radios || defaultRadioOptions"
          @update:modelValue="updateResult(menuPoint.inspection_point?.id, $event,'status')"
          @update:notes="val => updateResult(menuPoint.inspection_point?.id, val, 'note')"
          @update:images="val => updateImages(menuPoint.inspection_point?.id, val)"
          @save="saveResult(menuPoint.inspection_point?.id)"
          @hapus="HapusPoint(menuPoint.inspection_point?.id)"
        />
        
        <input-image
          v-if="menuPoint.input_type === 'image'"
          :model-value="getImagesValue(menuPoint.inspection_point?.id)"
          :inspection-id="inspectionId"
          :point-id="menuPoint.inspection_point?.id"
          :point="menuPoint.inspection_point"
          :point-name="menuPoint.inspection_point?.name"
          :settings="menuPoint.settings"
          @update:modelValue="updateImages(menuPoint.inspection_point?.id, $event)"
          @update:notes="val => updateResult(menuPoint.inspection_point?.id, val, 'note')"
          @update:status="val => updateResult(menuPoint.inspection_point?.id, val, 'status')"
        />

        <input-select
          v-if="menuPoint.input_type === 'select'"
          :model-value="getResultValue(menuPoint.inspection_point?.id, 'status')"
          :required="menuPoint.settings?.is_required"
          @update:modelValue="updateResult(menuPoint.inspection_point?.id, $event, 'status')"
          @save="saveResult(menuPoint.inspection_point?.id)"
        />
        
      </div>

      <div v-if="filteredPoints.length === 0" class="text-center py-8 text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="mt-2 text-sm">Tidak ada data untuk ditampilkan</p>
        <p v-if="category.isDamageMenu" class="text-xs text-gray-400">
          Tambahkan data melalui tombol "+" di pojok kanan bawah
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import InputText from './InputText.vue';
import InputNumber from './InputNumber.vue';
import InputDate from './InputDate.vue';
import InputAccount from './InputAccount.vue';
import InputTextarea from './InputTextarea.vue';
import InputSelect from './InputSelect.vue';
import InputRadio from './InputRadio.vue';
import InputImage from './InputImage.vue';
import InputImageToRadio from './InputImageToRadio.vue';
import { computed, ref } from 'vue';

const props = defineProps({
  category: Object,
  form: Object,
  inspectionId: String,
  selectedPoint: Object,
  isSyncing: Boolean
});

const emit = defineEmits(['saveResult', 'updateResult', 'removeImage', 'hapusPoint', 'updateImages']);

const showHidden = ref(false);

// PERBAIKAN: Gunakan computed untuk reactive values
const getResultValue = computed(() => (pointId, field) => {
  if (!pointId) return '';
  return props.form.results[pointId]?.[field] || '';
});

const getImagesValue = computed(() => (pointId) => {
  if (!pointId) return [];
  return props.form.images[pointId] || [];
});

const hasHiddenPoints = computed(() => {
  return (props.category.points || []).some(p => p.is_default === false);
});

const filteredPoints = computed(() => {
  if (!props.category.isDamageMenu) {
    const filtered = (props.category.points || []).filter(point => {
      const pointId = point.inspection_point?.id
      const hasData = hasPointData(pointId)

      if (showHidden.value) return true
      if (point.is_default) return true
      if (hasData) return true

      return false
    })
    return filtered
  }

  const filtered = (props.category.points || []).filter(point => {
    const pointId = point.inspection_point?.id
    const hasData = hasPointData(pointId)

    if (showHidden.value) return true
    if (point.is_default) return true
    if (!point.is_default && hasData) return true

    return hasData
  })

  return filtered
});

const hasPointData = (pointId) => {
  if (!pointId) return false;
  
  const hasLocalResult = props.form.results[pointId] && 
                        (props.form.results[pointId].status || props.form.results[pointId].note);
  
  const hasLocalImages = props.form.images[pointId] && props.form.images[pointId].length > 0;

  return hasLocalResult || hasLocalImages;
};

const defaultRadioOptions = [
  { value: 'good', label: 'Good' },
  { value: 'bad', label: 'Bad' },
  { value: 'na', label: 'N/A' }
];

const isPointComplete = (menuPoint) => {
  const result = props.form.results[menuPoint.inspection_point?.id];
  const image = props.form.images[menuPoint.inspection_point?.id];

  if (!result) return false;
  
  switch(menuPoint.input_type) {
    case 'text':
    case 'number':
    case 'date':
    case 'account':
    case 'textarea':
      return !!result.note;

    case 'select':
    case 'radio':
      if (!result.status) return false;
      
      const selectedOption = menuPoint.settings?.radios?.find(opt => opt.value === result.status);
      if (selectedOption?.settings) {
        if (selectedOption.settings.show_textarea && !result.note?.trim()) {
          return false;
        }
        if (selectedOption.settings.show_image_upload && image?.length === 0) {
          return false;
        }
      }
      return true;

    case 'imageTOradio':
      if (image?.length === 0 || !result.status) return false;
      
      const selectedOptionImage = menuPoint.settings?.radios?.find(opt => opt.value === result.status);
      if (selectedOptionImage?.settings) {
        if (selectedOptionImage.settings.show_textarea && !result.note?.trim()) {
          return false;
        }
      }
      return true;

    case 'image':
      return image?.length > 0;

    default:
      return !!result.status || !!result.note?.trim();
  }
};

const updateResult = (pointId, value, type) => {
  emit('updateResult', { pointId, type, value });
};

const updateImages = (pointId, images) => {
  emit('updateImages', { pointId, images });
};

const saveResult = (pointId) => {
  emit('saveResult', pointId);
};

const removeImage = (pointId, imageIndex) => {
  emit('removeImage', { pointId, imageIndex });
};

const HapusPoint = (pointId) => {
  emit("hapusPoint", pointId);
};
</script>

<style scoped>
/* Mobile-first styles */
@media (min-width: 640px) {
  .point-card {
    padding: 1.25rem;
  }
}
</style>
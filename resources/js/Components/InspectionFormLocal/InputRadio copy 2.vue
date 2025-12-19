<template>
  <div class="mt-2 space-y-2">
    <!-- Radio/Checkbox Options - Grid Layout untuk keseragaman -->
       <div
   class="grid gap-2 w-full mt-2"
  :class="`grid-cols-${Math.min(options.length, 3)}`"
    >
      <label
        v-for="(option, index) in options"
        :key="index"
        class="cursor-pointer"
      >
        <input
          :type="option.multi ? 'checkbox' : 'radio'"
          :name="option.multi ? `checkbox-${pointId}` : 'radio-' + pointId"
          :value="option.value"
          :checked="isSelected(option)"
          @change="handleOptionChange(option, $event)"
          class="hidden peer"
          :required="required && !option.multi"
        />
        <div
          class="w-full px-4 py-3 border rounded-lg text-center transition-colors whitespace-nowrap text-sm font-medium"
          :class="{
            'border-indigo-500 bg-indigo-50 text-indigo-700': isSelected(option),
            'border-gray-300 text-gray-700 hover:bg-gray-50': !isSelected(option)
          }"
        >
          {{ option.label }}
          <span v-if="option.multi" class="text-xs text-gray-500 block">(Multi)</span>
        </div>
      </label>
    </div>

    <!-- Display saved data - Hanya muncul jika radio memiliki settings -->
    <div
      v-if="modelValue && selectedOptions.length > 0 && selectedOptions.some(opt => hasSettings(opt))"
      class="p-2 rounded-lg bg-gray-50"
    >
      <div class="flex justify-between items-start">
        <h4 class="text-sm font-medium text-gray-700">Detail : </h4>
        <button
          @click="openOptionModal"
          class="text-sm text-indigo-600 hover:text-indigo-800"
        >
          Edit
        </button>
      </div>

      <!-- Display images -->
      <div
        v-if="images.length > 0 && selectedOptions.some(opt => opt.settings?.show_image_upload)"
        class="mt-2"
      >
        <div class="flex gap-2 overflow-x-auto" style="scrollbar-width: none">
          <div
            v-for="(image, index) in images"
            :key="index"
            class="relative flex-shrink-0"
            style="width: 130px; height: 130px;"
          >
            <img
              :src="getImageSrc(image)"
              class="w-full h-full object-cover rounded"
            />
            <span
              v-if="index === 3 && images.length > 4"
              class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center text-white text-xs"
            >
              +{{ images.length - 4 }}
            </span>
          </div>
        </div>
      </div>

      <!-- Display notes -->
      <div v-if="notes && selectedOptions.some(opt => opt.settings?.show_textarea)" class="mt-2">
        <p class="text-sm text-gray-600">{{ notes }}</p>
      </div>
    </div>

    <!-- Modal untuk opsi yang dipilih -->
    <RadioOptionModal
    v-if="showOptionModal"
    :key="selectedPoint?.id"
      :show="showOptionModal"
      :title="pointName || 'Detail'"
      :subtitle="subtitle || ''"
      :name="'modal-radio-' + pointId"
      :options="options"
      :selected-value="tempRadioValue"
      :notes-value="tempNotes"
      :images-value="tempImages"
      :point-id="pointId"
      :point="point"
      :inspection-id="inspectionId"
      :selected-point="selectedPoint"
      @update:selectedValue="tempRadioValue = $event"
      @update:notesValue="tempNotes = $event"
      @update:imagesValue="handleImageUpdate($event)"
      @close="closeOptionModal"
      @save="saveAllData"
      @saveTextarea="handleTextareaSave"
      @saveImage="handleImageSave"
      @hapus="HapusPoint(pointId)"
    />

    <p v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import RadioOptionModal from './RadioOptionModal.vue';

const props = defineProps({
  modelValue: [String, Array],
  required: Boolean,
  error: String,
  point: Object,
  pointId: [String, Number],
  pointName: String,
  subtitle: String,
  inspectionId: [String, Number],
  options: {
    type: Array,
    default: () => []
  },
   selectedPoint: Object,
  notes: { type: String, default: '' },
  images: { type: Array, default: () => [] }
});

const emit = defineEmits([
  'update:modelValue',
  'update:notes',
  'update:images',
  'save',
  'hapus'
]);

// reactive local state
const notesValue = ref(props.notes);
const imageValues = ref([...props.images]);
const showOptionModal = ref(false);
const tempNotes = ref('');
const tempImages = ref([]);
const tempRadioValue = ref('');
const originalImages = ref([]);

// computed
const selectedOption = computed(() =>
  props.options.find(opt => opt.value === props.modelValue)
);

const selectedOptions = computed(() => {
  if (Array.isArray(props.modelValue)) {
    return props.options.filter(opt => props.modelValue.includes(opt.value));
  } else {
    return props.modelValue ? [props.options.find(opt => opt.value === props.modelValue)].filter(Boolean) : [];
  }
});

// Function to check if an option is selected
const isSelected = (option) => {
  if (Array.isArray(props.modelValue)) {
    return props.modelValue.includes(option.value);
  } else {
    return props.modelValue === option.value;
  }
};

// Fungsi untuk mengecek apakah option memiliki settings
const hasSettings = (option) => {
  return option.settings && (
    option.settings.show_textarea ||
    option.settings.show_image_upload
  );
};

// watch for props changes
watch(() => props.notes, (val) => {
  notesValue.value = val;
});

watch(() => props.images, (val) => {
  imageValues.value = [...val];
});

watch(() => props.modelValue, (val) => {
  if (val) {
    tempRadioValue.value = Array.isArray(val) ? [...val] : val;
  } else {
    tempRadioValue.value = '';
  }
});

// Handle option change for both radio and checkbox
const handleOptionChange = (option, event) => {
  let newValue;

  if (option.multi) {
    // Handle checkbox (multi-select)
    const currentValue = Array.isArray(props.modelValue) ? props.modelValue : [];
    if (event.target.checked) {
      // Add to selection
      newValue = [...currentValue, option.value];
    } else {
      // Remove from selection
      newValue = currentValue.filter(v => v !== option.value);
    }
  } else {
    // Handle radio (single-select) - clear all multi selections when selecting non-multi
    newValue = option.value;
  }

  emit('update:modelValue', newValue);

  // Open modal if option has additional settings
  if (hasSettings(option)) {
    openOptionModal();
  } else {
    // Save immediately if no additional inputs
    emit('save', {
      pointId: props.pointId,
      inspectionId: props.inspectionId,
      value: newValue,
      notes: '',
      images: []
    });
  }
};

// Helper function untuk mendapatkan source gambar
const getImageSrc = (image) => {
  return image.preview || (image.image_path ? `/${image.image_path}` : image);
};

// modal functions
const openOptionModal = () => {
  tempRadioValue.value = props.modelValue;
  tempNotes.value = notesValue.value;
  tempImages.value = [...imageValues.value];
  originalImages.value = [...imageValues.value];
  showOptionModal.value = true;
};

const closeOptionModal = () => {
  // Revert to original values if cancelled
  tempNotes.value = '';
  tempImages.value = [];
  tempRadioValue.value = Array.isArray(props.modelValue) ? [...props.modelValue] : props.modelValue;
  showOptionModal.value = false;
};

// Handler untuk update gambar
const handleImageUpdate = (val) => {
  tempImages.value = val;
  // Gambar langsung tersimpan saat diupload
  imageValues.value = [...val];
  emit('update:images', [...val]);
  emit('save', { 
    pointId: props.pointId, 
    inspectionId: props.inspectionId, 
    value: props.modelValue,
    notes: notesValue.value,
    images: [...val]
  });
};

// Handler untuk save gambar
const handleImageSave = (data) => {
  // Gambar sudah langsung tersimpan saat diupload melalui handleImageUpdate
};

// // Handler untuk save textarea
// const handleTextareaSave = (data) => {
//   notesValue.value = data.notes;
//   emit('update:notes', data.notes);
//   emit('save', { 
//     pointId: props.pointId, 
//     inspectionId: props.inspectionId, 
//     value: props.modelValue,
//     notes: data.notes,
//     images: imageValues.value
//   });
// };

// Simpan semua data
const saveAllData = () => {
  // Update radio value jika berubah
  if (tempRadioValue.value !== props.modelValue) {
    emit('update:modelValue', tempRadioValue.value);
  }
  
  // Simpan notes
  notesValue.value = tempNotes.value;
  emit('update:notes', tempNotes.value);
  
  // Simpan images
  imageValues.value = [...tempImages.value];
  emit('update:images', [...tempImages.value]);
  
  // Kirim semua data untuk disimpan
  emit('save', { 
    pointId: props.pointId, 
    inspectionId: props.inspectionId, 
    value: tempRadioValue.value,
    notes: tempNotes.value,
    images: tempImages.value
  });
  
  // Tutup modal
  showOptionModal.value = false;
};

const HapusPoint = (pointId) => {
  // Reset semua state lokal
  tempRadioValue.value = '';
  tempNotes.value = '';
  tempImages.value = [];
  notesValue.value = '';
  imageValues.value = [];
  
  // Emit ke parent untuk reset state dan hapus data
  emit('update:modelValue', '');
  emit('update:notes', '');
  emit('update:images', []);
  emit('hapus', pointId); // Ini yang memanggil hapusData di parent
  
  showOptionModal.value = false;
};
</script>
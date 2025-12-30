<template>
  <BottomSheetModal
    :show="show"
    :title="title"
    :subtitle="subtitle"
    @close="$emit('close')"
  >
    <div class="space-y-4">

                  <!-- Input Text -->
      <div v-if="point.input_type === 'text'" class="mt-2">
        <input-text
           :model-value="notesValue"
          :required="point.settings?.is_required"
          :min-length="point.settings?.min_length"
          :max-length="point.settings?.max_length"
          :allowSpace="point.settings?.allow_space"
          :textTransform="point.settings?.text_transform"
          :placeholder="point.settings?.placeholder || 'Masukan text'"
          :error="error"
           @update:modelValue="$emit('update:notesValue', $event)"
          @save="$emit('saveText', $event)"
        />
      </div>

      <!-- Input Number -->
      <div v-if="point.input_type === 'number'" class="mt-2">
        <input-number
           :model-value="notesValue"
          :required="point.settings?.is_required"
          :min="point.settings?.min"
          :max="point.settings?.max"
          :step="point.settings?.step || 1"
          :placeholder="point.settings?.placeholder || 'Masukan number'"
          :error="error"
            @update:modelValue="$emit('update:notesValue', $event)"
          @save="$emit('saveNumber', $event)"
        />
      </div>

      <!-- Input Account -->
      <div v-if="point.input_type === 'account'" class="mt-2">
        <input-account
           :model-value="notesValue"
          :required="point.settings?.is_required"
          :placeholder="point.settings?.placeholder || 'Masukkan nilai'"
          :error="error"
          :point-id="pointId"
          :settings="point.settings"
            @update:modelValue="$emit('update:notesValue', $event)"
          @save="$emit('saveAccount', $event)"
        />
      </div>

      <!-- Input Date -->
      <div v-if="point.input_type === 'date'" class="mt-2">
        <input-date
           :model-value="notesValue"
          :required="point.settings?.is_required"
          :min-date="point.settings?.min_date"
          :max-date="point.settings?.max_date"
          :error="error"
          @update:modelValue="$emit('update:notesValue', $event)"
          @save="$emit('saveDate', $event)"
        />
      </div>

      <!-- imageTOradio -->
      <div v-if="point.input_type === 'imageTOradio'" class="mt-2">
        <h4 class="text-sm font-medium text-gray-700 mb-2">
          <span v-if="point.settings?.required" class="text-red-500">*</span>
        </h4>
        <InputImage
          :model-value="imagesValue"
          :point-id="pointId"
          :point="point.inspection_point"
          :inspection-id="inspectionId"
          :settings="point.settings"
          @update:modelValue="$emit('update:imagesValue', $event)"
          @save="$emit('saveImage', $event)"
        />
        <p v-if="point.settings?.required && imagesValue.length === 0" class="text-xs text-red-500">
          Foto wajib diupload
        </p>
      </div>

      <!-- Radio/Checkbox Options -->
      <div v-if="options.length"
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
            :name="option.multi ? `checkbox-${name}` : name"
            :value="option.value"
            :checked="isSelected(option)"
            @change="handleOptionChange(option, $event)"
            class="hidden peer"
          />
          <div
            class="w-full px-4 py-3 border rounded-lg text-center transition-colors whitespace-nowrap text-sm font-medium"
            :class="{
              'border-indigo-500 bg-indigo-50 text-indigo-700': isSelected(option),
              'border-gray-300 text-gray-700 hover:bg-gray-50': !isSelected(option)
            }"
          >
            {{ option.label }}
            <!-- <span v-if="option.multi" class="text-xs text-gray-500 block">(Multi)</span> -->
          </div>
        </label>
      </div>

      <!-- Image Upload for Options -->
      <div v-if="showImageUpload" class="mt-4">
        <h4 class="text-sm font-medium text-gray-700 mb-2">
          Upload Foto
          <span v-if="imageOption?.settings?.required" class="text-red-500">*</span>
        </h4>
        <InputImage
          :model-value="imagesValue"
          :point-id="pointId"
          :point="point"
          :inspection-id="inspectionId"
          :settings="imageOption?.settings"
          @update:modelValue="$emit('update:imagesValue', $event)"
          @save="$emit('saveImage', $event)"
        />
        <p v-if="imageOption?.settings?.required && imagesValue.length === 0" class="text-xs text-red-500">
          Foto wajib diupload
        </p>
      </div>

        <!-- Textarea for Options -->
      <div v-if="showTextarea" class="space-y-2">
        <span v-if="textareaOption?.settings?.required" class="text-red-500">*</span>
        <Textarea
          :model-value="notesValue"
          :point-id="pointId"
          :inspection-id="inspectionId"
          :settings="textareaOption?.settings"
          :required="textareaOption?.settings?.required"
          :min-length="textareaOption?.settings?.min_length"
          :max-length="textareaOption?.settings?.max_length"
          :placeholder="textareaOption?.settings?.placeholder || 'Tambahkan keterangan...'"
          @update:modelValue="$emit('update:notesValue', $event)"
          @save="$emit('saveTextarea', $event)"
        />
        <p v-if="textareaOption?.settings?.required && !notesValue" class="text-xs text-red-500">
          Keterangan wajib diisi
        </p>
      </div>

    </div>
    
    <template #footer>
      <!-- Tombol Hapus -->
      <button 
        v-if="selectedPoint" 
        @click="hapusPoint(pointId)"
        class="flex items-center justify-center text-red-600 hover:text-red-800 mb-3"
      >
        <svg xmlns="http://www.w3.org/2000/svg" 
             class="h-5 w-5 mr-1" 
             fill="none" 
             viewBox="0 0 24 24" 
             stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m2-3H7m5 0v3" />
        </svg>
        Hapus Data
      </button>

      <!-- Tombol Batal & Simpan -->
      <div class="flex gap-2">
        <button
          type="button"
          @click="$emit('close')"
          class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
        >
          Batal
        </button>
        <button
          type="button"
          @click="$emit('save')"
          :disabled="!isFormValid"
          class="flex-1 px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-white hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
        >
          Simpan
        </button>
      </div>
    </template>
  </BottomSheetModal>
</template>

<script setup>
import { computed } from 'vue';
import InputImage from './InputImage.vue';
import Textarea from './InputTextarea.vue';
import BottomSheetModal from './BottomSheetModal.vue';
import InputAccount from './InputAccount.vue';
import InputDate from './InputDate.vue';
import InputNumber from './InputNumber.vue';
import InputText from './InputText.vue';

const props = defineProps({
  show: Boolean,
  title: String,
  subtitle: String,
  name: String,
  options: { type: Array, default: () => [] },
  selectedValue: [String, Array],
  notesValue: String,
  imagesValue: { type: Array, default: () => [] },
  pointId: [String, Number],
  inspectionId: [String, Number],
  showTextarea: { type: Boolean, default: true },
  showImageUpload: { type: Boolean, default: true },
  selectedPoint: Object,
  point: Object
});

const emit = defineEmits([
  'update:selectedValue',
  'update:notesValue',
  'update:imagesValue',
  'close',
  'save',
  'hapus',
  'saveTextarea',
  'saveImage',
  'saveText',
  'saveNumber',
  'saveAccount',
  'saveDate',
]);

const hapusPoint = (pointId) => emit("hapus", pointId);

const selectedOption = computed(() => {
  if (Array.isArray(props.selectedValue)) {
    return props.options.find(opt => props.selectedValue.includes(opt.value));
  } else {
    return props.options.find(opt => opt.value === props.selectedValue);
  }
});

// Get all selected options
const selectedOptions = computed(() => {
  if (Array.isArray(props.selectedValue)) {
    return props.options.filter(opt => props.selectedValue.includes(opt.value));
  } else if (props.selectedValue) {
    // Handle comma-separated string from database (for multi-select)
    const values = typeof props.selectedValue === 'string'
      ? props.selectedValue.split(',').map(v => v.trim())
      : [props.selectedValue];

    return props.options.filter(opt => values.includes(opt.value));
  }
  return [];
});

// Check if textarea should be shown (for both single and multi options)
const showTextarea = computed(() => {
  if (Array.isArray(props.selectedValue)) {
    // For multi-select: show if any selected multi option has textarea enabled
    return selectedOptions.value.some(opt => opt.multi && opt.settings?.show_textarea);
  } else {
    // For single-select: show if the selected option has textarea enabled
    const selectedOption = props.options.find(opt => opt.value === props.selectedValue);
    return selectedOption?.settings?.show_textarea;
  }
});

// Check if image upload should be shown (for both single and multi options)
const showImageUpload = computed(() => {
  if (Array.isArray(props.selectedValue)) {
    // For multi-select: show if any selected multi option has image upload enabled
    return selectedOptions.value.some(opt => opt.multi && opt.settings?.show_image_upload);
  } else {
    // For single-select: show if the selected option has image upload enabled
    const selectedOption = props.options.find(opt => opt.value === props.selectedValue);
    return selectedOption?.settings?.show_image_upload;
  }
});

// Get the option that has textarea settings (for both single and multi)
const textareaOption = computed(() => {
  if (Array.isArray(props.selectedValue)) {
    // For multi-select: combine settings from all selected multi options that have textarea enabled
    const multiOptionsWithTextarea = selectedOptions.value.filter(opt => opt.multi && opt.settings?.show_textarea);
    if (multiOptionsWithTextarea.length === 0) return null;

    // Combine settings from all relevant options
    const combinedSettings = { ...multiOptionsWithTextarea[0].settings };

    // Merge damage_options from all selected multi options
    const allDamageOptions = [];
    multiOptionsWithTextarea.forEach(opt => {
      if (opt.settings?.damage_options) {
        allDamageOptions.push(...opt.settings.damage_options);
      }
    });

    // Remove duplicates based on value
    const uniqueDamageOptions = allDamageOptions.filter((damage, index, self) =>
      index === self.findIndex(d => d.value === damage.value)
    );

    combinedSettings.damage_options = uniqueDamageOptions;

    return {
      ...multiOptionsWithTextarea[0],
      settings: combinedSettings
    };
  } else {
    // For single-select: return the selected option if it has textarea enabled
    const selectedOption = props.options.find(opt => opt.value === props.selectedValue);
    return selectedOption?.settings?.show_textarea ? selectedOption : null;
  }
});

// Get the option that has image upload settings (for both single and multi)
const imageOption = computed(() => {
  if (Array.isArray(props.selectedValue)) {
    // For multi-select: return the first multi option that has image upload enabled
    return selectedOptions.value.find(opt => opt.multi && opt.settings?.show_image_upload);
  } else {
    // For single-select: return the selected option if it has image upload enabled
    const selectedOption = props.options.find(opt => opt.value === props.selectedValue);
    return selectedOption?.settings?.show_image_upload ? selectedOption : null;
  }
});

// Get the first multi option that has textarea settings (for backward compatibility)
const multiTextareaOption = computed(() => {
  return selectedOptions.value.find(opt => opt.multi && opt.settings?.show_textarea);
});

// Get the first multi option that has image upload settings (for backward compatibility)
const multiImageOption = computed(() => {
  return selectedOptions.value.find(opt => opt.multi && opt.settings?.show_image_upload);
});

// Function to check if an option is selected
const isSelected = (option) => {
  if (Array.isArray(props.selectedValue)) {
    return props.selectedValue.includes(option.value);
  } else if (props.selectedValue) {
    // Handle comma-separated string from database (for multi-select)
    const values = typeof props.selectedValue === 'string'
      ? props.selectedValue.split(',').map(v => v.trim())
      : [props.selectedValue];

    return values.includes(option.value);
  }
  return false;
};

// Handle option change for both radio and checkbox
const handleOptionChange = (option, event) => {
  let newValue;

  if (option.multi) {
    // Handle checkbox (multi-select)
    const currentValue = Array.isArray(props.selectedValue) ? props.selectedValue : [];
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

  emit('update:selectedValue', newValue);
};

// Helper function to get selected options for a given value
const getSelectedOptions = (value) => {
  if (Array.isArray(value)) {
    return props.options.filter(opt => value.includes(opt.value));
  } else if (value) {
    const values = typeof value === 'string'
      ? value.split(',').map(v => v.trim())
      : [value];
    return props.options.filter(opt => values.includes(opt.value));
  }
  return [];
};

// Helper function to get all available damage values from selected options
const getAvailableDamageValues = (selectedOpts) => {
  const damageValues = new Set();
  selectedOpts.forEach(opt => {
    if (opt.multi && opt.settings?.show_textarea && opt.settings?.damage_options) {
      opt.settings.damage_options.forEach(damage => {
        damageValues.add(damage.value);
      });
    }
  });
  return Array.from(damageValues);
};

// Helper function to clean notes value by keeping only available damage values and manual text
const cleanNotesValue = (notes, availableDamageValues) => {
  if (!notes) return '';

  const values = notes.split(',').map(v => v.trim());
  const keptValues = [];
  let manualText = '';

  values.forEach(value => {
    if (availableDamageValues.includes(value)) {
      keptValues.push(value);
    } else {
      // If it's not a damage value, treat it as manual text
      if (manualText) manualText += ', ';
      manualText += value;
    }
  });

  const result = [...keptValues, manualText].filter(v => v).join(', ');
  return result;
};

// ✅ Validasi hanya input yang tampil (aktif)
const isFormValid = computed(() => {
  // Jika ada text aktif
  if (props.point.input_type === 'text') {
    if (!props.notesValue?.trim()) return false;
    if (props.point.settings.min_length && props.notesValue.length < props.point.settings.min_length) return false;
    if (props.point.settings.max_length && props.notesValue.length > props.point.settings.max_length) return false;
  }

  if (props.point.input_type === 'number') {
    if (!props.notesValue?.trim()) return false;
    if (props.point.settings.min && props.notesValue.length < props.point.settings.min) return false;
    if (props.point.settings.max && props.notesValue.length > props.point.settings.max) return false;
  }
  if (props.point.input_type === 'account') {
    if (!props.notesValue?.trim()) return false;
    if (props.point.settings.min_value && props.notesValue.length < props.point.settings.min_value) return false;
    if (props.point.settings.max_value && props.notesValue.length > props.point.settings.max_value) return false;
  }

  if (props.point.input_type === 'date') {
    if (!props.notesValue?.trim()) return false;
    if (props.point.settings.min_date && props.notesValue.length < props.point.settings.min_date) return false;
    if (props.point.settings.max_date && props.notesValue.length > props.point.settings.max_date) return false;
  }

    if (props.point.input_type === 'imageTOradio') {
      if (props.imagesValue.length === 0) return false;

      // cek status upload
      const hasFailed = props.imagesValue.some(img => img.status === 'error');
      const stillUploading = props.imagesValue.some(img => img.status === 'uploading');
      if (hasFailed || stillUploading) return false;
    }

    


  // Jika ada radio
  if (props.options.length && (!props.selectedValue || (Array.isArray(props.selectedValue) && props.selectedValue.length === 0))) return false;

  const option = selectedOption.value;

  // Jika textarea aktif
  if (props.showTextarea && option?.settings?.show_textarea) {
    if (!props.notesValue?.trim()) return false;
    if (option.settings.min_length && props.notesValue.length < option.settings.min_length) return false;
    if (option.settings.max_length && props.notesValue.length > option.settings.max_length) return false;
  }

  if (props.showImageUpload && option?.settings?.show_image_upload) {
      if (props.imagesValue.length === 0) return false;
      if (option.settings.max_files && props.imagesValue.length > option.settings.max_files) return false;

      // cek status upload juga
      const hasFailed = props.imagesValue.some(img => img.status === 'error');
      const stillUploading = props.imagesValue.some(img => img.status === 'uploading');
      if (hasFailed || stillUploading) return false;
    }

  return true;
});
</script>



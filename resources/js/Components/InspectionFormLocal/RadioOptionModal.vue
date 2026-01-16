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
          @isUploading="handleUploading"
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
          </div>
        </label>
      </div>

      <!-- Image Upload for Options -->
      <div v-if="showImageUpload && imageSettings" class="mt-4">
        <h4 class="text-sm font-medium text-gray-700 mb-2">
          Upload Foto
          <span v-if="imageSettings.image_is_required !== false" class="text-red-500">*</span>
        </h4>
        <InputImage
          :model-value="imagesValue"
          :point-id="pointId"
          :point="point"
          :inspection-id="inspectionId"
          :settings="imageSettings"
          @update:modelValue="$emit('update:imagesValue', $event)"
          @save="$emit('saveImage', $event)"
          @isUploading="handleUploading"
        />
        <p v-if="imageSettings.image_is_required !== false && imagesValue.length === 0" class="text-xs text-red-500">
          Foto wajib diupload
        </p>
      </div>

      <!-- Textarea for Options -->
      <div v-if="showTextarea && textareaSettings" class="space-y-2">
        <!-- <span v-if="textareaSettings.textarea_is_required !== false" class="text-red-500">*</span> -->
        <Textarea
          :model-value="notesValue"
          :point-id="pointId"
          :inspection-id="inspectionId"
          :settings="textareaSettings"
          :required="textareaSettings.textarea_is_required !== false"
          :min-length="textareaSettings.min_length"
          :max-length="textareaSettings.max_length"
          :placeholder="textareaSettings.placeholder || 'Tambahkan keterangan...'"
          @update:modelValue="$emit('update:notesValue', $event)"
          @save="$emit('saveTextarea', $event)"
        />
       <p v-if="textareaError" class="text-xs text-red-500">
          {{ textareaError }}
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
import { ref, reactive, computed } from 'vue';
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

const isUploading = ref(false);

const handleUploading = (val) => {
  isUploading.value = val;
};

// Helper function untuk mendapatkan pengaturan berdasarkan prioritas is_repaired
const getPrioritySettings = () => {
  const selectedOpts = getSelectedOptions(props.selectedValue);
  
  if (selectedOpts.length === 0) {
    return { textareaSettings: null, imageSettings: null };
  }

  // Urutkan: is_repaired=true dulu, lalu ambil pengaturan yang paling ketat
  const repairedOptions = selectedOpts.filter(opt => opt.is_repaired === true);
  const nonRepairedOptions = selectedOpts.filter(opt => !opt.is_repaired || opt.is_repaired === false);
  
  // Gunakan opsi dengan is_repaired true jika ada, jika tidak gunakan semua
  const priorityOptions = repairedOptions.length > 0 ? repairedOptions : nonRepairedOptions;

  // Gabungkan pengaturan textarea dari semua opsi prioritas
  let textareaSettings = null;
  let hasTextarea = false;
  let textareaIsRequired = false;
  let maxMinLength = 0;
  let maxMaxLength = 0;
  let placeholder = '';
  
  // Gabungkan pengaturan image dari semua opsi prioritas
  let imageSettings = null;
  let hasImageUpload = false;
  let imageIsRequired = false;
  let maxFiles = 0;

  // Cari pengaturan yang paling ketat
  priorityOptions.forEach(opt => {
    if (opt.settings?.show_textarea) {
      hasTextarea = true;
      
      // Gunakan yang paling wajib (required=true lebih tinggi prioritasnya)
      const currentIsRequired = opt.settings.textarea_is_required !== false;
      if (currentIsRequired) {
        textareaIsRequired = true;
      }
      
      // Ambil min_length terbesar
      if (opt.settings.min_length) {
        const minLength = parseInt(opt.settings.min_length);
        maxMinLength = Math.max(maxMinLength, minLength);
      }
      
      // Ambil max_length terbesar
      if (opt.settings.max_length) {
        const maxLength = parseInt(opt.settings.max_length);
        maxMaxLength = Math.max(maxMaxLength, maxLength);
      }
      
      // Ambil placeholder dari opsi pertama yang ada
      if (opt.settings.placeholder && !placeholder) {
        placeholder = opt.settings.placeholder;
      }
      
      // Simpan semua pengaturan dari opsi pertama yang ada textarea
      if (!textareaSettings) {
        textareaSettings = { ...opt.settings };
      }
    }
    
    if (opt.settings?.show_image_upload) {
      hasImageUpload = true;
      
      // Gunakan yang paling wajib
      const currentImageIsRequired = opt.settings.image_is_required !== false;
      if (currentImageIsRequired) {
        imageIsRequired = true;
      }
      
      // Ambil max_files terbesar
      if (opt.settings.max_files) {
        const currentMaxFiles = parseInt(opt.settings.max_files);
        maxFiles = Math.max(maxFiles, currentMaxFiles);
      }
      
      // Simpan semua pengaturan dari opsi pertama yang ada image upload
      if (!imageSettings) {
        imageSettings = { ...opt.settings };
      }
    }
  });

  // Gabungkan pengaturan yang sudah dihitung
  if (hasTextarea && textareaSettings) {
    textareaSettings.textarea_is_required = textareaIsRequired;
    if (maxMinLength > 0) textareaSettings.min_length = maxMinLength;
    if (maxMaxLength > 0) textareaSettings.max_length = maxMaxLength;
    if (placeholder) textareaSettings.placeholder = placeholder;
    // Pastikan show_textarea tetap true
    textareaSettings.show_textarea = true;
  }
  
  if (hasImageUpload && imageSettings) {
    imageSettings.image_is_required = imageIsRequired;
    if (maxFiles > 0) imageSettings.max_files = maxFiles;
    // Pastikan show_image_upload tetap true
    imageSettings.show_image_upload = true;
  }

  return {
    textareaSettings: hasTextarea ? textareaSettings : null,
    imageSettings: hasImageUpload ? imageSettings : null
  };
};

// Helper function untuk mendapatkan opsi yang dipilih
const getSelectedOptions = (value) => {
  if (!value) return [];
  
  if (Array.isArray(value)) {
    return props.options.filter(opt => value.includes(opt.value));
  } else {
    const values = typeof value === 'string'
      ? value.split(',').map(v => v.trim())
      : [value];
    return props.options.filter(opt => values.includes(opt.value));
  }
};

const selectedOptions = computed(() => {
  return getSelectedOptions(props.selectedValue);
});

// Computed untuk mendapatkan pengaturan berdasarkan prioritas
const prioritySettings = computed(() => getPrioritySettings());

// Tentukan apakah harus menampilkan textarea - PERBAIKAN DI SINI
const showTextarea = computed(() => {
  return selectedOptions.value.some(opt => opt.settings?.show_textarea === true);
});

// Tentukan apakah harus menampilkan image upload - PERBAIKAN DI SINI
const showImageUpload = computed(() => {
  return selectedOptions.value.some(opt => opt.settings?.show_image_upload === true);
});

// Settings untuk textarea
const textareaSettings = computed(() => prioritySettings.value.textareaSettings);

// Settings untuk image upload
const imageSettings = computed(() => prioritySettings.value.imageSettings);

// Function to check if an option is selected
const isSelected = (option) => {
  if (Array.isArray(props.selectedValue)) {
    return props.selectedValue.includes(option.value);
  } else if (props.selectedValue) {
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
    const currentValue = Array.isArray(props.selectedValue) ? props.selectedValue : [];
    if (event.target.checked) {
      newValue = [...currentValue, option.value];
    } else {
      newValue = currentValue.filter(v => v !== option.value);
    }
  } else {
    newValue = option.value;
  }

  emit('update:selectedValue', newValue);
};


// ✅ Validasi form yang diperbarui
const isFormValid = computed(() => {
  // Periksa apakah wajib diisi
  const isRequired = props.point.settings?.is_required !== false;
  
  // Jika tidak wajib diisi, selalu valid
  if (!isRequired) {
    return true;
  }

  // Jika ada radio/select (termasuk single dan multi selection)
  if (props.point.input_type === 'radio' || props.point.input_type === 'select') {
    if (!props.selectedValue || 
        (Array.isArray(props.selectedValue) && props.selectedValue.length === 0)) {
      return false;
    }

    const selectedOpts = getSelectedOptions(props.selectedValue);
    if (selectedOpts.length === 0) return false;

    // Dapatkan pengaturan berdasarkan prioritas
    const settings = prioritySettings.value;

    // Validasi textarea jika ada dan wajib diisi
    if (settings.textareaSettings && settings.textareaSettings.textarea_is_required !== false) {
      if (!props.notesValue?.trim()) {
        return false;
      }
      
      // Validasi panjang jika ada isinya
      if (props.notesValue?.trim()) {
        if (settings.textareaSettings.min_length) {
          const minLength = parseInt(settings.textareaSettings.min_length);
          if (props.notesValue.trim().length < minLength) return false;
        }
        if (settings.textareaSettings.max_length) {
          const maxLength = parseInt(settings.textareaSettings.max_length);
          if (props.notesValue.trim().length > maxLength) return false;
        }
      }
    }
    
    // Jika textarea ada tapi tidak wajib, tetap boleh kosong
    // Tidak perlu validasi jika tidak wajib

    // Validasi image upload jika ada dan wajib diisi
    if (settings.imageSettings && settings.imageSettings.image_is_required !== false) {
      if (!props.imagesValue || props.imagesValue.length === 0) {
        return false;
      }
      
        // Cek status upload jika ada gambar
      if (props.imagesValue && props.imagesValue.length > 0) {
        if (!props.imagesValue || props.imagesValue.length === 0) {
          return false;
        }

        if (hasImageUploadIssue(props.imagesValue)) {
          return false;
        }
        
        // Validasi jumlah file
        if (settings.imageSettings.max_files) {
          const maxFiles = parseInt(settings.imageSettings.max_files);
          if (props.imagesValue.length > maxFiles) return false;
        }
      }
    }
    
    // Jika image upload ada tapi tidak wajib, tetap boleh kosong
    // Tidak perlu validasi jika tidak wajib

    return true;
  }

  // Jika ada imageTOradio
  if (props.point.input_type === 'imageTOradio') {
    if (!props.imagesValue || props.imagesValue.length === 0) return false;
    if (!props.selectedValue) return false;

    const settings = props.point.settings || {};
    if (settings.max_files) {
      const maxFiles = parseInt(settings.max_files);
      if (props.imagesValue.length > maxFiles) return false;
    }

    // ⛔ upload masih berjalan / gagal
      if (hasImageUploadIssue(props.imagesValue)) {
        return false;
      }

    const selectedOpts = getSelectedOptions(props.selectedValue);
    const settingsPriority = getPrioritySettings();

    // Validasi textarea jika ada dan wajib diisi
    if (settingsPriority.textareaSettings && settingsPriority.textareaSettings.textarea_is_required !== false) {
      if (!props.notesValue?.trim()) {
        return false;
      }
      
      if (props.notesValue?.trim()) {
        if (settingsPriority.textareaSettings.min_length) {
          const minLength = parseInt(settingsPriority.textareaSettings.min_length);
          if (props.notesValue.trim().length < minLength) return false;
        }
        if (settingsPriority.textareaSettings.max_length) {
          const maxLength = parseInt(settingsPriority.textareaSettings.max_length);
          if (props.notesValue.trim().length > maxLength) return false;
        }
      }
    }

    return true;
  }

  // Validasi untuk input types lainnya
  if (props.point.input_type === 'text') {
    if (!props.notesValue?.trim()) return false;
    
    const settings = props.point.settings || {};
    if (settings.min_length) {
      const minLength = parseInt(settings.min_length);
      if (props.notesValue.length < minLength) return false;
    }
    if (settings.max_length) {
      const maxLength = parseInt(settings.max_length);
      if (props.notesValue.length > maxLength) return false;
    }
  }

  if (props.point.input_type === 'number') {
    if (!props.notesValue?.trim()) return false;
    
    const numValue = parseFloat(props.notesValue);
    if (isNaN(numValue)) return false;
    
    const settings = props.point.settings || {};
    if (settings.min !== undefined) {
      const min = parseFloat(settings.min);
      if (numValue < min) return false;
    }
    
    if (settings.max !== undefined) {
      const max = parseFloat(settings.max);
      if (numValue > max) return false;
    }
    
    if (settings.step) {
      const step = parseFloat(settings.step);
      const min = settings.min ? parseFloat(settings.min) : 0;
      const remainder = (numValue - min) % step;
      if (Math.abs(remainder) > 0.00001 && Math.abs(remainder - step) > 0.00001) {
        return false;
      }
    }
  }

  if (props.point.input_type === 'account') {
    if (!props.notesValue?.trim()) return false;
    
    const cleanValue = props.notesValue.replace(/[^0-9]/g, '');
    const numAccount = parseFloat(cleanValue);
    if (isNaN(numAccount)) return false;
    
    const settings = props.point.settings || {};
    if (settings.min_value) {
      const minValue = parseFloat(settings.min_value);
      if (numAccount < minValue) return false;
    }
    
    if (settings.max_value) {
      const maxValue = parseFloat(settings.max_value);
      if (numAccount > maxValue) return false;
    }
  }

  if (props.point.input_type === 'date') {
    if (!props.notesValue?.trim()) return false;
    
    const selectedDate = new Date(props.notesValue);
    if (isNaN(selectedDate.getTime())) return false;
    
    const settings = props.point.settings || {};
    if (settings.min_date) {
      const minDate = new Date(settings.min_date);
      if (selectedDate < minDate) return false;
    }
    
    if (settings.max_date) {
      const maxDate = new Date(settings.max_date);
      if (selectedDate > maxDate) return false;
    }
  }

  return true;
});


const hasImageUploadIssue = (images) => {
  if (!images || images.length === 0) return false;

  const hasFailed = images.some(img => img.isFailed === true);
  const stillUploading = isUploading.value === true; // ⬅️ penting pakai .value

  return hasFailed || stillUploading;
};


const textareaError = computed(() => {
  if (!textareaSettings.value) return null;
  
  const isRequired = textareaSettings.value.textarea_is_required !== false;
  const minLength = textareaSettings.value.min_length ? parseInt(textareaSettings.value.min_length) : null;
  
  // Jika wajib diisi tapi kosong
  if (isRequired && (!props.notesValue || props.notesValue.trim() === '')) {
    return `Keterangan wajib diisi`;
  }
  
  // Jika ada min_length dan teks kurang dari min_length
  if (props.notesValue && minLength && props.notesValue.trim().length < minLength) {
    return `Minimal ${minLength} karakter (${props.notesValue.trim().length}/${minLength})`;
  }
  
  return null;
});
</script>
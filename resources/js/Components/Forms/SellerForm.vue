<!-- resources/js/Components/Forms/SellerForm.vue -->
<template>
  <div class="space-y-4" v-if="showForm">
    <h4 class="text-sm font-medium text-gray-700 mb-3 border-b pb-2">
      Data Lokasi Inspeksi & Penanggung Jawab
    </h4>

    <div class="space-y-4">
      <!-- Area Inspeksi -->
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">
          Area Inspeksi *
        </label>
        <input
          v-model="form.seller_inspection_area"
          type="text"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
          placeholder="Contoh: Jakarta Selatan, Surabaya Barat"
        />
      </div>

      <!-- Alamat Inspeksi -->
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">
          Alamat Lengkap Inspeksi *
        </label>
        <textarea
          v-model="form.seller_inspection_address"
          rows="3"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
          placeholder="Alamat lengkap lokasi inspeksi"
        ></textarea>
      </div>

      <!-- Google Maps Link -->
      <div>
        <div class="flex justify-between items-center mb-1">
          <label class="block text-xs font-medium text-gray-600">
            Link Google Maps
          </label>
          <button
            type="button"
            @click="getCurrentLocation"
            class="text-xs text-blue-600 hover:text-blue-800 flex items-center"
            title="Ambil lokasi saat ini"
          >
            <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/>
            </svg>
            Lokasi Saat Ini
          </button>
        </div>
        <div class="flex">
          <input
            v-model="form.seller_link_maps"
            type="text"
            placeholder="https://maps.google.com/?q=..."
            class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
          />
          <button
            type="button"
            @click="searchInMaps"
            class="px-3 py-2 bg-blue-500 text-white rounded-r-md hover:bg-blue-600 text-sm"
            :disabled="!form.seller_link_maps"
            title="Cari di Maps"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Penanggung Jawab Unit -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">
            Nama Penanggung Jawab *
          </label>
          <input
            v-model="form.seller_unit_holder_name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            placeholder="Nama orang yang memegang unit"
          />
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">
            Nomor WhatsApp Penanggung Jawab *
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <span class="text-gray-500 text-sm">+62</span>
            </div>
            <input
              v-model="form.seller_unit_holder_phone"
              type="tel"
              required
              class="w-full pl-10 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
              placeholder="8123456789"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';

const emit = defineEmits(['update:form']);

const props = defineProps({
  formData: {
    type: Object,
    required: true
  },
  showForm: {
    type: Boolean,
    default: false
  },
  existingSeller: {
    type: Object,
    default: null
  }
});

// Form
const form = ref({
  seller_inspection_area: '',
  seller_inspection_address: '',
  seller_link_maps: '',
  seller_unit_holder_name: '',
  seller_unit_holder_phone: '',
  seller_settings: {}
});

// Inisialisasi form
onMounted(() => {
  if (props.existingSeller) {
    form.value = {
      seller_inspection_area: props.existingSeller.inspection_area || '',
      seller_inspection_address: props.existingSeller.inspection_address || '',
      seller_link_maps: props.existingSeller.link_maps || '',
      seller_unit_holder_name: props.existingSeller.unit_holder_name || '',
      seller_unit_holder_phone: props.existingSeller.unit_holder_phone?.replace('62', '') || '',
      seller_settings: props.existingSeller.settings || {}
    };
  } else {
    form.value = { ...props.formData };
  }
});

// Watch form changes
watch(form, (newForm) => {
  emit('update:form', newForm);
}, { deep: true });

// Fungsi untuk mendapatkan lokasi saat ini
const getCurrentLocation = () => {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const { latitude, longitude } = position.coords;
        const mapsUrl = `https://www.google.com/maps?q=${latitude},${longitude}`;
        form.value.seller_link_maps = mapsUrl;
        
        // Reverse geocoding untuk mendapatkan alamat
        reverseGeocode(latitude, longitude);
      },
      (error) => {
        alert('Gagal mendapatkan lokasi: ' + error.message);
      }
    );
  } else {
    alert('Browser tidak mendukung geolocation');
  }
};

// Reverse geocoding
const reverseGeocode = async (latitude, longitude) => {
  try {
    const response = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`
    );
    const data = await response.json();
    
    if (data.address) {
      if (!form.value.seller_inspection_area) {
        form.value.seller_inspection_area = data.address.city || data.address.town || data.address.village || '';
      }
      
      if (!form.value.seller_inspection_address) {
        const addressParts = [];
        if (data.address.road) addressParts.push(data.address.road);
        if (data.address.suburb) addressParts.push(data.address.suburb);
        if (data.address.city_district) addressParts.push(data.address.city_district);
        if (data.address.city) addressParts.push(data.address.city);
        if (data.address.state) addressParts.push(data.address.state);
        
        form.value.seller_inspection_address = addressParts.join(', ');
      }
    }
  } catch (error) {
    console.error('Error reverse geocoding:', error);
  }
};

// Cari di Maps
const searchInMaps = () => {
  if (form.value.seller_link_maps && form.value.seller_link_maps.includes('google.com/maps')) {
    window.open(form.value.seller_link_maps, '_blank', 'noopener,noreferrer');
  } else if (form.value.seller_inspection_address) {
    const searchQuery = encodeURIComponent(form.value.seller_inspection_address);
    window.open(`https://www.google.com/maps/search/?api=1&query=${searchQuery}`, '_blank', 'noopener,noreferrer');
  }
};
</script>
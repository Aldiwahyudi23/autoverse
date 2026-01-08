<!-- resources/js/Components/Detail/CardCustomer.vue -->
<template>
  <div
    class="mb-2 p-2 rounded border"
    :class="{
      'bg-green-50 border-green-200': hasData,
      'bg-yellow-50 border-yellow-200': !hasData
    }"
  >
    <div class="flex justify-between items-center mb-3">
      <h4 class="text-sm font-medium text-gray-700 flex items-center">
        <UserIcon class="h-4 w-4 mr-1" />
        Customer & PIC
      </h4>
    </div>

    <div v-if="customer && seller" class="space-y-2">
      <!-- Alamat Seller -->
      <div>
        <h5 class="text-xs font-medium text-gray-600 mb-1">Alamat Lokasi</h5>
        <p class="text-sm text-gray-800">{{ seller.inspection_address }}</p>
      </div>

      <!-- Link Maps -->
      <div v-if="seller.link_maps" class="flex items-center">
        <a
          :href="seller.link_maps"
          target="_blank"
          class="inline-flex items-center text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded hover:bg-blue-100"
        >
          <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/>
          </svg>
          Lihat Lokasi
        </a>
      </div>

    <!-- Customer & PIC -->
      <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-200">

        <!-- CUSTOMER -->
        <div class="border rounded-lg p-2 bg-white shadow-sm">
          <h5 class="text-xs font-medium text-gray-500 mb-1">Customer</h5>

          <div class="flex items-center justify-between">
            <!-- Nama Customer -->
            <button
              @click="showCustomerDetailModal = true"
              class="text-left hover:bg-gray-100 p-1 rounded"
            >
              <p class="text-sm font-semibold text-green-800">
                {{ customer.name }}
              </p>
            </button>

            <!-- WhatsApp Customer -->
            <button
              v-if="customer.phone"
              @click="sendWhatsAppToCustomer"
              class="p-1 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-full"
              title="Kirim WhatsApp ke Customer"
            >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.864 3.488"/>
            </svg>
            </button>
          </div>
        </div>

        <!-- PIC -->
        <div class="border rounded-lg p-2 bg-white shadow-sm">
          <h5 class="text-xs font-medium text-gray-500 mb-1">PIC</h5>

          <div class="flex items-center justify-between">
            <!-- Nama PIC -->
            <p class="text-sm font-semibold text-blue-800">
              {{ seller.unit_holder_name }}
            </p>

            <!-- WhatsApp PIC -->
            <button
              v-if="seller.unit_holder_phone"
              @click="sendWhatsAppToPIC"
              class="p-1 text-blue-600 hover:text-blue-800 hover:bg-blue-100 rounded-full"
              title="Kirim WhatsApp ke PIC"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.864 3.488"/>
              </svg>
            </button>
          </div>
        </div>

      </div>

    </div>

    <div v-else>
      <p class="text-xs text-yellow-700">Data customer dan PIC belum tersedia</p>
    </div>

    <!-- Modal Detail Customer -->
    <div v-if="showCustomerDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl w-96">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-bold">Detail Customer</h3>
          <button @click="showCustomerDetailModal = false" class="text-gray-500 hover:text-gray-700">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="space-y-3">
          <div>
            <label class="block text-xs font-medium text-gray-600">Nama Customer</label>
            <p class="text-sm text-gray-800">{{ customer.name }}</p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600">Nomor WhatsApp</label>
            <p class="text-sm text-gray-800">{{ formatPhone(customer.phone) }}</p>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-600">Alamat Customer</label>
            <p class="text-sm text-gray-800">{{ customer.address || '-' }}</p>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <button
            @click="showCustomerDetailModal = false"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-400"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { UserIcon } from '@heroicons/vue/24/outline';
import { ref, computed } from 'vue';

// Props
const props = defineProps({
  customer: {
    type: Object,
    default: null
  },
  seller: {
    type: Object,
    default: null
  },
  inspection: {
    type: Object,
    default: null
  },
  userRole: {
    type: String,
    default: 'Inspektor'
  }
});

// State
const showCustomerDetailModal = ref(false);

const hasData = computed(() => props.customer !== null);

const emit = defineEmits(['edit']);

// Format phone number for display
const formatPhone = (phone) => {
  if (!phone) return '';
  if (phone.startsWith('62')) {
    return '0' + phone.substring(2);
  }
  return phone;
};

// Generate WhatsApp message for Customer
const generateCustomerMessage = () => {
  const userRole = props.userRole || 'Inspektor';
  const carInfo = props.inspection?.car 
    ? `${props.inspection.car.brand?.name} ${props.inspection.car.model?.name} ${props.inspection.car.type?.name} ${(props.inspection.car.cc / 1000).toFixed(1)}L ${props.inspection.car.year}`
    : props.inspection?.car_name || 'kendaraan';
  
  const inspectionDate = props.inspection?.inspection_date 
    ? new Date(props.inspection.inspection_date).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    : 'tanggal yang telah ditentukan';
  
  const inspectionAddress = props.seller?.inspection_address || 'lokasi yang telah disepakati';
  
  return `Hallo Bpk/Ibu ${props.customer?.name || ''},

Saya ${userRole} dari AutoInspeksi. Ijin mengkonfirmasi apakah ini benar dengan A/n *${props.customer?.name || 'Anda'}* yang mengajukan untuk pengecekan unit *${carInfo}* pada ${inspectionDate} di daerah ${inspectionAddress}.

Ijin mengkonfirmasi nanti di lokasi ketemu dengan siapa, dan untuk memastikan alamat sesuai boleh di kirim ulang sharelok-nya?

Terima kasih.`;
};

// Generate WhatsApp message for PIC
const generatePICMessage = () => {
  const userRole = props.userRole || 'Inspektor';
  const customerName = props.customer?.name || 'customer';
  const carInfo = props.inspection?.car 
    ? `${props.inspection.car.brand?.name} ${props.inspection.car.model?.name} ${props.inspection.car.type?.name} ${(props.inspection.car.cc / 1000).toFixed(1)}L ${props.inspection.car.year}`
    : props.inspection?.car_name || 'kendaraan';
  
  const inspectionTime = props.inspection?.inspection_date 
    ? new Date(props.inspection.inspection_date).toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
      })
    : 'jam yang telah ditentukan';
  
  const inspectionAddress = props.seller?.inspection_address || 'lokasi yang telah disepakati';
  
  return `Hallo Bpk/Ibu *${props.seller?.unit_holder_name || ''}*,

Saya ${userRole} dari AutoInspeksi. Maaf mengganggu waktunya, kebetulan saya ada jadwal untuk pengecekan unit *${carInfo}* di jam *${inspectionTime}* di daerah ${inspectionAddress}.

Ijin konfirmasi apakah Bapak/Ibu sudah janjian dengan A/n *${customerName}* ? 
Jika memang benar, apakah unit dan dokumen sudah ada dan Untuk keakuratan alamat lokasi boleh di kirim ulang untuk titik-nya.

Terima kasih.`;
};

// Send WhatsApp to Customer
const sendWhatsAppToCustomer = () => {
  if (!props.customer?.phone) return;
  
  const phone = props.customer.phone.startsWith('62') 
    ? props.customer.phone 
    : '62' + props.customer.phone.replace(/^0/, '');
  
  const message = encodeURIComponent(generateCustomerMessage());
  const url = `https://wa.me/${phone}?text=${message}`;
  
  window.open(url, '_blank', 'noopener,noreferrer');
};

// Send WhatsApp to PIC
const sendWhatsAppToPIC = () => {
  if (!props.seller?.unit_holder_phone) return;

  const phone = props.seller.unit_holder_phone.startsWith('62')
    ? props.seller.unit_holder_phone
    : '62' + props.seller.unit_holder_phone.replace(/^0/, '');

  const message = encodeURIComponent(generatePICMessage());
  const url = `https://wa.me/${phone}?text=${message}`;

  window.open(url, '_blank', 'noopener,noreferrer');
};

// Copy address to Google Maps search
const copyAddressToMaps = () => {
  const address = props.customer?.full_inspection_address || props.customer?.inspection_address;
  if (!address) return;
  
  const searchQuery = encodeURIComponent(address);
  const url = `https://www.google.com/maps/search/?api=1&query=${searchQuery}`;
  window.open(url, '_blank', 'noopener,noreferrer');
};
</script>
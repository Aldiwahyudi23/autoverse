<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import CustomerForm from '@/Components/Forms/CustomerForm.vue';
import SellerForm from '@/Components/Forms/SellerForm.vue';
import TransactionForm from '@/Components/Forms/TransactionForm.vue';

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  inspectionId: {
    type: String,
    required: true
  },
  existingCustomer: {
    type: Object,
    default: null
  },
  existingSeller: {
    type: Object,
    default: null
  },
  existingTransaction: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'saved']);

// State
const isLoading = ref(false);
const errorMessage = ref('');
const currentStep = ref(1);
const showSellerForm = ref(false);
const customerErrors = ref({});
const sellerErrors = ref({});

// Inertia Form
const form = useForm({
  // Customer Data
  customer_id: '',
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  customer_address: '',
  
  // Seller Data
  seller_inspection_area: '',
  seller_inspection_address: '',
  seller_link_maps: '',
  seller_unit_holder_name: '',
  seller_unit_holder_phone: '',
  seller_settings: {},
  
  // Transaction Data
  transaction_amount: '',
  transaction_payment_method: '',
  transaction_status: 'pending',
  transaction_payment_date: new Date().toISOString().split('T')[0],
  transaction_notes: ''
});

// Computed untuk validasi form
const isFormValid = computed(() => {
  // Validasi Customer Form
  if (!form.customer_name || !form.customer_phone) {
    return false;
  }

  // Check for customer validation errors
  if (Object.values(customerErrors.value).some(error => error)) {
    return false;
  }

  // Validasi Seller Form (hanya jika harus ditampilkan)
  if (showSellerForm.value) {
    if (!form.seller_inspection_area ||
        !form.seller_inspection_address ||
        !form.seller_unit_holder_name ||
        !form.seller_unit_holder_phone) {
      return false;
    }

    // Check for seller validation errors
    if (Object.values(sellerErrors.value).some(error => error)) {
      return false;
    }
  }

  // Validasi Transaction Form
  if (!form.transaction_amount ||
      !form.transaction_payment_method ||
      !form.transaction_status) {
    return false;
  }

  return true;
});

// Inisialisasi form dengan data existing
onMounted(() => {
  if (props.existingCustomer) {
    form.customer_id = props.existingCustomer.id;
    form.customer_name = props.existingCustomer.name;
    form.customer_phone = props.existingCustomer.phone?.replace('62', '') || '';
    form.customer_email = props.existingCustomer.email || '';
    form.customer_address = props.existingCustomer.address || '';
    
    showSellerForm.value = true;
    currentStep.value = 3;
  }
  
  if (props.existingSeller) {
    form.seller_inspection_area = props.existingSeller.inspection_area || '';
    form.seller_inspection_address = props.existingSeller.inspection_address || '';
    form.seller_link_maps = props.existingSeller.link_maps || '';
    form.seller_unit_holder_name = props.existingSeller.unit_holder_name || '';
    form.seller_unit_holder_phone = props.existingSeller.unit_holder_phone?.replace('62', '') || '';
    form.seller_settings = props.existingSeller.settings || {};
  }
  
  if (props.existingTransaction) {
    form.transaction_amount = props.existingTransaction.amount || '';
    form.transaction_payment_method = props.existingTransaction.payment_method || '';
    form.transaction_status = props.existingTransaction.status || 'pending';
    form.transaction_payment_date = props.existingTransaction.payment_date 
      ? new Date(props.existingTransaction.payment_date).toISOString().split('T')[0]
      : new Date().toISOString().split('T')[0];
    form.transaction_notes = props.existingTransaction.notes || '';
  }
});

// Watch customer form untuk menentukan apakah seller form harus ditampilkan
watch([() => form.customer_name, () => form.customer_phone], ([newName, newPhone]) => {
  if (newName && newPhone) {
    showSellerForm.value = true;
    currentStep.value = 2;
  } else {
    showSellerForm.value = false;
    currentStep.value = 1;
  }
});

// Handle ketika customer dipilih dari CustomerForm
const handleCustomerSelected = (data) => {
  // Isi form seller dengan data dari seller yang sudah ada
  if (data.sellers && data.sellers.length > 0) {
    const seller = data.sellers[0];
    form.seller_inspection_area = seller.inspection_area || '';
    form.seller_inspection_address = seller.inspection_address || '';
    form.seller_link_maps = seller.link_maps || '';
    form.seller_unit_holder_name = seller.unit_holder_name || '';
    form.seller_unit_holder_phone = seller.unit_holder_phone?.replace('62', '') || '';
    form.seller_settings = seller.settings || {};
  }
  
  showSellerForm.value = true;
  currentStep.value = 2;
};

// Update form dari child components
const updateCustomerForm = (data) => {
  Object.assign(form, data);
};

const updateSellerForm = (data) => {
  Object.assign(form, data);
};

const updateTransactionForm = (data) => {
  Object.assign(form, data);
};

// Submit semua data menggunakan Inertia
const submitForm = () => {
  if (!isFormValid.value) {
    errorMessage.value = 'Harap lengkapi semua field yang wajib diisi';
    return;
  }

  // Don't close modal immediately - wait for success
  // emit('close');

  // Format phone numbers sebelum submit
  const formattedData = {
    ...form.data(),

    // Format phone numbers dengan +62
    customer_phone: form.customer_phone.startsWith('62')
      ? form.customer_phone
      : '62' + form.customer_phone.replace(/^0/, ''),

    seller_unit_holder_phone: form.seller_unit_holder_phone.startsWith('62')
      ? form.seller_unit_holder_phone
      : '62' + form.seller_unit_holder_phone.replace(/^0/, ''),

    // Konversi amount ke number
    transaction_amount: parseInt(form.transaction_amount, 10)
  };

  form.post(`/inspections-data/${props.inspectionId}/store-all-data`, formattedData, {
    preserveScroll: true,
    onSuccess: () => {
      // Close modal only on success
      emit('close');
      emit('saved');
    },
    onError: (errors) => {
      // Don't reopen modal - keep it open to show errors
      // emit('reopen');
      errorMessage.value = Object.values(errors)[0] || 'Gagal menyimpan data';
    }
  });
};
</script>

<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6 pb-4 border-b">
        <h3 class="text-lg font-bold">
          {{ existingCustomer ? 'Edit Data Inspeksi' : 'Tambah Data Inspeksi' }}
        </h3>
        <button
          @click="$emit('close')"
          class="text-gray-500 hover:text-gray-700"
        >
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Form utama -->
      <form @submit.prevent="submitForm" class="space-y-8">
        <!-- Step 1: Customer Form -->
        <div>
          <div class="flex items-center mb-4">
            <div :class="[
              'w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium',
              currentStep >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'
            ]">
              1
            </div>
            <h4 class="ml-3 text-sm font-medium text-gray-700">Data Customer</h4>
          </div>
          
          <CustomerForm
            :form-data="{
              customer_id: form.customer_id,
              customer_name: form.customer_name,
              customer_phone: form.customer_phone,
              customer_email: form.customer_email,
              customer_address: form.customer_address
            }"
            :existing-customer="existingCustomer"
            :existing-seller="existingSeller"
            @update:form="updateCustomerForm"
            @customer-selected="handleCustomerSelected"
            @update:errors="(errors) => customerErrors = errors"
          />
        </div>

        <!-- Step 2: Seller Form (hanya muncul jika customer sudah diisi) -->
        <div v-if="showSellerForm">
          <div class="flex items-center mb-4">
            <div :class="[
              'w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium',
              currentStep >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'
            ]">
              2
            </div>
            <h4 class="ml-3 text-sm font-medium text-gray-700">Lokasi & Penanggung Jawab</h4>
          </div>
          
          <SellerForm
            :form-data="{
              seller_inspection_area: form.seller_inspection_area,
              seller_inspection_address: form.seller_inspection_address,
              seller_link_maps: form.seller_link_maps,
              seller_unit_holder_name: form.seller_unit_holder_name,
              seller_unit_holder_phone: form.seller_unit_holder_phone,
              seller_settings: form.seller_settings
            }"
            :show-form="showSellerForm"
            :existing-seller="existingSeller"
            @update:form="updateSellerForm"
            @update:errors="(errors) => sellerErrors = errors"
          />
        </div>

        <!-- Step 3: Transaction Form -->
        <div>
          <div class="flex items-center mb-4">
            <div :class="[
              'w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium',
              currentStep >= 3 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'
            ]">
              3
            </div>
            <h4 class="ml-3 text-sm font-medium text-gray-700">Pembayaran</h4>
          </div>
          
          <TransactionForm
            :form-data="{
              transaction_amount: form.transaction_amount,
              transaction_payment_method: form.transaction_payment_method,
              transaction_status: form.transaction_status,
              transaction_payment_date: form.transaction_payment_date,
              transaction_notes: form.transaction_notes
            }"
            :existing-transaction="existingTransaction"
            @update:form="updateTransactionForm"
          />
        </div>

        <!-- Error Message -->
        <div v-if="form.hasErrors || errorMessage" class="p-3 bg-red-50 border border-red-200 rounded-md">
          <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>
          <p v-else-if="form.errors" class="text-sm text-red-600">
            {{ Object.values(form.errors)[0] }}
          </p>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex justify-end space-x-3 pt-6 border-t">
          <button
            type="button"
            @click="$emit('close')"
            :disabled="form.processing"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-400 disabled:opacity-50"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="form.processing || !isFormValid"
            class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 disabled:opacity-50"
          >
            {{ form.processing ? 'Menyimpan...' : 'Simpan Semua Data' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
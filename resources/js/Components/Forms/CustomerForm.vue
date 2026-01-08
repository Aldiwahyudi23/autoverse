<!-- resources/js/Components/Forms/CustomerForm.vue -->
<template>
  <div class="space-y-4">
    <!-- Pilih Customer yang sudah ada -->
    <div>
      <label class="block text-xs font-medium text-gray-600 mb-1">
        Pilih Customer yang sudah ada (opsional)
      </label>
      <div class="relative">
        <input
          v-model="customerSearch"
          @input="searchCustomers"
          @focus="showCustomerList = true"
          type="text"
          placeholder="Cari customer..."
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
        />
        
        <!-- Daftar Customer -->
        <div 
          v-if="showCustomerList && customers.length > 0"
          class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto"
        >
          <div 
            v-for="customer in customers"
            :key="customer.id"
            @click="selectCustomer(customer)"
            class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0"
          >
            <div class="font-medium text-sm">{{ customer.name }}</div>
            <div class="text-xs text-gray-500">{{ customer.phone }} • {{ customer.email }}</div>
          </div>
        </div>
      </div>
      <p class="text-xs text-gray-500 mt-1">
        Kosongkan jika ingin membuat customer baru
      </p>
    </div>

    <!-- Form Customer Baru (muncul jika tidak memilih customer yang sudah ada) -->
    <div v-if="!selectedCustomer">
      <h4 class="text-sm font-medium text-gray-700 mb-3 border-b pb-2">
        Data Customer Baru
      </h4>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">
            Nama Customer *
          </label>
          <input
            v-model="form.customer_name"
            type="text"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            placeholder="Nama lengkap customer"
          />
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">
            Nomor WhatsApp *
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <span class="text-gray-500 text-sm">+62</span>
            </div>
            <input
              v-model="form.customer_phone"
              type="tel"
              required
              class="w-full pl-10 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
              placeholder="8123456789"
            />
          </div>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-600 mb-1">
            Email
          </label>
          <input
            v-model="form.customer_email"
            type="email"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
            placeholder="customer@email.com"
          />
        </div>

      </div>
    </div>

    <!-- Info Customer yang dipilih -->
    <div v-if="selectedCustomer" class="p-3 bg-blue-50 border border-blue-200 rounded-md">
      <div class="flex justify-between items-start">
        <div>
          <h5 class="text-sm font-semibold text-blue-800">Customer terpilih:</h5>
          <p class="text-sm text-gray-700">{{ selectedCustomer.name }}</p>
          <p class="text-xs text-gray-600">{{ selectedCustomer.phone }}</p>
          <p class="text-xs text-gray-600">{{ selectedCustomer.email }}</p>
        </div>
        <button
          @click="clearSelectedCustomer"
          type="button"
          class="text-xs text-red-600 hover:text-red-800"
        >
          Ubah
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import debounce from 'lodash/debounce';

const emit = defineEmits(['update:form', 'customer-selected']);

const props = defineProps({
  formData: {
    type: Object,
    required: true
  },
  existingCustomer: {
    type: Object,
    default: null
  },
  existingSeller: {
    type: Object,
    default: null
  }
});

// State
const customerSearch = ref('');
const customers = ref([]);
const showCustomerList = ref(false);
const selectedCustomer = ref(null);

// Form
const form = ref({
  customer_id: '',
  customer_name: '',
  customer_phone: '',
  customer_email: '',
  customer_address: ''
});

// Inisialisasi form
onMounted(() => {
  if (props.existingCustomer) {
    form.value = {
      customer_id: props.existingCustomer.id,
      customer_name: props.existingCustomer.name,
      customer_phone: props.existingCustomer.phone?.replace('62', '') || '',
      customer_email: props.existingCustomer.email || '',
      customer_address: props.existingCustomer.address || ''
    };
    selectedCustomer.value = props.existingCustomer;
  } else {
    form.value = { ...props.formData };
  }
});

// Watch form changes
watch(form, (newForm) => {
  emit('update:form', newForm);
}, { deep: true });

// Search customers dengan debounce
const searchCustomers = debounce(async () => {
  if (!customerSearch.value.trim()) {
    customers.value = [];
    return;
  }

  try {
    const response = await fetch(`/customers/search?search=${encodeURIComponent(customerSearch.value)}`);
    if (response.ok) {
      customers.value = await response.json();
    }
  } catch (error) {
    console.error('Error searching customers:', error);
    customers.value = [];
  }
}, 300);

// Select customer
const selectCustomer = (customer) => {
  selectedCustomer.value = customer;
  form.value = {
    customer_id: customer.id,
    customer_name: customer.name,
    customer_phone: customer.phone?.replace('62', '') || '',
    customer_email: customer.email || '',
    customer_address: customer.address || ''
  };
  
  // Jika customer memiliki seller, isi form seller
  if (customer.sellers && customer.sellers.length > 0) {
    emit('customer-selected', {
      customer,
      sellers: customer.sellers
    });
  }
  
  showCustomerList.value = false;
  customerSearch.value = '';
};

// Clear selected customer
const clearSelectedCustomer = () => {
  selectedCustomer.value = null;
  form.value = {
    customer_id: '',
    customer_name: '',
    customer_phone: '',
    customer_email: '',
    customer_address: ''
  };
};

// Close customer list when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    showCustomerList.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
<!-- resources/js/Components/Forms/TransactionForm.vue -->
<template>
  <div class="space-y-4">
    <h4 class="text-sm font-medium text-gray-700 mb-3 border-b pb-2">
      Data Pembayaran
    </h4>

    <div class="space-y-4">
      <!-- Nominal Pembayaran -->
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">
          Nominal Pembayaran *
        </label>
        <input
          v-model="formattedAmount"
          @input="handleAmountInput"
          type="text"
          required
          placeholder="Contoh: 250000"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
        />
      </div>

      <!-- Metode Pembayaran -->
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">
          Metode Pembayaran *
        </label>
        <select
          v-model="form.transaction_payment_method"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
        >
          <option value="">Pilih Metode</option>
          <option value="cash">Cash</option>
          <option value="transfer">Transfer</option>
          <option value="debit_card">Kartu Debit</option>
          <option value="credit_card">Kartu Kredit</option>
          <option value="qris">QRIS</option>
        </select>
      </div>

      <!-- Status Pembayaran -->
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">
          Status *
        </label>
        <select
          v-model="form.transaction_status"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
        >
          <option value="pending">Menunggu Pembayaran</option>
          <option value="paid">Sudah Dibayar</option>
          <option value="failed">Gagal</option>
          <option value="refunded">Dikembalikan</option>
          <option value="expired">Kadaluarsa</option>
        </select>
      </div>

      <!-- Tanggal Pembayaran (muncul jika status = paid) -->
      <div v-if="form.transaction_status === 'paid'">
        <label class="block text-xs font-medium text-gray-600 mb-1">
          Tanggal Pembayaran
        </label>
        <input
          v-model="form.transaction_payment_date"
          type="date"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
        />
      </div>

      <!-- Catatan -->
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">
          Catatan
        </label>
        <textarea
          v-model="form.transaction_notes"
          rows="2"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
          placeholder="Catatan tambahan..."
        ></textarea>
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
  existingTransaction: {
    type: Object,
    default: null
  }
});

// Format amount untuk input
const formattedAmount = ref('');

// Form
const form = ref({
  transaction_amount: '',
  transaction_payment_method: '',
  transaction_status: 'pending',
  transaction_payment_date: new Date().toISOString().split('T')[0],
  transaction_notes: ''
});

// Inisialisasi form
onMounted(() => {
  if (props.existingTransaction) {
    const amount = props.existingTransaction.amount || 0;
    form.value = {
      transaction_amount: amount,
      transaction_payment_method: props.existingTransaction.payment_method || '',
      transaction_status: props.existingTransaction.status || 'pending',
      transaction_payment_date: props.existingTransaction.payment_date 
        ? new Date(props.existingTransaction.payment_date).toISOString().split('T')[0]
        : new Date().toISOString().split('T')[0],
      transaction_notes: props.existingTransaction.notes || ''
    };
    
    // Format amount untuk display
    formattedAmount.value = formatAmountForInput(amount);
  } else {
    form.value = { ...props.formData };
  }
});

// Fungsi format mata uang
const formatAmountForInput = (value) => {
  if (!value) return '';
  const cleanNumber = String(value).replace(/\D/g, '');
  const parsedValue = parseInt(cleanNumber, 10);
  if (isNaN(parsedValue)) return '';
  
  const formatter = new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  });

  return formatter.format(parsedValue);
};

// Handle input amount
const handleAmountInput = (event) => {
  const cleanValue = event.target.value.replace(/\D/g, '');
  form.value.transaction_amount = cleanValue;
  formattedAmount.value = formatAmountForInput(cleanValue);
};

// Watch form changes
watch(form, (newForm) => {
  emit('update:form', newForm);
}, { deep: true });
</script>
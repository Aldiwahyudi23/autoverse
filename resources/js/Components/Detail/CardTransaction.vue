<!-- resources/js/Components/Detail/CardTransaction.vue -->
<template>
  <div 
    class="p-3 rounded border"
    :class="{
      'bg-green-50 border-green-200': isPaid,
      'bg-red-50 border-red-200': isFailed,
      'bg-yellow-50 border-yellow-200': isPending
    }"
  >
    <div class="flex justify-between items-center mb-2">
      <h4 class="text-sm font-medium text-gray-700 flex items-center">
        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        Pembayaran
      </h4>
    </div>

    <div v-if="transaction" class="space-y-2">
      <!-- Status -->
      <div class="flex justify-between items-center">
        <span class="text-xs font-medium text-gray-600">Status:</span>
        <span 
          :class="{
            'text-green-600 font-semibold': isPaid,
            'text-yellow-600 font-semibold': isPending,
            'text-red-600 font-semibold': isFailed,
            'text-gray-600 font-semibold': isOtherStatus
          }"
          class="text-xs"
        >
          {{ statusTranslations[transaction.status] }}
        </span>
      </div>

      <!-- Amount -->
      <div class="flex justify-between items-center">
        <span class="text-xs font-medium text-gray-600">Nominal:</span>
        <span 
          :class="{
            'text-green-700 font-semibold': isPaid,
            'text-yellow-700 font-semibold': isPending,
            'text-red-700 font-semibold': isFailed
          }"
          class="text-xs"
        >
          {{ formatCurrency(transaction.amount) }}
        </span>
      </div>

      <!-- Payment Method -->
      <div v-if="transaction.payment_method" class="flex justify-between items-center">
        <span class="text-xs font-medium text-gray-600">Metode:</span>
        <span 
          :class="{
            'text-green-700': isPaid,
            'text-yellow-700': isPending,
            'text-red-700': isFailed
          }"
          class="text-xs"
        >
          {{ paymentMethodTranslations[transaction.payment_method] || transaction.payment_method }}
        </span>
      </div>

      <!-- Invoice Number -->
      <div v-if="transaction.invoice_number" class="flex justify-between items-center">
        <span class="text-xs font-medium text-gray-600">Invoice:</span>
        <span class="text-xs text-gray-700 font-mono">{{ transaction.invoice_number }}</span>
      </div>

      <!-- Payment Date -->
      <div v-if="transaction.payment_date" class="flex justify-between items-center">
        <span class="text-xs font-medium text-gray-600">Tanggal:</span>
        <span class="text-xs text-gray-700">
          {{ new Date(transaction.payment_date).toLocaleDateString('id-ID') }}
        </span>
      </div>

      <!-- Payer Info -->
      <div v-if="transaction.payer && transaction.payer.name" class="flex justify-between items-center">
        <span class="text-xs font-medium text-gray-600">Dibayar oleh:</span>
        <span class="text-xs text-gray-700">{{ transaction.payer.name }}</span>
      </div>
    </div>

    <div v-else>
      <p class="text-xs text-yellow-700">Data transaksi belum tersedia</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  transaction: {
    type: Object,
    default: null
  },
  showEditButton: {
    type: Boolean,
    default: true
  }
});

// Status terjemahan
const statusTranslations = {
  'pending': 'Menunggu Pembayaran',
  'paid': 'Sudah Dibayar',
  'failed': 'Gagal',
  'refunded': 'Dikembalikan',
  'expired': 'Kadaluarsa'
};

// Metode pembayaran terjemahan
const paymentMethodTranslations = {
  'cash': 'Cash',
  'transfer': 'Transfer',
  'debit_card': 'Kartu Debit',
  'credit_card': 'Kartu Kredit',
  'qris': 'QRIS'
};

// Status computed
const isPaid = computed(() => props.transaction?.status === 'paid');
const isPending = computed(() => props.transaction?.status === 'pending');
const isFailed = computed(() => props.transaction?.status === 'failed');
const isOtherStatus = computed(() => !isPaid.value && !isPending.value && !isFailed.value);

// Format currency
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(amount);
};

defineEmits(['edit']);
</script>
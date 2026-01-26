<template>
  <div class="relative">
    <input
      type="number"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :placeholder="placeholder"
      :disabled="disabled"
      :min="min"
      :max="max"
      :step="step"
      :class="[
        'shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md pr-10',
        disabled ? 'bg-gray-100 cursor-not-allowed' : ''
      ]"
    />
    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
      <span class="text-gray-500 sm:text-sm">{{ unit }}</span>
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: [String, Number],
  placeholder: {
    type: String,
    default: 'Masukkan angka...'
  },
  disabled: Boolean,
  min: Number,
  max: Number,
  step: {
    type: [String, Number],
    default: 1
  },
  unit: {
    type: String,
    default: ''
  }
});

defineEmits(['update:modelValue']);
</script>
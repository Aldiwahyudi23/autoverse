<template>
  <div>
    <select
      :value="modelValue"
      @change="$emit('update:modelValue', $event.target.value)"
      :disabled="disabled"
      :class="[
        'shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md',
        disabled ? 'bg-gray-100 cursor-not-allowed' : ''
      ]"
    >
      <option value="" disabled>{{ placeholder }}</option>
      <option v-for="option in options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>
  </div>
</template>

<script setup>
defineProps({
  modelValue: [String, Number],
  placeholder: {
    type: String,
    default: 'Pilih opsi...'
  },
  disabled: Boolean,
  options: {
    type: Array,
    default: () => []
  }
});

defineEmits(['update:modelValue']);
</script>
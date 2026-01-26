<template>
  <div>
    <input
      type="date"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :disabled="disabled"
      :min="minDate"
      :max="maxDate"
      :class="[
        'shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md',
        disabled ? 'bg-gray-100 cursor-not-allowed' : ''
      ]"
    />
  </div>
</template>

<script setup>
defineProps({
  modelValue: [String, Number],
  disabled: Boolean,
  minDate: String,
  maxDate: String
});

defineEmits(['update:modelValue']);
</script>
<template>
  <div>
    <input
      :type="inputType"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :placeholder="placeholder"
      :disabled="disabled"
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
  placeholder: {
    type: String,
    default: 'Masukkan teks...'
  },
  disabled: Boolean,
  inputType: {
    type: String,
    default: 'text'
  }
});

defineEmits(['update:modelValue']);
</script>
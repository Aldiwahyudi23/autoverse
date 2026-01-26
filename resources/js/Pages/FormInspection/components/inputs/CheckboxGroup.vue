<template>
  <div class="space-y-2">
    <div v-for="option in options" :key="option.value" class="flex items-center">
      <input
        :id="`checkbox-${option.value}`"
        type="checkbox"
        :checked="isChecked(option.value)"
        @change="toggleCheck(option.value)"
        :disabled="disabled"
        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded"
      />
      <label :for="`checkbox-${option.value}`" class="ml-3 block text-sm font-medium text-gray-700">
        {{ option.label }}
      </label>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  disabled: Boolean,
  options: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue']);

const isChecked = (value) => {
  return props.modelValue.includes(value);
};

const toggleCheck = (value) => {
  const newValue = [...props.modelValue];
  const index = newValue.indexOf(value);
  
  if (index > -1) {
    newValue.splice(index, 1);
  } else {
    newValue.push(value);
  }
  
  emit('update:modelValue', newValue);
};
</script>
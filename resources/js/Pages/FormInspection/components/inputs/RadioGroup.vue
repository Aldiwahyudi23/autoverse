<template>
  <div class="space-y-2">
    <div v-for="option in options" :key="option.value" class="flex items-center">
      <input
        :id="`radio-${option.value}`"
        type="radio"
        :checked="modelValue === option.value"
        @change="$emit('update:modelValue', option.value)"
        :disabled="disabled"
        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300"
      />
      <label :for="`radio-${option.value}`" class="ml-3 block text-sm font-medium text-gray-700">
        {{ option.label }}
      </label>
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: [String, Number],
  disabled: Boolean,
  options: {
    type: Array,
    default: () => []
  }
});

defineEmits(['update:modelValue']);
</script>
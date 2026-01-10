<template>
  <!-- Kamera HP Native (TANPA GALERI) -->
  <input
    ref="cameraInput"
    type="file"
    accept="image/*"
    capture="environment"
    class="hidden"
    @change="onCapture"
  />
</template>

<script setup>
import { ref } from 'vue'

const emit = defineEmits(['photoCaptured'])

const cameraInput = ref(null)

/* =========================
   PUBLIC METHOD
========================= */
const openCamera = () => {
  cameraInput.value?.click()
}

defineExpose({ openCamera })

/* =========================
   HANDLE RESULT
========================= */
const onCapture = async (e) => {
  const file = e.target.files?.[0]
  if (!file) return

  emit('directPhotoCaptured', file)

  // Reset input agar bisa capture ulang
  e.target.value = ''
}
</script>

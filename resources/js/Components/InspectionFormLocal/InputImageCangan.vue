<template>
  <div>
    <!-- GALLERY INPUT -->
    <input
      ref="galleryInput"
      type="file"
      accept="image/*"
      class="hidden"
      @change="onFileSelected"
    />

    <!-- CAMERA INPUT (KAMERA HP ASLI) -->
    <input
      ref="cameraInput"
      type="file"
      accept="image/*"
      capture="environment"
      class="hidden"
      @change="onFileSelected"
    />

    <!-- BUTTON -->
    <div
      class="border-2 border-dashed rounded-lg h-28 flex flex-col items-center justify-center cursor-pointer bg-gray-50"
      @click="openSourceModal"
    >
      <span class="text-gray-600 font-medium">Upload Foto</span>
      <span class="text-xs text-gray-400">Kamera / Galeri</span>
    </div>

    <!-- PREVIEW -->
    <div v-if="preview" class="mt-3">
      <img :src="preview" class="w-32 h-32 object-cover rounded-md border" />
    </div>

    <!-- SOURCE OPTIONS MODAL -->
    <ImageSourceOptionsModal
      :show="showSourceModal"
      @close="showSourceModal = false"
      @camera="openCamera"
      @gallery="openGallery"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ImageSourceOptionsModal from './Modal-uploader/ImageSourceOptionsModal.vue';
const emit = defineEmits(['selected'])

const galleryInput = ref(null)
const cameraInput = ref(null)
const showSourceModal = ref(false)
const preview = ref(null)

const openSourceModal = () => {
  showSourceModal.value = true
}

const openGallery = () => {
  showSourceModal.value = false
  galleryInput.value.click()
}

const openCamera = () => {
  showSourceModal.value = false
  cameraInput.value.click()
}

const onFileSelected = (e) => {
  const file = e.target.files[0]
  if (!file) return

  preview.value = URL.createObjectURL(file)
  emit('selected', file)

  e.target.value = ''
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-end">
    <!-- BACKDROP -->
    <div
      class="absolute inset-0 bg-black/50"
      @click="$emit('close')"
    ></div>

    <!-- MODAL -->
    <div class="relative w-full bg-white rounded-t-2xl p-5">
      <h3 class="text-center font-semibold mb-4">
        Pilih Sumber Foto
      </h3>

      <div class="flex justify-around">
        <button
          class="flex flex-col items-center gap-2"
          @click="$emit('camera')"
        >
          <div class="w-14 h-14 rounded-full bg-indigo-100 flex items-center justify-center">
            📷
          </div>
          <span class="text-sm">Kamera</span>
        </button>

        <button
          class="flex flex-col items-center gap-2"
          @click="$emit('gallery')"
        >
          <div class="w-14 h-14 rounded-full bg-green-100 flex items-center justify-center">
            🖼️
          </div>
          <span class="text-sm">Galeri</span>
        </button>
      </div>

      <button
        class="mt-6 w-full text-center text-gray-500 text-sm"
        @click="$emit('close')"
      >
        Batal
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean
})
</script>

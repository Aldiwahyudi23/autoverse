<template>
  <div v-if="show" class="lg:col-span-5 bg-white rounded-lg shadow-md p-4 overflow-y-auto h-[calc(100vh-2rem)] sticky top-4">
    <!-- Header Sidebar -->
    <div class="mb-4 pb-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-800">Review Gambar</h3>
        <button
          @click="$emit('close')"
          class="text-gray-500 hover:text-gray-700"
          title="Tutup sidebar"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Konten Gambar -->
    <div class="space-y-4">
      <!-- Gambar Utama -->
      <div
        ref="imageContainer"
        class="relative bg-gray-100 rounded-lg overflow-hidden"
        @mousedown="startDrag"
        @mousemove="doDrag"
        @mouseup="endDrag"
        @mouseleave="endDrag"
        @wheel="handleWheelZoom"
      >
        <img
          ref="imageElement"
          :src="imageUrl"
          alt="Gambar Preview"
          class="w-full h-auto max-h-80 object-contain cursor-grab active:cursor-grabbing"
          :style="{
            transform: `scale(${zoomLevel}) translate(${dragOffset.x}px, ${dragOffset.y}px) rotate(${rotationAngle}deg)`,
            transition: isDragging ? 'none' : 'transform 0.2s ease',
            transformOrigin: 'center center'
          }"
          @load="handleImageLoad"
        >

        <!-- Navigation Buttons -->
        <div class="absolute inset-x-0 top-1/2 transform -translate-y-1/2 flex justify-between px-2">
          <button
            @click="$emit('previous')"
            :disabled="!hasPrevious"
            class="bg-black bg-opacity-50 text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-70 transition-all disabled:opacity-30 disabled:cursor-not-allowed"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button
            @click="$emit('next')"
            :disabled="!hasNext"
            class="bg-black bg-opacity-50 text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-70 transition-all disabled:opacity-30 disabled:cursor-not-allowed"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Zoom Controls -->
      <div class="bg-gray-50 rounded-lg p-3">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700">Zoom</span>
          <span class="text-sm font-medium text-gray-900">{{ Math.round(zoomLevel * 100) }}%</span>
        </div>
        <div class="flex items-center gap-2 mb-3">
          <button
            @click="zoomOut"
            :disabled="zoomLevel <= 0.5"
            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
            </svg>
          </button>
          <button
            @click="resetZoomAndPosition"
            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition-colors"
          >
            Reset
          </button>
          <button
            @click="zoomIn"
            :disabled="zoomLevel >= 3"
            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
          </button>
        </div>

        <!-- Rotation Controls -->
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700">Rotasi</span>
          <span class="text-sm font-medium text-gray-900">{{ rotationAngle }}°</span>
        </div>
        <div class="flex items-center gap-2">
          <button
            @click="rotateLeft"
            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 rounded-lg transition-colors"
            title="Putar ke Kiri"
          >
            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12a9 9 0 019-9 9.75 9.75 0 017.5 3.5L21 12m0 0l-3.5-3.5M21 12H15" />
            </svg>
          </button>
          <button
            @click="rotateRight"
            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-2 rounded-lg transition-colors"
            title="Putar ke Kanan"
          >
            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9 9.75 9.75 0 01-7.5-3.5L3 12m0 0l3.5 3.5M3 12h6" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Informasi Gambar -->
      <div class="bg-gray-50 rounded-lg p-4">
        <div class="space-y-3">
          <!-- Informasi Komponen -->
          <div>
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Komponen</label>
            <p class="text-sm font-medium text-gray-900 mt-1">{{ currentImageInfo.componentName || '-' }}</p>
          </div>

          <!-- Informasi Poin -->
          <div>
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Poin Inspeksi</label>
            <div class="flex items-center gap-2 mt-1">
              <p class="text-sm font-medium text-gray-900 flex-1">{{ currentImageInfo.pointName || '-' }}</p>
              <span 
                v-if="currentImageInfo.status"
                :class="['px-2 py-1 rounded-full text-xs font-semibold', getStatusClass(currentImageInfo.status)]"
              >
                {{ currentImageInfo.status }}
              </span>
            </div>
          </div>

          <!-- Catatan -->
          <div v-if="currentImageInfo.note">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Catatan</label>
            <p class="text-sm text-gray-700 italic mt-1">{{ currentImageInfo.note }}</p>
          </div>

          <!-- Informasi Terkait -->
          <div v-if="currentImageInfo.relatedInfo" class="pt-3 border-t border-gray-200">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Informasi Terkait</label>
            <div class="mt-2 space-y-1">
              <div v-for="(info, key) in currentImageInfo.relatedInfo" :key="key" class="text-xs text-gray-600">
                <span class="font-medium">{{ info.name }}:</span> {{ info.value }}
              </div>
            </div>
          </div>

          <!-- Estimasi Perbaikan -->
          <div v-if="showRepairEstimationButton" class="pt-3 border-t border-gray-200">
            <button
              @click="$emit('repair-estimation')"
              class="w-full bg-orange-600 hover:bg-orange-700 text-white py-2 px-4 rounded-lg transition-colors flex items-center justify-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <span class="text-sm font-medium">Tambah Estimasi Perbaikan</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Image Counter -->
      <div class="text-center text-sm text-gray-500">
        Gambar {{ currentIndex + 1 }} dari {{ totalImages }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick, watch } from 'vue'

const props = defineProps({
  show: Boolean,
  imageUrl: String,
  currentIndex: Number,
  totalImages: Number,
  hasPrevious: Boolean,
  hasNext: Boolean,
  currentImageInfo: {
    type: Object,
    default: () => ({
      componentName: '',
      pointName: '',
      status: '',
      note: '',
      relatedInfo: null
    })
  },
  showRepairEstimationButton: Boolean
})

const emit = defineEmits([
  'close',
  'previous',
  'next',
  'repair-estimation',
  'zoom-change',
  'rotation-change'
])

// Image controls
const zoomLevel = ref(1)
const rotationAngle = ref(0)
const isDragging = ref(false)
const dragStart = ref({ x: 0, y: 0 })
const dragOffset = ref({ x: 0, y: 0 })
const imageDimensions = ref({ width: 0, height: 0 })

// Refs
const imageContainer = ref(null)
const imageElement = ref(null)

// Zoom functions
const zoomIn = () => {
  if (zoomLevel.value < 3) {
    zoomLevel.value = Math.min(zoomLevel.value + 0.25, 3)
    emit('zoom-change', zoomLevel.value)
  }
}

const zoomOut = () => {
  if (zoomLevel.value > 0.5) {
    zoomLevel.value = Math.max(zoomLevel.value - 0.25, 0.5)
    emit('zoom-change', zoomLevel.value)
  }
}

const resetZoomAndPosition = () => {
  zoomLevel.value = 1
  dragOffset.value = { x: 0, y: 0 }
  rotationAngle.value = 0
  emit('zoom-change', zoomLevel.value)
  emit('rotation-change', rotationAngle.value)
}

// Rotation functions
const rotateLeft = () => {
  rotationAngle.value = (rotationAngle.value - 90) % 360
  emit('rotation-change', rotationAngle.value)
}

const rotateRight = () => {
  rotationAngle.value = (rotationAngle.value + 90) % 360
  emit('rotation-change', rotationAngle.value)
}

// Draggable functions
const startDrag = (event) => {
  if (zoomLevel.value <= 1) return

  isDragging.value = true
  dragStart.value = {
    x: event.clientX - dragOffset.value.x,
    y: event.clientY - dragOffset.value.y
  }

  event.preventDefault()
}

const doDrag = (event) => {
  if (!isDragging.value) return

  const newX = event.clientX - dragStart.value.x
  const newY = event.clientY - dragStart.value.y

  const containerRect = imageContainer.value?.getBoundingClientRect()
  if (!containerRect) return

  const scaledWidth = imageDimensions.value.width * zoomLevel.value
  const scaledHeight = imageDimensions.value.height * zoomLevel.value

  const maxX = Math.max(0, (scaledWidth - containerRect.width) / 2)
  const maxY = Math.max(0, (scaledHeight - containerRect.height) / 2)

  dragOffset.value = {
    x: Math.max(-maxX, Math.min(maxX, newX)),
    y: Math.max(-maxY, Math.min(maxY, newY))
  }

  event.preventDefault()
}

const endDrag = () => {
  isDragging.value = false
}

// Wheel zoom for desktop
const handleWheelZoom = (event) => {
  event.preventDefault()
  const delta = event.deltaY > 0 ? -0.1 : 0.1
  zoomLevel.value = Math.max(0.5, Math.min(3, zoomLevel.value + delta))
  emit('zoom-change', zoomLevel.value)
}

// Handle image load
const handleImageLoad = () => {
  nextTick(() => {
    if (imageElement.value) {
      const img = imageElement.value
      imageDimensions.value = {
        width: img.naturalWidth,
        height: img.naturalHeight
      }
    }
  })
}

// Helper function untuk status class
const getStatusClass = (status) => {
  if (!status) return 'status-warning'

  const statusStr = String(status).toLowerCase().trim()

  if (['normal', 'ada', 'baik', 'good', 'ok'].includes(statusStr)) {
    return 'status-good'
  } else if (['tidak normal', 'tidak ada', 'rusak', 'bad', 'not ok','rusak','repaired'].includes(statusStr)) {
    return 'status-bad'
  }
  return 'status-warning'
}

// Reset when image changes
watch(() => props.imageUrl, () => {
  zoomLevel.value = 1
  dragOffset.value = { x: 0, y: 0 }
  rotationAngle.value = 0
})
</script>

<style scoped>
.cursor-grab {
  cursor: grab;
}

.cursor-grabbing {
  cursor: grabbing;
}

.status-good {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.status-bad {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.status-warning {
  background-color: #fff3cd;
  color: #856404;
  border: 1px solid #ffeaa7;
}
</style>
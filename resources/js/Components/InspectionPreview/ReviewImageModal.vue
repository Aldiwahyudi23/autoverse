<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-95 flex flex-col items-center justify-center z-50"
       @click.self="$emit('close')">

    <!-- Header Modal -->
    <div class="absolute top-0 left-0 right-0 bg-black bg-opacity-90 text-white p-3 md:p-4 flex items-center justify-between z-20">
      <div class="flex-1 text-center">
        <h3 class="text-base md:text-lg font-bold truncate max-w-[70vw] mx-auto">
          {{ currentImageInfo.componentName || 'Gambar Inspeksi' }}
        </h3>
      </div>
      
      <button
        @click="$emit('close')"
        class="bg-red-600 hover:bg-red-700 text-white rounded-full w-8 h-8 md:w-10 md:h-10 flex items-center justify-center transition-all flex-shrink-0 ml-2 md:ml-4"
      >
        <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <!-- Konten Gambar dengan Draggable -->
    <div class="relative w-full h-full flex items-center justify-center p-2 md:p-4 pt-12 md:pt-16 pb-32 md:pb-40">
      <!-- Previous Button -->
      <button
        v-if="hasPrevious"
        @click="$emit('previous')"
        class="absolute left-2 md:left-4 top-1/2 transform -translate-y-1/2 z-10 bg-black bg-opacity-60 text-white rounded-full w-10 h-10 md:w-12 md:h-12 flex items-center justify-center hover:bg-opacity-80 transition-all hover:scale-110"
      >
        <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
      </button>

      <!-- Gambar dengan Zoom dan Draggable -->
      <div
        ref="imageContainer"
        class="relative overflow-hidden w-full h-full flex items-center justify-center"
        :class="{ 'dragging': isDragging }"
        :style="{ touchAction: zoomLevel > 1 ? 'none' : 'pan-y' }"
        @touchstart.stop="handleTouchStart"
        @touchmove="handleTouchMove"
        @touchend="handleTouchEnd"
        @mousedown="startDrag"
        @mousemove="doDrag"
        @mouseup="endDrag"
        @mouseleave="endDrag"
      >
        <img
          v-if="imageUrl"
          ref="imageElement"
          :src="imageUrl"
          alt="Full size image"
          class="absolute max-w-none object-contain cursor-grab active:cursor-grabbing"
          :style="{
            transform: `scale(${zoomLevel}) translate(${dragOffset.x}px, ${dragOffset.y}px) rotate(${rotationAngle}deg)`,
            transition: isDragging ? 'none' : 'transform 0.2s ease',
            width: imageDimensions.width + 'px',
            height: imageDimensions.height + 'px'
          }"
          @load="handleImageLoad"
          @click.stop
        />
        <div v-else class="text-white text-base md:text-lg">
          Gambar tidak dapat dimuat
        </div>
      </div>

      <!-- Next Button -->
      <button
        v-if="hasNext"
        @click="$emit('next')"
        class="absolute right-2 md:right-4 top-1/2 transform -translate-y-1/2 z-10 bg-black bg-opacity-60 text-white rounded-full w-10 h-10 md:w-12 md:h-12 flex items-center justify-center hover:bg-opacity-80 transition-all hover:scale-110"
      >
        <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>

    <!-- Footer Modal -->
    <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-90 z-20 max-h-[60vh] overflow-y-auto">
      <!-- Zoom Controls -->
      <div class="p-2 md:p-3 border-b border-gray-700 sticky top-0 bg-black bg-opacity-90">
        <div class="flex items-center justify-between">
          <!-- Image Counter -->
          <div class="text-xs md:text-sm font-medium text-gray-300">
            Gambar {{ currentIndex + 1 }} / {{ totalImages }}
          </div>

          <!-- Zoom Controls -->
          <div class="flex items-center gap-2 md:gap-3">
            <div class="flex items-center gap-1 md:gap-2">
              <button
                @click="zoomOut"
                class="bg-gray-800 hover:bg-gray-700 text-white rounded-lg w-7 h-7 md:w-9 md:h-9 flex items-center justify-center transition-colors"
                :disabled="zoomLevel <= 0.5"
                :class="{ 'opacity-50 cursor-not-allowed': zoomLevel <= 0.5 }"
                title="Zoom Out"
              >
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
              </button>

              <div class="w-12 md:w-16 text-center">
                <span class="text-xs md:text-sm font-medium text-white">{{ Math.round(zoomLevel * 100) }}%</span>
              </div>

              <button
                @click="zoomIn"
                class="bg-gray-800 hover:bg-gray-700 text-white rounded-lg w-7 h-7 md:w-9 md:h-9 flex items-center justify-center transition-colors"
                :disabled="zoomLevel >= 3"
                :class="{ 'opacity-50 cursor-not-allowed': zoomLevel >= 3 }"
                title="Zoom In"
              >
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </button>
            </div>

            <!-- Rotation Controls -->
            <div class="flex items-center gap-1 md:gap-2">
              <button
                @click="rotateLeft"
                class="bg-gray-800 hover:bg-gray-700 text-white rounded-lg w-7 h-7 md:w-9 md:h-9 flex items-center justify-center transition-colors"
                title="Putar ke Kiri"
              >
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12a9 9 0 019-9 9.75 9.75 0 017.5 3.5L21 12m0 0l-3.5-3.5M21 12H15" />
                </svg>
              </button>

              <div class="w-8 md:w-10 text-center">
                <span class="text-xs md:text-sm font-medium text-white">{{ rotationAngle }}°</span>
              </div>

              <button
                @click="rotateRight"
                class="bg-gray-800 hover:bg-gray-700 text-white rounded-lg w-7 h-7 md:w-9 md:h-9 flex items-center justify-center transition-colors"
                title="Putar ke Kanan"
              >
                <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9 9.75 9.75 0 01-7.5-3.5L3 12m0 0l3.5 3.5M3 12h6" />
                </svg>
              </button>
            </div>

            <button
              @click="resetZoomAndPosition"
              class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-2 py-1 md:px-3 md:py-1.5 text-xs md:text-sm transition-colors whitespace-nowrap"
              title="Reset Zoom dan Posisi"
            >
              Reset
            </button>
          </div>
        </div>
      </div>

      <!-- Info Point, Status, dan Catatan -->
      <div class="p-3 md:p-4">
        <!-- Nama Point dan Status -->
        <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-2 mb-1 md:mb-2">
          <h4 class="text-sm md:text-base font-semibold text-white truncate">
            {{ currentImageInfo.pointName || 'Poin Inspeksi' }}
          </h4>
          <div v-if="currentImageInfo.status" class="flex items-center flex-shrink-0">
            <span class="text-xs md:text-sm font-medium text-gray-300">(</span>
            <span :class="['px-2 py-0.5 md:py-1 rounded-full text-xs md:text-sm font-semibold', getStatusClass(currentImageInfo.status)]">
              {{ currentImageInfo.status }}
            </span>
            <span class="text-xs md:text-sm font-medium text-gray-300">)</span>
          </div>
        </div>
        
        <!-- Catatan -->
        <div v-if="currentImageInfo.note" class="mt-1 md:mt-2 mb-2">
          <div class="text-xs md:text-sm text-gray-200 italic break-words leading-relaxed">
            {{ currentImageInfo.note }}
          </div>
        </div>

        <!-- Informasi Terkait -->
        <div v-if="currentImageInfo.relatedInfo" class="mt-2 mb-2 p-2 bg-gray-800 rounded-lg">
          <div class="text-xs font-semibold text-gray-300 mb-1">Informasi Terkait:</div>
          <div v-for="(info, key) in currentImageInfo.relatedInfo" :key="key" class="text-xs text-gray-200">
            <span class="font-medium">{{ info.name }}:</span> {{ info.value }}
          </div>
        </div>

        <!-- Tombol Estimasi Perbaikan -->
        <div v-if="showRepairEstimationButton" class="mt-2 pt-2 border-t border-gray-700">
          <button
            @click="$emit('repair-estimation')"
            class="w-full bg-orange-600 hover:bg-orange-700 text-white py-2 px-3 rounded-lg transition-colors flex items-center justify-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span class="text-sm font-medium">Tambah Estimasi Perbaikan</span>
          </button>
        </div>
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

// Touch gesture state
const isPinching = ref(false)
const initialDistance = ref(0)
const initialZoom = ref(1)

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
  const clientX = event.type.includes('mouse') ? event.clientX : event.touches[0].clientX
  const clientY = event.type.includes('mouse') ? event.clientY : event.touches[0].clientY

  dragStart.value = {
    x: clientX - dragOffset.value.x,
    y: clientY - dragOffset.value.y
  }

  event.preventDefault()
}

const doDrag = (event) => {
  if (!isDragging.value) return

  const clientX = event.type.includes('mouse') ? event.clientX : event.touches[0].clientX
  const clientY = event.type.includes('mouse') ? event.clientY : event.touches[0].clientY

  const newX = clientX - dragStart.value.x
  const newY = clientY - dragStart.value.y

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

// Touch gesture functions
const getTouchDistance = (touch1, touch2) => {
  const dx = touch1.clientX - touch2.clientX
  const dy = touch1.clientY - touch2.clientY
  return Math.sqrt(dx * dx + dy * dy)
}

const handleTouchStart = (event) => {
  if (event.touches.length === 2) {
    event.preventDefault()
    isPinching.value = true
    const touch1 = event.touches[0]
    const touch2 = event.touches[1]
    initialDistance.value = getTouchDistance(touch1, touch2)
    initialZoom.value = zoomLevel.value
  } else if (event.touches.length === 1) {
    startDrag(event)
  }
}

const handleTouchMove = (event) => {
  if (event.touches.length === 2 && isPinching.value) {
    event.preventDefault()
    const touch1 = event.touches[0]
    const touch2 = event.touches[1]
    const currentDistance = getTouchDistance(touch1, touch2)

    if (initialDistance.value > 0) {
      const scale = currentDistance / initialDistance.value
      const newZoom = Math.max(0.5, Math.min(3, initialZoom.value * scale))
      zoomLevel.value = newZoom
      emit('zoom-change', newZoom)
    }
  } else if (event.touches.length === 1 && isDragging.value) {
    doDrag(event)
  }
}

const handleTouchEnd = (event) => {
  if (event.touches.length === 0) {
    isPinching.value = false
    endDrag()
  } else if (event.touches.length === 1 && isPinching.value) {
    isPinching.value = false
    startDrag(event)
  }
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
  if (!status) return 'status-warning-modal'

  const statusStr = String(status).toLowerCase().trim()

  if (['normal', 'ada', 'baik', 'good', 'ok'].includes(statusStr)) {
    return 'status-good-modal'
  } else if (['tidak normal', 'tidak ada', 'rusak', 'bad', 'not ok','rusak','repaired'].includes(statusStr)) {
    return 'status-bad-modal'
  }
  return 'status-warning-modal'
}

// Reset when image changes
watch(() => props.imageUrl, () => {
  zoomLevel.value = 1
  dragOffset.value = { x: 0, y: 0 }
  rotationAngle.value = 0
})
</script>

<style scoped>
.dragging {
  cursor: grabbing;
}

.cursor-grab {
  cursor: grab;
}

.cursor-grabbing {
  cursor: grabbing;
}

.status-good-modal {
  background-color: #10b981;
  color: white;
}

.status-bad-modal {
  background-color: #ef4444;
  color: white;
}

.status-warning-modal {
  background-color: #f59e0b;
  color: white;
}
</style>
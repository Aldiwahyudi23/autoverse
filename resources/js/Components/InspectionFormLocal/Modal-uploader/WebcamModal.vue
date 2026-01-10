<template>
  <div v-if="show" class="fixed inset-0 z-40 p-0 webcam-modal-container">
    <div class="webcam-content-box">
      <div class="webcam-header">
         <p class="rounded-full text-white hover:bg-gray-700 transition-colors ">{{ settings.camera_aspect_ratio }}</p>
        <div class="inspection-point-name">{{ point?.name || 'Camera' }}</div>
        <button @click="closeModal" class="p-2 rounded-full text-white hover:bg-gray-700 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div 
        class="webcam-video-container" 
        :style="videoContainerStyle" 
        @click="handleTapToFocus"
        @touchstart="handleTapToFocus"
      >
        <video ref="webcamVideo" autoplay playsinline class="webcam-video"></video>
        <canvas ref="webcamCanvas" class="hidden"></canvas>
        <div v-if="showScreenFlash" class="screen-flash-overlay"></div>
        
        <div class="aspect-ratio-guide" :style="aspectRatioGuideStyle"></div>

        <div v-if="showFocusIndicator" class="focus-indicator" :style="focusIndicatorStyle">
          <div class="focus-ring"></div>
        </div>
        
        <div class="hd-badge">
          <span class="hd-text">HD</span>
        </div>

        <div v-if="isZoomSupported" class="zoom-controls">
          <input
            type="range"
            min="1"
            :max="maxZoom"
            step="0.1"
            v-model="zoomLevel"
            @input="setZoom(parseFloat($event.target.value))"
            class="zoom-slider"
          >
        </div>
      </div>

      <div class="webcam-footer">
        <div class="camera-controls">
          <button v-if="settings.enable_flash && isFlashSupported" 
            @click="toggleFlash" 
            class="control-button"
            :class="{'active': isFlashOn, 'disabled': !isFlashSupported}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </button>
          
          <button @click="capturePhoto" class="capture-button" :disabled="isTakingPhoto">
            <div class="camera-icon-container">
              <div v-if="isTakingPhoto" class="camera-spinner"></div>
              <div v-else class="camera-body">
                <div class="camera-lens"></div>
                <div class="camera-flash"></div>
              </div>
            </div>
          </button>

          <button v-if="settings.enable_camera_switch && hasMultipleCameras" 
            @click="switchCamera" 
            class="control-button">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, computed, watch, onUnmounted } from 'vue'

/* ================= PROPS & EMIT ================= */
const props = defineProps({
  show: Boolean,
  aspectRatio: { type: Number, default: 4 / 3 },
  settings: {
    type: Object,
    default: () => ({
      enable_flash: true,
      enable_camera_switch: true,
      max_size: 2048
    })
  }
})

const emit = defineEmits(['close', 'photoCaptured'])

/* ================= REFS ================= */
const webcamVideo = ref(null)
const webcamCanvas = ref(null)

let mediaStream = null
let videoTrack = null

/* ================= STATE ================= */
const currentFacingMode = ref('environment')
const hasMultipleCameras = ref(false)

const isFlashSupported = ref(false)
const isFlashOn = ref(false)
const showScreenFlash = ref(false)

const isZoomSupported = ref(false)
const zoomLevel = ref(1)
const maxZoom = ref(1)

const isTakingPhoto = ref(false)

/* ================= CAMERA INIT ================= */
const initCamera = async () => {
  stopCamera()

  try {
    mediaStream = await navigator.mediaDevices.getUserMedia({
      video: {
        facingMode: { ideal: currentFacingMode.value },
        width: { ideal: 1920 },
        height: { ideal: 1440 },
        aspectRatio: { ideal: 4 / 3 },
        frameRate: { ideal: 30 }
      },
      audio: false
    })

    videoTrack = mediaStream.getVideoTracks()[0]
    webcamVideo.value.srcObject = mediaStream

    await webcamVideo.value.play()
    await detectCapabilities()

  } catch (e) {
    alert('Kamera tidak bisa diakses')
    emit('close')
  }
}

/* ================= CAPABILITIES ================= */
const detectCapabilities = async () => {
  const caps = videoTrack.getCapabilities()

  // Flash
  isFlashSupported.value = !!caps.torch

  // Zoom
  if (caps.zoom) {
    isZoomSupported.value = true
    maxZoom.value = caps.zoom.max
  }

  // Multi camera
  const devices = await navigator.mediaDevices.enumerateDevices()
  hasMultipleCameras.value =
    devices.filter(d => d.kind === 'videoinput').length > 1
}

/* ================= ACTIONS ================= */
const toggleFlash = async () => {
  if (!isFlashSupported.value) return

  try {
    await videoTrack.applyConstraints({
      advanced: [{ torch: !isFlashOn.value }]
    })
    isFlashOn.value = !isFlashOn.value
  } catch {
    console.warn('Torch not supported')
  }
}

const setZoom = async (value) => {
  if (!isZoomSupported.value) return

  const z = Math.min(Math.max(value, 1), maxZoom.value)
  zoomLevel.value = z

  await videoTrack.applyConstraints({
    advanced: [{ zoom: z }]
  })
}

const switchCamera = async () => {
  currentFacingMode.value =
    currentFacingMode.value === 'environment' ? 'user' : 'environment'
  await initCamera()
}

/* ================= CAPTURE ================= */
const capturePhoto = async () => {
  if (isTakingPhoto.value) return
  isTakingPhoto.value = true

  const video = webcamVideo.value
  const canvas = webcamCanvas.value
  const ctx = canvas.getContext('2d')

  canvas.width = video.videoWidth
  canvas.height = video.videoHeight

  ctx.drawImage(video, 0, 0)

  const blob = await new Promise(r =>
    canvas.toBlob(r, 'image/jpeg', 0.95)
  )

  emit(
    'photoCaptured',
    new File([blob], `photo_${Date.now()}.jpg`, {
      type: 'image/jpeg'
    })
  )

  showScreenFlash.value = true
  setTimeout(() => (showScreenFlash.value = false), 120)

  isTakingPhoto.value = false
}

/* ================= CLEANUP ================= */
const stopCamera = () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(t => t.stop())
    mediaStream = null
  }
}

watch(() => props.show, v => (v ? initCamera() : stopCamera()))
onUnmounted(stopCamera)
</script>


---

<style scoped>
.webcam-video {
  width: 100%;
  height: 100%;
  object-fit: contain; /* 🔥 kunci jarak normal */
  background: black;
}

.webcam-video-container {
  position: relative;
  flex: 1;
  overflow: hidden;
  background: black;
}

.webcam-footer {
  padding: 16px;
  background: rgba(0, 0, 0, 0.85);
}

.camera-controls {
  display: flex;
  justify-content: space-around;
  align-items: center;
}

.control-button {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: white;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
}

.control-button.active {
  background: #ffeb3b;
}

.capture-button {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: white;
  border: none;
}

.screen-flash-overlay {
  position: absolute;
  inset: 0;
  background: white;
  opacity: 0.8;
  animation: flash 0.15s ease-out;
  z-index: 10;
}

@keyframes flash {
  from {
    opacity: 0.8;
  }
  to {
    opacity: 0;
  }
}

.zoom-controls {
  position: absolute;
  bottom: 12px;
  width: 80%;
  left: 50%;
  transform: translateX(-50%);
}

</style>
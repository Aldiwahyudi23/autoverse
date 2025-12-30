<template>
  <div v-if="show" class="fixed inset-0 z-50 bg-black flex flex-col">

    <!-- HEADER -->
    <div class="flex items-center justify-between px-4 py-3 text-white bg-black/80">
      <div class="font-semibold text-center flex-1">
        {{ point?.name || 'Camera' }} aas
      </div>
      <button @click="close">✕</button>
    </div>

    <!-- CAMERA PREVIEW -->
    <div
      ref="previewBox"
      class="relative flex-1 overflow-hidden"
      @click="handleTapFocus"
      @touchstart.prevent="handleTapFocus"
    >
      <video
        ref="video"
        autoplay
        playsinline
        class="w-full h-full object-cover"
      ></video>

      <!-- Focus Ring -->
      <div
        v-if="showFocus"
        class="absolute w-20 h-20 border-2 border-green-400 rounded-full pointer-events-none"
        :style="focusStyle"
      ></div>
    </div>

    <!-- FOOTER -->
    <div class="py-5 flex justify-center bg-black/80">
      <button
        @click="capture"
        :disabled="loading"
        class="w-16 h-16 rounded-full bg-red-600 border-4 border-white"
      ></button>
    </div>

    <canvas ref="canvas" class="hidden"></canvas>
  </div>
</template>

<script setup>
import { ref, watch, onUnmounted, nextTick } from 'vue'

/* ======================
   PROPS (MATCH WebCamRTC)
====================== */
const props = defineProps({
  show: Boolean,
  aspectRatio: { type: Number, default: 4 / 3 },
  settings: { type: Object, default: () => ({}) },
  point: { type: Object, default: null }
})

/* ======================
   EMITS (MATCH WebCamRTC)
====================== */
const emit = defineEmits(['close', 'photo-captured'])

/* ======================
   REFS
====================== */
const video = ref(null)
const canvas = ref(null)
const previewBox = ref(null)

let stream = null
let videoTrack = null
const loading = ref(false)

/* ======================
   FOCUS UI
====================== */
const showFocus = ref(false)
const focusStyle = ref({})

/* ======================
   START CAMERA
====================== */
const startCamera = async () => {
  stopCamera()
  loading.value = true

  try {
    stream = await navigator.mediaDevices.getUserMedia({
      video: {
        facingMode: { ideal: 'environment' },
        width: { ideal: 3840, min: 1280 },
        height: { ideal: 2160, min: 720 },
        frameRate: { ideal: 30 }
      },
      audio: false
    })

    videoTrack = stream.getVideoTracks()[0]
    video.value.srcObject = stream
    await video.value.play()

    // Autofocus continuous (best effort)
    try {
      const caps = videoTrack.getCapabilities?.()
      if (caps?.focusMode?.includes('continuous')) {
        await videoTrack.applyConstraints({
          advanced: [{ focusMode: 'continuous' }]
        })
      }
    } catch (_) {}

  } catch (err) {
    console.error('Camera error:', err)
  } finally {
    loading.value = false
  }
}

/* ======================
   STOP CAMERA
====================== */
const stopCamera = () => {
  if (stream) {
    stream.getTracks().forEach(t => t.stop())
    stream = null
    videoTrack = null
  }
}

/* ======================
   CAPTURE PHOTO
====================== */
const capture = async () => {
  if (!video.value) return

  const vw = video.value.videoWidth
  const vh = video.value.videoHeight

  let sw, sh, sx, sy

  if (vw / vh > props.aspectRatio) {
    sh = vh
    sw = vh * props.aspectRatio
    sx = (vw - sw) / 2
    sy = 0
  } else {
    sw = vw
    sh = vw / props.aspectRatio
    sx = 0
    sy = (vh - sh) / 2
  }

  canvas.value.width = sw
  canvas.value.height = sh

  const ctx = canvas.value.getContext('2d')
  ctx.drawImage(video.value, sx, sy, sw, sh, 0, 0, sw, sh)

  const blob = await new Promise(res =>
    canvas.value.toBlob(res, 'image/jpeg', 0.92)
  )

  const file = new File(
    [blob],
    `inspeksi_${props.point?.name || 'foto'}_${Date.now()}.jpg`,
    { type: 'image/jpeg' }
  )

  emit('photo-captured', file)
}

/* ======================
   TAP TO FOCUS (UI + AF RESET)
====================== */
const handleTapFocus = async (e) => {
  if (!videoTrack) return

  const rect = previewBox.value.getBoundingClientRect()
  const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left
  const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top

  showFocus.value = true
  focusStyle.value = {
    left: `${x - 40}px`,
    top: `${y - 40}px`
  }

  setTimeout(() => showFocus.value = false, 800)

  // Trigger autofocus ulang (best effort)
  try {
    await videoTrack.applyConstraints({
      advanced: [{ focusMode: 'continuous' }]
    })
  } catch (_) {}
}

/* ======================
   CLOSE
====================== */
const close = () => {
  stopCamera()
  emit('close')
}

/* ======================
   WATCH
====================== */
watch(() => props.show, async v => {
  if (v) {
    await nextTick()
    startCamera()
  } else {
    stopCamera()
  }
})

onUnmounted(stopCamera)
</script>

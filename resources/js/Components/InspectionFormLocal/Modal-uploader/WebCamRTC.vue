<template>
  <div v-if="show" class="fixed inset-0 z-40 p-0 webcam-modal-container">
    <div class="webcam-content-box">
      <div class="webcam-header">
        <div class="inspection-point-name">{{ point?.name || 'Camera' }}</div>
        <div class="camera-quality-indicator">
          <span class="quality-badge">{{ cameraQuality }}</span>
        </div>
        <button @click="closeModal" class="p-2 rounded-full text-white hover:bg-gray-700 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div
        class="webcam-video-container"
        :style="videoContainerStyle"
        @touchstart="handleTouchFocus"
        @click="handleTouchFocus"
      >
        <video 
          ref="webcamVideo" 
          autoplay 
          playsinline 
          class="webcam-video"
        ></video>
        <canvas ref="webcamCanvas" class="hidden"></canvas>

        <!-- Focus Indicator -->
        <div v-if="showFocusIndicator" class="focus-indicator" :style="focusIndicatorStyle">
          <div class="focus-ring"></div>
        </div>

        <!-- Auto Focus Indicator -->
        <div v-if="showAutoFocus && !showFocusIndicator" class="auto-focus-indicator">
          <div class="auto-focus-ring"></div>
        </div>

        <!-- Loading State -->
        <!-- <div v-if="isLoading" class="loading-overlay">
          <div class="loading-spinner"></div>
          <p class="loading-text">Menyiapkan kamera...</p>
        </div> -->

        <!-- Error State -->
        <div v-if="error" class="error-overlay">
          <div class="error-content">
            <div class="error-icon">⚠️</div>
            <h3 class="error-title">Gagal Mengakses Kamera</h3>
            <p class="error-message">{{ error }}</p>
            <button @click="retryCamera" class="retry-button">
              Coba Lagi
            </button>
          </div>
        </div>
      </div>

      <div class="webcam-footer">
        <div class="camera-controls">
          <button
            v-if="settings.enable_flash && isFlashSupported"
            @click="toggleFlash"
            class="control-button flash-button"
            :class="{'active': isFlashOn}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </button>

          <button @click="capturePhoto" class="capture-button" :disabled="isTakingPhoto || isLoading">
            <div class="camera-icon-container">
              <div v-if="isTakingPhoto" class="camera-spinner"></div>
              <div v-else class="camera-shutter"></div>
            </div>
          </button>

          <button
            v-if="settings.enable_camera_switch && hasMultipleCameras"
            @click="switchCamera"
            class="control-button switch-button"
            :disabled="isLoading">
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
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
  show: Boolean,
  aspectRatio: {
    type: Number,
    default: 4/3
  },
  settings: {
    type: Object,
    default: () => ({
      enable_flash: true,
      enable_camera_switch: true,
      max_size: 2048
    })
  },
  point: Object,
  cameraQuality: {
    type: String,
  }
});
const emit = defineEmits(['close', 'photoCaptured']);

const webcamVideo = ref(null);
const webcamCanvas = ref(null);

// Camera State
let mediaStream = null;
let videoTrack = null;
const currentFacingMode = ref('environment');
const currentDeviceId = ref(null);
const cameraDevices = ref([]);
const hasMultipleCameras = ref(false);
const isFlashSupported = ref(false);
const isFlashOn = ref(false);
const isTakingPhoto = ref(false);
const isLoading = ref(false);
const error = ref(null);
const cameraCapabilities = ref(null);

// Auto Focus
const showFocusIndicator = ref(false);
const showAutoFocus = ref(true);
const focusIndicatorStyle = ref({});

// Computed Properties
const videoContainerStyle = computed(() => {
  return {
    aspectRatio: `${props.aspectRatio} / 1`,
    maxWidth: '100%',
    maxHeight: '70vh',
    margin: '0 auto'
  };
});

// Adaptive Camera Initialization
const initializeWebcam = async () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }

  error.value = null;
  isLoading.value = true;

  try {
    // STRATEGI 1: Coba dengan resolusi optimal untuk pengambilan foto
    const constraints = getOptimalConstraints();
    
    console.log('Mencoba inisialisasi kamera dengan:', constraints);
    
    mediaStream = await navigator.mediaDevices.getUserMedia({
      video: constraints,
      audio: false
    });

    videoTrack = mediaStream.getVideoTracks()[0];
    webcamVideo.value.srcObject = mediaStream;

    // Tunggu sampai video siap
    await new Promise((resolve, reject) => {
      webcamVideo.value.onloadedmetadata = resolve;
      webcamVideo.value.onerror = reject;
      setTimeout(resolve, 1000); // Fallback timeout
    });

    await webcamVideo.value.play();
    
    // Dapatkan kemampuan kamera setelah berhasil
    await checkCameraCapabilities();
    
    // Setup auto-focus dan pencahayaan otomatis
    setupAutoFocusAndExposure();
    
    // Dapatkan daftar perangkat secara async
    setTimeout(async () => {
      try {
        await getCameraDevices();
      } catch (err) {
        console.warn("Gagal mendapatkan daftar perangkat:", err);
      }
    }, 500);
    
    isLoading.value = false;
    console.log('Kamera berhasil diinisialisasi');

  } catch (err) {
    console.error("Error mengakses kamera: ", err);

    // STRATEGI 2: Fallback ke resolusi berdasarkan kualitas kamera yang dipilih
    if (!mediaStream) {
      try {
        console.log('Mencoba fallback berdasarkan kualitas kamera...');

        // Tentukan fallback constraints berdasarkan kualitas kamera
        let fallbackWidthIdeal, fallbackHeightIdeal, fallbackWidthMin, fallbackHeightMin;

        switch (props.cameraQuality) {
          case 'SD':
            fallbackWidthIdeal = 1280;
            fallbackHeightIdeal = 720;
            fallbackWidthMin = 640;
            fallbackHeightMin = 480;
            break;
          case 'HD':
            fallbackWidthIdeal = 1920;
            fallbackHeightIdeal = 1080;
            fallbackWidthMin = 1280;
            fallbackHeightMin = 720;
            break;
          case '4K':
            fallbackWidthIdeal = 3840;
            fallbackHeightIdeal = 2160;
            fallbackWidthMin = 1920;
            fallbackHeightMin = 1080;
            break;
          default:
            // Default ke HD
            fallbackWidthIdeal = 1920;
            fallbackHeightIdeal = 1080;
            fallbackWidthMin = 1280;
            fallbackHeightMin = 720;
        }

        const fallbackConstraints = {
          video: {
            facingMode: currentFacingMode.value,
            width: { min: fallbackWidthMin, ideal: fallbackWidthIdeal },
            height: { min: fallbackHeightMin, ideal: fallbackHeightIdeal }
          }
        };

        mediaStream = await navigator.mediaDevices.getUserMedia(fallbackConstraints);
        videoTrack = mediaStream.getVideoTracks()[0];
        webcamVideo.value.srcObject = mediaStream;
        await webcamVideo.value.play();
        isLoading.value = false;
        console.log('Fallback berhasil dengan kualitas:', props.cameraQuality);
      } catch (fallbackErr) {
        console.error("Fallback juga gagal:", fallbackErr);
        error.value = getErrorMessage(fallbackErr);
        isLoading.value = false;
      }
    }
  }
};

// Fungsi untuk mendapatkan constraints optimal berdasarkan kemampuan perangkat
const getOptimalConstraints = () => {
  // Tentukan resolusi berdasarkan pengaturan kualitas kamera
  let widthIdeal, heightIdeal, widthMax, heightMax, widthMin, heightMin;

  switch (props.cameraQuality) {
    case 'SD':
      widthIdeal = 1280;
      heightIdeal = 720;
      widthMax = 1280;
      heightMax = 720;
      widthMin = 640;
      heightMin = 480;
      break;
    case 'HD':
      widthIdeal = 1920;
      heightIdeal = 1080;
      widthMax = 1920;
      heightMax = 1080;
      widthMin = 1280;
      heightMin = 720;
      break;
    case '4K':
      widthIdeal = 3840;
      heightIdeal = 2160;
      widthMax = 3840;
      heightMax = 2160;
      widthMin = 1920;
      heightMin = 1080;
      break;
    default:
      // Default ke HD jika tidak dikenali
      widthIdeal = 1920;
      heightIdeal = 1080;
      widthMax = 1920;
      heightMax = 1080;
      widthMin = 1280;
      heightMin = 720;
  }

  // Prioritas: kamera belakang dengan auto-focus dan exposure
  const baseConstraints = {
    facingMode: { ideal: 'environment' },
    // Resolusi berdasarkan kualitas kamera yang dipilih
    width: { ideal: widthIdeal, max: widthMax, min: widthMin },
    height: { ideal: heightIdeal, max: heightMax, min: heightMin },
    // Pengaturan untuk fokus dan pencahayaan otomatis
    focusMode: { ideal: ['continuous', 'single-shot', 'manual'] },
    exposureMode: { ideal: ['continuous', 'manual'] },
    whiteBalanceMode: { ideal: ['continuous', 'manual'] }
  };

  return baseConstraints;
};

// Setup auto-focus dan exposure otomatis
const setupAutoFocusAndExposure = async () => {
  if (!videoTrack) return;
  
  try {
    const capabilities = videoTrack.getCapabilities();
    cameraCapabilities.value = capabilities;
    
    // Coba atur auto-focus dan auto-exposure jika didukung
    const supportsAutoFocus = capabilities.focusMode && 
      (capabilities.focusMode.includes('continuous') || capabilities.focusMode.includes('single-shot'));
    
    const supportsAutoExposure = capabilities.exposureMode && 
      capabilities.exposureMode.includes('continuous');
    
    if (supportsAutoFocus || supportsAutoExposure) {
      const constraints = {};
      
      if (supportsAutoFocus) {
        constraints.focusMode = 'continuous';
      }
      
      if (supportsAutoExposure) {
        constraints.exposureMode = 'continuous';
        constraints.exposureCompensation = { ideal: 0 }; // Netral
      }
      
      if (Object.keys(constraints).length > 0) {
        await videoTrack.applyConstraints({ advanced: [constraints] });
        console.log('Auto-focus/exposure diaktifkan');
      }
    }
    
    // Cek dukungan flash
    isFlashSupported.value = !!capabilities.torch || !!capabilities.fillLightMode;
    
  } catch (err) {
    console.warn("Tidak bisa mengatur auto-focus/exposure:", err);
  }
};

const getCameraDevices = async () => {
  try {
    // Gunakan stream sementara untuk enumerasi
    const tempStream = await navigator.mediaDevices.getUserMedia({ video: true });
    tempStream.getTracks().forEach(track => track.stop());
    
    const devices = await navigator.mediaDevices.enumerateDevices();
    cameraDevices.value = devices.filter(d => d.kind === 'videoinput');
    hasMultipleCameras.value = cameraDevices.value.length > 1;
    
    // Set device ID default jika belum ada
    if (!currentDeviceId.value && cameraDevices.value.length > 0) {
      const rearCamera = cameraDevices.value.find(device => 
        device.label.toLowerCase().includes('back') || 
        device.label.toLowerCase().includes('rear') ||
        device.label.toLowerCase().includes('environment')
      );
      currentDeviceId.value = rearCamera ? rearCamera.deviceId : cameraDevices.value[0].deviceId;
    }
  } catch (err) {
    console.warn('Error getting camera devices:', err);
  }
};

const getErrorMessage = (error) => {
  switch(error.name) {
    case 'NotAllowedError':
      return 'Izin akses kamera ditolak. Silakan izinkan akses kamera di pengaturan browser.';
    case 'NotFoundError':
      return 'Tidak ada kamera yang ditemukan.';
    case 'NotSupportedError':
      return 'Browser tidak mendukung akses kamera.';
    case 'NotReadableError':
      return 'Kamera sedang digunakan oleh aplikasi lain.';
    case 'OverconstrainedError':
      return 'Perangkat tidak mendukung pengaturan kamera yang diminta.';
    default:
      return `Tidak dapat mengakses kamera: ${error.message}`;
  }
};

const checkCameraCapabilities = () => {
  if (!videoTrack) return;
  
  try {
    const capabilities = videoTrack.getCapabilities();
    cameraCapabilities.value = capabilities;
    isFlashSupported.value = !!capabilities.torch || !!capabilities.fillLightMode;
    
    console.log('Kamera mendukung:', {
      maxRes: `${capabilities.width?.max || 'N/A'}x${capabilities.height?.max || 'N/A'}`,
      torch: capabilities.torch,
      focusModes: capabilities.focusMode,
      exposureModes: capabilities.exposureMode
    });
    
  } catch (err) {
    console.warn("Tidak bisa mendapatkan kemampuan kamera:", err);
  }
};

const toggleFlash = async () => {
  if (!videoTrack || !isFlashSupported.value) return;
  
  try {
    // Hanya gunakan flash untuk kamera belakang
    if (currentFacingMode.value === 'environment') {
      await videoTrack.applyConstraints({ 
        advanced: [{ torch: !isFlashOn.value }] 
      });
      isFlashOn.value = !isFlashOn.value;
    } else {
      // Untuk kamera depan, matikan flash di UI
      console.log('Flash tidak didukung untuk kamera depan');
      isFlashOn.value = false;
    }
  } catch (err) {
    console.error("Gagal mengontrol flash:", err);
    isFlashOn.value = false;
  }
};

const switchCamera = async () => {
  if (!hasMultipleCameras.value || isLoading.value) return;
  
  isLoading.value = true;
  
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  
  const currentIndex = cameraDevices.value.findIndex(d => d.deviceId === currentDeviceId.value);
  const nextIndex = (currentIndex + 1) % cameraDevices.value.length;
  currentDeviceId.value = cameraDevices.value[nextIndex].deviceId;
  
  const nextCamera = cameraDevices.value[nextIndex];
  currentFacingMode.value = nextCamera.label.toLowerCase().includes('front') ? 'user' : 'environment';
  
  await initializeWebcam();
};

// Fokus manual saat tap/klik
const handleTouchFocus = async (event) => {
  if (!webcamVideo.value || !videoTrack) return;

  const rect = event.currentTarget.getBoundingClientRect();
  const x = event.type.includes('mouse') ? event.clientX - rect.left : event.touches[0].clientX - rect.left;
  const y = event.type.includes('mouse') ? event.clientY - rect.top : event.touches[0].clientY - rect.top;

  // Tampilkan indikator fokus
  showFocusIndicator.value = true;
  focusIndicatorStyle.value = {
    left: `${x - 40}px`,
    top: `${y - 40}px`
  };

  // Sembunyikan setelah animasi
  setTimeout(() => {
    showFocusIndicator.value = false;
  }, 1000);

  // Coba atur fokus manual pada titik yang di-tap
  try {
    const capabilities = videoTrack.getCapabilities();
    
    if (capabilities.focusMode && capabilities.focusMode.includes('manual')) {
      // Hitung posisi relatif (0-1)
      const relativeX = x / rect.width;
      const relativeY = y / rect.height;
      
      // Untuk perangkat yang mendukung, atur area fokus
      if (capabilities.focusDistance && capabilities.pan && capabilities.tilt) {
        // Simulasi fokus pada titik tertentu
        await videoTrack.applyConstraints({
          advanced: [{ 
            focusMode: 'manual',
            focusDistance: 0.3, // Jarak fokus sedang
            pointsOfInterest: [{x: relativeX, y: relativeY}]
          }]
        });
      }
    } else if (capabilities.focusMode && capabilities.focusMode.includes('single-shot')) {
      // Trigger single-shot auto-focus
      await videoTrack.applyConstraints({
        advanced: [{ focusMode: 'single-shot' }]
      });
    }
  } catch (error) {
    console.warn("Penyesuaian fokus tidak didukung:", error);
  }
};

const capturePhoto = async () => {
  if (isTakingPhoto.value || !webcamVideo.value || !webcamCanvas.value) return;
  
  isTakingPhoto.value = true;
  
  try {
    const video = webcamVideo.value;
    const canvas = webcamCanvas.value;
    
    // Tunggu frame stabil
    await new Promise(resolve => setTimeout(resolve, 100));
    
    const vw = video.videoWidth;
    const vh = video.videoHeight;
    
    // Hitung crop berdasarkan aspect ratio
    let sw, sh, sx, sy;
    
    if (vw / vh > props.aspectRatio) {
      // Video lebih lebar
      sh = vh;
      sw = vh * props.aspectRatio;
      sx = (vw - sw) / 2;
      sy = 0;
    } else {
      // Video lebih tinggi
      sw = vw;
      sh = vw / props.aspectRatio;
      sx = 0;
      sy = (vh - sh) / 2;
    }
    
    canvas.width = sw;
    canvas.height = sh;
    
    const ctx = canvas.getContext('2d');
    
    // Ambil gambar dengan kualitas tinggi
    ctx.drawImage(video, sx, sy, sw, sh, 0, 0, sw, sh);
    
    // Kompresi gambar dengan kualitas optimal
    const quality = Math.min(0.95, 0.7 + (vw * vh) / (3840 * 2160) * 0.25); // Adaptif berdasarkan resolusi
    
    const blob = await new Promise(resolve => {
      canvas.toBlob(resolve, 'image/jpeg', quality);
    });
    
    if (blob) {
      // Cek ukuran file
      if (blob.size / 1024 > props.settings.max_size) {
        // Kompres ulang dengan kualitas lebih rendah jika terlalu besar
        const resizedBlob = await resizeImageBlob(blob, props.settings.max_size);
        const fileName = `inspeksi_${props.point?.name || 'foto'}_${Date.now()}.jpg`;
        const file = new File([resizedBlob], fileName, { 
          type: 'image/jpeg',
          lastModified: Date.now()
        });
        
        emit('photoCaptured', file);
      } else {
        const fileName = `inspeksi_${props.point?.name || 'foto'}_${Date.now()}.jpg`;
        const file = new File([blob], fileName, { 
          type: 'image/jpeg',
          lastModified: Date.now()
        });
        
        emit('photoCaptured', file);
      }
    }
    
  } catch (error) {
    console.error("Error mengambil foto:", error);
  } finally {
    isTakingPhoto.value = false;
  }
};

// Fungsi untuk resize gambar jika terlalu besar
const resizeImageBlob = async (blob, maxSizeKB) => {
  return new Promise((resolve) => {
    const img = new Image();
    img.onload = () => {
      const canvas = document.createElement('canvas');
      let width = img.width;
      let height = img.height;
      
      // Kurangi ukuran jika perlu
      const maxDimension = 1920;
      if (width > maxDimension || height > maxDimension) {
        if (width > height) {
          height = (height * maxDimension) / width;
          width = maxDimension;
        } else {
          width = (width * maxDimension) / height;
          height = maxDimension;
        }
      }
      
      canvas.width = width;
      canvas.height = height;
      
      const ctx = canvas.getContext('2d');
      ctx.drawImage(img, 0, 0, width, height);
      
      // Kompres dengan kualitas menurun secara bertahap
      let quality = 0.9;
      const tryCompress = () => {
        canvas.toBlob((compressedBlob) => {
          if (compressedBlob.size / 1024 <= maxSizeKB || quality <= 0.3) {
            resolve(compressedBlob);
          } else {
            quality -= 0.1;
            tryCompress();
          }
        }, 'image/jpeg', quality);
      };
      
      tryCompress();
    };
    
    img.src = URL.createObjectURL(blob);
  });
};

const closeModal = () => {
  if (mediaStream) {
    mediaStream.getTracks().forEach(track => track.stop());
  }
  emit('close');
};

const retryCamera = async () => {
  error.value = null;
  await initializeWebcam();
};

// Watchers and lifecycle
watch(() => props.show, async (v) => { 
  if (v) {
    await nextTick();
    await initializeWebcam();
  } else {
    if (mediaStream) {
      mediaStream.getTracks().forEach(track => track.stop());
    }
  }
});

onUnmounted(() => { 
  if (mediaStream) {
    mediaStream.getTracks().forEach(t => t.stop());
  }
});
</script>

<style scoped>
.webcam-modal-container {
  background-color: rgba(0, 0, 0, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
}

.webcam-content-box {
  background: #000;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  max-width: 100%;
  max-height: 100vh;
}

.webcam-header {
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.inspection-point-name {
  flex-grow: 1;
  text-align: center;
  font-weight: 600;
  font-size: 1.1rem;
}

.camera-quality-indicator {
  display: flex;
  align-items: center;
  margin-right: 1rem;
}

.quality-badge {
  background: rgba(0, 123, 255, 0.9);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.webcam-video-container {
  position: relative;
  flex-grow: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  background: #000;
  overflow: hidden;
  margin: 0 auto;
  touch-action: manipulation;
}

.webcam-video {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Auto Focus Indicator */
.auto-focus-indicator {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 120px;
  height: 120px;
  pointer-events: none;
  z-index: 10;
}

.auto-focus-ring {
  width: 100%;
  height: 100%;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  animation: autoFocusPulse 2s ease-in-out infinite;
}

@keyframes autoFocusPulse {
  0%, 100% { 
    transform: scale(0.8);
    opacity: 0.3;
    border-color: rgba(255, 255, 255, 0.3);
  }
  50% { 
    transform: scale(1.1);
    opacity: 0.6;
    border-color: rgba(0, 255, 0, 0.5);
  }
}

/* Focus Indicator */
.focus-indicator {
  position: absolute;
  width: 80px;
  height: 80px;
  z-index: 15;
  pointer-events: none;
  animation: focusAppear 0.3s ease-out;
}

@keyframes focusAppear {
  0% { transform: scale(0.5); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}

.focus-ring {
  width: 100%;
  height: 100%;
  border: 3px solid #00ff00;
  border-radius: 50%;
  background: rgba(0, 255, 0, 0.1);
  animation: focusPulse 1s ease-out;
}

@keyframes focusPulse {
  0% { transform: scale(0.8); opacity: 1; }
  50% { transform: scale(1.1); opacity: 1; }
  100% { transform: scale(1); opacity: 0.8; }
}

/* Footer Controls */
.webcam-footer {
  background: rgba(0, 0, 0, 0.8);
  padding: 1.5rem 1rem;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-shrink: 0;
}

.camera-controls {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  max-width: 320px;
}

.flash-button {
  position: absolute;
  left: 0;
}

.switch-button {
  position: absolute;
  right: 0;
}

.control-button {
  background: rgba(255, 255, 255, 0.9);
  border: none;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.control-button.active {
  background: #FFC107;
  animation: flashActive 1s ease-in-out infinite;
}

@keyframes flashActive {
  0%, 100% { background: #FFC107; }
  50% { background: #FFA000; }
}

.control-button:hover:not(:disabled) {
  transform: scale(1.08);
  background: rgba(255, 255, 255, 1);
}

.control-button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.control-button svg {
  stroke: #000;
  width: 24px;
  height: 24px;
}

.capture-button {
  background: #fff;
  border: none;
  width: 70px;
  height: 70px;
  border-radius: 50%;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: all 0.1s ease;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
}

.capture-button:hover:not(:disabled) {
  transform: scale(1.05);
  background: #f8f9fa;
}

.capture-button:active:not(:disabled) {
  transform: scale(0.95);
}

.capture-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.camera-icon-container {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  height: 100%;
}

.camera-shutter {
  width: 60px;
  height: 60px;
  background: #dc3545;
  border-radius: 50%;
  border: 4px solid white;
  transition: all 0.1s ease;
}

.capture-button:active:not(:disabled) .camera-shutter {
  background: #c82333;
  transform: scale(0.9);
}

.camera-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Loading and Error States */
.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 30;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

.loading-text {
  color: white;
  font-size: 1rem;
}

.error-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 30;
}

.error-content {
  text-align: center;
  color: white;
  padding: 2rem;
  max-width: 80%;
}

.error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.error-title {
  font-size: 1.5rem;
  margin-bottom: 1rem;
  font-weight: bold;
}

.error-message {
  margin-bottom: 2rem;
  line-height: 1.5;
}

.retry-button {
  padding: 0.75rem 2rem;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 25px;
  cursor: pointer;
  font-size: 1rem;
  transition: background 0.2s ease;
}

.retry-button:hover {
  background: #0056b3;
}
</style>
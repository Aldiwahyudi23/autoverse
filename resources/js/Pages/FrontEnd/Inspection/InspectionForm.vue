<template>
  <!-- Header dengan Menu Navigasi -->
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
     <!-- overlay redup -->
  <div
    v-if="damageDragging || menuDragging"
    class="fixed inset-0 bg-black bg-opacity-40 z-30 pointer-events-none transition-opacity duration-200"
  ></div>
   
    <!-- Tombol toggle HANYA muncul kalau vertical -->
     <button
    v-if="menuMode === 'vertical'"
      @mousedown="startToggleLongPress"
      @mouseup="stopToggleDrag"
      @mouseleave="cancelToggleLongPress"
      @mousemove="onToggleDrag"
      @touchstart="startToggleLongPress"
      @touchend="stopToggleDrag"
      @touchmove="onToggleDrag"
      @click="toggleMenu"
      class="fixed z-40 p-4 bg-gradient-to-r from-indigo-700 to-sky-600 text-white border shadow-lg rounded-full hover:shadow-xl transition"
      :style="{ left: togglePos.x + 'px', top: togglePos.y + 'px' }"
      aria-label="Toggle vertical menu"
    >
    <template v-if="!isMenuOpen">
      <!-- icon buka -->
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" stroke-width="2">
        <path fill-rule="evenodd" d="M3 5h14v2H3V5zm0 4h10v2H3V9zm0 4h6v2H3v-2z" clip-rule="evenodd" />
      </svg>
    </template>
    <template v-else>
      <!-- icon tutup -->
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor" stroke-width="2">
        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
      </svg>
    </template>
  </button>

    <!-- Horizontal menu -->
    <div
      v-if="menuMode === 'horizontal'"
      class="z-10 bg-white shadow-sm"
      :class="{
        'sticky top-0 mb-2': menuPosition === 'top',
        'fixed bottom-0 left-0 right-0': menuPosition === 'bottom',
      }"
    >
      <div class="flex overflow-x-auto scrollbar-hide py-3 px-4">
            <!-- Menu Detail Kendaraan -->
            <button
              @click="changeCategory('vehicle')"
              class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors duration-200"
              :class="{
                'bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white': activeCategory === 'vehicle',
                'bg-gray-100 text-gray-700 hover:bg-gray-200': activeCategory !== 'vehicle'
              }"
            >
              Detail Kendaraan
              <span 
                v-if="isVehicleFormComplete"
                class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-green-500 text-white"
              >
                ✓
              </span>
            </button>
              
            <!-- Menu Inspeksi -->
            <button
              v-for="menu in appMenus"
              :key="menu.id"
              @click="changeCategory(menu.id)"
              class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors duration-200"
              :class="{
                'bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white': activeCategory === menu.id,
                'bg-gray-100 text-gray-700 hover:bg-gray-200': activeCategory !== menu.id
              }"
            >
              {{ menu.name }}
              <span 
                v-if="isMenuComplete(menu)"
                class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-green-500 text-white"
              >
                ✓
              </span>
            </button>
              
            <!-- Menu Kesimpulan -->
            <button
              @click="changeCategory('conclusion')"
              class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors duration-200"
              :class="{
                'bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white': activeCategory === 'conclusion',
                'bg-gray-100 text-gray-700 hover:bg-gray-200': activeCategory !== 'conclusion'
              }"
            >
              Kesimpulan
              <span 
                v-if="conclusionStatus.isComplete"
                class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-green-500 text-white"
              >
                ✓
              </span>
            </button>
      </div>
    </div>

    <!-- Vertical menu overlay -->
    <div v-if="menuMode === 'vertical' && isMenuOpen" class="fixed inset-0 z-30">
      <!-- overlay -->
      <div class="absolute inset-0 bg-black bg-opacity-30" @click="closeMenu"></div>

      <!-- panel -->
      <div
        class="absolute z-40 w-60 bg-white rounded-xl shadow-xl p-2 max-h-[75vh] overflow-y-auto"
        :class="{
          'top-16 right-4': menuPosition === 'top-right',
          'top-16 left-4': menuPosition === 'top-left',
          'bottom-16 right-4': menuPosition === 'bottom-right',
          'bottom-16 left-4': menuPosition === 'bottom-left',
        }"
      >
        <!-- header -->
        <div class="flex items-center justify-between px-2 py-1 border-b">
          <div class="text-sm font-semibold">Pilih Menu</div>
          <button @click="closeMenu" class="text-xs text-gray-500 hover:text-gray-700">Tutup</button>
        </div>

          <div class="mt-2 space-y-2">
          <!-- Vehicle -->
          <button
            @click="changeCategory('vehicle'); closeMenu()"
            :class="menuButtonClass(activeCategory === 'vehicle')"
            class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm"
          >
            <span>Detail Kendaraan</span>
            <span v-if="isVehicleFormComplete" class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-green-500 text-white">✓</span>
          </button>

          <!-- regular menus -->
          <button
            v-for="menu in appMenus"
            :key="`vmenu-${menu.id}`"
            @click="() => { changeCategory(menu.id); closeMenu(); }"
            :class="[menuButtonClass(activeCategory === menu.id), 'w-full flex items-center justify-between px-3 py-2 rounded-md text-sm']"
          >
            <span>{{ menu.name }}</span>
            <span v-if="isMenuComplete(menu)" class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-green-500 text-white">✓</span>
          </button>

          <!-- conclusion -->
          <button
            @click="changeCategory('conclusion'); closeMenu()"
            :class="menuButtonClass(activeCategory === 'conclusion')"
            class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm"
          >
            <span>Kesimpulan</span>
            <span v-if="conclusionStatus.isComplete" class="ml-2 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full bg-green-500 text-white">✓</span>
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-6 py-1">

      <!-- Pesan sukses -->
      <transition name="fade">
        <div
          v-if="successMessage"
          class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-green-400 text-white px-4 py-2 rounded shadow-lg z-50"
        >
          {{ successMessage }}
        </div>
      </transition>

      <!-- Konten Utama -->
      <div class="relative overflow-hidden">
        <transition name="category-slide" mode="out-in">
          <!-- Detail Kendaraan -->
          <VehicleDetails
            v-if="activeCategory === 'vehicle'"
            :inspection="inspection"
            :CarDetail="CarDetail"
            :allInspections="props.allInspections" 
            @update-vehicle="updateVehicleDetails"
            @save-car-details="saveNewCarDetails"
            @update:validation="handleVehicleValidation"
          @update:hasUnsavedChanges="handleUnsavedChanges"
          />

<!-- UPDATE bagian CategorySection di template induk: -->
<category-section
  v-else-if="activeMenuData && activeCategory !== 'conclusion'"
  :key="activeMenuData.id"
  :category="activeMenuData"
  :inspection-id="inspection.id"
  :form="getFormDataForChild"
  :is-syncing="isSyncing"
  @saveResult="saveResult"
  @updateResult="updateResult"
  @updateImages="updateImages"
  @hapusPoint="hapusData"
  @removeImage="removeImage"
/>

          <!-- Kesimpulan -->
          <conclusion-section
            v-else-if="activeCategory === 'conclusion'"
            :form="form"
            :inspection-id="inspection.id"
            :inspection="inspection"   
            :settings="inspection.settings || {}"
            @updateConclusion="updateConclusion"
          />
        </transition>
      </div>




      <!-- Tombol Simpan Final
      <div v-if="activeCategory === 'conclusion'" class="flex justify-end gap-4 mt-2 p-4 bg-white rounded-xl shadow-md">
        <Link
              :href="route('inspections.pending', inspectionId)"
              :disabled="isLoading"
              @click="handleAction(route('inspections.pending', inspectionId), 'pending')"
              class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600"
              :class="{ 'opacity-50 cursor-not-allowed': isLoading && currentAction === 'pending' }"
            >
              <span v-if="isLoading && currentAction === 'pending'" class="flex items-center">
                <svg class="animate-spin h-5 w-5 text-white mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses...
              </span>
              <span v-else>Tunda</span>
            </Link>
      
        <button
          type="button"
          @click="submitAll"
          :disabled="!allMenusComplete || form.processing"
          class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ form.processing ? 'Mengirim...' : 'Final Kirim Inspeksi' }}</span>
        </button>
      </div> -->

      <!-- Tombol Simpan Final -->
    <div
      v-if="activeCategory === 'conclusion'"
      class="flex justify-end gap-4 mt-2 p-4 bg-white rounded-xl shadow-md"
    >
      <!-- Tombol Pending -->
      <Link
        :href="route('inspections.pending', inspectionId)"
        as="button"
        type="button"
        :disabled="isLoading && currentAction === 'pending'"
        @click.prevent="handleAction(route('inspections.pending', inspectionId), 'pending')"
        class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium rounded-md shadow-sm bg-yellow-500 text-white hover:bg-yellow-600 transition disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <template v-if="isLoading && currentAction === 'pending'">
          <svg
            class="animate-spin h-4 w-4 text-white mr-2"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="currentColor"
              stroke-width="4"
            />
            <path
              class="opacity-75"
              fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 
                5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 
                5.824 3 7.938l3-2.647z"
            />
          </svg>
          <span>Memproses...</span>
        </template>
        <template v-else>
          <span>Tunda</span>
        </template>
      </Link>

      <!-- Tombol Final -->
      <button
        type="button"
        @click="submitAll"
        :disabled="!allMenusComplete || form.processing"
        class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm bg-gradient-to-r from-indigo-700 to-sky-600 shadow-lg text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <svg
          v-if="form.processing"
          class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          />
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 
              5.373 0 12h4zm2 5.291A7.962 7.962 0 
              014 12H0c0 3.042 1.135 5.824 3 
              7.938l3-2.647z"
          />
        </svg>
        <span>{{ form.processing ? 'Mengirim...' : 'Final Kirim Inspeksi' }}</span>
      </button>
    </div>

   <!-- Floating Button untuk Damage Points -->
 <button
    v-if="hasDamage"
    @mousedown="startDamageLongPress"
    @mouseup="stopDamageDrag"
    @mouseleave="cancelDamageLongPress"
    @mousemove="onDamageDrag"
    @touchstart="startDamageLongPress"
    @touchend="stopDamageDrag"
    @touchmove="onDamageDrag"
    @click="showSearchModal = true"
    class="fixed z-40 p-4 bg-gradient-to-r from-indigo-700 to-sky-600 text-white rounded-full shadow-lg"
    :style="{ left: damagePos.x + 'px', top: damagePos.y + 'px' }"
  >
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
    </svg>
  </button>

      <!-- Modal Pencarian Damage Points -->
      <BottomSheetModal
        :show="showSearchModal"
        title="Cari Point Inspeksi"
        subtitle="Pilih point inspeksi untuk ditambahkan"
        @close="closeSearchModal"
      >
        <div class="space-y-4">
          <!-- Search Input -->
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari point inspeksi..."
              class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            >
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
          </div>

          <!-- List Hasil Pencarian -->
          <div class="max-h-64 overflow-y-auto">
            <div v-if="filteredDamagePoints.length === 0" class="text-center py-4 text-gray-500">
              Tidak ada point yang ditemukan
            </div>
            
            <button
              v-for="point in filteredDamagePoints"
              :key="point.id"
              @click="selectPoint(point)"
              class="w-full text-left p-3 border-b border-gray-200 hover:bg-gray-50 transition-colors"
            >
              <div class="font-medium text-gray-900">{{ point.inspection_point?.name }}</div>
              <div class="text-sm text-gray-500">{{ point.inspection_point?.description }}</div>
              <div class="text-xs text-gray-400 mt-1">
                {{ getComponentName(point) }}
              </div>
              <div v-if="hasPointData(point.inspection_point_id)" class="text-xs text-green-600 mt-1">
                ✓ Sudah ada data
              </div>
            </button>
          </div>
        </div>
      </BottomSheetModal>

      <!-- Modal Radio Option untuk Damage Points -->
      <RadioOptionModal
        v-if="showRadioModal"
        :key="selectedPoint?.id"
        :show="showRadioModal"
        :title="selectedPoint?.inspection_point?.name || 'Detail Point'"
        :subtitle="selectedPoint?.inspection_point?.description"
        :name="`point-${selectedPoint?.id}`"
        :inspection-id="inspection.id"
        :point-id="selectedPoint?.inspection_point_id"
        :settings="selectedPoint?.settings || {}"
        :options="selectedPoint?.settings?.radios || []"
        :selected-Point="selectedPoint"
        :selected-value="tempRadioValue"
        :images-value="form.images[selectedPoint?.inspection_point_id]"
        :notes-value="tempNotes"
        :point="selectedPoint"
        :existing-data="getExistingPointData(selectedPoint?.inspection_point_id)"
        @update:selectedValue="tempRadioValue = $event"
        @update:notesValue="tempNotes = $event"
        @update:imagesValue="updateImagesValue"
        @save="saveAllData"
        @close="closeRadioModal"
        @hapus="hapusData"
      />

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useForm, usePage, Link, router } from '@inertiajs/vue3';
import { debounce, throttle } from 'lodash';
import VehicleDetails from '@/Components/InspectionForm/VehicleDetails.vue';
import CategorySection from '@/Components/InspectionForm/CategorySection.vue';
import ConclusionSection from '@/Components/InspectionForm/ConclusionSection.vue';
import RadioOptionModal from '@/Components/InspectionForm/RadioOptionModal.vue';
import BottomSheetModal from '@/Components/InspectionForm/BottomSheetModal.vue';
import { useDraggableButton } from '@/Composables/useDraggableButton';

const props = defineProps({
  inspection: Object,
  inspectionId: Object,
  category: Object,
  appMenus: Array,
  damagePoints: Array,
  existingResults: Object,
  existingImages: Object,
  CarDetail: Array,
  allInspections: Array,
});

// ================ OPTIMISTIC UPDATES STATE ================
// State untuk optimistic updates
const localFormState = ref({
  results: {},
  images: {}
});

const syncQueue = ref(new Map());
const isSyncing = ref(false);
const pendingSync = ref(new Set());
const lastSavedState = ref({});

// ================ MENU DRAGGABLE ================
const menuMode = ref(props.category?.settings?.menu_model || 'horizontal')
const menuPosition = ref(props.category?.settings?.position || 'top')
const isMenuOpen = ref(false)

// draggable button untuk Toggle Menu
const {
  pos: togglePos,
  dragging: menuDragging,
  startLongPress: startToggleLongPress,
  cancelLongPress: cancelToggleLongPress,
  onDrag: onToggleDrag,
  stopDrag: stopToggleDrag,
} = useDraggableButton('toggleMenuButtonPos', {
  x: window.innerWidth - 80,
  y: 20,
});

// toggle untuk vertical
const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const closeMenu = () => {
  isMenuOpen.value = false
}

const menuButtonClass = (isActive) => {
  return isActive
    ? 'bg-gradient-to-r from-indigo-700 to-sky-600 text-white'
    : 'bg-gray-50 hover:bg-gray-100 text-gray-700'
}

// ================ DAMAGE DRAGGABLE ================
const {
  pos: damagePos,
  dragging: damageDragging,
  startLongPress: startDamageLongPress,
  cancelLongPress: cancelDamageLongPress,
  onDrag: onDamageDrag,
  stopDrag: stopDamageDrag,
} = useDraggableButton('damageButtonPos', {
  x: window.innerWidth - 80,
  y: window.innerHeight - 80,
});

// ================ MODAL STATE ================
const showSearchModal = ref(false);
const showRadioModal = ref(false);
const searchQuery = ref('');
const selectedPoint = ref(null);
const tempRadioValue = ref('');
const tempNotes = ref('');
const successMessage = ref('');

const isLoading = ref(false)
const currentAction = ref(null)

// ================ INITIALIZATION FUNCTIONS ================
// Inisialisasi local state
const initializeLocalState = () => {
  const results = {};
  const images = {};

  const allPoints = [
    ...props.appMenus.flatMap(menu => menu.menu_point || []),
    ...(props.damagePoints || [])
  ];

  allPoints.forEach(point => {
    const pointId = point.inspection_point_id;
    const existingResult = props.existingResults[pointId];
    
    results[pointId] = {
      status: existingResult?.status || '',
      note: existingResult?.note || '',
    };
    
    images[pointId] = props.existingImages[pointId] || [];
  });
  
  localFormState.value = { results, images };
  lastSavedState.value = JSON.parse(JSON.stringify(localFormState.value));
};

// Inisialisasi form untuk final submit
const initializeForm = () => {
  return {
    inspection_id: props.inspection.id,
    results: {},
    images: {},
    overall_note: props.inspection.overall_note || ''
  };
};

const form = useForm(initializeForm());

// ================ SYNC QUEUE SYSTEM ================
// Queue system untuk sync
const addToSyncQueue = (pointId, type, data) => {
  const queueKey = `${pointId}-${type}-${Date.now()}`;
  syncQueue.value.set(queueKey, {
    pointId,
    type,
    data,
    timestamp: Date.now()
  });
  pendingSync.value.add(queueKey);
};

const processSyncQueue = async () => {
  if (isSyncing.value || syncQueue.value.size === 0) return;
  
  isSyncing.value = true;
  
  try {
    const queueEntries = Array.from(syncQueue.value.entries());
    
    for (const [key, entry] of queueEntries) {
      if (pendingSync.value.has(key)) {
        await syncToServer(entry);
        pendingSync.value.delete(key);
        syncQueue.value.delete(key);
        
        // Update last saved state setelah berhasil sync
        lastSavedState.value = JSON.parse(JSON.stringify(localFormState.value));
      }
    }
  } catch (error) {
    console.error('Sync error:', error);
  } finally {
    isSyncing.value = false;
    
    if (pendingSync.value.size > 0) {
      setTimeout(() => processSyncQueue(), 500);
    }
  }
};

const syncToServer = async (entry) => {
  try {
    await router.post(route('inspections.save-result'), {
      inspection_id: props.inspection.id,
      point_id: entry.pointId,
      status: entry.type === 'result' ? entry.data.status : undefined,
      note: entry.type === 'result' ? entry.data.note : undefined,
      images: entry.type === 'images' ? entry.data : undefined,
    }, {
      preserveScroll: true,
      preserveState: true,
      onError: (errors) => {
        console.error('Sync failed:', errors);
      }
    });
  } catch (error) {
    console.error('Network error:', error);
  }
};

const findChanges = (oldState, newState) => {
  const changes = [];
  
  // Cek perubahan di results
  Object.keys(newState.results).forEach(pointId => {
    const oldResult = oldState.results[pointId] || {};
    const newResult = newState.results[pointId] || {};
    
    if (oldResult.status !== newResult.status || oldResult.note !== newResult.note) {
      changes.push({
        pointId,
        type: 'result',
        data: { ...newResult }
      });
    }
  });
  
  // Cek perubahan di images
  Object.keys(newState.images).forEach(pointId => {
    const oldImages = oldState.images[pointId] || [];
    const newImages = newState.images[pointId] || [];
    
    if (JSON.stringify(oldImages) !== JSON.stringify(newImages)) {
      changes.push({
        pointId,
        type: 'images',
        data: [...newImages]
      });
    }
  });
  
  return changes;
};

// Debounced sync function
const debouncedSync = debounce(async () => {
  await processSyncQueue();
}, 1000);

// Watcher untuk sync perubahan ke server
watch(
  () => localFormState.value,
  (newState, oldState) => {
    const changes = findChanges(lastSavedState.value, newState);
    
    if (changes.length > 0) {
      changes.forEach(change => {
        addToSyncQueue(change.pointId, change.type, change.data);
      });
      
      debouncedSync();
    }
  },
  { deep: true, immediate: false }
);

// ================ COMPUTED PROPERTIES ================
const hasDamage = computed(() => {
  return props.appMenus.some(menu => menu.input_type === 'damage')
})

const allCategories = computed(() => {
  return ['vehicle', ...props.appMenus.map(menu => menu.id), 'conclusion'];
});

const activeCategory = ref(allCategories.value[0]);
const activeIndex = ref(0);

watch(activeCategory, (newVal) => {
  activeIndex.value = allCategories.value.indexOf(newVal);
});

const activeMenuData = computed(() => {
  if (activeCategory.value === 'vehicle' || activeCategory.value === 'conclusion') {
    return null;
  }
  const menu = props.appMenus.find(m => m.id === activeCategory.value);
  if (!menu) return null;

  return {
    ...menu,
    points: getVisiblePoints(menu.menu_point, menu.input_type === 'damage')
  };
});

// Data untuk dikirim ke child components
const getFormDataForChild = computed(() => ({
  results: localFormState.value.results,
  images: localFormState.value.images,
  isSyncing: isSyncing.value
}));

// ================ VEHICLE DETAILS ================
const vehicleDetails = ref({
  plate_number: props.inspection.plate_number,
  car_id: props.inspection.car_id,
  car_name: props.inspection.car_name
});

const hasUnsavedChanges = ref(false);
const isVehicleDetailsInvalid = ref(false);

const handleUnsavedChanges = (hasChanges) => {
  hasUnsavedChanges.value = hasChanges;
};

const handleVehicleValidation = (isInvalid) => {
  isVehicleDetailsInvalid.value = isInvalid;
};

const isVehicleFormComplete = computed(() => {
  const isPlateValid = vehicleDetails.value.plate_number && /^[A-Z]{1,2}\d{1,4}[A-Z]{0,3}$/.test(vehicleDetails.value.plate_number);
  const isCarNameFilled = !!vehicleDetails.value.car_name?.trim();
  const hasNoBlockingValidation = !isVehicleDetailsInvalid.value;
  const hasUnsavedChangesValidation = !hasUnsavedChanges.value;
  
  return isPlateValid && isCarNameFilled && hasNoBlockingValidation && hasUnsavedChangesValidation;
});

// ================ DAMAGE POINTS ================
const filteredDamagePoints = computed(() => {
  if (!searchQuery.value.trim()) {
    return props.damagePoints || [];
  }
  
  const query = searchQuery.value.toLowerCase().trim();
  return (props.damagePoints || []).filter(point => 
    point.inspection_point?.name?.toLowerCase().includes(query) ||
    point.inspection_point?.description?.toLowerCase().includes(query) ||
    getComponentName(point)?.toLowerCase().includes(query)
  );
});

// Cek apakah point sudah memiliki data (gunakan local state)
const hasPointData = (pointId) => {
  const hasServerResult = props.existingResults && props.existingResults[pointId] !== undefined;
  const hasServerImages = props.existingImages && props.existingImages[pointId] && props.existingImages[pointId].length > 0;
  
  const hasLocalResult = localFormState.value.results[pointId] && 
                         (localFormState.value.results[pointId].status || localFormState.value.results[pointId].note);
  
  const hasLocalImages = localFormState.value.images[pointId] && localFormState.value.images[pointId].length > 0;
  
  return hasServerResult || hasServerImages || hasLocalResult || hasLocalImages;
};

const getComponentName = (point) => {
  return point.inspection_point.component?.name || 'Komponen Tidak Diketahui';
};

const getExistingPointData = (pointId) => {
  return props.existingResults[pointId] || null;
};

// ================ POINT MANAGEMENT ================
const selectPoint = (point) => {
  selectedPoint.value = point;
  const pointId = point.inspection_point_id;
  
  const existingResult = localFormState.value.results[pointId] || {};
  tempRadioValue.value = existingResult.status || '';
  tempNotes.value = existingResult.note || '';

  showSearchModal.value = false;
  showRadioModal.value = true;
};

const updateImagesValue = (images) => {
  if (selectedPoint.value) {
    const pointId = selectedPoint.value.inspection_point_id;
    localFormState.value.images[pointId] = images;
    form.images[pointId] = images;
  }
};

const saveAllData = () => {
  if (selectedPoint.value) {
    const pointId = selectedPoint.value.inspection_point_id;
    
    localFormState.value.results[pointId] = {
      ...localFormState.value.results[pointId],
      status: tempRadioValue.value,
      note: tempNotes.value,
    };
    
    form.results[pointId] = { ...localFormState.value.results[pointId] };
    
    // Trigger sync
    addToSyncQueue(pointId, 'result', localFormState.value.results[pointId]);
    processSyncQueue();
    
    successMessage.value = "Data berhasil disimpan!";
    setTimeout(() => successMessage.value = "", 1000);
  }
  
  closeRadioModal();
};

const closeSearchModal = () => {
  showSearchModal.value = false;
  searchQuery.value = '';
};

const closeRadioModal = () => {
  showRadioModal.value = false;
  selectedPoint.value = null;
  tempRadioValue.value = '';
  tempNotes.value = '';
};

// ================ FORM OPERATIONS ================
// Update result dengan optimistic update
const updateResult = ({ pointId, type, value }) => {
  if (!localFormState.value.results[pointId]) {
    localFormState.value.results[pointId] = { status: '', note: '' };
  }
  
  localFormState.value.results[pointId][type] = value;
  
  // Untuk kompatibilitas, update form juga
  if (!form.results[pointId]) {
    form.results[pointId] = { status: '', note: '' };
  }
  form.results[pointId][type] = value;
};

// Update images dengan optimistic update
const updateImages = (pointId, images) => {
  localFormState.value.images[pointId] = images;
  form.images[pointId] = images;
};

// Save result untuk kasus khusus (immediate save)
const saveResult = debounce(async (pointId) => {
  if (localFormState.value.results[pointId]) {
    addToSyncQueue(pointId, 'result', localFormState.value.results[pointId]);
  }
  if (localFormState.value.images[pointId]) {
    addToSyncQueue(pointId, 'images', localFormState.value.images[pointId]);
  }
  await processSyncQueue();
}, 500);

// Delete data dengan optimistic update
const hapusData = async (pointId) => {
  if (!pointId) return;

  if (confirm("Apakah kamu yakin ingin menghapus data ini?")) {
    try {
      // Optimistic update - reset local state dulu
      if (localFormState.value.results[pointId]) {
        localFormState.value.results[pointId] = { status: '', note: '' };
      }
      if (localFormState.value.images[pointId]) {
        localFormState.value.images[pointId] = [];
      }
      
      // Reset form state juga
      if (form.results[pointId]) {
        form.results[pointId] = { status: '', note: '' };
      }
      if (form.images[pointId]) {
        form.images[pointId] = [];
      }
      
      await router.post(route('inspections.delete-result'), {
        inspection_id: props.inspection.id,
        point_id: pointId,
      }, {
        preserveScroll: true,
        onSuccess: () => {
          successMessage.value = "Data berhasil dihapus!";
          // Update last saved state
          lastSavedState.value = JSON.parse(JSON.stringify(localFormState.value));
        },
        onError: (errors) => {
          console.error('Error menghapus hasil:', errors);
          // Rollback bisa ditambahkan di sini jika needed
        }
      });
    } catch (error) {
      console.error('Error menghapus hasil:', error);
    }
    closeRadioModal();
  }
};

// ================ MENU COMPLETION LOGIC ================
const getVisiblePoints = (menuPoints, isDamageMenu) => {
  if (!isDamageMenu) {
    return menuPoints || [];
  }
  
  return (menuPoints || []).filter(point => {
    const pointId = point.inspection_point_id;
    return hasPointData(pointId);
  });
};

const isMenuComplete = (menu) => {
  if (menu.input_type === 'damage') {
    const pointsWithData = getVisiblePoints(menu.menu_point, true);
    return pointsWithData.length > 0;
  }

  if (menu.id === 'conclusion') {
    return isConclusionComplete();
  }
  if (menu.id === 'vehicle') {
    return isVehicleFormComplete();
  }

  const requiredPoints = (getVisiblePoints(menu.menu_point, false) || [])
    .filter(point => point.is_default);

  return requiredPoints.every(point => {
    const result = localFormState.value.results[point.inspection_point_id];
    const image = localFormState.value.images[point.inspection_point_id];
    if (!result) return false;
    
    const settings = parseSettings(point.settings);
    
    switch(point.input_type) {
      case 'text':
      case 'number':
      case 'date':
      case 'account':
      case 'textarea':
        return !!result.note?.trim();
      
      case 'select':
      case 'radio':
        if (!result.status) return false;
        
        const selectedOption = settings.radios?.find(opt => opt.value === result.status);
        if (selectedOption?.settings) {
          if (selectedOption.settings.show_textarea && !result.note?.trim()) {
            return false;
          }
          if (selectedOption.settings.show_image_upload && image?.length === 0) {
            return false;
          }
        }
        return true;
      
      case 'imageTOradio':
        if (image?.length === 0 || !result.status) return false;
        
        const selectedOptionImage = settings.radios?.find(opt => opt.value === result.status);
        if (selectedOptionImage?.settings) {
          if (selectedOptionImage.settings.show_textarea && selectedOptionImage.settings.required && !result.note?.trim()) {
            return false;
          }
        }
        return true;
      
      case 'image':
        return image?.length > 0;
      
      default:
        return !!result.status || !!result.note?.trim();
    }
  });
};

const parseSettings = (settings) => {
  if (!settings) return {};
  if (typeof settings === 'string') {
    try {
      return JSON.parse(settings) || {};
    } catch (e) {
      console.error('Error parsing settings JSON:', e);
      return {};
    }
  }
  if (typeof settings === 'object' && settings !== null) {
    return settings;
  }
  return {};
};

const isConclusionComplete = () => {
  const settings = parseSettings(props.inspection.settings);
  const conclusionData = settings.conclusion || {};
  
  const hasFlooded = !!conclusionData.flooded;
  const hasCollision = !!conclusionData.collision;
  const hasValidCollision = conclusionData.collision === 'yes' 
    ? !!conclusionData.collision_severity 
    : true;
  const hasConclusionNote = !!props.inspection.notes?.trim();
  
  return hasFlooded && hasCollision && hasValidCollision && hasConclusionNote;
};

const conclusionStatus = computed(() => {
  const settings = parseSettings(props.inspection.settings);
  const conclusionData = settings.conclusion || {};
  
  return {
    flooded: conclusionData.flooded || null,
    collision: conclusionData.collision || null,
    collision_severity: conclusionData.collision_severity || null,
    note: props.inspection.notes || null,
    isComplete: isConclusionComplete()
  };
});

const allMenusComplete = computed(() => {
  const vehicleComplete = isVehicleFormComplete.value;
  const regularMenusComplete = props.appMenus.every(menu => isMenuComplete(menu));
  const conclusionComplete = isConclusionComplete();
  return vehicleComplete && regularMenusComplete && conclusionComplete;
});

// ================ OTHER FUNCTIONS ================
const changeCategory = (menuId) => {
  activeCategory.value = menuId;
};

const navigate = (direction) => {
  let newIndex = activeIndex.value + direction;
  
  if (newIndex >= 0 && newIndex < allCategories.value.length) {
    activeCategory.value = allCategories.value[newIndex];
  }
};

const handleAction = (route, actionType) => {
  isLoading.value = true;
  currentAction.value = actionType;

  router.visit(route, {
    onFinish: () => {
      isLoading.value = false;
      currentAction.value = null;
    }
  });
};

const updateVehicleDetails = (vehicleData) => {
  console.log('Update vehicle:', vehicleData);
};

const saveNewCarDetails = (carDetails) => {
  console.log('Save car details:', carDetails);
};

const updateConclusion = (conclusionData) => {
  Object.assign(form.conclusion, conclusionData);
  saveConclusion();
};

const saveConclusion = debounce(async () => {
  try {
    await router.post(route('inspections.save-conclusion'), {
      inspection_id: props.inspection.id,
      ...form.conclusion
    }, {
      preserveScroll: true,
      preserveState: true,
    });
  } catch (error) {
    console.error('Error menyimpan kesimpulan:', error);
  }
}, 500);

const submitAll = async () => {
  // Pastikan semua pending sync selesai dulu
  if (pendingSync.value.size > 0) {
    await processSyncQueue();
  }
  
  // Copy data dari local state ke form untuk submit
  form.results = { ...localFormState.value.results };
  form.images = { ...localFormState.value.images };
  
  if (!allMenusComplete.value) {
    alert('Harap lengkapi semua menu inspeksi termasuk kesimpulan sebelum submit final');
    return;
  }

  form.post(route('inspections.final-submit', { 
    id: props.inspection.id 
  }), {
    preserveScroll: true,
    onSuccess: () => {},
    onError: (errors) => {
      console.error('Kesalahan pengiriman:', errors);
    }
  });
};

const removeImage = ({ pointId, imageId }) => {
  if (localFormState.value.images[pointId]) {
    localFormState.value.images[pointId] = localFormState.value.images[pointId].filter(img => img.id !== imageId);
    form.images[pointId] = localFormState.value.images[pointId];
    saveResult(pointId);
  }
};

// ================ LIFECYCLE ================
onMounted(() => {
  initializeLocalState();
  // setupSwipe();
});
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.category-slide-enter-active,
.category-slide-leave-active {
  transition: all 0.3s ease-out;
  position: absolute; 
  width: 100%; 
  top: 0;
  left: 0;
}
.category-slide-enter-from {
  transform: translateX(150%);
  opacity: 0;
}
.category-slide-leave-to {
  transform: translateX(-150%);
  opacity: 0;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

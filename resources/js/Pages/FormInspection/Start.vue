<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header Inspection Info -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-start">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ inspection.name }}</h1>
            <p class="text-gray-600 mt-1">
              Kategori: <span class="font-medium">{{ inspection.category.name }}</span>
            </p>
            <div class="flex items-center mt-3 space-x-4">
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                ID: {{ inspection.id }}
              </span>
              <span class="text-sm text-gray-500">
                Dibuat: {{ formatDate(new Date()) }}
              </span>
            </div>
          </div>
          
          <div class="flex space-x-3">
            <button 
              @click="saveAllData" 
              :disabled="isSavingAll"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="isSavingAll" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Simpan Semua
            </button>
            
            <button 
              @click="showSummary = !showSummary"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              Ringkasan
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Left Sidebar - Menu Navigation -->
        <div class="lg:col-span-1">
          <MenuNavigation
            :menus="allMenus"
            :current-menu-id="currentMenuData.id"
            :current-index="currentMenuIndex"
            @menu-change="changeMenu"
            @damage-add="showAddDamage"
          />
        </div>

        <!-- Right Content - Form -->
        <div class="lg:col-span-3">
          <div class="bg-white rounded-xl shadow overflow-hidden">
            <!-- Current Menu Header -->
            <div class="px-6 py-4 border-b bg-gray-50">
              <div class="flex justify-between items-center">
                <div>
                  <h2 class="text-lg font-semibold text-gray-900">
                    {{ currentMenuData.name }}
                    <span :class="[
                      'ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      currentMenuData.type === 'menu'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-yellow-100 text-yellow-800'
                    ]">
                      {{ currentMenuData.type === 'menu' ? 'Menu' : 'Damage' }}
                    </span>
                  </h2>
                  <p class="text-sm text-gray-600 mt-1">
                    Menu {{ currentMenuIndex + 1 }} dari {{ totalMenus }}
                  </p>
                </div>
                
                <div class="flex items-center space-x-3">
                  <div v-if="uiConfig.autoSave" class="flex items-center text-sm text-gray-500">
                    <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Auto-save aktif
                  </div>
                  
                  <button 
                    v-if="navigation.prevId"
                    @click="changeMenu(navigation.prevId)"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  >
                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Sebelumnya
                  </button>
                  
                  <button 
                    v-if="navigation.nextId"
                    @click="changeMenu(navigation.nextId)"
                    class="inline-flex items-center px-3 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  >
                    Selanjutnya
                    <svg class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Form Points -->
            <div class="p-6">
              <div v-if="isLoadingMenu" class="flex justify-center py-12">
                <div class="text-center">
                  <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <p class="text-gray-600">Memuat data menu...</p>
                </div>
              </div>
              
              <div v-else>
                <!-- Damage Empty State -->
                <div v-if="currentMenu.type === 'damage' && currentMenuData.points.length === 0" class="text-center py-12">
                  <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada damage</h3>
                  <p class="mt-1 text-sm text-gray-500">
                    Tambahkan damage terlebih dahulu untuk menampilkan form ini.
                  </p>
                  <div class="mt-6">
                    <button 
                      @click="showAddDamageModal = true"
                      type="button"
                      class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                      <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                      Tambah Damage
                    </button>
                  </div>
                </div>
                
                <!-- Form Points List -->
                <div v-else class="space-y-6">
                  <div v-for="point in currentMenuData.points" :key="point.id" class="border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors">
                    <PointRenderer
                      :point="point"
                      :inspection-id="inspection.id"
                      @value-changed="handleValueChange"
                      @save="savePointData"
                      @validation="handleValidation"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Damage Modal -->
    <AddDamageModal
      v-if="showAddDamageModal"
      :inspection-id="inspection.id"
      :current-menu-id="currentMenuData.id"
      @close="showAddDamageModal = false"
      @damage-added="handleDamageAdded"
    />

    <!-- Summary Modal -->
    <SummaryModal 
      v-if="showSummary"
      :inspection="inspection"
      :all-menus="allMenus"
      @close="showSummary = false"
    />
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import MenuNavigation from './components/MenuNavigation.vue';
import PointRenderer from './components/PointRenderer.vue';
import AddDamageModal from './components/AddDamageModal.vue';
import SummaryModal from './components/SummaryModal.vue';
import axios from 'axios';

// Props dari Inertia
const props = defineProps({
  inspection: Object,
  currentMenu: Object,
  points: Array,
  allMenus: Array,
  uiConfig: Object,
  initialData: Object
});

// Reactive State
const isLoadingMenu = ref(false);
const isSavingAll = ref(false);
const showAddDamageModal = ref(false);
const showSummary = ref(false);

// Data State
const currentMenuData = reactive({
  ...props.currentMenu,
  points: [...props.points]
});

const navigation = reactive({
  prevId: null,
  nextId: null,
  currentIndex: props.initialData.currentMenuIndex,
  totalMenus: props.initialData.totalMenus
});

const unsavedChanges = reactive({});
const validationErrors = reactive({});

// Computed
const currentMenuIndex = computed(() => navigation.currentIndex);
const totalMenus = computed(() => navigation.totalMenus);
const hasUnsavedChanges = computed(() => Object.keys(unsavedChanges).length > 0);

// Methods
const changeMenu = async (menuId) => {
  if (hasUnsavedChanges.value) {
    if (!confirm('Ada perubahan yang belum disimpan. Pindah menu?')) {
      return;
    }
    await saveUnsavedChanges();
  }

  isLoadingMenu.value = true;
  
  try {
    const response = await axios.get(`/api/form-inspection/${props.inspection.id}/menu/${menuId}`, {
      params: {
        current_values: unsavedChanges
      }
    });

    if (response.data.success) {
      // Update current menu data
      Object.assign(currentMenuData, response.data.menu);
      currentMenuData.points = response.data.points;
      
      // Update navigation
      Object.assign(navigation, response.data.navigation);
      
      // Clear unsaved changes for old menu
      Object.keys(unsavedChanges).forEach(key => {
        delete unsavedChanges[key];
      });
      
      // Clear validation errors
      Object.keys(validationErrors).forEach(key => {
        delete validationErrors[key];
      });
    }
  } catch (error) {
    console.error('Error changing menu:', error);
    alert('Gagal memuat menu');
  } finally {
    isLoadingMenu.value = false;
  }
};

const handleValueChange = ({ pointId, value }) => {
  unsavedChanges[pointId] = value;
  
  // Auto-save jika diaktifkan
  if (props.uiConfig.autoSave) {
    debouncedSave(pointId, value);
  }
};

const savePointData = async (pointId) => {
  const value = unsavedChanges[pointId];
  if (value === undefined) return;

  try {
    const response = await axios.post(`/api/form-inspection/${props.inspection.id}/save`, {
      point_id: pointId,
      value: value
    });

    if (response.data.success) {
      // Remove from unsaved changes
      delete unsavedChanges[pointId];
      
      // Remove validation errors
      delete validationErrors[pointId];
      
      // Handle triggers
      if (response.data.triggers && response.data.triggers.length > 0) {
        handleTriggers(response.data.triggers);
      }
      
      // Update point current value
      const pointIndex = currentMenuData.points.findIndex(p => p.id === pointId);
      if (pointIndex !== -1) {
        currentMenuData.points[pointIndex].currentValue = value;
        currentMenuData.points[pointIndex].existingDataId = response.data.data.id;
      }
    }
  } catch (error) {
    console.error('Error saving point:', error);
    if (error.response?.data?.errors) {
      validationErrors[pointId] = error.response.data.errors;
    }
  }
};

const saveUnsavedChanges = async () => {
  const savePromises = Object.keys(unsavedChanges).map(pointId => 
    savePointData(pointId)
  );
  
  await Promise.all(savePromises);
};

const saveAllData = async () => {
  isSavingAll.value = true;
  try {
    await saveUnsavedChanges();
    alert('Semua data berhasil disimpan!');
  } catch (error) {
    console.error('Error saving all:', error);
    alert('Gagal menyimpan semua data');
  } finally {
    isSavingAll.value = false;
  }
};

const handleValidation = ({ pointId, errors }) => {
  if (errors && errors.length > 0) {
    validationErrors[pointId] = errors;
  } else {
    delete validationErrors[pointId];
  }
};

const handleTriggers = (triggers) => {
  triggers.forEach(trigger => {
    switch (trigger.action) {
      case 'show_damage':
        // Implementasi untuk menampilkan damage menu
        break;
      case 'show_alert':
        alert(trigger.message);
        break;
      case 'enable_field':
        // Implementasi untuk enable field
        break;
    }
  });
};

const handleDamageAdded = (damageData) => {
  // Reload current menu to show new damage
  changeMenu(currentMenuData.id);
  showAddDamageModal.value = false;
};

const formatDate = (date) => {
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  }).format(date);
};

// Debounce function untuk auto-save
let saveTimeout = null;
const debouncedSave = (pointId, value) => {
  clearTimeout(saveTimeout);
  saveTimeout = setTimeout(() => {
    savePointData(pointId);
  }, props.uiConfig.debounceDelay || 1000);
};

// Lifecycle Hooks
onMounted(() => {
  // Warn user jika ada unsaved changes saat refresh/close
  window.addEventListener('beforeunload', (e) => {
    if (hasUnsavedChanges.value) {
      e.preventDefault();
      e.returnValue = '';
    }
  });
});

onBeforeUnmount(() => {
  window.removeEventListener('beforeunload');
});
</script>
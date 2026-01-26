<template>
  <div class="bg-white rounded-xl shadow overflow-hidden">
    <!-- Header -->
    <div class="px-4 py-5 sm:px-6 border-b">
      <h3 class="text-lg font-medium text-gray-900">Menu Inspeksi</h3>
      <p class="mt-1 text-sm text-gray-500">
        Pilih menu untuk navigasi
      </p>
    </div>
    
    <!-- Progress -->
    <div class="px-4 py-3 bg-gray-50 border-b">
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-gray-700">Progress</span>
        <span class="text-sm font-semibold text-blue-600">{{ completedCount }}/{{ totalMenus }}</span>
      </div>
      <div class="mt-2">
        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
          <div 
            class="h-full bg-blue-600 rounded-full transition-all duration-300"
            :style="{ width: progressPercentage + '%' }"
          ></div>
        </div>
      </div>
    </div>
    
    <!-- Menu List -->
    <nav class="divide-y divide-gray-200">
      <div v-for="(menu, index) in menus" :key="menu.id">
        <!-- Menu Item -->
        <div 
          :class="[
            'px-4 py-3 cursor-pointer transition-colors',
            menu.id === currentMenuId
              ? 'bg-blue-50 border-r-4 border-blue-600'
              : 'hover:bg-gray-50'
          ]"
          @click="handleMenuClick(menu)"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <!-- Status Indicator -->
              <div 
                :class="[
                  'w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium mr-3',
                  getMenuStatusClass(menu)
                ]"
              >
                {{ index + 1 }}
              </div>
              
              <div class="flex-1">
                <div class="flex items-center">
                  <span class="text-sm font-medium text-gray-900 truncate">
                    {{ menu.name }}
                  </span>
                  <span 
                    v-if="menu.type === 'damage'"
                    :class="[
                      'ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                      menu.has_data ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                    ]"
                  >
                    {{ menu.has_data ? 'Ada Data' : 'Kosong' }}
                  </span>
                </div>
                
                <div class="flex items-center mt-1 text-xs text-gray-500">
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  {{ menu.type === 'menu' ? 'Menu Utama' : 'Damage Report' }}
                </div>
              </div>
            </div>
            
            <!-- Current Indicator -->
            <div v-if="menu.id === currentMenuId" class="ml-2">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </div>
        
        <!-- Damage Add Button -->
        <div 
          v-if="menu.type === 'damage' && menu.id === currentMenuId && !menu.has_data"
          class="px-4 py-2 bg-yellow-50 border-l-4 border-yellow-400"
        >
          <button
            @click="handleAddDamage"
            class="w-full inline-flex items-center justify-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-yellow-500"
          >
            <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Damage
          </button>
        </div>
      </div>
    </nav>
    
    <!-- Footer -->
    <div class="px-4 py-3 bg-gray-50 border-t">
      <div class="text-center">
        <button
          @click="$emit('damage-add')"
          class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Damage Baru
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  menus: Array,
  currentMenuId: Number,
  currentIndex: Number
});

const emit = defineEmits(['menu-change', 'damage-add']);

// Computed
const totalMenus = computed(() => props.menus.length);
const completedCount = computed(() => {
  return props.menus.filter(menu => {
    if (menu.type === 'damage') {
      return menu.has_data;
    }
    return true; // Menu utama selalu considered completed
  }).length;
});

const progressPercentage = computed(() => {
  return totalMenus.value > 0 ? Math.round((completedCount.value / totalMenus.value) * 100) : 0;
});

// Methods
const handleMenuClick = (menu) => {
  // Skip jika damage belum ada data
  if (menu.type === 'damage' && !menu.has_data) {
    return;
  }
  emit('menu-change', menu.id);
};

const handleAddDamage = () => {
  emit('damage-add');
};

const getMenuStatusClass = (menu) => {
  if (menu.id === props.currentMenuId) {
    return 'bg-blue-100 text-blue-800 border-2 border-blue-300';
  }
  
  if (menu.type === 'damage') {
    return menu.has_data 
      ? 'bg-green-100 text-green-800 border border-green-300' 
      : 'bg-gray-100 text-gray-800 border border-gray-300';
  }
  
  // Menu utama dengan data
  return 'bg-blue-50 text-blue-700 border border-blue-200';
};
</script>
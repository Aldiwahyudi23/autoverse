<template>
  <div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 transition-opacity" aria-hidden="true">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>

      <!-- Modal -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
          <div class="sm:flex sm:items-start">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
              <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900">
                Ringkasan Inspeksi
              </h3>
              <div class="mt-4">
                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                  <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500">Total Menu</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ stats.totalMenus }}</div>
                  </div>
                  <div class="bg-green-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-green-500">Selesai</div>
                    <div class="mt-1 text-2xl font-semibold text-green-900">{{ stats.completed }}</div>
                  </div>
                  <div class="bg-yellow-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-yellow-500">Dalam Proses</div>
                    <div class="mt-1 text-2xl font-semibold text-yellow-900">{{ stats.inProgress }}</div>
                  </div>
                  <div class="bg-blue-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-blue-500">Damage</div>
                    <div class="mt-1 text-2xl font-semibold text-blue-900">{{ stats.damageCount }}</div>
                  </div>
                </div>

                <!-- Menu List -->
                <div class="border rounded-lg overflow-hidden">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Menu
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Tipe
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Progress
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="menu in allMenus" :key="menu.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                          {{ menu.name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          <span :class="[
                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                            menu.type === 'menu' 
                              ? 'bg-blue-100 text-blue-800'
                              : 'bg-yellow-100 text-yellow-800'
                          ]">
                            {{ menu.type === 'menu' ? 'Menu' : 'Damage' }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          <span :class="[
                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                            getStatusClass(menu)
                          ]">
                            {{ getStatusText(menu) }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="w-full bg-gray-200 rounded-full h-2">
                            <div 
                              :class="['h-2 rounded-full', getProgressColor(menu)]"
                              :style="{ width: getProgressWidth(menu) + '%' }"
                            ></div>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Notes -->
                <div class="mt-6">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan Akhir Inspeksi
                  </label>
                  <textarea 
                    v-model="finalNotes"
                    rows="3"
                    placeholder="Tambahkan catatan keseluruhan inspeksi..."
                    class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button
            @click="completeInspection"
            type="button"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Selesaikan Inspeksi
          </button>
          <button
            @click="$emit('close')"
            type="button"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  inspection: Object,
  allMenus: Array
});

const emit = defineEmits(['close']);

const finalNotes = ref('');

// Computed
const stats = computed(() => {
  const totalMenus = props.allMenus.length;
  const completed = props.allMenus.filter(menu => 
    menu.type === 'menu' || (menu.type === 'damage' && menu.has_data)
  ).length;
  const damageCount = props.allMenus.filter(menu => 
    menu.type === 'damage' && menu.has_data
  ).length;
  
  return {
    totalMenus,
    completed,
    inProgress: totalMenus - completed,
    damageCount
  };
});

// Methods
const getStatusClass = (menu) => {
  if (menu.type === 'damage') {
    return menu.has_data 
      ? 'bg-green-100 text-green-800' 
      : 'bg-gray-100 text-gray-800';
  }
  return 'bg-blue-100 text-blue-800';
};

const getStatusText = (menu) => {
  if (menu.type === 'damage') {
    return menu.has_data ? 'Ada Data' : 'Belum Ada';
  }
  return 'Menu Utama';
};

const getProgressColor = (menu) => {
  if (menu.type === 'damage') {
    return menu.has_data ? 'bg-green-600' : 'bg-gray-300';
  }
  return 'bg-blue-600';
};

const getProgressWidth = (menu) => {
  if (menu.type === 'damage') {
    return menu.has_data ? 100 : 0;
  }
  return 100; // Menu utama selalu 100%
};

const completeInspection = async () => {
  try {
    const response = await axios.post(`/api/inspections/${props.inspection.id}/complete`, {
      notes: finalNotes.value
    });
    
    if (response.data.success) {
      emit('close');
      // Redirect atau show success message
      alert('Inspeksi berhasil diselesaikan!');
    }
  } catch (error) {
    console.error('Error completing inspection:', error);
    alert('Gagal menyelesaikan inspeksi');
  }
};
</script>
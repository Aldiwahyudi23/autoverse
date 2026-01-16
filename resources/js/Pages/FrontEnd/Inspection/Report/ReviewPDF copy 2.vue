php<template>
  <div class="flex flex-col lg:grid lg:grid-cols-12 gap-4">
    <!-- Konten Utama Laporan -->
    <div class="p-4 md:p-6 bg-white rounded-lg shadow-md lg:col-span-7">
      <!-- Alert Messages -->
      <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
        <div class="flex">
          <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
          </svg>
          <span>{{ $page.props.flash.success }}</span>
        </div>
      </div>

      <div v-if="$page.props.flash.error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        <div class="flex">
          <svg class="w-5 h-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
          </svg>
          <span>{{ $page.props.flash.error }}</span>
        </div>
      </div>

      <!-- Konten Laporan -->
      <div v-if="hasDataOrImages">
        <div class="header flex flex-col gap-5 mb-6 mt-2 md:mt-6">
          <div class="flex flex-col md:flex-row items-center md:items-start gap-5">
            <!-- Gambar Utama -->
            <div class="flex-shrink-0">
              <img
                v-if="coverImage && imageExists(coverImage.image_path)"
                :src="getImageUrl(coverImage.image_path)"
                alt="Foto Utama"
                class="w-32 h-32 md:w-48 md:h-48 object-cover rounded-lg border border-gray-300 cursor-pointer hover:opacity-90 transition-opacity"
                @click="openImageModal(getImageUrl(coverImage.image_path), { type: 'cover', name: 'Foto Utama Kendaraan' }, 0)"
              >
              <div v-else class="w-32 h-32 md:w-48 md:h-48 border border-gray-300 rounded-lg flex items-center justify-center bg-gray-100 text-gray-400">
                <span class="text-sm md:text-base">Gambar tidak tersedia</span>
              </div>
            </div>
            <div class="text-center md:text-left mt-2 md:mt-8">
              <h2 class="text-2xl md:text-3xl font-bold m-0 text-gray-800">{{ inspection.car_name }}</h2>
            </div>
          </div>

          <!-- Tabel Informasi Mobil - Responsive -->
          <div v-if="inspection.car_id" class="car-info">
            <table class="w-full border-collapse border border-gray-300 rounded-lg overflow-hidden text-sm md:text-base">
              <tr class="bg-gray-50">
                <td class="p-2 border border-gray-300 font-bold text-gray-700 w-1/3 md:w-1/4">ID Order</td>
                <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.order_code }}</td>
              </tr>
              <tr class="bg-gray-50">
                <td class="p-2 border border-gray-300 font-bold text-gray-700">Nomor Polisi</td>
                <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.plate_number }}</td>
              </tr>
              <tr>
                <td class="p-2 border border-gray-300 font-bold text-gray-700">Merek</td>
                <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.brand?.name }}</td>
              </tr>
              <tr class="bg-gray-50">
                <td class="p-2 border border-gray-300 font-bold text-gray-700">Model</td>
                <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.model?.name }}</td>
              </tr>
              <tr>
                <td class="p-2 border border-gray-300 font-bold text-gray-700">Tipe</td>
                <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.type?.name }}</td>
              </tr>
              <tr class="bg-gray-50">
                <td class="p-2 border border-gray-300 font-bold text-gray-700">CC</td>
                <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.cc }}</td>
              </tr>
              <tr>
                <td class="p-2 border border-gray-300 font-bold text-gray-700">Bahan Bakar</td>
                <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.fuel_type }}</td>
              </tr>
              <tr class="bg-gray-50">
                <td class="p-2 border border-gray-300 font-bold text-gray-700">Transmisi</td>
                <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.transmission }}</td>
              </tr>
              <tr>
                <td class="p-2 border border-gray-300 font-bold text-gray-700">Periode Model</td>
                <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.production_period || '-' }}</td>
              </tr>
              <tr class="bg-gray-50">
                <td class="p-2 border border-gray-300 font-bold text-gray-700">Tahun Pembuatan</td>
                <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.car?.year }}</td>
              </tr>
              <tr class="bg-gray-50">
                <td class="p-2 border border-gray-300 font-bold text-gray-700">Warna</td>
                <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.color }}</td>
              </tr>
              <tr class="bg-gray-50">
                <td class="p-2 border border-gray-300 font-bold text-gray-700">No Rangka</td>
                <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.noka }}</td>
              </tr>
              <tr class="bg-gray-50">
                <td class="p-2 border border-gray-300 font-bold text-gray-700">No Mesin</td>
                <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.nosin }}</td>
              </tr>
              <tr class="bg-gray-50">
                <td class="p-2 border border-gray-300 font-bold text-gray-700">KM</td>
                <td class="p-2 border border-gray-300 text-gray-600">{{ inspection.km }}</td>
              </tr>
            </table>
          </div>

          <!-- Tabel Status Banjir dan Tabrak - Responsive -->
          <div v-if="inspection.settings" class="flex flex-col md:flex-row gap-4 mt-4">
            <div class="flex-1 text-center p-4 bg-gray-50 rounded-lg">
              <div class="flex flex-col items-center">
                <img v-if="inspection.settings.conclusion.flooded === 'yes'" 
                     src="/images/icons/banjir.png" 
                     alt="Banjir" 
                     class="w-16 h-16 md:w-20 md:h-20">
                <img v-else 
                     src="/images/icons/aman.png" 
                     alt="Aman" 
                     class="w-16 h-16 md:w-20 md:h-20">
                
                <p v-if="inspection.settings.conclusion.flooded === 'yes'" 
                   class="font-bold text-sm md:text-base text-red-600 mt-2">
                  Bekas Banjir
                </p>
                <p v-else 
                   class="font-bold text-sm md:text-base text-green-600 mt-2">
                  Bebas Banjir
                </p>
              </div>
            </div>
            
            <div class="flex-1 text-center p-4 bg-gray-50 rounded-lg">
              <div class="flex flex-col items-center">
                <template v-if="inspection.settings.conclusion.collision === 'yes'">
                  <img :src="collisionImage" 
                       alt="Tabrak" 
                       class="w-16 h-16 md:w-20 md:h-20">
                  <p :style="{ fontWeight: 'bold', fontSize: '14px', color: collisionColor, marginTop: '8px' }">
                    {{ collisionText }}
                  </p>
                </template>
                <template v-else>
                  <img src="/images/icons/aman.png" 
                       alt="Aman" 
                       class="w-16 h-16 md:w-20 md:h-20">
                  <p class="font-bold text-sm md:text-base text-green-600 mt-2">
                    Bebas Tabrak
                  </p>
                </template>
              </div>
            </div>
          </div>

          <!-- Kesimpulan Inspeksi -->
          <div v-if="inspection.notes" class="conclusion p-4 bg-gray-50 border-l-4 border-gray-800 rounded-lg mt-4">
            <h3 class="text-lg font-bold mb-2 text-gray-800">Kesimpulan Inspeksi:</h3>
            <p class="m-0 text-gray-600 text-sm md:text-base">
              <div v-html="inspection.notes || '-'"></div>
            </p>
          </div>
        </div>

        <h2 class="text-xl md:text-2xl font-bold border-b-2 border-gray-800 pb-2 mb-4 md:mb-6">Hasil Inspeksi</h2>

        <!-- Loop untuk setiap komponen inspeksi -->
        <div 
          v-for="group in groupedPoints" 
          :key="group.component.id"
          :class="['section mb-4 md:mb-6', group.component.name === 'Foto Kendaraan' ? 'photo-component' : '']">

          <!-- Judul Komponen -->
          <div class="component-title bg-gray-100 px-3 py-2 border-l-4 border-gray-800 font-bold rounded-l-lg text-sm md:text-base">
            {{ group.component.name || 'Tanpa Komponen' }}
          </div>

          <!-- Bagian Foto Kendaraan -->
          <div v-if="group.component.name === 'Foto Kendaraan'" class="images flex flex-wrap gap-2 mt-3 md:mt-4">
            <div v-for="point in group.points" :key="point.id">
              <div v-for="(img, imgIndex) in point.inspection_point?.images" :key="img.id" class="image-container">
                <img
                  v-if="imageExists(img.image_path)"
                  :src="getImageUrl(img.image_path)"
                  alt="Foto Kendaraan"
                  class="w-24 h-24 md:w-32 md:h-32 object-cover border border-gray-300 rounded-lg cursor-pointer hover:opacity-90 transition-opacity hover:scale-105"
                  @click="openImageModal(getImageUrl(img.image_path), point, imgIndex)"
                >
                <div v-else class="w-24 h-24 md:w-32 md:h-32 border border-gray-300 rounded-lg flex items-center justify-center bg-gray-100 text-xs text-gray-400">
                  <span>Gambar tidak ditemukan</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Bagian Poin Inspeksi non-foto -->
          <template v-else>
            <div v-for="point in group.points" :key="point.id" class="point ml-3 md:ml-4 py-3 border-b border-gray-100">
              <template v-if="hasResult(point) || hasImage(point)">
                <!-- Nama Poin Inspeksi -->
                <span class="point-name inline-block font-bold align-top text-gray-700 text-sm md:text-base min-w-[140px] md:min-w-[180px]">
                  {{ point.inspection_point?.name || '-' }}
                </span>
                
                <div class="point-content inline-block align-top w-[calc(100%-150px)] md:w-[calc(100%-190px)]">
                  <template v-if="hasResult(point)">
                    <!-- Badge Status -->
                    <div v-if="shouldShowStatusBadge(point)" class="status-badges-container">
                      <span
                        v-for="status in getStatusArray(point)"
                        :key="status"
                        :class="['status-badge', getStatusClass(status)]"
                      >
                        {{ status.trim() }}
                      </span>
                      <!-- Button to add repair estimation -->
                      <button
                        v-if="canEditEstimations && hasBadStatus(point) && group.component.name !== 'Dokumen'"
                        @click="openEstimationModal(point)"
                        class="ml-2 bg-orange-500 text-white px-2 py-1 rounded text-xs hover:bg-orange-600 transition-colors inline-flex items-center"
                        title="Tambah Estimasi Perbaikan"
                      >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                      </button>
                    </div>

                    <!-- Catatan (Note) -->
                    <div v-if="shouldShowNote(point)" class="point-note italic text-gray-600 my-1 text-xs md:text-sm">
                      {{ formatNote(point) }}
                    </div>
                  </template>
                </div>

                <!-- Gambar Poin Inspeksi -->
                <div v-if="shouldShowImages(point)" class="inspection-images flex flex-wrap gap-2 mt-3 md:mt-4">
                  <div v-for="(img, imgIndex) in point.inspection_point?.images" :key="img.id" class="image-container">
                    <img
                      v-if="imageExists(img.image_path)"
                      :src="getImageUrl(img.image_path)"
                      alt="image"
                      class="w-16 h-16 md:w-24 md:h-24 object-cover border border-gray-300 rounded-lg cursor-pointer hover:opacity-90 transition-opacity hover:scale-105"
                      @click="openImageModal(getImageUrl(img.image_path), point, imgIndex)"
                    >
                    <div v-else class="w-16 h-16 md:w-24 md:h-24 border border-gray-300 rounded-lg flex items-center justify-center bg-gray-100 text-xs text-gray-400">
                      <span>Gambar tidak ditemukan</span>
                    </div>
                  </div>
                </div>

                <!-- Catatan Textarea -->
                <div v-if="shouldShowTextarea(point) && hasNote(point)" class="textarea-note italic text-gray-600 my-2 text-xs md:text-sm">
                  {{ point.inspection_point?.results[0].note }}
                </div>
              </template>
            </div>
          </template>
        </div>

        <!-- Bagian Estimasi Perbaikan (Component Terpisah) -->
        <RepairEstimationSection
          :estimations="repairEstimations"
          :inspection-id="inspection.id"
          :encrypted-ids="encryptedIds"
          :can-edit="canEditEstimations"
          @update:estimations="updateEstimations"
          @update:totalCost="updateTotalCost"
        />

        <!-- Tombol Persetujuan Laporan -->
        <div v-if="canApproveReport" class="mt-4 md:mt-6 mb-4 md:mb-6">
          <button 
            @click="showConfirmationModal = true"
            class="w-full md:w-auto bg-blue-600 text-white px-4 py-2 md:px-6 md:py-3 rounded-lg shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 text-sm md:text-base"
          >
            Setujui Laporan
          </button>
          <p class="text-xs md:text-sm text-gray-500 mt-2 italic">
            Catatan: Halaman ini hanya simulasi dan mungkin berbeda dengan tampilan file PDF. Silakan periksa lebih detail untuk memastikan tidak ada kesalahan. Jika sudah yakin, silakan setujui untuk proses selanjutnya.
          </p>
        </div>
      </div>
      
      <!-- Tampilan Jika Data Tidak Ada -->
      <div v-else class="flex flex-col items-center justify-center h-64 md:h-96 text-center p-4">
        <p class="text-base md:text-lg font-semibold text-gray-700">
          Data sudah tidak ada, karena sudah dijadikan PDF.
        </p>
        <a 
          :href="route('inspections.download.approved.pdf', encryptedIds)"
          class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 md:px-6 md:py-3 rounded-lg shadow-md hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 text-sm md:text-base"
        >
          Download PDF
        </a>
      </div>

      <!-- Modal Konfirmasi Setujui Laporan -->
      <div v-if="showConfirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-4 md:p-6 rounded-lg shadow-xl w-11/12 md:w-96 max-w-sm mx-4">
          <h3 class="text-lg font-bold mb-3 md:mb-4">Konfirmasi Laporan</h3>
          <p class="text-gray-700 mb-4 md:mb-6 text-sm md:text-base">
            Apakah Anda yakin semua data sudah sesuai? Setelah disetujui, laporan akan diproses menjadi file PDF.
          </p>
          <div class="flex justify-end space-x-3 md:space-x-4">
            <button @click="showConfirmationModal = false" class="bg-gray-300 text-gray-800 px-3 py-1.5 md:px-4 md:py-2 rounded-lg hover:bg-gray-400 text-sm md:text-base">
              Batal
            </button>
            <button
              v-if="!inspection.file"
              @click="approveReport"
              class="bg-sky-600 text-white px-3 py-1.5 md:px-4 md:py-2 rounded-lg hover:bg-sky-700 transition-colors duration-200 text-sm md:text-base"
              :disabled="isLoading"
            >
              <span v-if="isLoading">Membuat file PDF...</span>
              <span v-else>Setujui Laporan</span>
            </button>
            <button v-else class="bg-indigo-600 text-white px-3 py-1.5 md:px-4 md:py-2 rounded-lg hover:bg-indigo-700 transition-colors duration-200 cursor-not-allowed text-sm md:text-base">
              <span>Laporan sudah di buat</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Modal untuk Mobile -->
      <div v-if="showImageModal && !isDesktopSidebarVisible"
           class="fixed inset-0 bg-black bg-opacity-95 flex flex-col items-center justify-center z-50"
           @click.self="closeImageModal">

        <!-- Header Modal - Hanya Komponen dan Tombol Close -->
        <div class="absolute top-0 left-0 right-0 bg-black bg-opacity-90 text-white p-3 md:p-4 flex items-center justify-between z-20">
          <!-- Komponen di tengah -->
          <div class="flex-1 text-center">
            <h3 class="text-base md:text-lg font-bold truncate max-w-[70vw] mx-auto">
              {{ currentImageInfo.componentName || 'Gambar Inspeksi' }}
            </h3>
          </div>
          
          <!-- Close Button di kanan -->
          <button
            @click="closeImageModal"
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
            v-if="hasPreviousImage"
            @click="showPreviousImage"
            class="absolute left-2 md:left-4 top-1/2 transform -translate-y-1/2 z-10 bg-black bg-opacity-60 text-white rounded-full w-10 h-10 md:w-12 md:h-12 flex items-center justify-center hover:bg-opacity-80 transition-all hover:scale-110"
            :class="{ 'opacity-50': !hasPreviousImage }"
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
              v-if="currentImageUrl"
              ref="imageElement"
              :src="currentImageUrl"
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
            v-if="hasNextImage"
            @click="showNextImage"
            class="absolute right-2 md:right-4 top-1/2 transform -translate-y-1/2 z-10 bg-black bg-opacity-60 text-white rounded-full w-10 h-10 md:w-12 md:h-12 flex items-center justify-center hover:bg-opacity-80 transition-all hover:scale-110"
            :class="{ 'opacity-50': !hasNextImage }"
          >
            <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>

        <!-- Footer Modal - Zoom Controls, Info Point, dan Estimasi -->
        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-90 z-20 max-h-[60vh] overflow-y-auto">
          <!-- Zoom Controls -->
          <div class="p-2 md:p-3 border-b border-gray-700 sticky top-0 bg-black bg-opacity-90">
            <div class="flex items-center justify-between">
              <!-- Image Counter -->
              <div class="text-xs md:text-sm font-medium text-gray-300">
                Gambar {{ currentImageIndex + 1 }} / {{ allImages.length }}
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
            <!-- Nama Point dan Status dalam satu baris -->
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

            <!-- Tombol Estimasi Perbaikan (Hanya Mobile) -->
            <div v-if="canEditEstimations && hasBadStatusForCurrentImage" class="mt-2 pt-2 border-t border-gray-700">
              <button
                @click="openEstimationModalForCurrentImage"
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

      <!-- Repair Estimation Modal -->
      <RepairEstimationModal
        :showModal="showEstimationModal"
        :estimationData="selectedEstimationData"
        :inspectionId="inspection.id"
        :encryptedIds="encryptedIds"
        @close="closeEstimationModal"
        @saved="handleEstimationSaved"
      />

      <!-- Scroll to Top/Bottom Buttons -->
      <div v-if="showScrollButtons" class="fixed bottom-4 md:bottom-6 right-4 md:right-6 flex flex-col gap-2 z-40">
        <!-- Scroll to Top Button - Show when scrolling up -->
        <button
          v-if="scrollDirection === 'up'"
          @click="scrollToTop"
          class="bg-blue-600 hover:bg-blue-700 text-white p-2 md:p-3 rounded-full shadow-lg transition-all duration-300 transform hover:scale-110"
          title="Scroll ke atas"
        >
          <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
          </svg>
        </button>

        <!-- Scroll to Bottom Button - Show when scrolling down -->
        <button
          v-if="scrollDirection === 'down'"
          @click="scrollToBottom"
          class="bg-green-600 hover:bg-green-700 text-white p-2 md:p-3 rounded-full shadow-lg transition-all duration-300 transform hover:scale-110"
          title="Scroll ke bawah"
        >
          <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V4" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Sidebar Gambar untuk Desktop -->
    <div v-if="isDesktopSidebarVisible && selectedImage" class="hidden lg:block lg:col-span-5 bg-white rounded-lg shadow-md p-4 overflow-y-auto h-[calc(100vh-2rem)] sticky top-4">
      <!-- Header Sidebar -->
      <div class="mb-4 pb-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-bold text-gray-800">Review Gambar</h3>
          <button
            @click="closeDesktopSidebar"
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
          ref="desktopImageContainer"
          class="relative bg-gray-100 rounded-lg overflow-hidden"
          @mousedown="startDrag"
          @mousemove="doDrag"
          @mouseup="endDrag"
          @mouseleave="endDrag"
          @wheel="handleWheelZoom"
        >
          <img
            ref="desktopImageElement"
            :src="currentImageUrl"
            alt="Gambar Preview"
            class="w-full h-auto max-h-80 object-contain cursor-grab active:cursor-grabbing"
            :style="{
              transform: `scale(${zoomLevel}) translate(${dragOffset.x}px, ${dragOffset.y}px) rotate(${rotationAngle}deg)`,
              transition: isDragging ? 'none' : 'transform 0.2s ease',
              transformOrigin: 'center center'
            }"
            @load="handleDesktopImageLoad"
          >

          <!-- Navigation Buttons -->
          <div class="absolute inset-x-0 top-1/2 transform -translate-y-1/2 flex justify-between px-2">
            <button
              @click="showPreviousImage"
              :disabled="!hasPreviousImage"
              class="bg-black bg-opacity-50 text-white rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-70 transition-all disabled:opacity-30 disabled:cursor-not-allowed"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <button
              @click="showNextImage"
              :disabled="!hasNextImage"
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

          <!-- Rotation Controls for Desktop -->
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

            <!-- Estimasi Perbaikan untuk Desktop -->
            <div v-if="canEditEstimations && hasBadStatusForCurrentImage" class="pt-3 border-t border-gray-200">
              <button
                @click="openEstimationModalForCurrentImage"
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
          Gambar {{ currentImageIndex + 1 }} dari {{ allImages.length }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from "axios"
import RepairEstimationSection from '@/Components/InspectionFormLocal/RepairEstimationSection.vue'
import RepairEstimationModal from '@/Components/InspectionFormLocal/RepairEstimationModal.vue'

const props = defineProps({
  inspection: {
    type: Object,
    required: true
  },
  menu_points: {
    type: Array,
    default: () => []
  },
  coverImage: {
    type: Object,
    default: null
  },
  encryptedIds: Object,
  repairEstimations: {
    type: Array,
    default: () => []
  },
  totalRepairCost: {
    type: Number,
    default: 0
  }
});

const page = usePage()
const showConfirmationModal = ref(false)
const isLoading = ref(false)
const repairEstimations = ref(props.repairEstimations)
const totalRepairCost = ref(props.totalRepairCost)

// Repair estimation modal state
const showEstimationModal = ref(false)
const selectedEstimationData = ref(null)

// Scroll buttons state
const showScrollButtons = ref(false)
const scrollDirection = ref('')
const lastScrollY = ref(0)
const idleTimer = ref(null)

// Image viewer state
const showImageModal = ref(false)
const currentImageUrl = ref('')
const currentImageIndex = ref(0)
const allImages = ref([])
const zoomLevel = ref(1)

// Desktop sidebar state
const selectedImage = ref(null)
const isDesktopSidebarVisible = ref(false)

// Check if desktop view
const isDesktop = ref(window.innerWidth >= 1024)

// Struktur data untuk informasi gambar
const currentImageInfo = ref({
  pointName: '',
  componentName: '',
  status: '',
  note: '',
  relatedInfo: null
})

// Draggable image state
const imageContainer = ref(null)
const imageElement = ref(null)
const desktopImageContainer = ref(null)
const desktopImageElement = ref(null)
const isDragging = ref(false)
const dragStart = ref({ x: 0, y: 0 })
const dragOffset = ref({ x: 0, y: 0 })
const imageDimensions = ref({ width: 0, height: 0 })

// Rotation state
const rotationAngle = ref(0)

// Pinch zoom state for mobile
const isPinching = ref(false)
const initialDistance = ref(0)
const initialZoom = ref(1)

// Role checking
const userRoles = computed(() => page.props.global?.has_roles || [])
const canViewRepairEstimation = computed(() => {
  return userRoles.value.includes('quality_control') || userRoles.value.includes('qc')
})
const canEditEstimations = computed(() => {
  return (userRoles.value.includes('quality_control') || userRoles.value.includes('qc')) && inspection.value.status === 'pending_review'
})
const canApproveReport = computed(() => {
  return (userRoles.value.includes('quality_control') || userRoles.value.includes('qc')) && inspection.value.status === 'pending_review'
})

// Check if current image has bad status
const hasBadStatusForCurrentImage = computed(() => {
  const currentImageData = allImages.value[currentImageIndex.value]
  if (!currentImageData?.pointData) return false
  
  return hasBadStatus(currentImageData.pointData)
})

// Reactive data from props
const inspection = ref(props.inspection)
const menuPoints = ref(props.menu_points)
const coverImage = ref(props.coverImage)

// Polling API detail
const fetchInspection = async () => {
  if (inspection.value.status === 'in_progress') {
    try {
      const response = await axios.get(route("inspections.detail", props.encryptedIds))
      inspection.value = response.data.inspection
      menuPoints.value = response.data.menu_points
      coverImage.value = response.data.coverImage
      repairEstimations.value = response.data.repairEstimations || []
      totalRepairCost.value = response.data.totalRepairCost || 0
    } catch (err) {
      console.error("Polling gagal:", err)
    }
  }
}

// Update isDesktop on resize
const updateIsDesktop = () => {
  isDesktop.value = window.innerWidth >= 1024
}

onMounted(() => {
  setInterval(fetchInspection, 5000)

  // Scroll event listener for showing/hiding scroll buttons
  const handleScroll = () => {
    const currentScrollY = window.scrollY

    // Detect scroll direction
    if (currentScrollY > lastScrollY.value) {
      scrollDirection.value = 'down'
    } else if (currentScrollY < lastScrollY.value) {
      scrollDirection.value = 'up'
    }

    lastScrollY.value = currentScrollY

    // Show buttons only if scrolled past threshold and during scroll activity
    if (currentScrollY > 200) {
      showScrollButtons.value = true

      // Clear existing timer
      if (idleTimer.value) {
        clearTimeout(idleTimer.value)
      }

      // Set timer to hide buttons after 2 seconds of inactivity
      idleTimer.value = setTimeout(() => {
        showScrollButtons.value = false
      }, 2000)
    } else {
      showScrollButtons.value = false
    }
  }

  window.addEventListener('scroll', handleScroll)
  window.addEventListener('resize', updateIsDesktop)
  window.addEventListener('keydown', handleKeydown)

  // Cleanup on unmount
  return () => {
    window.removeEventListener('scroll', handleScroll)
    window.removeEventListener('resize', updateIsDesktop)
    window.removeEventListener('keydown', handleKeydown)
    if (idleTimer.value) {
      clearTimeout(idleTimer.value)
    }
  }
})

// Keyboard navigation
const handleKeydown = (event) => {
  if (isDesktopSidebarVisible.value) {
    switch(event.key) {
      case 'ArrowLeft':
        event.preventDefault()
        showPreviousImage()
        break
      case 'ArrowRight':
        event.preventDefault()
        showNextImage()
        break
      case 'Escape':
        event.preventDefault()
        closeDesktopSidebar()
        break
      case '+':
      case '=':
        event.preventDefault()
        zoomIn()
        break
      case '-':
        event.preventDefault()
        zoomOut()
        break
      case '0':
        event.preventDefault()
        resetZoomAndPosition()
        break
    }
  } else if (showImageModal.value) {
    switch(event.key) {
      case 'ArrowLeft':
        event.preventDefault()
        showPreviousImage()
        break
      case 'ArrowRight':
        event.preventDefault()
        showNextImage()
        break
      case 'Escape':
        event.preventDefault()
        closeImageModal()
        break
      case '+':
      case '=':
        event.preventDefault()
        zoomIn()
        break
      case '-':
        event.preventDefault()
        zoomOut()
        break
      case '0':
        event.preventDefault()
        resetZoomAndPosition()
        break
    }
  }
}

const updateEstimations = (newEstimations) => {
  repairEstimations.value = newEstimations
}

const updateTotalCost = (newTotal) => {
  totalRepairCost.value = newTotal
}

// Function to open estimation modal with pre-filled part name
const openEstimationModal = (point) => {
  selectedEstimationData.value = {
    part_name: point.inspection_point?.name || '',
    repair_description: '',
    urgency: 'segera',
    status: 'perlu',
    estimated_cost: 0,
    notes: ''
  }
  showEstimationModal.value = true
}

// Function to open estimation modal for current image
const openEstimationModalForCurrentImage = () => {
  const currentImageData = allImages.value[currentImageIndex.value]
  if (currentImageData?.pointData) {
    openEstimationModal(currentImageData.pointData)
  }
}

// Function to close estimation modal
const closeEstimationModal = () => {
  showEstimationModal.value = false
  selectedEstimationData.value = null
}

// Function to handle saved estimation
const handleEstimationSaved = (estimation) => {
  repairEstimations.value.push(estimation)
  closeEstimationModal()
}

// Scroll functions
const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  })
}

const scrollToBottom = () => {
  window.scrollTo({
    top: document.documentElement.scrollHeight,
    behavior: 'smooth'
  })
}

// Image modal functions
const openImageModal = (imageUrl, pointData = null, imageIndex = 0) => {
  collectAllImages()
  
  const clickedIndex = allImages.value.findIndex(img => 
    img.url === imageUrl || 
    (img.pointData === pointData && img.imageIndex === imageIndex)
  )
  
  if (clickedIndex !== -1) {
    currentImageIndex.value = clickedIndex
    loadImageData(clickedIndex)
  } else {
    currentImageIndex.value = allImages.value.length
    allImages.value.push({
      url: imageUrl,
      pointData: pointData,
      imageIndex: imageIndex
    })
    loadImageData(currentImageIndex.value)
  }
  
  // Desktop: Show sidebar, Mobile: Show modal
  if (isDesktop.value) {
    selectedImage.value = imageUrl
    isDesktopSidebarVisible.value = true
  } else {
    showImageModal.value = true
  }
  
  zoomLevel.value = 1
  dragOffset.value = { x: 0, y: 0 }
  
  // Reset image dimensions
  imageDimensions.value = { width: 0, height: 0 }
}

// Fungsi untuk mengumpulkan semua gambar dari halaman
const collectAllImages = () => {
  allImages.value = []
  
  // 1. Gambar cover utama
  if (coverImage.value && coverImage.value.image_path) {
    allImages.value.push({
      url: getImageUrl(coverImage.value.image_path),
      type: 'cover',
      pointName: 'Foto Utama Kendaraan',
      componentName: 'Cover',
      pointData: { type: 'cover', name: 'Foto Utama Kendaraan' }
    })
  }
  
  // 2. Gambar dari setiap point inspeksi
  props.menu_points.forEach(point => {
    const images = point.inspection_point?.images || []
    const componentName = point.inspection_point?.component?.name || 'Tanpa Komponen'
    const pointName = point.inspection_point?.name || 'Unknown'
    const statusArray = getStatusArray(point)
    const status = statusArray.join(', ') || ''
    const note = point.inspection_point?.results?.[0]?.note || ''
    
    images.forEach((img, imgIndex) => {
      if (img.image_path) {
        allImages.value.push({
          url: getImageUrl(img.image_path),
          pointData: point,
          imageIndex: imgIndex,
          pointName: pointName,
          componentName: componentName,
          status: status,
          note: note
        })
      }
    })
  })
}

// Fungsi untuk mencari informasi terkait berdasarkan nama point
const findRelatedInfo = (pointName, pointData) => {
  if (!pointData || !props.menu_points) return null

  const relatedInfo = {}

  // Jika point adalah "No Fisik Mesin", cari "No Mesin"
  if (pointName === 'No Fisik Mesin') {
    const mesinPoint = props.menu_points.find(p =>
      p.inspection_point?.name === 'No Mesin'
    )
    if (mesinPoint && hasResult(mesinPoint)) {
      relatedInfo.mesin = {
        name: 'No Mesin',
        value: mesinPoint.inspection_point.results[0].note || '-'
      }
    }
  }

  // Jika point adalah "No Fisik Rangka", cari "No Rangka"
  if (pointName === 'No Fisik Rangka') {
    const rangkaPoint = props.menu_points.find(p =>
      p.inspection_point?.name === 'No Rangka'
    )
    if (rangkaPoint && hasResult(rangkaPoint)) {
      relatedInfo.rangka = {
        name: 'No Rangka',
        value: rangkaPoint.inspection_point.results[0].note || '-'
      }
    }
  }

  // Jika point adalah "STNK", tampilkan No Mesin, No Rangka, Pajak Tahunan, dan Pajak 5 Tahunan
  if (pointName === 'STNK') {
    const mesinPoint = props.menu_points.find(p =>
      p.inspection_point?.name === 'No Mesin'
    )
    const rangkaPoint = props.menu_points.find(p =>
      p.inspection_point?.name === 'No Rangka'
    )
    const pajakTahunanPoint = props.menu_points.find(p =>
      p.inspection_point?.name === 'Pajak Tahunan' || p.inspection_point?.name?.includes('Pajak Tahunan')
    )
    const pajak5TahunanPoint = props.menu_points.find(p =>
      p.inspection_point?.name === 'Pajak 5 Tahunan' || p.inspection_point?.name?.includes('Pajak 5 Tahunan')
    )

    if (mesinPoint && hasResult(mesinPoint)) {
      relatedInfo.mesin = {
        name: 'No Mesin',
        value: mesinPoint.inspection_point.results[0].note || '-'
      }
    }

    if (rangkaPoint && hasResult(rangkaPoint)) {
      relatedInfo.rangka = {
        name: 'No Rangka',
        value: rangkaPoint.inspection_point.results[0].note || '-'
      }
    }

    if (pajakTahunanPoint && hasResult(pajakTahunanPoint)) {
      relatedInfo.pajakTahunan = {
        name: 'Pajak Tahunan',
        value: pajakTahunanPoint.inspection_point.results[0].note || '-'
      }
    }

    if (pajak5TahunanPoint && hasResult(pajak5TahunanPoint)) {
      relatedInfo.pajak5Tahunan = {
        name: 'Pajak 5 Tahunan',
        value: pajak5TahunanPoint.inspection_point.results[0].note || '-'
      }
    }
  }

  // Jika point adalah "BPKB Asli", tampilkan No Mesin dan No Rangka
  if (pointName === 'BPKB Asli') {
    const mesinPoint = props.menu_points.find(p =>
      p.inspection_point?.name === 'No Mesin'
    )
    const rangkaPoint = props.menu_points.find(p =>
      p.inspection_point?.name === 'No Rangka'
    )

    if (mesinPoint && hasResult(mesinPoint)) {
      relatedInfo.mesin = {
        name: 'No Mesin',
        value: mesinPoint.inspection_point.results[0].note || '-'
      }
    }

    if (rangkaPoint && hasResult(rangkaPoint)) {
      relatedInfo.rangka = {
        name: 'No Rangka',
        value: rangkaPoint.inspection_point.results[0].note || '-'
      }
    }
  }

  // Jika point adalah "Samsat Online", tampilkan Pajak Tahunan dan Pajak 5 Tahunan
  if (pointName === 'Samsat Online') {
    const pajakTahunanPoint = props.menu_points.find(p =>
      p.inspection_point?.name === 'Pajak Tahunan' || p.inspection_point?.name?.includes('Pajak Tahunan')
    )
    const pajak5TahunanPoint = props.menu_points.find(p =>
      p.inspection_point?.name === 'Pajak 5 Tahunan' || p.inspection_point?.name?.includes('Pajak 5 Tahunan')
    )

    if (pajakTahunanPoint && hasResult(pajakTahunanPoint)) {
      relatedInfo.pajakTahunan = {
        name: 'Pajak Tahunan',
        value: pajakTahunanPoint.inspection_point.results[0].note || '-'
      }
    }

    if (pajak5TahunanPoint && hasResult(pajak5TahunanPoint)) {
      relatedInfo.pajak5Tahunan = {
        name: 'Pajak 5 Tahunan',
        value: pajak5TahunanPoint.inspection_point.results[0].note || '-'
      }
    }
  }

  return Object.keys(relatedInfo).length > 0 ? relatedInfo : null
}

// Fungsi untuk memuat data gambar berdasarkan index
const loadImageData = (index) => {
  if (index < 0 || index >= allImages.value.length) return

  const imageData = allImages.value[index]
  currentImageUrl.value = imageData.url

  // Set informasi gambar
  currentImageInfo.value = {
    pointName: imageData.pointName || 'Gambar Inspeksi',
    componentName: imageData.componentName || '',
    status: imageData.status || '',
    note: imageData.note || '',
    relatedInfo: findRelatedInfo(imageData.pointName, imageData.pointData)
  }
}

const closeImageModal = () => {
  showImageModal.value = false
  currentImageUrl.value = ''
  currentImageIndex.value = 0
  allImages.value = []
  zoomLevel.value = 1
  dragOffset.value = { x: 0, y: 0 }
}

const closeDesktopSidebar = () => {
  isDesktopSidebarVisible.value = false
  selectedImage.value = null
  currentImageUrl.value = ''
  currentImageIndex.value = 0
  allImages.value = []
  zoomLevel.value = 1
  dragOffset.value = { x: 0, y: 0 }
}

// Navigation functions
const hasPreviousImage = computed(() => currentImageIndex.value > 0)
const hasNextImage = computed(() => currentImageIndex.value < allImages.value.length - 1)

const showPreviousImage = () => {
  if (hasPreviousImage.value) {
    currentImageIndex.value--
    loadImageData(currentImageIndex.value)
    zoomLevel.value = 1
    dragOffset.value = { x: 0, y: 0 }
    rotationAngle.value = 0
  }
}

const showNextImage = () => {
  if (hasNextImage.value) {
    currentImageIndex.value++
    loadImageData(currentImageIndex.value)
    zoomLevel.value = 1
    dragOffset.value = { x: 0, y: 0 }
    rotationAngle.value = 0
  }
}

// Zoom functions
const zoomIn = () => {
  if (zoomLevel.value < 3) {
    zoomLevel.value = Math.min(zoomLevel.value + 0.25, 3)
  }
}

const zoomOut = () => {
  if (zoomLevel.value > 0.5) {
    zoomLevel.value = Math.max(zoomLevel.value - 0.25, 0.5)
  }
}

const resetZoomAndPosition = () => {
  zoomLevel.value = 1
  dragOffset.value = { x: 0, y: 0 }
  rotationAngle.value = 0
}

// Rotation functions
const rotateLeft = () => {
  rotationAngle.value = (rotationAngle.value - 90) % 360
}

const rotateRight = () => {
  rotationAngle.value = (rotationAngle.value + 90) % 360
}

// Handle mouse wheel zoom for desktop
const handleWheelZoom = (event) => {
  event.preventDefault()
  const delta = event.deltaY > 0 ? -0.1 : 0.1
  zoomLevel.value = Math.max(0.5, Math.min(3, zoomLevel.value + delta))
}

// Draggable functions (for both mobile modal and desktop sidebar)
const startDrag = (event) => {
  if (zoomLevel.value <= 1) return // Only allow dragging when zoomed in

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

  // Calculate boundaries based on zoom level
  // Use appropriate container based on which view is active
  const containerRect = (isDesktopSidebarVisible.value ? desktopImageContainer.value : imageContainer.value)?.getBoundingClientRect()
  if (!containerRect) return

  const scaledWidth = imageDimensions.value.width * zoomLevel.value
  const scaledHeight = imageDimensions.value.height * zoomLevel.value

  const maxX = Math.max(0, (scaledWidth - containerRect.width) / 2)
  const maxY = Math.max(0, (scaledHeight - containerRect.height) / 2)

  // Limit drag within boundaries
  dragOffset.value = {
    x: Math.max(-maxX, Math.min(maxX, newX)),
    y: Math.max(-maxY, Math.min(maxY, newY))
  }

  event.preventDefault()
}

const endDrag = () => {
  isDragging.value = false
}

// Pinch zoom functions for mobile
const getTouchDistance = (touch1, touch2) => {
  const dx = touch1.clientX - touch2.clientX
  const dy = touch1.clientY - touch2.clientY
  return Math.sqrt(dx * dx + dy * dy)
}

const handleTouchStart = (event) => {
  if (event.touches.length === 2) {
    // Pinch gesture started
    event.preventDefault()
    isPinching.value = true
    const touch1 = event.touches[0]
    const touch2 = event.touches[1]
    initialDistance.value = getTouchDistance(touch1, touch2)
    initialZoom.value = zoomLevel.value
  } else if (event.touches.length === 1) {
    // Single touch - start dragging
    startDrag(event)
  }
}

const handleTouchMove = (event) => {
  if (event.touches.length === 2 && isPinching.value) {
    // Pinch gesture in progress
    event.preventDefault()
    const touch1 = event.touches[0]
    const touch2 = event.touches[1]
    const currentDistance = getTouchDistance(touch1, touch2)

    if (initialDistance.value > 0) {
      const scale = currentDistance / initialDistance.value
      const newZoom = Math.max(0.5, Math.min(3, initialZoom.value * scale))
      zoomLevel.value = newZoom
    }
  } else if (event.touches.length === 1 && isDragging.value) {
    // Single touch drag
    doDrag(event)
  }
}

const handleTouchEnd = (event) => {
  if (event.touches.length === 0) {
    // All touches ended
    isPinching.value = false
    endDrag()
  } else if (event.touches.length === 1 && isPinching.value) {
    // Pinch ended, now single touch - start dragging
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

// Handle desktop image load
const handleDesktopImageLoad = () => {
  nextTick(() => {
    if (desktopImageElement.value) {
      const img = desktopImageElement.value
      imageDimensions.value = {
        width: img.naturalWidth,
        height: img.naturalHeight
      }
    }
  })
}

const groupedPoints = computed(() => {
  if (!menuPoints.value) return []

  const map = new Map()
  menuPoints.value.forEach(point => {
    const comp = point.inspection_point?.component
    const compId = comp?.id || 0
    if (!map.has(compId)) {
      map.set(compId, {
        component: comp || { id: 0, name: 'Tanpa Komponen', order: 9999 },
        points: []
      })
    }
    map.get(compId).points.push(point)
  })

  const groups = Array.from(map.values()).sort((a, b) => {
    if (a.component.order === b.component.order) {
      return new Date(a.component.created_at) - new Date(b.component.created_at)
    }
    return (a.component.order || 9999) - (b.component.order || 9999)
  })

  groups.forEach(group => {
    group.points.sort((a, b) => {
      if (a.order === b.order) {
        return new Date(a.created_at) - new Date(b.created_at)
      }
      return (a.order || 9999) - (b.order || 9999)
    })
  })

  return groups
})

const approveReport = () => {
  isLoading.value = true
  setTimeout(() => {
    window.location.href = route('inspections.download.pdf', props.encryptedIds)
    isLoading.value = false
    showConfirmationModal.value = false
  }, 2000)
}

const hasDataOrImages = computed(() => {
  const hasContent = props.menu_points.some(point => hasResult(point) || hasImage(point))
  const hasCoverImage = props.coverImage && props.coverImage.image_path
  return hasContent || hasCoverImage
})

// Helper functions
const imageExists = (imagePath) => imagePath && imagePath.length > 0
const getImageUrl = (imagePath) => `/${imagePath}`

// Mengakses data hasil dan gambar dari 'inspection_point' dengan aman
const hasResult = (point) => point.inspection_point?.results && point.inspection_point.results.length > 0
const hasImage = (point) => point.inspection_point?.images && point.inspection_point.images.length > 0
const hasNote = (point) => hasResult(point) && point.inspection_point.results[0].note

// FUNGSI BARU: Mendapatkan status dalam bentuk array (menangani string dan array)
const getStatusArray = (point) => {
  if (!hasResult(point)) return []
  
  const status = point.inspection_point?.results[0]?.status
  
  if (!status) return []
  
  if (Array.isArray(status)) {
    return status
  }
  
  if (typeof status === 'string' && status.includes(',')) {
    return status.split(',').map(s => s.trim()).filter(s => s.length > 0)
  }
  
  return [status]
}

const shouldShowStatusBadge = (point) => {
  const inputType = point.input_type || ''
  const statusArray = getStatusArray(point)
  return ['radio', 'imageTOradio'].includes(inputType) && statusArray.length > 0
}

const shouldShowNote = (point) => {
  const inputType = point.input_type || ''
  return ['text', 'number', 'date', 'textarea'].includes(inputType) && hasNote(point)
}

const shouldShowImages = (point) => {
  const inputType = point.input_type || ''
  const settings = point.settings || {}
  const result = point.inspection_point?.results[0] || {}

  if (inputType === 'image' && hasImage(point)) {
    return true
  }

  if (inputType === 'imageTOradio' && hasImage(point)) {
    return true
  }

  if (inputType === 'radio') {
    const statusArray = getStatusArray(point)

    for (const status of statusArray) {
      const selectedOption = settings.radios?.find(radio => radio.value === status) || {}
      const showImageUpload = selectedOption.settings?.show_image_upload || false

      if (showImageUpload && hasImage(point)) {
        return true
      }
    }
  }

  return false
}

const shouldShowTextarea = (point) => {
  const settings = point.settings || {}
  const result = point.inspection_point?.results[0] || {}
  const statusArray = getStatusArray(point)
  
  for (const status of statusArray) {
    const selectedOption = settings.radios?.find(radio => radio.value === status) || {}
    if (selectedOption.settings?.show_textarea) {
      return true
    }
  }
  
  return false
}

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

const hasBadStatus = (point) => {
  const statusArray = getStatusArray(point)
  return statusArray.some(status => {
    const statusStr = String(status).toLowerCase().trim()
    return !['normal', 'ada', 'baik', 'good', 'ok'].includes(statusStr)
  })
}

const formatNote = (point) => {
  const inputType = point.input_type || ''
  const result = point.inspection_point?.results[0] || {}
  const settings = point.settings || {}

  if (inputType === 'account' && result.note) {
    const cleanedNote = result.note.replace(/[^\d.-]/g, '')
    const value = parseFloat(cleanedNote)
    
    if (isNaN(value)) {
      return result.note
    }

    const symbol = settings.currency_symbol || 'Rp'
    const formatter = new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
      maximumFractionDigits: 2
    })

    const formattedValue = formatter.format(value).replace('Rp', symbol)
    return formattedValue
  }

  return result.note
}

// Computed properties for collision status
const collisionImage = computed(() => {
  if (!inspection.value.settings || inspection.value.settings.conclusion.collision !== 'yes') return '/images/icons/aman.png'
  const severity = inspection.value.settings.conclusion.collision_severity
  if (severity === 'moderate') return '/images/icons/beruntun.png'
  if (severity === 'heavy') return '/images/icons/berat.png'
  return '/images/icons/ringan.png'
})

const collisionText = computed(() => {
  if (!inspection.value.settings || inspection.value.settings.conclusion.collision !== 'yes') return 'Bebas Tabrak'
  const severity = inspection.value.settings.conclusion.collision_severity
  if (severity === 'moderate') return 'Tabrak Beruntun'
  if (severity === 'heavy') return 'Tabrak Berat'
  return 'Tabrak Ringan'
})

const collisionColor = computed(() => {
  if (!inspection.value.settings || inspection.value.settings.conclusion.collision !== 'yes') return '#28a745'
  const severity = inspection.value.settings.conclusion.collision_severity
  if (severity === 'moderate') return '#fd7e14'
  if (severity === 'heavy') return '#dc3545'
  return '#ffc107'
})

// Watch for desktop sidebar visibility changes
watch(isDesktopSidebarVisible, (newValue) => {
  if (newValue && allImages.value.length === 0) {
    collectAllImages()
    if (allImages.value.length > 0) {
      currentImageIndex.value = 0
      loadImageData(0)
    }
  }
})
</script>

<style scoped>
/* Responsive Base Styles */
* {
  font-size: 14px;
  line-height: 1.5;
}

@media (min-width: 768px) {
  * {
    font-size: 16px;
  }
}

.point-name {
  min-width: 140px;
  font-size: 14px;
}

@media (min-width: 768px) {
  .point-name {
    min-width: 180px;
    font-size: 16px;
  }
}

.point-content {
  width: calc(100% - 150px);
  font-size: 13px;
  line-height: 1.4;
}

@media (min-width: 768px) {
  .point-content {
    width: calc(100% - 190px);
    font-size: 15px;
  }
}

/* Container untuk multiple badges */
.status-badges-container {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-bottom: 6px;
}

.status-badge {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 9999px;
  font-size: 11px;
  white-space: nowrap;
  font-weight: 600;
}

@media (min-width: 768px) {
  .status-badge {
    padding: 3px 10px;
    font-size: 12px;
  }
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

/* Style untuk status di modal/sidebar */
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

.photo-component .point {
  display: none;
}

.conclusion {
  margin-top: 15px;
  padding: 12px;
  background-color: #f9f9f9;
  border-left: 3px solid #333;
  border-radius: 4px;
  font-size: 14px;
  line-height: 1.5;
}

@media (min-width: 768px) {
  .conclusion {
    font-size: 16px;
    padding: 16px;
  }
}

.textarea-note {
  margin: 4px 0;
  font-style: italic;
  color: #555;
  font-size: 12px;
}

@media (min-width: 768px) {
  .textarea-note {
    font-size: 14px;
  }
}

.car-info table {
  margin-top: 8px;
  border-collapse: collapse;
  width: 100%;
  font-size: 13px;
}

@media (min-width: 768px) {
  .car-info table {
    font-size: 15px;
  }
}

.car-info td {
  padding: 4px 6px;
  vertical-align: top;
  border: 1px solid #ddd;
}

@media (min-width: 768px) {
  .car-info td {
    padding: 6px 8px;
  }
}

.car-info td:first-child {
  width: 28%;
  font-weight: bold;
  font-size: 13px;
}

@media (min-width: 768px) {
  .car-info td:first-child {
    font-size: 15px;
    width: 25%;
  }
}

.component-title {
  font-weight: bold;
  font-size: 14px;
  margin-top: 12px;
  background-color: #f5f5f5;
  padding: 6px 10px;
  border-left: 3px solid #333;
}

@media (min-width: 768px) {
  .component-title {
    font-size: 16px;
    padding: 8px 12px;
  }
}

.point {
  margin-left: 12px;
  margin-bottom: 8px;
  padding: 6px 0;
  border-bottom: 1px solid #eee;
  font-size: 13px;
}

@media (min-width: 768px) {
  .point {
    margin-left: 16px;
    margin-bottom: 12px;
    padding: 8px 0;
    font-size: 15px;
  }
}

/* Ukuran gambar responsive */
img {
  max-width: 100%;
  height: auto;
}

/* Modal gambar fullscreen */
.fixed.inset-0.bg-black {
  background-color: rgba(0, 0, 0, 0.95) !important;
}

/* Hover effect untuk gambar kecil */
img.cursor-pointer:hover {
  opacity: 0.9;
  transform: scale(1.02);
  transition: all 0.2s ease;
}

/* Animasi untuk modal */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}

/* Draggable image cursor */
.cursor-grab {
  cursor: grab;
}

.cursor-grabbing {
  cursor: grabbing;
}

.touch-pan-y {
  touch-action: pan-y;
}

/* Responsive scroll buttons */
@media (max-width: 767px) {
  .fixed.bottom-4.right-4 {
    bottom: 16px;
    right: 16px;
  }
  
  .fixed.bottom-4.right-4 button {
    width: 40px;
    height: 40px;
  }
}

/* Desktop sidebar styles */
@media (min-width: 1024px) {
  .main-container {
    display: flex;
    gap: 1rem;
  }
  
  .desktop-sidebar {
    width: 24rem;
    flex-shrink: 0;
  }
}
</style>
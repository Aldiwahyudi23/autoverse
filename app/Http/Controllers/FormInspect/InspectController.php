<?php

namespace App\Http\Controllers\FormInspect;

use App\Http\Controllers\Controller;
use App\Models\DataInspection\Categorie;
use App\Models\DataInspection\AppMenu;
use App\Models\DataInspection\MenuPoint;
use App\Models\DataInspection\Inspection;
use App\Models\DataInspection\InspectionResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class InspectController extends Controller
{
    /**
     * Mulai inspeksi - Buka halaman form dengan Inertia
     */
    public function start($inspectionId)
    {
        try {
            // 1. Ambil data inspeksi berdasarkan ID
             // Dekripsi dan validasi
            $id = Crypt::decrypt($inspectionId);
            
            $inspection = Inspection::with(['category'])->findOrFail($id);
            
            // 2. Ambil category dari inspeksi
            $categoryId = $inspection->category_id;
            
            // 3. Ambil AppMenu aktif untuk category ini (diurutkan)
            $appMenus = AppMenu::where('category_id', $categoryId)
                ->where('is_active', true)
                ->ordered()
                ->get(['id', 'name', 'input_type', 'order']);

            if ($appMenus->isEmpty()) {
                return Inertia::render('Error', [
                    'message' => 'Tidak ada menu yang tersedia untuk kategori ini'
                ])->with('status', 404);
            }

            // 4. Preload all menu points and existing data for all menus
            $allMenuIds = $appMenus->pluck('id');
            $allMenuPoints = MenuPoint::whereIn('app_menu_id', $allMenuIds)
                ->where('is_active', true)
                ->with(['inspection_point', 'app_menu'])
                ->ordered()
                ->get();

            // Cache all existing data for this inspection in one query
            $allPointIds = $allMenuPoints->pluck('id');
            $allExistingData = InspectionResult::where('inspection_id', $id)
                ->whereIn('point_id', $allPointIds)
                ->get()
                ->keyBy('point_id');

            // Cache damage statuses for all damage menus
            $damageMenus = $appMenus->where('input_type', 'damage');
            $damageStatuses = [];
            if ($damageMenus->isNotEmpty()) {
                $damageMenuIds = $damageMenus->pluck('id');
                $damageStatuses = MenuPoint::whereIn('app_menu_id', $damageMenuIds)
                    ->whereHas('results', function($query) use ($id) {
                        $query->where('inspection_id', $id);
                    })
                    ->pluck('app_menu_id')
                    ->unique()
                    ->toArray();
            }

            // 5. Ambil MENU PERTAMA untuk ditampilkan di awal
            $firstMenu = $appMenus->first();

            // 6. Ambil MenuPoint untuk menu pertama dengan data yang sudah diisi (using cached data)
            $menuPoints = $this->getMenuPointsWithDataCached($firstMenu->id, $id, $allMenuPoints, $allExistingData);

            // 7. Siapkan semua menu untuk navigation (format untuk Vue) with cached damage status
            $allMenus = $appMenus->map(function($menu) use ($damageStatuses) {
                return [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'type' => $menu->input_type,
                    'order' => $menu->order,
                    // Untuk damage, cek apakah sudah ada data (using cached data)
                    'has_data' => $menu->input_type === 'damage'
                        ? in_array($menu->id, $damageStatuses)
                        : true
                ];
            });

            // 8. Preload all menu data for client-side caching
            $preloadedMenuData = [];
            foreach ($appMenus as $menu) {
                $points = $this->getMenuPointsWithDataCached($menu->id, $id, $allMenuPoints, $allExistingData);
                $preloadedMenuData[$menu->id] = [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'type' => $menu->input_type,
                    'order' => $menu->order,
                    'points' => $points
                ];
            }

            // dd($allMenus);
            
            // 7. Return Inertia page dengan props
            return Inertia::render('FormInspection/Start', [
                'inspection' => [
                    'id' => $inspection->id,
                    'name' => $inspection->name,
                    'category' => [
                        'id' => $inspection->category->id,
                        'name' => $inspection->category->name
                    ]
                ],
                'currentMenu' => [
                    'id' => $firstMenu->id,
                    'name' => $firstMenu->name,
                    'type' => $firstMenu->input_type,
                    'order' => $firstMenu->order
                ],
                'points' => $menuPoints,
                'allMenus' => $allMenus,
                'uiConfig' => [
                    'showDamageAsButton' => true,
                    'autoSave' => true,
                    'realTimeValidation' => true,
                    'debounceDelay' => 300, // ms
                    'saveOnMenuChange' => true
                ],
                'initialData' => [
                    'currentMenuIndex' => 0,
                    'totalMenus' => $allMenus->count(),
                    'isLoading' => false,
                    'validationErrors' => []
                ]
            ]);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memulai inspeksi: ' . $e->getMessage());
        }
    }
    
    /**
     * API untuk Inertia: Get MenuPoints untuk AppMenu tertentu
     * Dipanggil via axios di Vue component saat pindah menu
     */
    public function getMenuData(Request $request, $inspectionId, $menuId)
    {
        try {
            // Validasi request
            $request->validate([
                'current_values' => 'nullable|array' // Data yang belum tersimpan
            ]);
            
            // Validasi inspection
            $inspection = Inspection::findOrFail($inspectionId);
            
            // Validasi menu
            $menu = AppMenu::findOrFail($menuId);
            
            // Pastikan menu sesuai dengan category inspection
            if ($menu->category_id != $inspection->category_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Menu tidak sesuai dengan kategori inspeksi'
                ], 400);
            }
            
            // Ambil MenuPoints
            $points = $this->getMenuPointsWithData($menuId, $inspectionId, $request->current_values ?? []);
            
            // Ambil semua menu untuk navigation
            $allMenus = AppMenu::where('category_id', $inspection->category_id)
                ->where('is_active', true)
                ->ordered()
                ->get(['id', 'name', 'input_type', 'order']);
            
            // Cari index menu saat ini
            $currentIndex = $allMenus->search(function($item) use ($menuId) {
                return $item->id == $menuId;
            });
            
            // Tentukan prev dan next
            $prevMenu = $currentIndex > 0 ? $allMenus->get($currentIndex - 1) : null;
            $nextMenu = $currentIndex < ($allMenus->count() - 1) ? $allMenus->get($currentIndex + 1) : null;
            
            return response()->json([
                'success' => true,
                'menu' => [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'type' => $menu->input_type,
                    'order' => $menu->order
                ],
                'points' => $points,
                'navigation' => [
                    'prevId' => $prevMenu ? $prevMenu->id : null,
                    'nextId' => $nextMenu ? $nextMenu->id : null,
                    'currentIndex' => $currentIndex,
                    'totalMenus' => $allMenus->count()
                ],
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * API untuk validasi real-time
     */
    public function validateField(Request $request, $pointId)
    {
        try {
            $request->validate([
                'value' => 'nullable',
                'is_partial' => 'boolean', // true = masih typing
                'inspection_id' => 'required|exists:inspections,id'
            ]);
            
            $point = MenuPoint::with(['app_menu'])->findOrFail($pointId);
            $value = $request->value;
            $isPartial = $request->boolean('is_partial', true);
            
            // Skip validation jika value kosong dan masih typing
            if ($isPartial && ($value === '' || $value === null)) {
                return response()->json([
                    'valid' => true,
                    'is_partial' => true,
                    'message' => null
                ]);
            }
            
            // Generate validation rules
            $rules = $this->generateValidationRules($point);
            
            // Validasi
            $validator = Validator::make(['value' => $value], [
                'value' => $rules
            ]);
            
            $isValid = !$validator->fails();
            $errors = $isValid ? [] : $validator->errors()->all();
            
            // Cek triggers jika validasi lolos
            $triggers = [];
            if ($isValid && isset($point->settings['triggers'])) {
                $triggers = $this->checkTriggers($point, $value, $request->inspection_id);
            }
            
            return response()->json([
                'valid' => $isValid,
                'errors' => $errors,
                'triggers' => $triggers,
                'is_partial' => $isPartial,
                'point_id' => $pointId
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'errors' => ['System error: ' . $e->getMessage()]
            ], 500);
        }
    }
    
    /**
     * Save data point (auto-save atau manual save)
     */
    public function savePoint(Request $request, $inspectionId)
    {
        try {
            $request->validate([
                'point_id' => 'required|exists:menu_points,id',
                'value' => 'nullable',
                'notes' => 'nullable|string',
                'images' => 'nullable|array',
                'images.*' => 'image|max:2048'
            ]);
            
            $point = MenuPoint::findOrFail($request->point_id);
            
            // Validasi sebelum save
            $rules = $this->generateValidationRules($point);
            $validator = Validator::make(['value' => $request->value], [
                'value' => $rules
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()->all()
                ], 422);
            }
            
            // Save atau update data
            $result = InspectionResult::updateOrCreate(
                [
                    'inspection_id' => $inspectionId,
                    'point_id' => $request->point_id
                ],
                [
                    'value' => $request->value,
                    'notes' => $request->notes,
                    'inspector_id' => Auth::id() // jika ada auth
                ]
            );
            
            // Handle image upload jika diperlukan
            if ($request->hasFile('images') && $point->show_image_upload) {
                $this->handleImageUpload($result, $request->file('images'));
            }
            
            // Cek triggers setelah save
            $triggers = $this->checkTriggers($point, $request->value, $inspectionId);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $result->id,
                    'value' => $result->value,
                    'saved_at' => $result->updated_at->toISOString()
                ],
                'triggers' => $triggers,
                'message' => 'Data berhasil disimpan'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Helper: Ambil MenuPoints dengan data yang sudah diisi
     */
    private function getMenuPointsWithData($menuId, $inspectionId, $unsavedValues = [])
    {
        // Ambil MenuPoints aktif untuk menu tertentu (diurutkan)
        $menuPoints = MenuPoint::where('app_menu_id', $menuId)
            ->where('is_active', true)
            ->with(['inspection_point', 'app_menu'])
            ->ordered()
            ->get();

        // Eager load all existing data for this menu's points in one query
        $pointIds = $menuPoints->pluck('id');
        $existingDataMap = InspectionResult::where('inspection_id', $inspectionId)
            ->whereIn('point_id', $pointIds)
            ->get()
            ->keyBy('point_id')
            ->map(function($result) {
                return [
                    'id' => $result->id,
                    'value' => $result->value,
                    'notes' => $result->notes,
                    'created_at' => $result->created_at->toISOString(),
                    'updated_at' => $result->updated_at->toISOString()
                ];
            });

        // Map data untuk frontend
        return $menuPoints->map(function($point) use ($inspectionId, $unsavedValues, $existingDataMap) {
            // Ambil data yang sudah diisi (dari database)
            $existingData = $existingDataMap->get($point->id);

            // Prioritize unsaved values jika ada (data yang belum disimpan)
            $currentValue = $unsavedValues[$point->id] ?? $existingData['value'] ?? null;

            // Generate UI config
            $uiConfig = $this->generateUIConfig($point);

            // Cek visibility
            $isVisible = $this->shouldPointBeVisible($point, $existingData, $inspectionId);

            return [
                'id' => $point->id,
                'order' => $point->order,
                'inspectionPoint' => [
                    'id' => $point->inspection_point->id,
                    'name' => $point->inspection_point->name,
                    'description' => $point->inspection_point->description,
                    'notes' => $point->inspection_point->notes
                ],
                'inputType' => $point->input_type,
                'settings' => $point->settings ?? [],
                'uiConfig' => $uiConfig,
                'validationRules' => $this->generateValidationRules($point),
                'currentValue' => $currentValue,
                'existingDataId' => $existingData['id'] ?? null,
                'isVisible' => $isVisible,
                'isRequired' => $point->settings['is_required'] ?? false,
                'hasExistingDamage' => !empty($existingData),
                'showTextarea' => $point->getShowTextareaAttribute(),
                'showImageUpload' => $point->getShowImageUploadAttribute()
            ];
        })->filter(function($point) {
            // Filter hanya yang visible
            return $point['isVisible'];
        })->values();
    }
    
    /**
     * Helper: Cek apakah damage menu sudah ada data
     */
    private function hasDamageData($menuId, $inspectionId)
    {
        return MenuPoint::where('app_menu_id', $menuId)
            ->whereHas('results', function($query) use ($inspectionId) {
                $query->where('inspection_id', $inspectionId);
            })
            ->exists();
    }
    
    /**
     * Helper: Ambil data yang sudah diisi
     */
    private function getExistingPointData($pointId, $inspectionId)
    {
        $result = InspectionResult::where([
            'point_id' => $pointId,
            'inspection_id' => $inspectionId
        ])->first();
        
        if ($result) {
            return [
                'id' => $result->id,
                'value' => $result->value,
                'notes' => $result->notes,
                'created_at' => $result->created_at->toISOString(),
                'updated_at' => $result->updated_at->toISOString()
            ];
        }
        
        return null;
    }
    
    /**
     * Helper: Generate UI config
     */
    private function generateUIConfig($point)
    {
        $settings = $point->settings ?? [];
        
        return [
            'component' => $this->getComponentType($point->input_type),
            'disabled' => $settings['disabled'] ?? false,
            'placeholder' => $settings['placeholder'] ?? 'Masukkan nilai...',
            'options' => $settings['options'] ?? [],
            'min' => $settings['min'] ?? null,
            'max' => $settings['max'] ?? null,
            'step' => $settings['step'] ?? 1,
            'rows' => $settings['rows'] ?? 3, // untuk textarea
            'showNotes' => $settings['show_notes'] ?? false,
            'notePlaceholder' => $settings['note_placeholder'] ?? 'Tambahkan catatan...'
        ];
    }
    
    /**
     * Helper: Get component type untuk Vue
     */
    private function getComponentType($inputType)
    {
        $components = [
            'text' => 'TextInput',
            'number' => 'NumberInput',
            'select' => 'SelectInput',
            'radio' => 'RadioGroup',
            'checkbox' => 'CheckboxGroup',
            'textarea' => 'TextareaInput',
            'date' => 'DatePicker',
            'time' => 'TimePicker',
            'datetime' => 'DateTimePicker'
        ];
        
        return $components[$inputType] ?? 'TextInput';
    }
    
    /**
     * Helper: Generate validation rules
     */
    private function generateValidationRules($point)
    {
        $rules = [];
        $settings = $point->settings ?? [];
        
        if ($settings['is_required'] ?? false) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        
        switch ($point->input_type) {
            case 'number':
                $rules[] = 'numeric';
                if (isset($settings['min'])) $rules[] = 'min:' . $settings['min'];
                if (isset($settings['max'])) $rules[] = 'max:' . $settings['max'];
                break;
            case 'text':
                if (isset($settings['min_length'])) $rules[] = 'min:' . $settings['min_length'];
                if (isset($settings['max_length'])) $rules[] = 'max:' . $settings['max_length'];
                if (isset($settings['pattern'])) $rules[] = 'regex:' . $settings['pattern'];
                break;
            case 'select':
            case 'radio':
                if (isset($settings['options'])) {
                    $rules[] = 'in:' . implode(',', $settings['options']);
                }
                break;
            case 'email':
                $rules[] = 'email';
                break;
            case 'date':
                $rules[] = 'date';
                break;
        }
        
        return implode('|', $rules);
    }
    
    /**
     * Helper: Cek visibility point
     */
    private function shouldPointBeVisible($point, $existingData, $inspectionId)
    {
        $menuType = $point->app_menu->input_type ?? 'menu';
        
        // Untuk damage: hanya tampilkan jika sudah ada data
        if ($menuType === 'damage') {
            return !empty($existingData);
        }
        
        // Untuk menu: cek settings
        $settings = $point->settings ?? [];
        
        // Jika ada visibility condition
        if (isset($settings['visibility_condition'])) {
            return $this->evaluateCondition($settings['visibility_condition'], $inspectionId);
        }
        
        return $settings['is_visible'] ?? true;
    }
    
    /**
     * Helper: Check triggers
     */
    private function checkTriggers($point, $value, $inspectionId)
    {
        $triggers = [];
        $settings = $point->settings ?? [];
        
        if (isset($settings['triggers'])) {
            foreach ($settings['triggers'] as $trigger) {
                $conditionMet = $this->evaluateTriggerCondition($trigger['condition'], $value);
                
                if ($conditionMet) {
                    $triggers[] = [
                        'action' => $trigger['action'],
                        'target' => $trigger['target'],
                        'message' => $trigger['message'] ?? null
                    ];
                }
            }
        }
        
        return $triggers;
    }
    
    /**
     * Helper: Evaluate condition
     */
    private function evaluateCondition($condition, $inspectionId)
    {
        // Implementasi evaluasi kondisi
        // Contoh sederhana
        return true;
    }
    
    /**
     * Helper: Evaluate trigger condition
     */
    private function evaluateTriggerCondition($condition, $value)
    {
        // Contoh: ">50" atau "==OK"
        if (str_starts_with($condition, '>')) {
            $threshold = (float) substr($condition, 1);
            return (float) $value > $threshold;
        }
        
        if (str_starts_with($condition, '<')) {
            $threshold = (float) substr($condition, 1);
            return (float) $value < $threshold;
        }
        
        if (str_starts_with($condition, '==')) {
            $expected = substr($condition, 2);
            return $value == $expected;
        }
        
        return false;
    }
    
    /**
     * Helper: Handle image upload
     */
    private function handleImageUpload($result, $images)
    {
        foreach ($images as $image) {
            $path = $image->store('inspections/images', 'public');
            
            $result->images()->create([
                'path' => $path,
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'size' => $image->getSize()
            ]);
        }
    }
}
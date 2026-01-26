<?php

namespace App\Services\Inspection;

use App\Models\DataInspection\AppMenu;
use App\Models\DataInspection\Inspection;
use App\Models\DataInspection\InspectionImage;
use App\Models\DataInspection\InspectionResult;
use App\Models\DataInspection\MenuPoint;
use App\Services\Inspection\CompatibilityService;
use App\Services\Inspection\TriggerService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InspectionDataService
{
    protected $compatibilityService;
    protected $triggerService;

    public function __construct(
        CompatibilityService $compatibilityService,
        TriggerService $triggerService
    ) {
        $this->compatibilityService = $compatibilityService;
        $this->triggerService = $triggerService;
    }

    /**
     * Prepare app menus with all backend processing
     */
    public function prepareAppMenus($inspectionId, $categoryId, $car)
    {
        $cacheKey = "inspection_{$inspectionId}_menus";
        
        return Cache::remember($cacheKey, 300, function () use ($inspectionId, $categoryId, $car) {
            // Get raw menus with points
            $appMenus = AppMenu::with([
                'menu_point' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('order')
                        ->with([
                            'inspection_point.component',
                            'inspection_point' => function ($query) {
                                $query->where('is_active', true);
                            }
                        ]);
                }
            ])
            ->where('is_active', true)
            ->where('category_id', $categoryId)
            ->where('input_type', '!=', 'damage')
            ->orderBy('order')
            ->get();

            // Process each menu
            return $appMenus->map(function ($menu) use ($inspectionId, $car) {
                return $this->processMenu($menu, $inspectionId, $car);
            })->toArray();
        });
    }

    /**
     * Prepare damage points
     */
    public function prepareDamagePoints($inspectionId, $categoryId, $car)
    {
        $cacheKey = "inspection_{$inspectionId}_damage_points";
        
        return Cache::remember($cacheKey, 300, function () use ($inspectionId, $categoryId, $car) {
            $damagePoints = MenuPoint::with([
                'inspection_point',
                'inspection_point.component',
                'app_menu'
            ])
            ->where('is_active', true)
            ->whereHas('app_menu', function ($query) use ($categoryId) {
                $query->where('input_type', 'damage')
                    ->where('is_active', true)
                    ->where('category_id', $categoryId);
            })
            ->orderBy('order')
            ->get();

            return $damagePoints->map(function ($point) use ($inspectionId, $car) {
                return $this->processPoint($point, $inspectionId, $car, true);
            })->toArray();
        });
    }

    /**
     * Get existing data (results & images)
     */
    public function getExistingData($inspectionId, $menus, $damagePoints)
    {
        // Collect all point IDs
        $allPointIds = collect($menus)
            ->flatMap(function ($menu) {
                return collect($menu['points'] ?? [])->pluck('inspection_point_id');
            })
            ->merge(collect($damagePoints)->pluck('inspection_point_id'))
            ->unique()
            ->values();

        // Get results
        $results = InspectionResult::where('inspection_id', $inspectionId)
            ->whereIn('point_id', $allPointIds)
            ->get()
            ->keyBy('point_id')
            ->map(function ($result) {
                return [
                    'status' => $result->status,
                    'note' => $result->note,
                    'created_at' => $result->created_at,
                    'updated_at' => $result->updated_at,
                ];
            })
            ->toArray();

        // Get images
        $images = InspectionImage::where('inspection_id', $inspectionId)
            ->whereIn('point_id', $allPointIds)
            ->get()
            ->groupBy('point_id')
            ->map(function ($pointImages) {
                return $pointImages->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'path' => $image->image_path,
                        'preview' => Storage::url($image->image_path),
                        'file_name' => $image->file_name,
                        'file_size' => $image->file_size,
                        'created_at' => $image->created_at,
                    ];
                })->toArray();
            })
            ->toArray();

        return [
            'results' => $results,
            'images' => $images,
        ];
    }

    /**
     * Get updated state after point change
     */
    public function getUpdatedState($inspectionId, $pointId, $car)
    {
        // Get the specific point
        $menuPoint = MenuPoint::with(['inspection_point', 'app_menu'])
            ->whereHas('inspection_point', function ($query) use ($pointId) {
                $query->where('id', $pointId);
            })
            ->first();

        if (!$menuPoint) {
            return [];
        }

        // Check if it's damage menu
        $isDamage = $menuPoint->app_menu->input_type === 'damage';

        // Process the point
        $processedPoint = $this->processPoint($menuPoint, $inspectionId, $car, $isDamage);

        // Get any triggered points
        $triggeredPoints = $this->triggerService->getAffectedPoints($inspectionId, $pointId);

        return [
            'updated_point' => $processedPoint,
            'triggered_points' => $triggeredPoints,
            'completion_changes' => $this->getCompletionChanges($inspectionId, $menuPoint->app_menu_id),
        ];
    }

    /**
     * Search damage points
     */
    public function searchDamagePoints($inspectionId, $categoryId, $car, $query, $limit)
    {
        $points = MenuPoint::with(['inspection_point', 'inspection_point.component', 'app_menu'])
            ->where('is_active', true)
            ->whereHas('app_menu', function ($q) use ($categoryId) {
                $q->where('input_type', 'damage')
                    ->where('is_active', true)
                    ->where('category_id', $categoryId);
            })
            ->whereHas('inspection_point', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%")
                    ->orWhereHas('component', function ($cq) use ($query) {
                        $cq->where('name', 'LIKE', "%{$query}%");
                    });
            })
            ->limit($limit)
            ->get();

        return $points->map(function ($point) use ($inspectionId, $car) {
            return $this->processPoint($point, $inspectionId, $car, true);
        })->toArray();
    }

    /**
     * Get category data for lazy loading
     */
    public function getCategoryData($inspectionId, $categoryId, $car)
    {
        // For regular categories
        if (is_numeric($categoryId)) {
            $menu = AppMenu::with([
                'menu_point' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('order')
                        ->with([
                            'inspection_point.component',
                            'inspection_point'
                        ]);
                }
            ])
            ->where('id', $categoryId)
            ->where('is_active', true)
            ->firstOrFail();

            return $this->processMenu($menu, $inspectionId, $car);
        }

        // For special categories (vehicle, conclusion)
        return $this->getSpecialCategoryData($categoryId, $inspectionId, $car);
    }

    /**
     * Get visible points based on existing data
     */
    public function getVisiblePoints($existingResults)
    {
        $visiblePoints = [];

        foreach ($existingResults as $pointId => $result) {
            // Show points that have data
            if (!empty($result['status']) || !empty($result['note'])) {
                $visiblePoints[] = $pointId;
            }
        }

        return $visiblePoints;
    }

    // =========================================================================
    // PRIVATE METHODS
    // =========================================================================

    private function processMenu($menu, $inspectionId, $car)
    {
        // Get existing results for this menu's points
        $pointIds = $menu->menu_point->pluck('inspection_point_id');
        $existingResults = InspectionResult::where('inspection_id', $inspectionId)
            ->whereIn('point_id', $pointIds)
            ->get()
            ->keyBy('point_id');

        $existingImages = InspectionImage::where('inspection_id', $inspectionId)
            ->whereIn('point_id', $pointIds)
            ->get()
            ->groupBy('point_id');

        // Process points
        $processedPoints = $menu->menu_point->map(function ($point) use ($inspectionId, $car, $existingResults, $existingImages) {
            return $this->processPoint($point, $inspectionId, $car, false, $existingResults, $existingImages);
        });

        // Group points for display (Backend handles grouping!)
        $groupedPoints = $this->groupPointsForDisplay($processedPoints);

        // Check completion
        $isComplete = $this->checkMenuCompletion($processedPoints, $existingResults);

        return [
            'id' => $menu->id,
            'name' => $menu->name,
            'input_type' => $menu->input_type,
            'is_damage_menu' => $menu->input_type === 'damage',
            'is_complete' => $isComplete,
            'points' => $groupedPoints,
            'stats' => [
                'total_points' => $processedPoints->count(),
                'completed_points' => $processedPoints->where('has_data', true)->count(),
                'required_points' => $processedPoints->where('is_required', true)->count(),
                'visible_points' => $processedPoints->where('should_display', true)->count(),
            ],
        ];
    }

    private function processPoint($point, $inspectionId, $car, $isDamage = false, $existingResults = null, $existingImages = null)
    {
        if (!$existingResults) {
            $existingResults = InspectionResult::where('inspection_id', $inspectionId)
                ->where('point_id', $point->inspection_point_id)
                ->get()
                ->keyBy('point_id');
        }

        if (!$existingImages) {
            $existingImages = InspectionImage::where('inspection_id', $inspectionId)
                ->where('point_id', $point->inspection_point_id)
                ->get()
                ->groupBy('point_id');
        }

        // Check compatibility
        $isCompatible = $this->compatibilityService->checkCompatibility($point, $car);
        
        // Check if point has data
        $hasData = isset($existingResults[$point->inspection_point_id]) || 
                  isset($existingImages[$point->inspection_point_id]);
        
        // Check if point is triggered
        $isTriggered = $this->triggerService->isPointTriggered($inspectionId, $point->id);
        
        // Determine if should display (BACKEND LOGIC!)
        $shouldDisplay = $this->shouldPointDisplay(
            $point, 
            $isCompatible, 
            $hasData, 
            $isTriggered,
            $isDamage
        );

        return [
            'id' => $point->id,
            'inspection_point_id' => $point->inspection_point_id,
            'inspection_point' => [
                'id' => $point->inspection_point->id,
                'name' => $point->inspection_point->name,
                'description' => $point->inspection_point->description,
                'component' => $point->inspection_point->component ? [
                    'id' => $point->inspection_point->component->id,
                    'name' => $point->inspection_point->component->name,
                ] : null,
            ],
            'input_type' => $point->input_type,
            'is_default' => (bool) $point->is_default,
            'is_required' => (bool) $point->is_required,
            'order' => $point->order,
            'settings' => $point->settings ?: [],
            
            // Backend calculated flags
            'is_compatible' => $isCompatible,
            'has_data' => $hasData,
            'is_triggered' => $isTriggered,
            'is_visible' => $shouldDisplay,
            'should_display' => $shouldDisplay,
            'is_complete' => $hasData && $this->isPointComplete($point, $existingResults[$point->inspection_point_id] ?? null),
            
            // Existing data
            'existing_result' => $existingResults[$point->inspection_point_id] ?? null,
            'existing_images' => $existingImages[$point->inspection_point_id] ?? [],
        ];
    }

    private function shouldPointDisplay($point, $isCompatible, $hasData, $isTriggered, $isDamage)
    {
        // Damage menus always show compatible points
        if ($isDamage) {
            return $isCompatible;
        }

        // Always show if:
        // 1. Point has data
        // 2. Point is default and compatible
        // 3. Point is triggered and compatible
        if ($hasData) {
            return true;
        }

        if ($point->is_default && $isCompatible) {
            return true;
        }

        if ($isTriggered && $isCompatible) {
            return true;
        }

        return false;
    }

    private function groupPointsForDisplay($points)
    {
        $displayPoints = [];
        $hiddenGroup = [];
        
        foreach ($points as $point) {
            if ($point['should_display']) {
                // Jika ada hidden group, tambahkan sebagai link
                if (!empty($hiddenGroup)) {
                    $displayPoints[] = [
                        'is_link' => true,
                        'count' => count($hiddenGroup),
                        'hidden_ids' => array_column($hiddenGroup, 'id'),
                    ];
                    $hiddenGroup = [];
                }
                
                $displayPoints[] = $point;
            } else {
                $hiddenGroup[] = $point;
            }
        }
        
        // Tambahkan hidden group terakhir jika ada
        if (!empty($hiddenGroup)) {
            $displayPoints[] = [
                'is_link' => true,
                'count' => count($hiddenGroup),
                'hidden_ids' => array_column($hiddenGroup, 'id'),
            ];
        }
        
        return $displayPoints;
    }

    private function checkMenuCompletion($points, $existingResults)
    {
        $requiredPoints = $points->where('is_required', true);
        
        if ($requiredPoints->isEmpty()) {
            return $points->where('has_data', true)->count() > 0;
        }
        
        foreach ($requiredPoints as $point) {
            if (!$point['has_data']) {
                return false;
            }
            
            $result = $existingResults[$point['inspection_point_id']] ?? null;
            if (!$this->isPointComplete($point, $result)) {
                return false;
            }
        }
        
        return true;
    }

    private function isPointComplete($point, $result)
    {
        if (!$result) {
            return false;
        }
        
        // Check based on input type
        switch ($point->input_type) {
            case 'radio':
            case 'imageTOradio':
            case 'select':
                return !empty($result['status']);
            case 'text':
            case 'textarea':
            case 'number':
            case 'account':
            case 'date':
                return !empty($result['note']);
            case 'image':
                return true; // Image upload considered complete
            default:
                return !empty($result['status']) || !empty($result['note']);
        }
    }

    private function getCompletionChanges($inspectionId, $menuId)
    {
        $menu = AppMenu::find($menuId);
        if (!$menu) {
            return [];
        }
        
        // Recalculate completion for this menu
        $points = $menu->menu_point;
        $pointIds = $points->pluck('inspection_point_id');
        
        $existingResults = InspectionResult::where('inspection_id', $inspectionId)
            ->whereIn('point_id', $pointIds)
            ->get()
            ->keyBy('point_id');
        
        $isComplete = $this->checkMenuCompletion($points, $existingResults);
        
        return [
            'menu_id' => $menuId,
            'is_complete' => $isComplete,
            'completed_points' => $points->where(function ($point) use ($existingResults) {
                return isset($existingResults[$point->inspection_point_id]);
            })->count(),
            'total_points' => $points->count(),
        ];
    }

    private function getSpecialCategoryData($categoryId, $inspectionId, $car)
    {
        switch ($categoryId) {
            case 'vehicle':
                return [
                    'id' => 'vehicle',
                    'name' => 'Detail Kendaraan',
                    'input_type' => 'vehicle',
                    'is_damage_menu' => false,
                    'is_complete' => false, // Will be calculated separately
                    'points' => [],
                    'stats' => [
                        'total_points' => 0,
                        'completed_points' => 0,
                        'required_points' => 0,
                        'visible_points' => 0,
                    ],
                    'vehicle_data' => $this->formatVehicleData($car),
                ];
                
            case 'conclusion':
                $inspection = Inspection::find($inspectionId);
                return [
                    'id' => 'conclusion',
                    'name' => 'Kesimpulan',
                    'input_type' => 'conclusion',
                    'is_damage_menu' => false,
                    'is_complete' => !empty($inspection->overall_note),
                    'points' => [],
                    'stats' => [
                        'total_points' => 0,
                        'completed_points' => 0,
                        'required_points' => 0,
                        'visible_points' => 0,
                    ],
                    'conclusion_data' => is_string($inspection->settings['conclusion'] ?? null) ? json_decode($inspection->settings['conclusion'], true) : ($inspection->settings['conclusion'] ?? []),
                ];
                
            default:
                return [];
        }
    }

    private function formatVehicleData($car)
    {
        if (!$car) return null;
        
        return [
            'plate_number' => $car->plate_number,
            'brand_id' => $car->brand_id,
            'model_id' => $car->model_id,
            'type_id' => $car->type_id,
            'transmission' => $car->transmission,
            'fuel_type' => $car->fuel_type,
            'year' => $car->year,
            'color' => $car->color,
            'has_rear_door' => $car->has_rear_door,
            'is_pick_up' => $car->is_pick_up,
            'has_box' => $car->has_box,
            'brand' => $car->brand ? $car->brand->only(['id', 'name']) : null,
            'model' => $car->model ? $car->model->only(['id', 'name']) : null,
            'type' => $car->type ? $car->type->only(['id', 'name']) : null,
        ];
    }
}
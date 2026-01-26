<?php

namespace App\Services\Inspection;

use App\Models\DataInspection\AppMenu;
use App\Models\DataInspection\Inspection;
use App\Models\DataInspection\InspectionResult;
use App\Models\DataInspection\MenuPoint;

class CompletionService
{
    /**
     * Calculate overall completion
     */
    public function calculateOverallCompletion($inspection, $menus, $existingResults)
    {
        // Check vehicle completion
        $vehicleComplete = $this->checkVehicleCompletion($inspection);
        
        // Check regular menus
        $regularMenusComplete = true;
        $completedMenus = 0;
        
        foreach ($menus as $menu) {
            if (!$menu['is_complete']) {
                $regularMenusComplete = false;
            } else {
                $completedMenus++;
            }
        }
        
        // Check damage points
        $damageComplete = $this->checkDamageCompletion($inspection->id);
        
        // Check conclusion
        $conclusionComplete = !empty($inspection->overall_note);
        
        return [
            'vehicle' => $vehicleComplete,
            'regular_menus' => $regularMenusComplete,
            'damage' => $damageComplete,
            'conclusion' => $conclusionComplete,
            'overall' => $vehicleComplete && $regularMenusComplete && $damageComplete && $conclusionComplete,
            'stats' => [
                'total_menus' => count($menus),
                'completed_menus' => $completedMenus,
                'completion_percentage' => $this->calculatePercentage($inspection->id),
            ],
        ];
    }

    /**
     * Update completion after point change
     */
    public function updateCompletion($inspectionId, $pointId)
    {
        // Recalculate menu completion that contains this point
        $menu = AppMenu::whereHas('menu_point', function ($query) use ($pointId) {
            $query->where('inspection_point_id', $pointId);
        })->first();

        if ($menu) {
            $isComplete = $this->checkMenuCompletion($inspectionId, $menu->id);
            
            // You might want to store this in cache or database
            return [
                'menu_id' => $menu->id,
                'is_complete' => $isComplete,
            ];
        }
        
        return null;
    }

    /**
     * Validate final submission
     */
    public function validateFinalSubmission($inspectionId)
    {
        $inspection = Inspection::findOrFail($inspectionId);
        
        $completion = $this->calculateOverallCompletion(
            $inspection,
            $this->getAllMenus($inspection->category_id),
            $this->getAllResults($inspectionId)
        );
        
        return $completion['overall'];
    }

    /**
     * Get progress details
     */
    public function getProgressDetails($inspectionId)
    {
        $inspection = Inspection::findOrFail($inspectionId);
        
        $menus = $this->getAllMenus($inspection->category_id);
        $results = $this->getAllResults($inspectionId);
        
        $completion = $this->calculateOverallCompletion($inspection, $menus, $results);
        
        // Get detailed completion per menu
        $menuDetails = [];
        foreach ($menus as $menu) {
            $menuDetails[] = [
                'id' => $menu['id'],
                'name' => $menu['name'],
                'is_complete' => $menu['is_complete'],
                'completed_points' => $menu['stats']['completed_points'],
                'total_points' => $menu['stats']['total_points'],
            ];
        }
        
        return [
            'overall' => $completion,
            'menu_details' => $menuDetails,
            'last_updated' => $inspection->updated_at,
        ];
    }

    /**
     * Check vehicle completion
     */
    public function checkVehicleCompletion(Inspection $inspection)
    {
        // Check if vehicle details are filled
        return !empty($inspection->plate_number) && 
               !empty($inspection->car_id) &&
               !empty($inspection->car_name);
    }

    /**
     * Check damage completion
     */
    public function checkDamageCompletion($inspectionId)
    {
        // Get all damage points for this inspection
        $damagePoints = MenuPoint::whereHas('app_menu', function ($query) use ($inspectionId) {
            $query->where('input_type', 'damage');
        })->count();

        if ($damagePoints === 0) {
            return true; // No damage points, considered complete
        }

        // Count completed damage points
        $completedDamage = InspectionResult::where('inspection_id', $inspectionId)
            ->whereHas('point.menu_point.app_menu', function ($query) {
                $query->where('input_type', 'damage');
            })
            ->count();

        return $completedDamage >= $damagePoints;
    }

    // =========================================================================
    // PRIVATE METHODS
    // =========================================================================

    private function checkMenuCompletion($inspectionId, $menuId)
    {
        $menu = AppMenu::with(['menu_point'])->findOrFail($menuId);
        
        $requiredPoints = $menu->menu_point->where('is_required', true);
        
        if ($requiredPoints->isEmpty()) {
            // If no required points, check if any point has data
            $hasData = InspectionResult::where('inspection_id', $inspectionId)
                ->whereIn('point_id', $menu->menu_point->pluck('inspection_point_id'))
                ->exists();
                
            return $hasData;
        }
        
        // Check all required points
        foreach ($requiredPoints as $point) {
            $result = InspectionResult::where('inspection_id', $inspectionId)
                ->where('point_id', $point->inspection_point_id)
                ->first();
                
            if (!$result || !$this->isPointResultValid($point, $result)) {
                return false;
            }
        }
        
        return true;
    }

    private function isPointResultValid($point, $result)
    {
        switch ($point->input_type) {
            case 'radio':
            case 'imageTOradio':
            case 'select':
                return !empty($result->status);
            case 'text':
            case 'textarea':
            case 'number':
            case 'account':
            case 'date':
                return !empty($result->note);
            case 'image':
                return true; // Image considered valid
            default:
                return !empty($result->status) || !empty($result->note);
        }
    }

    private function calculatePercentage($inspectionId)
    {
        $inspection = Inspection::findOrFail($inspectionId);
        $menus = $this->getAllMenus($inspection->category_id);
        
        if (empty($menus)) {
            return 0;
        }
        
        $completed = 0;
        foreach ($menus as $menu) {
            if ($menu['is_complete']) {
                $completed++;
            }
        }
        
        $vehicleComplete = $this->checkVehicleCompletion($inspection) ? 1 : 0;
        $damageComplete = $this->checkDamageCompletion($inspectionId) ? 1 : 0;
        $conclusionComplete = !empty($inspection->overall_note) ? 1 : 0;
        
        $totalItems = count($menus) + 3; // +3 for vehicle, damage, conclusion
        $completedItems = $completed + $vehicleComplete + $damageComplete + $conclusionComplete;
        
        return round(($completedItems / $totalItems) * 100);
    }

    private function getAllMenus($categoryId)
    {
        // This would fetch and process all menus
        // Implement based on your DataService
        return [];
    }

    private function getAllResults($inspectionId)
    {
        return InspectionResult::where('inspection_id', $inspectionId)
            ->get()
            ->keyBy('point_id')
            ->toArray();
    }
}
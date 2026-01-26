<?php

namespace App\Services\Inspection;

use App\Models\DataInspection\InspectionResult;
use App\Models\DataInspection\MenuPoint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TriggerService
{
    /**
     * Process triggers after point update
     */
    public function processTriggers($inspectionId, $pointId, $value)
    {
        // Get the point
        $menuPoint = MenuPoint::with(['inspection_point'])
            ->whereHas('inspection_point', function ($query) use ($pointId) {
                $query->where('id', $pointId);
            })
            ->first();

        if (!$menuPoint || empty($menuPoint->settings)) {
            return [];
        }

        $settings = $menuPoint->settings;

        // Get triggered points based on value
        $triggeredPoints = $this->getTriggeredPointsByValue($settings, $value);

        // Clear cache to recalculate triggers
        Cache::forget("inspection_{$inspectionId}_triggers");

        return $triggeredPoints;
    }

    /**
     * Get current triggered points
     */
    public function getCurrentTriggers($inspectionId)
    {
        $cacheKey = "inspection_{$inspectionId}_triggers";

        return Cache::remember($cacheKey, 300, function () use ($inspectionId) {
            $triggeredPoints = [];

            // Get all inspection results for this inspection
            $results = InspectionResult::where('inspection_id', $inspectionId)
                ->get()
                ->keyBy('point_id');

            // Get all menu points with settings
            $menuPoints = MenuPoint::whereNotNull('settings')
                ->where('settings', '!=', '')
                ->get();

            // Check radio-based triggers (target_point_id)
            foreach ($results as $pointId => $result) {
                $menuPoint = $menuPoints->firstWhere('inspection_point_id', $pointId);
                if (!$menuPoint || !$menuPoint->settings) {
                    continue;
                }

                $settings = $menuPoint->settings;
                if (empty($settings['radios'])) {
                    continue;
                }

                foreach ($settings['radios'] as $radio) {
                    if (($radio['value'] === $result->status ||
                        (is_array($result->status) && in_array($radio['value'], $result->status))) &&
                        !empty($radio['settings']['show_trigger'])) {

                        $targetPoints = $radio['settings']['target_point_id'] ?? [];
                        $triggeredPoints = array_merge($triggeredPoints, $targetPoints);
                    }
                }
            }

            // Check is_triggered with parent_point_id
            foreach ($menuPoints as $menuPoint) {
                $settings = $menuPoint->settings;
                if (empty($settings['is_triggered']) || empty($settings['parent_point_id'])) {
                    continue;
                }

                $parentPoints = is_array($settings['parent_point_id'])
                    ? $settings['parent_point_id']
                    : [$settings['parent_point_id']];

                // Check if any parent has data
                $hasParentData = false;
                foreach ($parentPoints as $parentPointId) {
                    if (isset($results[$parentPointId])) {
                        $hasParentData = true;
                        break;
                    }
                }

                if ($hasParentData) {
                    $triggeredPoints[] = $menuPoint->id;
                }
            }

            return array_unique($triggeredPoints);
        });
    }

    /**
     * Check if point is triggered
     */
    public function isPointTriggered($inspectionId, $pointId)
    {
        $triggers = $this->getCurrentTriggers($inspectionId);
        return in_array($pointId, $triggers);
    }

    /**
     * Get affected points after trigger
     */
    public function getAffectedPoints($inspectionId, $pointId)
    {
        // This would check which points are affected by this point's trigger
        // For now, return empty - implement based on your business logic
        return [];
    }

    // =========================================================================
    // PRIVATE METHODS
    // =========================================================================

    private function getTriggeredPointsByValue($settings, $value)
    {
        if (empty($settings['radios'])) {
            return [];
        }

        $triggeredPoints = [];
        
        foreach ($settings['radios'] as $radio) {
            if (($radio['value'] === $value || 
                (is_array($value) && in_array($radio['value'], $value))) &&
                !empty($radio['settings']['show_trigger'])) {
                
                $targetPoints = $radio['settings']['target_point_id'] ?? [];
                $triggeredPoints = array_merge($triggeredPoints, $targetPoints);
            }
        }

        return array_unique($triggeredPoints);
    }


}
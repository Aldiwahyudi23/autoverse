<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;

use App\Models\Team\Region;
use App\Models\Team\RegionTeam;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    /**
     * Get active regions with information about active teams
     */
    public function getActiveRegionsWithTeams()
    {
        try {
            $regions = Region::where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(function ($region) {
                    // Check if region has active teams
                    $hasActiveTeam = RegionTeam::where('region_id', $region->id)
                        ->where('status', 'active')
                        ->exists();

                    return [
                        'id' => $region->id,
                        'name' => $region->name,
                        'has_active_team' => $hasActiveTeam,
                    ];
                });

            return response()->json([
                'success' => true,
                'regions' => $regions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch regions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Services\Inspection;


class CompatibilityService
{
    /**
     * Check if point is compatible with vehicle
     */
    public function checkCompatibility($point, $car)
    {
        if (!$car) {
            return true; // Jika tidak ada data kendaraan, tampilkan semua
        }

        $settings = is_string($point->settings) ? json_decode($point->settings, true) : ($point->settings ?: []);
        
        // Check transmission
        if (!empty($settings['transmission'])) {
            if (!in_array($car->transmission, $settings['transmission'])) {
                return false;
            }
        }
        
        // Check fuel type
        if (!empty($settings['fuel_type'])) {
            if ($car->fuel_type !== $settings['fuel_type']) {
                return false;
            }
        }
        
        // Check rear door
        if (isset($settings['rear_door']) && $settings['rear_door']) {
            if (!$car->has_rear_door) {
                return false;
            }
        }
        
        // Check pick up
        if (isset($settings['pick_up']) && $settings['pick_up']) {
            if (!$car->is_pick_up) {
                return false;
            }
        }
        
        // Check box
        if (isset($settings['box']) && $settings['box']) {
            if (!$car->has_box) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get compatibility info for display
     */
    public function getCompatibilityInfo($point, $car)
    {
        $settings = is_string($point->settings) ? json_decode($point->settings, true) : ($point->settings ?: []);
        $info = [];
        
        if (!empty($settings['transmission'])) {
            $info[] = 'Transmisi: ' . implode(', ', $settings['transmission']);
        }
        
        if (!empty($settings['fuel_type'])) {
            $info[] = 'Bahan bakar: ' . $settings['fuel_type'];
        }
        
        if (!empty($settings['rear_door'])) {
            $info[] = 'Kendaraan dengan pintu belakang';
        }
        
        if (!empty($settings['pick_up'])) {
            $info[] = 'Tipe pick up';
        }
        
        if (!empty($settings['box'])) {
            $info[] = 'Kendaraan dengan box';
        }
        
        return $info;
    }
}
<?php

namespace App\Models\Estimasi;

use App\Models\DataInspection\Inspection;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RepairEstimations extends Model
{
    protected $fillable = [
        'inspection_id',
        'part_name',
        'repair_description',
        'urgency',
        'status',
        'estimated_cost',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
    ];

    /**
     * Relasi ke inspeksi
     */
    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    /**
     * Relasi ke user yang membuat
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope untuk filter urgensi
     */
    public function scopeUrgent($query)
    {
        return $query->where('urgency', 'segera');
    }

    public function scopeLongTerm($query)
    {
        return $query->where('urgency', 'jangka_panjang');
    }

    /**
     * Get formatted cost
     */
    public function getFormattedCostAttribute(): string
    {
        return number_format($this->estimated_cost, 0, ',', '.');
    }

    /**
     * Get urgency badge color
     */
    public function getUrgencyColorAttribute(): string
    {
        return match($this->urgency) {
            'segera' => 'danger',
            'jangka_panjang' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'perlu' => 'danger',
            'disarankan' => 'warning',
            'opsional' => 'info',
            default => 'secondary',
        };
    }
}
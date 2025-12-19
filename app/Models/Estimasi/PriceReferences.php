<?php

namespace App\Models\Estimasi;

use Illuminate\Database\Eloquent\Model;

class PriceReferences extends Model

{
    protected $fillable = [
        'category_id',
        'part_category',
        'part_name',
        'repair_type',
        'min_price',
        'max_price',
        'average_price',
        'unit',
        'currency',
        'description',
        'brand',
        'model',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'min_price' => 'decimal:2',
        'max_price' => 'decimal:2',
        'average_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke kategori
     */
    // public function category()
    // {
    //     return $this->belongsTo(Category::class);
    // }

    /**
     * Scope untuk data aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk pencarian
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('part_name', 'like', "%{$search}%")
              ->orWhere('part_category', 'like', "%{$search}%")
              ->orWhere('repair_type', 'like', "%{$search}%");
        });
    }

    /**
     * Get price range
     */
    public function getPriceRangeAttribute(): string
    {
        $min = number_format($this->min_price, 0, ',', '.');
        $max = number_format($this->max_price, 0, ',', '.');
        return "Rp {$min} - Rp {$max}";
    }

    /**
     * Get average price formatted
     */
    public function getAveragePriceFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->average_price, 0, ',', '.');
    }
}
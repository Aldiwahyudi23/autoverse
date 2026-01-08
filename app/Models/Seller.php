<?php

namespace App\Models;

use App\Models\DataInspection\Inspection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seller extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'inspection_id', // Tambahkan ini
        'inspection_area',
        'inspection_address',
        'link_maps',
        'unit_holder_name',
        'unit_holder_phone',
        'settings',
        'status',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Tambahkan relasi inspection
    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DataCar\CarDetail;
use App\Models\DataInspection\Inspection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class VehicleApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

        /**
     * API untuk validasi nomor polisi
     * Endpoint: POST /api/vehicle/validate-plate
     */
    public function validatePlateNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plate_number' => 'required|string|max:20',
            'current_inspection_id' => 'nullable|integer', // Untuk exclude current inspection
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $plateNumber = strtoupper(trim($request->plate_number));
        $currentInspectionId = $request->current_inspection_id;
        
        // Cek apakah ada inspeksi dengan nomor polisi yang sama
        $query = Inspection::where('plate_number', $plateNumber)
            ->whereIn('status', ['in_progress', 'pending', 'draft', 'revision'])
            ->where('user_id', Auth::id());
            
        if ($currentInspectionId) {
            $query->where('id', '!=', $currentInspectionId);
        }
        
        $existingInspection = $query->first();
        
        if ($existingInspection) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor polisi sudah digunakan dalam inspeksi lain',
                'data' => [
                    'exists' => true,
                    'inspection' => [
                        'id' => $existingInspection->id,
                        'plate_number' => $existingInspection->plate_number,
                        'status' => $existingInspection->status,
                        'car_name' => $existingInspection->car_name,
                        'created_at' => $existingInspection->created_at->format('Y-m-d H:i:s'),
                    ]
                ]
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Nomor polisi tersedia',
            'data' => ['exists' => false]
        ]);
    }
    
    /**
     * API untuk search car details
     * Endpoint: GET /api/vehicle/search-cars
     */
    public function searchCars(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $search = $request->search;
        $limit = $request->limit ?? 20;
        
        $query = CarDetail::with(['brand', 'model', 'type'])
            ->select(['id', 'plate_number', 'brand_id', 'model_id', 'type_id', 'transmission', 'fuel_type', 'year'])
            ->where('is_active', true);
            
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhereHas('brand', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('model', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('type', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $cars = $query->limit($limit)->get()->map(function ($car) {
            return [
                'id' => $car->id,
                'plate_number' => $car->plate_number,
                'brand' => $car->brand ? $car->brand->name : '',
                'model' => $car->model ? $car->model->name : '',
                'type' => $car->type ? $car->type->name : '',
                'transmission' => $car->transmission,
                'fuel_type' => $car->fuel_type,
                'year' => $car->year,
                'display_name' => "{$car->brand->name} {$car->model->name} {$car->type->name} ({$car->plate_number})",
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $cars,
            'meta' => [
                'total' => $cars->count(),
                'limit' => $limit,
                'has_more' => false, // Bisa implement pagination jika perlu
            ]
        ]);
    }
    
    /**
     * API untuk mendapatkan detail kendaraan spesifik
     * Endpoint: GET /api/vehicle/{id}
     */
    public function getCarDetails($id)
    {
        $car = CarDetail::with(['brand', 'model', 'type'])
            ->where('id', $id)
            ->where('is_active', true)
            ->first();
            
        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'Data kendaraan tidak ditemukan'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $car->id,
                'plate_number' => $car->plate_number,
                'brand' => $car->brand ? [
                    'id' => $car->brand->id,
                    'name' => $car->brand->name,
                ] : null,
                'model' => $car->model ? [
                    'id' => $car->model->id,
                    'name' => $car->model->name,
                ] : null,
                'type' => $car->type ? [
                    'id' => $car->type->id,
                    'name' => $car->type->name,
                ] : null,
                'transmission' => $car->transmission,
                'fuel_type' => $car->fuel_type,
                'year' => $car->year,
                'chassis_number' => $car->chassis_number,
                'engine_number' => $car->engine_number,
                'color' => $car->color,
                'created_at' => $car->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}

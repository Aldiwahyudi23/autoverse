<?php

namespace App\Http\Controllers\Inspection;

use App\Http\Controllers\Controller;
use App\Models\DataInspection\Inspection;
use App\Models\Estimasi\RepairEstimations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RepairEstimationController extends Controller
{
    public function index(Inspection $inspection)
    {
        $estimations = $inspection->repairEstimations()
            ->orderBy('urgency', 'desc')
            ->orderBy('estimated_cost', 'desc')
            ->get();
        
        return response()->json([
            'estimations' => $estimations,
            'total' => $estimations->sum('estimated_cost')
        ]);
    }

    public function store(Request $request, Inspection $inspection)
    {
        $validated = $request->validate([
            'part_name' => 'required|string|max:255',
            'repair_description' => 'required|string',
            'urgency' => 'required|in:segera,jangka_panjang',
            'status' => 'required|in:perlu,disarankan,opsional',
            'estimated_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $estimation = $inspection->repairEstimations()->create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Estimasi perbaikan berhasil ditambahkan.',
            'estimation' => $estimation
        ]);
    }

    public function update(Request $request, Inspection $inspection, RepairEstimations $repairEstimation)
    {
        $validated = $request->validate([
            'part_name' => 'required|string|max:255',
            'repair_description' => 'required|string',
            'urgency' => 'required|in:segera,jangka_panjang',
            'status' => 'required|in:perlu,disarankan,opsional',
            'estimated_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $repairEstimation->update($validated);

        return response()->json([
            'message' => 'Estimasi perbaikan berhasil diperbarui.',
            'estimation' => $repairEstimation
        ]);
    }

    public function destroy(Inspection $inspection, RepairEstimations $repairEstimation)
    {
        $repairEstimation->delete();

        return response()->json([
            'message' => 'Estimasi perbaikan berhasil dihapus.'
        ]);
    }

}

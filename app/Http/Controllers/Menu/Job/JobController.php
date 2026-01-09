<?php

namespace App\Http\Controllers\Menu\Job;

use App\Http\Controllers\Controller;
use App\Models\DataInspection\Inspection;
use App\Models\Finance\Transaction;
use App\Models\Team\RegionTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Inspection::query();
        
        // Role-based filtering
        if ($user->hasRole('inspector')) {
            // Inspector hanya melihat tugasnya sendiri
            $query->where('user_id', $user->id)
                  ->whereIn('status', ['draft', 'in_progress', 'pending', 'revision','pending_review']);
        } 
        elseif ($user->hasRole('quality_control')) {
            // QC melihat data mereka sendiri untuk diinspeksi seperti inspector, dan pending_review untuk direview
            $query->where(function($q) use ($user) {
                // Inspections assigned to QC user - can inspect like inspector
                $q->where('user_id', $user->id)
                  ->whereIn('status', ['draft', 'in_progress', 'pending', 'revision']);
            })->orWhere(function($q) use ($user) {
                // Pending review inspections for QC to review
                $q->whereIn('status', ['pending_review']);
            });
        }
        elseif ($user->hasRole('admin_plann') || $user->hasRole('coordinator') || $user->hasRole('Admin') ) {
            // Admin plant melihat semua data dengan status tertentu
            $query->whereIn('status', ['draft', 'in_progress', 'pending', 'revision','pending_review']);
        } 
        // else {
        //     // Default untuk role lain
        //     $query->whereIn('status', ['draft', 'in_progress', 'pending_review', 'pending', 'revision']);
        // }
        
        // Filter pencarian untuk admin plant dan QC
        if (($user->hasRole('admin_plann') || $user->hasRole('quality_control') || $user->hasRole('coordinator') || $user->hasRole('Admin')) && $request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhere('car_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $tasks = $query->with([
                'car',
                'car.brand',
                'car.model',
                'car.type',
                'category',
                'customer',
                'customer.sellers',
                'transaction',
                'user' // Tambahkan relasi user untuk melihat siapa inspectornya
            ])
            ->orderBy('inspection_date', 'asc')
            ->get();
        
        // Encrypt semua ID inspection
        $encryptedIds = $tasks->mapWithKeys(function($task) {
            return [$task->id => Crypt::encrypt($task->id)];
        });
        
        $team = RegionTeam::with(['user','regions'])
        ->where('status','active')
        ->get()
        ->map(function($regionTeam) {
            return [
                'id' => $regionTeam->user->id,
                'name' => $regionTeam->user->name,
                'email' => $regionTeam->user->email,
                'numberPhone' => $regionTeam->user->numberPhone,
                'region_name' => $regionTeam->regions->name,
            ];
        });

        return Inertia::render('FrontEnd/Menu/Tugas/Index', [
            'tasks' => $tasks,
            'encryptedIds' => $encryptedIds,
            'userRole' => $user->roles->first()->name,
            'userId' => $user->id,
            'team' => $team,
            'filters' => [
                'search' => $request->search ?? '',
            ],
        ]);
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        $query = Inspection::query();
        
        // Role-based filtering untuk history
        if ($user->hasRole('inspector')) {
            // Inspector hanya melihat riwayatnya sendiri
            $query->where('user_id', $user->id);
        } 
        // Role-based filtering untuk history
        elseif ($user->hasRole('admin_plann')) {
            // Inspector hanya melihat riwayatnya sendiri
            $query->where('submitted_by', $user->id);
        } 
        // QC dan Admin Plant melihat semua riwayat
        elseif ($user->hasRole('quality_control')) {
            // Tidak ada filter user_id, tampilkan semua
        } 
        else {
            // Default untuk role lain
            $query->where('user_id', $user->id);
        }
        
        // Filter status untuk history (selain yang aktif)
        $query->whereNotIn('status', ['draft', 'in_progress', 'pending_review', 'pending', 'revision']);
        
        // Filter berdasarkan bulan dan tahun
        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('created_at', $request->month)
                  ->whereYear('created_at', $request->year);
        } else {
            // Default ke bulan dan tahun saat ini
            $query->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        }
        
        // Filter pencarian
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhere('car_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $tasks = $query->with([
                'car',
                'car.brand',
                'car.model',
                'car.type',
                'category',
                'user' // Tambahkan relasi user untuk melihat inspectornya
            ])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get available months and years for filter dropdown
        $availableMonths = Inspection::when($user->hasRole('inspector'), function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->selectRaw('MONTH(inspection_date) as month, YEAR(inspection_date) as year')
            ->distinct()
            ->get()
            ->groupBy('year')
            ->map(function($yearGroup) {
                return $yearGroup->pluck('month')->unique()->sort();
            });
        
        // Encrypt semua ID inspection
        $encryptedIds = $tasks->mapWithKeys(function($task) {
            return [$task->id => Crypt::encrypt($task->id)];
        });
        
        return Inertia::render('FrontEnd/Menu/Tugas/History', [
            'tasks' => $tasks,
            'encryptedIds' => $encryptedIds,
            'filters' => [
                'month' => $request->month ?? now()->month,
                'year' => $request->year ?? now()->year,
                'search' => $request->search ?? '',
            ],
            'availableMonths' => $availableMonths,
            'currentMonth' => now()->month,
            'currentYear' => now()->year,
            'userRole' => $user->roles->first()->name,
        ]);
    }
    
    // Fungsi untuk transfer tugas (hanya admin plant)
    public function transfer(Request $request, $inspection)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('admin_plann')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $id = Crypt::decrypt($inspection);
            $inspection = Inspection::findOrFail($id);
            
            $request->validate([
                'new_inspector_id' => 'required|exists:users,id',
                'transfer_reason' => 'required|string|min:10|max:500',
            ]);
            
            // Simpan inspector lama untuk log
            $oldInspectorId = $inspection->user_id;
            $oldInspector = \App\Models\User::find($oldInspectorId);
            
            // Update inspector
            $inspection->update([
                'user_id' => $request->new_inspector_id,
            ]);
            
            // Tambahkan log transfer
            $inspection->addLog('transferred', 
                "Tugas dialihkan dari inspector: {$oldInspector->name} ke inspector ID: {$request->new_inspector_id}. Alasan: {$request->transfer_reason}",
                $user->id
            );
            
            return redirect()->back()->with('success', 'Tugas berhasil dialihkan ke inspector baru.');
            
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404, 'ID Inspeksi Tidak Valid');
        }
    }
}
<?php

namespace App\Http\Controllers\Menu\Team;

use App\Http\Controllers\Controller;
use App\Models\Team\Region;
use App\Models\Team\RegionTeam;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class TeamController extends Controller
{
    /**
     * Menampilkan halaman team dengan data dinamis berdasarkan role
     */
    public function index()
    {
        $user = Auth::user();
        
        // Ambil semua region
        $regions = Region::all();
        $allUser = User::all();
        
        // Ambil semua data team dengan relasi user dan region
        $teamMembers = RegionTeam::with(['user', 'region'])
            ->get()
            ->map(function ($team) {
                return [
                    'id' => $team->id,
                    'user' => [
                        'id' => $team->user->id,
                        'name' => $team->user->name,
                        'email' => $team->user->email,
                        'phone' => $team->user->numberPhone,
                        'profile_photo_url' => $team->user->profile_photo_url,
                        'roles' => $team->user->getRoleNames(),
                        'is_active' => $team->user->is_active,
                    ],
                    'region_id' => $team->region_id,
                    'region_name' => $team->region->name ?? 'Unknown',
                    'region_code' => $team->region->code ?? '',
                    'role' => $team->role,
                    'status' => $team->status,
                    'created_at' => $team->created_at,
                ];
            });

        // Siapkan data untuk frontend
        $allUsersData = $allUser->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'numberPhone' => $user->numberPhone,
                'is_active' => $user->is_active,
            ];
        });

        return Inertia::render('FrontEnd/Menu/Team/Index', [
            'regions' => $regions,
            'allUsers' => $allUsersData,
            'roleName' => $user->getRoleNames(),
            'teamMembers' => $teamMembers
        ]);
    }

    /**
     * Menambahkan user baru ke team
     */
    public function addUser(Request $request)
    {
        // Cek apakah user memiliki role Admin atau Coordinator
        $user = Auth::user();
        if (!$user->hasRole('Admin') && !$user->hasRole('coordinator')) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menambahkan user'
            ], 403);
        }

        // Tentukan apakah user saat ini admin atau coordinator
        $isAdmin = $user->hasRole('Admin');
        $isCoordinator = $user->hasRole('coordinator');

        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:13|unique:users,numberPhone',
            'role' => 'required|in:admin_region,inspector,quality_control,admin_plann',
            'region_id' => 'required|exists:regions,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // 🔎 Filter email & phone sudah ada atau belum
        $exists = User::where('email', $request->email)
            ->orWhere('numberPhone', $request->phone)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau nomor telepon sudah terdaftar pada pengguna lain'
            ], 409);
        }

        try {
            // Tentukan status berdasarkan role yang ditambahkan
            $status = 'active'; // Default untuk inspector
            $isActive = true; // User tidak aktif sampai disetujui
            
            // Jika role adalah coordinator atau admin_region, langsung aktifkan
            if (in_array($request->role, ['coordinator', 'admin_region','admin_plann','quality_control'])) {
                // Hanya Admin yang bisa menambahkan coordinator atau admin_region
                if (!$isAdmin && in_array($request->role, ['coordinator', 'admin_region'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Hanya Admin yang dapat menambahkan ' . $request->role
                    ], 403);
                }
                
                $status = 'active';
                $isActive = true;
            }

            // Buat user baru
            $newUser = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'numberPhone' => $request->phone,
                'password' => Hash::make('cekMobil123'), // Password default
                'is_active' => $isActive
            ]);

            // Tambahkan user ke team
            $regionTeam = RegionTeam::create([
                'user_id' => $newUser->id,
                'region_id' => $request->region_id,
                'role' => $request->role,
                'status' => $status
            ]);

            // Berikan role kepada user
            $newUser->assignRole($request->role);

            // Siapkan data response
            $teamData = [
                'id' => $regionTeam->id,
                'user' => [
                    'id' => $newUser->id,
                    'name' => $newUser->name,
                    'email' => $newUser->email,
                    'phone' => $newUser->numberPhone,
                    'profile_photo_url' => $newUser->profile_photo_url,
                    'roles' => $newUser->getRoleNames(),
                    'is_active' => $newUser->is_active,
                ],
                'region_id' => $regionTeam->region_id,
                'region_name' => Region::find($regionTeam->region_id)->name ?? 'Unknown',
                'role' => $regionTeam->role,
                'status' => $regionTeam->status,
                'created_at' => $regionTeam->created_at,
            ];

            return response()->json([
                'success' => true,
                'message' => $status === 'active' 
                    ? 'User ' . $request->role . ' berhasil ditambahkan dan aktif' 
                    : 'User berhasil ditambahkan. Status: Menunggu persetujuan admin',
                'user' => $teamData
            ]);
            

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil anggota team berdasarkan region
     */
    public function getByRegion($regionId)
    {
        $user = Auth::user();
        
        // Jika user bukan admin/coordinator, cek apakah region adalah region mereka
        if (!$user->hasRole('Admin') && !$user->hasRole('coordinator')) {
            $userRegion = RegionTeam::where('user_id', $user->id)->first();
            if ($userRegion && $userRegion->region_id != $regionId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke region ini'
                ], 403);
            }
        }

        $teamMembers = RegionTeam::with(['user', 'region'])
            ->where('region_id', $regionId)
            ->get()
            ->map(function ($team) {
                return [
                    'id' => $team->id,
                    'user' => [
                        'id' => $team->user->id,
                        'name' => $team->user->name,
                        'email' => $team->user->email,
                        'phone' => $team->user->numberPhone,
                        'profile_photo_url' => $team->user->profile_photo_url,
                        'roles' => $team->user->getRoleNames(),
                    ],
                    'region_id' => $team->region_id,
                    'region_name' => $team->region->name ?? 'Unknown',
                    'role' => $team->role,
                    'status' => $team->status,
                    'created_at' => $team->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'members' => $teamMembers,
            'count' => $teamMembers->count()
        ]);
    }

    /**
     * Menampilkan halaman setting team
     */
    public function setting_team($id)
    {
        $regionTeam = RegionTeam::with('user')
        ->where('id',$id)
        ->first();

         return inertia('FrontEnd/Menu/Team/TeamSettings', [
            'regionTeam' => $regionTeam
        ]);
    }
    /**
     * Mengupdate pengaturan team
     */
public function updateettingTeam(Request $request, $id)
    {
        $user = Auth::user();
        
        // Hanya admin dan coordinator yang bisa mengakses
        if (!$user->hasRole('Admin') && !$user->hasRole('coordinator')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'status' => 'required|in:active,inactive,paused',
            'settings.inspection_price_self' => 'required|numeric|min:0',
            'settings.inspection_price_external' => 'required|numeric|min:0',
        ]);

        try {
            $team = RegionTeam::findOrFail($id);
            $team->update([
                'status' => $request->status,
                'settings' => $request->settings
            ]);

            return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
    /**
     * Filter team members dengan berbagai kriteria (API endpoint)
     */
    public function filter(Request $request)
    {
        $user = Auth::user();
        
        // Jika user biasa, hanya bisa filter region mereka sendiri
        if (!$user->hasRole('Admin') && !$user->hasRole('coordinator')) {
            $userRegion = RegionTeam::where('user_id', $user->id)->first();
            if (!$userRegion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum tergabung dalam region'
                ], 400);
            }
            $request->merge(['region_id' => $userRegion->region_id]);
        }

        $query = RegionTeam::with(['user', 'region']);

        // Filter berdasarkan region
        if ($request->has('region_id') && $request->region_id) {
            $query->where('region_id', $request->region_id);
        }

        // Filter berdasarkan status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan role
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // Filter berdasarkan pencarian nama/email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $teamMembers = $query->get()->map(function ($team) {
            return [
                'id' => $team->id,
                'user' => [
                    'id' => $team->user->id,
                    'name' => $team->user->name,
                    'email' => $team->user->email,
                    'phone' => $team->user->numberPhone,
                    'profile_photo_url' => $team->user->profile_photo_url,
                    'roles' => $team->user->getRoleNames(),
                    'is_active' => $team->user->is_active,
                ],
                'region_id' => $team->region_id,
                'region_name' => $team->region->name ?? 'Unknown',
                'role' => $team->role,
                'status' => $team->status,
                'created_at' => $team->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'teamMembers' => $teamMembers,
            'total' => $teamMembers->count(),
            'filters' => [
                'region_id' => $request->region_id,
                'status' => $request->status,
                'role' => $request->role,
                'search' => $request->search
            ]
        ]);
    }

    /**
     * Export data team ke Excel/CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        // Hanya admin dan coordinator yang bisa export
        if (!$user->hasRole('Admin') && !$user->hasRole('coordinator')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = RegionTeam::with(['user', 'region']);

        // Terapkan filter jika ada
        if ($request->has('region_id') && $request->region_id) {
            $query->where('region_id', $request->region_id);
        }
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        $teamMembers = $query->get();

        // Format data untuk CSV
        $csvData = [];
        $csvData[] = ['Nama', 'Email', 'Nomor HP', 'Posisi', 'Status', 'Wilayah', 'Tanggal Bergabung'];

        foreach ($teamMembers as $member) {
            $csvData[] = [
                $member->user->name,
                $member->user->email,
                $member->user->numberPhone,
                $member->role,
                $member->status,
                $member->region->name ?? 'Unknown',
                $member->created_at->format('d-m-Y')
            ];
        }

        // Generate CSV content
        $output = fopen('php://output', 'w');
        foreach ($csvData as $row) {
            fputcsv($output, $row);
        }
        fclose($output);

        // Return as download
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="team_members_' . date('Y-m-d') . '.csv"',
        ];

        return response()->stream(function () use ($csvData) {
            $output = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        }, 200, $headers);
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
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
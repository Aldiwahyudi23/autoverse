<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\TransactionDistribution;
use App\Models\Finance\Withdrawal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class WithdrawalController extends Controller
{
    /**
     * Get pending distributions for withdrawal request
     */
    public function getPendingDistributions(Request $request)
    {
        try {
            $user = Auth::user();
            
            $distributions = TransactionDistribution::where('user_id', $user->id)
                ->where('is_released', false)
                ->whereNull('withdrawal_id')
                ->with(['transaction' => function($query) {
                    $query->select('id', 'transaction_code', 'transaction_date', 'customer_name');
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $distributions,
                'total_pending_amount' => $distributions->sum('amount')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data distribusi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create withdrawal request
     */
    public function createWithdrawal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'distribution_ids' => 'required|array|min:1',
            'distribution_ids.*' => 'exists:transaction_distributions,id',
            'bank_name' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            
            // Get selected distributions
            $distributions = TransactionDistribution::whereIn('id', $request->distribution_ids)
                ->where('user_id', $user->id)
                ->where('is_released', false)
                ->whereNull('withdrawal_id')
                ->get();

            if ($distributions->isEmpty()) {
                throw new \Exception('Tidak ada data distribusi yang valid untuk penarikan');
            }

            // Calculate total amount
            $totalAmount = $distributions->sum('amount');

            // Create withdrawal
            $withdrawal = Withdrawal::create([
                'user_id' => $user->id,
                'status' => Withdrawal::STATUS_PENDING,
                'total_amount' => $totalAmount,
                'payment_method' => 'transfer',
                'bank_name' => $request->bank_name,
                'account_name' => $request->account_name,
                'account_number' => $request->account_number,
                'requested_at' => now(),
            ]);

            // Update distributions with withdrawal_id
            TransactionDistribution::whereIn('id', $distributions->pluck('id'))
                ->update([
                    'withdrawal_id' => $withdrawal->id
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan penarikan berhasil dibuat',
                'data' => $withdrawal
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pengajuan penarikan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get withdrawal requests for admin
     */
    public function getWithdrawalRequests(Request $request)
    {
        $status = $request->get('status', Withdrawal::STATUS_PENDING);
        $search = $request->get('search', '');
        
        $withdrawals = Withdrawal::with(['user' => function($query) {
                $query->select('id', 'name', 'email');
            }])
            ->when($status, function($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($search, function($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('account_name', 'like', "%{$search}%")
                      ->orWhere('account_number', 'like', "%{$search}%")
                      ->orWhere('bank_name', 'like', "%{$search}%")
                      ->orWhereHas('user', function($q2) use ($search) {
                          $q2->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('requested_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $withdrawals
        ]);
    }

    /**
     * Get withdrawal detail
     */
    public function getWithdrawalDetail($id)
    {
        try {
            $withdrawal = Withdrawal::with([
                'user' => function($query) {
                    $query->select('id', 'name', 'email', 'phone');
                },
                'transactionDistributions' => function($query) {
                    $query->with(['transaction' => function($q) {
                        $q->select('id', 'transaction_code', 'transaction_date', 'customer_name');
                    }]);
                },
                'processor' => function($query) {
                    $query->select('id', 'name');
                }
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $withdrawal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data penarikan tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Process withdrawal (approve/reject)
     */
    public function processWithdrawal(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'admin_fee' => 'nullable|numeric|min:0',
            'payment_method' => 'required_if:action,approve|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string',
            'rejection_reason' => 'required_if:action,reject|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $withdrawal = Withdrawal::findOrFail($id);
            $user = Auth::user();

            if ($withdrawal->status !== Withdrawal::STATUS_PENDING) {
                throw new \Exception('Penarikan sudah diproses sebelumnya');
            }

            // Handle file upload
            $filePath = null;
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('withdrawals/proofs', $fileName, 'public');
            }

            if ($request->action === 'approve') {
                $withdrawal->admin_fee = $request->admin_fee ?? 0;
                $withdrawal->payment_method = $request->payment_method;
                $withdrawal->file_path = $filePath;
                $withdrawal->notes = $request->notes;
                $withdrawal->status = Withdrawal::STATUS_APPROVED;
                $withdrawal->processed_at = now();
                $withdrawal->processed_by = $user->id;
                
                // Calculate final amount
                $withdrawal->calculateFinalAmount();
            } else {
                $withdrawal->status = Withdrawal::STATUS_REJECTED;
                $withdrawal->rejection_reason = $request->rejection_reason;
                $withdrawal->processed_at = now();
                $withdrawal->processed_by = $user->id;
                
                // Reset withdrawal_id on distributions
                TransactionDistribution::where('withdrawal_id', $withdrawal->id)
                    ->update([
                        'withdrawal_id' => null
                    ]);
            }

            $withdrawal->save();

            DB::commit();

            $actionMessage = $request->action === 'approve' ? 'disetujui' : 'ditolak';
            
            return response()->json([
                'success' => true,
                'message' => "Penarikan berhasil {$actionMessage}",
                'data' => $withdrawal
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses penarikan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete withdrawal (mark as completed)
     */
    public function completeWithdrawal($id)
    {
        DB::beginTransaction();
        try {
            $withdrawal = Withdrawal::findOrFail($id);
            $user = Auth::user();

            if ($withdrawal->status !== Withdrawal::STATUS_APPROVED) {
                throw new \Exception('Hanya penarikan yang disetujui yang bisa diselesaikan');
            }

            $withdrawal->complete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Penarikan berhasil diselesaikan',
                'data' => $withdrawal
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan penarikan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's withdrawal history
     */
    public function getMyWithdrawals(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status', '');
        
        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->when($status, function($query) use ($status) {
                $query->where('status', $status);
            })
            ->with(['processor' => function($query) {
                $query->select('id', 'name');
            }])
            ->orderBy('requested_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $withdrawals
        ]);
    }

    /**
     * Get withdrawal statistics
     */
    public function getStatistics()
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('Admin') || $user->hasRole('coordinator');
        
        $statistics = [
            'total_pending' => Withdrawal::pending()->count(),
            'total_approved' => Withdrawal::where('status', Withdrawal::STATUS_APPROVED)->count(),
            'total_completed' => Withdrawal::completed()->count(),
            'total_rejected' => Withdrawal::where('status', Withdrawal::STATUS_REJECTED)->count(),
            'total_amount_pending' => Withdrawal::pending()->sum('total_amount'),
            'total_amount_approved' => Withdrawal::where('status', Withdrawal::STATUS_APPROVED)->sum('total_amount'),
            'total_amount_completed' => Withdrawal::completed()->sum('total_amount'),
        ];

        if (!$isAdmin) {
            $statistics = array_merge($statistics, [
                'my_pending_amount' => TransactionDistribution::where('user_id', $user->id)
                    ->where('is_released', false)
                    ->whereNull('withdrawal_id')
                    ->sum('amount'),
                'my_pending_withdrawals' => Withdrawal::where('user_id', $user->id)
                    ->pending()
                    ->count(),
                'my_total_withdrawn' => Withdrawal::where('user_id', $user->id)
                    ->whereIn('status', [Withdrawal::STATUS_COMPLETED])
                    ->sum('final_amount'),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }
}
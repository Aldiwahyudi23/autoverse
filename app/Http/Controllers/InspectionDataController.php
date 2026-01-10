<?php

namespace App\Http\Controllers;

use App\Events\InspectionApproved;
use App\Models\Customer;
use App\Models\Seller;
use App\Models\DataInspection\Inspection;
use App\Models\Finance\Transaction;
use App\Models\Finance\TransactionDistribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;

class InspectionDataController extends Controller
{
    /**
     * Simpan semua data (customer, seller, transaksi)
     */
    public function storeAllData(Request $request, $id)
    {
        try {
            $inspectionId = Crypt::decrypt($id);
            $inspection = Inspection::findOrFail($inspectionId);
            
            // Validasi
            $validated = $request->validate([
                // Data Customer
                'customer_id' => 'nullable|exists:customers,id',
                'customer_name' => 'required_if:customer_id,null|string|max:255',
                'customer_phone' => 'required_if:customer_id,null|string|max:20',
                'customer_email' => 'nullable|email|max:255',
                'customer_address' => 'nullable|string',
                
                // Data Seller
                'seller_inspection_area' => 'required|string|max:255',
                'seller_inspection_address' => 'required|string',
                'seller_link_maps' => 'nullable|url',
                'seller_unit_holder_name' => 'required|string|max:255',
                'seller_unit_holder_phone' => 'required|string|max:20',
                'seller_settings' => 'nullable|array',
                
                // Data Transaksi
                'transaction_amount' => 'required|numeric|min:0',
                'transaction_payment_method' => 'required|string|in:cash,transfer,debit_card,credit_card,qris',
                'transaction_status' => 'required|string|in:pending,paid,failed,refunded,expired',
                'transaction_payment_date' => 'nullable|date',
                'transaction_notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // 1. Handle Customer Data
            if ($request->filled('customer_id')) {
                // Gunakan customer yang sudah ada
                $customer = Customer::find($request->customer_id);
                $inspection->customer_id = $customer->id;
            } else {
                // Buat customer baru
                $customer = Customer::create([
                    'name' => $validated['customer_name'],
                    'phone' => $validated['customer_phone'],
                    'email' => $validated['customer_email'] ?? null,
                    'address' => $validated['customer_address'] ?? null,
                ]);
                
                $inspection->customer_id = $customer->id;
            }
            
            $inspection->save();

            // 2. Handle Seller Data
            $seller = Seller::updateOrCreate(
                [
                    'customer_id' => $inspection->customer_id,
                    'inspection_id' => $inspection->id,
                ],
                [
                    'inspection_area' => $validated['seller_inspection_area'],
                    'inspection_address' => $validated['seller_inspection_address'],
                    'link_maps' => $validated['seller_link_maps'] ?? null,
                    'unit_holder_name' => $validated['seller_unit_holder_name'],
                    'unit_holder_phone' => $validated['seller_unit_holder_phone'],
                    'settings' => $validated['seller_settings'] ?? [],
                    'status' => 'in_progress',
                ]
            );

            // 3. Handle Transaction Data
            $transaction = Transaction::updateOrCreate(
                ['inspection_id' => $inspection->id],
                [
                    'amount' => $validated['transaction_amount'],
                    'payment_method' => $validated['transaction_payment_method'],
                    'status' => $validated['transaction_status'],
                    'payment_date' => $validated['transaction_payment_date'] ?? ($validated['transaction_status'] === 'paid' ? now() : null),
                    'notes' => $validated['transaction_notes'] ?? null,
                    'paid_by' => $validated['transaction_status'] === 'paid' ? Auth::id() : null,
                ]
            );

        $inspection->addLog(
            'Transaction',
            'Penginputan data customer, seller, dan transaksi - Status: ' . ($validated['transaction_status'] ?? '-')
        );


        // ============================================
        // 5. ALTERNATIF: Inspection sudah approved, 
        //    tapi transaksi baru dibayar dan belum ada distribusi
        // ============================================

        $isStatusChangedToPaid = $transaction->status === 'paid';
        if ($isStatusChangedToPaid && $inspection->status === 'approved') {
            event(new InspectionApproved($inspection));
        }
            DB::commit();

            return redirect()->back()->with('success', 'Data berhasil disimpan');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors(['general' => 'Gagal menyimpan data: ' . $e->getMessage()]);
        }
    }

    /**
     * Get data customer untuk autocomplete
     */
    public function getCustomers(Request $request)
    {
        $search = $request->get('search', '');
        
        $customers = Customer::with('sellers')
            ->where('name', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->limit(10)
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'address' => $customer->address,
                    'sellers' => $customer->sellers,
                ];
            });

        return response()->json($customers);
    }

    /**
     * Get existing data untuk form
     */
    public function getExistingData($id)
    {
        try {
            $inspectionId = Crypt::decrypt($id);
            
            $inspection = Inspection::with([
                'customer.sellers',
                'customer.sellers' => function ($query) use ($inspectionId) {
                    $query->where('inspection_id', $inspectionId);
                },
                'transaction'
            ])->findOrFail($inspectionId);

            return response()->json([
                'success' => true,
                'data' => [
                    'customer' => $inspection->customer,
                    'seller' => $inspection->customer->sellers->first() ?? null,
                    'transaction' => $inspection->transaction,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
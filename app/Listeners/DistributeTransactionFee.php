<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\InspectionApproved;
use App\Models\Finance\Transaction;
use App\Models\Finance\TransactionDistribution;
use App\Models\Team\Region;
use App\Models\Team\RegionTeam;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DistributeTransactionFee
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(InspectionApproved $event)
    {
        $inspection = $event->inspection;

        $transaction = Transaction::where('inspection_id', $inspection->id)
            ->where('status', 'paid')
            ->first();

        // ❌ Tidak ada transaksi / belum dibayar
        if (!$transaction) {
            return;
        }

        $team = RegionTeam::where('user_id', $inspection->user_id)->firstOrFail();
        $teamSettings = $team->settings ?? [];
        $region = Region::find($team->region_id);

        // ======================
        // LOGIKA YANG KAMU MAU
        // ======================
        $isSelf = $inspection->submitted_by == $inspection->user_id;
        $priceKey = $isSelf
            ? 'inspection_price_self'
            : 'inspection_price_external';

        if (!isset($teamSettings[$priceKey])) {
            throw new \Exception("Setting {$priceKey} belum tersedia");
        }

        $inspectionFee = $teamSettings[$priceKey];
        $ownerAmount   = $transaction->amount - $inspectionFee;

        DB::transaction(function () use (
            $transaction,
            $inspection,
            $inspectionFee,
            $ownerAmount,
            $region,
        ) {
            // hapus distribusi lama biar aman
            TransactionDistribution::where('transaction_id', $transaction->id)->delete();

            // ======================
            // INSPECTOR
            // ======================
            TransactionDistribution::create([
                'transaction_id' => $transaction->id,
                'user_id'        => $inspection->user_id,
                'region_id'        => $region->id,
                'role_type'      => 'Fee Inspeksi',
                'amount'         => $inspectionFee,
                'percentage'       => round(($inspectionFee / $transaction->amount) * 100, 2),
                'calculation_note' => "Kalkulasi fee inspektor sesuai kesepakatan awal. Nominal yang diterima: Rp " . number_format($inspectionFee, 0, ',', '.') . " dari total pembayaran inspeksi Rp " . number_format($transaction->amount, 0, ',', '.') . ".",
                'is_released'    => false,
            ]);

            // ======================
            // OWNER
            // ======================
            TransactionDistribution::create([
                'transaction_id' => $transaction->id,
                'user_id'        => null, // owner global
                'region_id'        => null,
                'role_type'      => 'Pendapatan',
                'amount'         => $ownerAmount,
                'percentage'       => round(($ownerAmount / $transaction->amount) * 100, 2),
                'calculation_note' => "Pendapatan owner setelah dikurangi fee inspektor. Nominal yang diterima: Rp " . number_format($ownerAmount, 0, ',', '.') . " dari total pembayaran inspeksi Rp " . number_format($transaction->amount, 0, ',', '.') . ".",
                'is_released'    => true,
                'released_at'      => now(),
                'released_by'      => Auth::id(),
            ]);

            $inspection->addLog(
                'Distribusi',
                'Pembagian fee transaksi inspeksi telah dibuat ulang dan disimpan.'
            );
        });
    }
}


<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\TransactionDistribution;
use App\Models\Finance\Withdrawal;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class WithdrawalController extends Controller
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService)
    {
        $this->fonnteService = $fonnteService;
    }

    /**
     * Display the withdrawal request form
     */
    public function create()
    {
        // Hanya user biasa yang bisa mengakses
        if (Auth::user()->hasRole(['Admin', 'coordinator', 'admin_plann'])) {
            return redirect()->route('dashboard')->with('error', 'Hanya user biasa yang dapat mengajukan penarikan.');
        }

        // Ambil distribusi yang belum di-release dan belum ada withdrawal_id
        $distributions = TransactionDistribution::where('user_id', Auth::id())
            ->where('is_released', false)
            ->whereNull('withdrawal_id')
            ->with('transaction','transaction.inspection')
            ->get();

        $processing = Withdrawal::where('user_id', Auth::id())
            ->with(['user', 'processor', 'transactionDistributions.transaction'])
            ->whereIn('status', [Withdrawal::STATUS_PENDING, Withdrawal::STATUS_PROCESSING, Withdrawal::STATUS_APPROVED])
            ->orderBy('requested_at', 'desc')
            ->get();


        return Inertia::render('FrontEnd/Menu/Home/Finance/Withdrawal/Create', [
            'distributions' => $distributions,
            'processing' => $processing,
            'paymentMethods' => Withdrawal::getPaymentMethodOptions(),
        ]);
    }

    /**
     * Store withdrawal request
     */
    public function store(Request $request)
    {
        $request->validate([
            'selected_distributions' => 'required|array|min:1',
            'selected_distributions.*' => 'exists:transaction_distributions,id',
            'payment_method' => 'required|in:transfer,cash,ewallet',
            'account_number' => 'required_if:payment_method,transfer,ewallet|nullable|string|max:50',
            'account_name' => 'required_if:payment_method,transfer,ewallet|nullable|string|max:100',
            'bank_name' => 'required_if:payment_method,transfer|nullable|string|max:100',
        ]);

        DB::beginTransaction();

        try {
            // Hitung total amount dari distribusi yang dipilih
            $totalAmount = TransactionDistribution::whereIn('id', $request->selected_distributions)
                ->where('user_id', Auth::id())
                ->where('is_released', false)
                ->whereNull('withdrawal_id')
                ->sum('amount');

            if ($totalAmount <= 0) {
                throw new \Exception('Tidak ada dana yang dapat ditarik');
            }

            // Buat withdrawal
            $withdrawal = Withdrawal::create([
                'user_id' => Auth::id(),
                'status' => Withdrawal::STATUS_PENDING,
                'total_amount' => $totalAmount,
                'admin_fee' => 0,
                'payment_method' => $request->payment_method,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'bank_name' => $request->bank_name,
                'requested_at' => now(),
            ]);

            // Update withdrawal_id di distribusi yang dipilih
            TransactionDistribution::whereIn('id', $request->selected_distributions)
                ->where('user_id', Auth::id())
                ->update(['withdrawal_id' => $withdrawal->id]);


            $phoneNumber = '085923233782'; // Ganti dengan nomor tujuan yang valid

            // Pesan untuk Bendahara
            $message = "*PENGAJUAN TARIK TUNAI BARU*\n\n";
            $message .= "*Pemohon:* " . Auth::user()->name . "\n";
            $message .= "*Nominal Pengajuan:* Rp " . number_format($totalAmount, 0, ',', '.') . "\n\n";

            if ($request->payment_method === 'transfer') {
                $message .= "*Metode:* Transfer " . ($request->bank_name ?: 'Bank/E-Wallet') . "\n";
                $message .= "*No. Rekening:* " . ($request->account_number ?: '-') . "\n";
                $message .= "*Atas Nama:* " . ($request->account_name ?: '-') . "\n";
            } else {
                $message .= "*Metode:* Tunai\n";
            }

            // $message .= "*ID Penarikan:* #" . $withdrawal->id . "\n";
            $message .= "*Tanggal:* " . now()->format('d/m/Y H:i') . "\n\n";

            $message .= "---\n";
            $message .= "*PERMINTAAN:*\n";
            $message .= "Segera proses pengajuan ini untuk menjaga kepercayaan anggota dan kelancaran distribusi dana.\n\n";

            $message .= "Salam,\n";
            $message .= "*Sistem Penarikan Dana*";

            // Kirim pesan hanya jika nomor tersedia
            if (!empty($phoneNumber) && $this->fonnteService->validatePhoneNumber($phoneNumber)) {
                $this->fonnteService->sendWhatsAppMessage($phoneNumber, $message);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Pengajuan penarikan berhasil dikirim. Menunggu konfirmasi admin.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengajukan penarikan: ' . $e->getMessage());
        }
    }

    /**
     * Display pending withdrawals for admin
     */
    public function index(Request $request)
    {

        // Jika user TIDAK memiliki role tersebut, alihkan
        if (!Auth::user()->hasRole(['Admin', 'coordinator', 'admin_plann'])) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak ada akses');
        }

        $withdrawals = Withdrawal::with(['user', 'processor', 'transactionDistributions.transaction'])
            ->whereIn('status', [Withdrawal::STATUS_PENDING, Withdrawal::STATUS_PROCESSING])
            ->orderBy('requested_at', 'desc')
            ->get();

        return Inertia::render('FrontEnd/Menu/Home/Finance/Withdrawal/Index', [
            'withdrawals' => $withdrawals,
            'paymentMethods' => Withdrawal::getPaymentMethodOptions(),
            'statusOptions' => Withdrawal::getStatusOptions(),
        ]);
    }
    

    /**
     * Show withdrawal detail for approval
     */
    public function show(Withdrawal $withdrawal)
    {

        $withdrawal->load([
            'user',
            'processor',
            'transactionDistributions.transaction',
            'transactionDistributions.transaction.inspection',
        ]);

        return Inertia::render('FrontEnd/Menu/Home/Finance/Withdrawal/Show', [
            'withdrawal' => $withdrawal,
            'paymentMethods' => Withdrawal::getPaymentMethodOptions(),
        ]);
    }

    /**
     * Show withdrawal detail for approval
     */
    public function process(Withdrawal $withdrawal)
    {

        // Jika user TIDAK memiliki role tersebut, alihkan
        if (!Auth::user()->hasRole(['Admin', 'coordinator', 'admin_plann'])) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak ada akses');
        }
        $withdrawal->load([
            'user',
            'processor',
            'transactionDistributions.transaction',
            'transactionDistributions.transaction.inspection',
        ]);

        $withdrawal->markAsProcessing();
        $withdrawal->save();

        return Inertia::render('FrontEnd/Menu/Home/Finance/Withdrawal/Process', [
            'withdrawal' => $withdrawal,
            'paymentMethods' => Withdrawal::getPaymentMethodOptions(),
        ]);
    }

    /**
     * Approve withdrawal
     */
    public function approve(Request $request, Withdrawal $withdrawal)
    {
        // Jika user TIDAK memiliki role tersebut, alihkan
        if (!Auth::user()->hasRole(['Admin', 'coordinator', 'admin_plann'])) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak ada akses');
        }

        $request->validate([
            'admin_fee' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'proof_file' => 'required_if:payment_method,transfer|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $withdrawal->admin_fee = $request->admin_fee ?? 0;
            $withdrawal->notes = $request->notes;
            $withdrawal->approve(Auth::id());

            // Simpan file bukti jika ada
            if ($request->hasFile('proof_file')) {
                $path = $request->file('proof_file')->store('withdrawal-proofs', 'public');
                $withdrawal->file_path = $path;
                
            }

            $withdrawal->save();

            // Jika status approved, ubah ke processing
            // $withdrawal->markAsProcessing();
            // $withdrawal->save();

             // Muat ulang relationship user untuk mendapatkan data yang fresh
            $withdrawal->load('user');

           // Pesan untuk Pemohon
            $PhoneNumber = $withdrawal->user->numberPhone; // Ambil nomor dari user

            if (!empty($PhoneNumber) && $this->fonnteService->validatePhoneNumber($PhoneNumber)) {
                $Message = "*🎉 PEMBERITAHUAN - DANA PENARIKAN SUDAH DICAIHKAN*\n\n";
                
                $Message .= "Halo, " . $withdrawal->user->name . "!\n\n";
                
                $Message .= "*✅ DANA SUDAH DITRANSFER!*\n";
                $Message .= "Penarikan dana Anda telah berhasil dicairkan dan dana sudah dikirim.\n\n";
                
                $Message .= "*💰 RINCIAN PENCAIRAN:*\n";
                $Message .= "• *Jumlah Dicairkan:* Rp " . number_format($withdrawal->total_amount, 0, ',', '.') . "\n";
                
                if ($withdrawal->admin_fee > 0) {
                    $Message .= "• *Biaya Admin:* Rp " . number_format($withdrawal->admin_fee, 0, ',', '.') . "\n";
                    $Message .= "• *Dana Diterima:* Rp " . number_format($withdrawal->final_amount, 0, ',', '.') . "\n";
                }
                
                if ($withdrawal->payment_method === 'transfer') {
                    $Message .= "• *Metode:* Transfer " . ($withdrawal->bank_name ?: 'Bank/E-Wallet') . "\n";
                    $Message .= "• *Tujuan:* " . ($withdrawal->account_number ?: '-') . "\n";
                    $Message .= "• *Atas Nama:* " . ($withdrawal->account_name ?: '-') . "\n\n";
                } else {
                    $Message .= "• *Metode:* Tunai\n";
                    $Message .= "• *Catatan:* Siap diambil di lokasi\n\n";
                }
                
                $Message .= "• *Di Cairkan Oleh:* " . Auth::user()->name . "\n";
                $Message .= "• *Tanggal Cair:* " . now()->format('d/m/Y H:i') . "\n\n";
                
                $Message .= "*📱 TINDAKAN SEGERA:*\n";
                $Message .= "1. ✅ *CEK SALDO* di rekening/E-Wallet Anda\n";
                $Message .= "2. 📲 *KONFIRMASI PENERIMAAN* di aplikasi\n";
                $Message .= "3. ✅ *PASTIKAN* dana sudah masuk dengan benar\n\n";
                
                $Message .= "*⏰ PENTING DIPERHATIKAN:*\n";
                $Message .= "• Harap konfirmasi penerimaan segera setelah dana masuk\n";
                $Message .= "• Konfirmasi membantu sistem menandai transaksi selesai\n";
                $Message .= "• Jika ada masalah, segera hubungi bendahara\n\n";
                
                $Message .= "*🔍 CARA KONFIRMASI:*\n";
                $Message .= "1. Buka aplikasi\n";
                $Message .= "2. Pilih menu 'Laporan'\n";
                $Message .= "3. Klik 'Riwayat atau Tarik Fee'\n";
                $Message .= "4. Klik 'Konfirmasi Diterima' pada transaksi ini\n\n";
                
                if ($withdrawal->payment_method === 'transfer') {
                    $Message .= "*💡 INFO TRANSFER:*\n";
                    $Message .= "Transfer biasanya membutuhkan beberapa menit untuk proses. Jika setelah 30 menit dana belum masuk, silakan cek dengan bank/E-Wallet Anda.\n\n";
                }
                
                $Message .= "Terima kasih dan selamat menikmati dana Anda! 🎊\n\n";
                
                $Message .= "Salam,\n";
                $Message .= "*Tim Keuangan Cek Mobil*\n";
                $Message .= "📞 Hubungi kami jika ada kendala";

                $this->fonnteService->sendWhatsAppMessage($PhoneNumber, $Message);
            }

            DB::commit();

            return redirect()->route('withdrawals.index')->with('success', 'Penarikan berhasil disetujui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyetujui penarikan: ' . $e->getMessage());
        }
    }

    /**
     * Reject withdrawal
     */
    public function reject(Request $request, Withdrawal $withdrawal)
    {
        // Jika user TIDAK memiliki role tersebut, alihkan
        if (!Auth::user()->hasRole(['Admin', 'coordinator', 'admin_plann'])) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak ada akses');
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $withdrawal->reject($request->rejection_reason, Auth::id());
            $withdrawal->save();

            // Hapus withdrawal_id dari distribusi
            $withdrawal->transactionDistributions()->update(['withdrawal_id' => null]);

                         // Muat ulang relationship user untuk mendapatkan data yang fresh
            $withdrawal->load('user');

           // Pesan untuk Pemohon
            $PhoneNumber = $withdrawal->user->numberPhone; // Ambil nomor dari user

            if (!empty($PhoneNumber) && $this->fonnteService->validatePhoneNumber($PhoneNumber)) {
                 $Message = "*⚠️ PENGAJUAN PENARIKAN DITOLAK*\n\n";
            
                $Message .= "Halo, " . $withdrawal->user->name . "\n\n";
                
                $Message .= "*📌 STATUS:* DITOLAK\n";
                $Message .= "*Nominal:* Rp " . number_format($withdrawal->total_amount, 0, ',', '.') . "\n";
                $Message .= "*ID Transaksi:* #" . $withdrawal->id . "\n";
                $Message .= "*Ditolah Oleh:* " . Auth::user()->name . "\n";
                $Message .= "*Waktu:* " . now()->format('d/m/Y H:i') . "\n\n";
                
                $Message .= "*📝 ALASAN PENOLAKAN:*\n";
                $Message .= $request->rejection_reason . "\n\n";
                
                $Message .= "*💡 INFORMASI:*\n";
                $Message .= "• Distribusi dana telah dikembalikan ke akun Anda\n";
                $Message .= "• Anda dapat mengajukan penarikan kembali\n";
                $Message .= "• Hubungi admin jika ada pertanyaan\n\n";
                
                $Message .= "Salam,\n";
                $Message .= "*Tim Keuangan*";

                $this->fonnteService->sendWhatsAppMessage($PhoneNumber, $Message);
            }

            DB::commit();

            return redirect()->route('withdrawals.index')->with('success', 'Penarikan berhasil ditolak.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak penarikan: ' . $e->getMessage());
        }
    }

    /**
     * Complete withdrawal (mark as completed by user)
     */
    public function complete(Withdrawal $withdrawal)
    {
        // Hanya user pemilik yang bisa complete
        if ($withdrawal->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$withdrawal->isApproved() && !$withdrawal->isProcessing()) {
            return back()->with('error', 'Status penarikan tidak valid untuk diselesaikan.');
        }

        DB::beginTransaction();

        try {
            $withdrawal->complete();
            $withdrawal->save();

            DB::commit();

            return redirect()->route('withdrawals.history')->with('success', 'Penarikan telah diselesaikan. Dana telah diterima.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyelesaikan penarikan: ' . $e->getMessage());
        }
    }

    /**
     * Display withdrawal history for user
     */
    public function history(Request $request)
    {
        $query = Withdrawal::with(['processor', 'transactionDistributions'])
            ->where('user_id', Auth::id());

        // Filter by year
        if ($request->has('year') && $request->year) {
            $query->whereYear('requested_at', $request->year);
        }

        // Filter by month
        if ($request->has('month') && $request->month) {
            $query->whereMonth('requested_at', $request->month);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $withdrawals = $query->orderBy('requested_at', 'desc')->get();

        return Inertia::render('FrontEnd/Menu/Home/Finance/Withdrawal/History', [
            'withdrawals' => $withdrawals,
            'filters' => $request->only(['year', 'month', 'status']),
        ]);
    }

    /**
     * Download proof file
     */
    public function downloadProof(Withdrawal $withdrawal)
    {
        if (!$withdrawal->file_path) {
            abort(404);
        }


        return Storage::disk('public')->download($withdrawal->file_path);
    }
}
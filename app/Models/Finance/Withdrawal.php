<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Withdrawal extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'status',
        'total_amount',
        'admin_fee',
        'final_amount',
        'payment_method',
        'account_number',
        'account_name',
        'bank_name',
        'file_path',
        'rejection_reason',
        'notes',
        'requested_at',
        'processed_at',
        'processed_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'decimal:0',
        'admin_fee' => 'decimal:0',
        'final_amount' => 'decimal:0',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';

    /**
     * Payment method constants
     */
    const PAYMENT_METHOD_TRANSFER = 'transfer';
    const PAYMENT_METHOD_CASH = 'cash';
    const PAYMENT_METHOD_EWALLET = 'ewallet';

    /**
     * Get the status options for dropdown
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_PROCESSING => 'Diproses',
            self::STATUS_COMPLETED => 'Selesai',
        ];
    }

    /**
     * Get the payment method options for dropdown
     */
    public static function getPaymentMethodOptions(): array
    {
        return [
            self::PAYMENT_METHOD_TRANSFER => 'Transfer Bank',
            self::PAYMENT_METHOD_CASH => 'Tunai',
            self::PAYMENT_METHOD_EWALLET => 'E-Wallet',
        ];
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter berdasarkan user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope untuk filter withdrawal pending
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope untuk filter withdrawal completed
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope untuk filter withdrawal dalam rentang tanggal
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('requested_at', [$startDate, $endDate]);
    }

    /**
     * Relationship dengan User yang membuat withdrawal
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship dengan User yang memproses withdrawal
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Relationship dengan TransactionDistributions
     */
    public function transactionDistributions()
    {
        return $this->hasMany(TransactionDistribution::class);
    }

    /**
     * Calculate final amount automatically
     */
    public function calculateFinalAmount(): void
    {
        $this->final_amount = $this->total_amount - ($this->admin_fee ?? 0);
    }

    /**
     * Check if withdrawal is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if withdrawal is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if withdrawal is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if withdrawal is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Approve the withdrawal
     */
    public function approve($processedBy = null): void
    {
        $this->status = self::STATUS_APPROVED;
        $this->processed_at = now();
        $this->processed_by = $processedBy ?? Auth::id();
    }

    /**
     * Reject the withdrawal
     */
    public function reject($reason, $processedBy = null): void
    {
        $this->status = self::STATUS_REJECTED;
        $this->rejection_reason = $reason;
        $this->processed_at = now();
        $this->processed_by = $processedBy ?? Auth::id();
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing(): void
    {
        $this->status = self::STATUS_PROCESSING;
    }

    /**
     * Complete the withdrawal
     */
    public function complete(): void
    {
        $this->status = self::STATUS_COMPLETED;
        
        // Update related transaction distributions
        $this->transactionDistributions()->update([
            'is_released' => true,
            'released_at' => now(),
            'released_by' => $this->processed_by,
        ]);
    }

    /**
     * Get formatted status with badge color
     */
    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            self::STATUS_PENDING => '<span class="badge badge-warning">Pending</span>',
            self::STATUS_APPROVED => '<span class="badge badge-info">Disetujui</span>',
            self::STATUS_REJECTED => '<span class="badge badge-danger">Ditolak</span>',
            self::STATUS_PROCESSING => '<span class="badge badge-primary">Diproses</span>',
            self::STATUS_COMPLETED => '<span class="badge badge-success">Selesai</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge badge-secondary">Unknown</span>';
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    /**
     * Get formatted final amount
     */
    public function getFormattedFinalAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->final_amount, 0, ',', '.');
    }

    /**
     * Get formatted admin fee
     */
    public function getFormattedAdminFeeAttribute(): string
    {
        return $this->admin_fee ? 'Rp ' . number_format($this->admin_fee, 0, ',', '.') : '-';
    }

    /**
     * Get formatted requested date
     */
    public function getFormattedRequestedAtAttribute(): string
    {
        return $this->requested_at ? $this->requested_at->format('d/m/Y H:i') : '-';
    }

    /**
     * Get formatted processed date
     */
    public function getFormattedProcessedAtAttribute(): string
    {
        return $this->processed_at ? $this->processed_at->format('d/m/Y H:i') : '-';
    }

    /**
     * Get the total amount from related distributions
     */
    public function calculateTotalFromDistributions(): float
    {
        return $this->transactionDistributions()->sum('amount');
    }

    /**
     * Boot method untuk event listener
     */
    protected static function boot()
    {
        parent::boot();

        // Auto calculate final amount before saving
        static::saving(function ($withdrawal) {
            $withdrawal->calculateFinalAmount();
        });

        // Auto update requested_at jika null
        static::creating(function ($withdrawal) {
            if (empty($withdrawal->requested_at)) {
                $withdrawal->requested_at = now();
            }
        });
    }
}
<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionRefund extends Model
{
        use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'processed_by',
        'amount',
        'fee',
        'method',
        'reference',
        'reason',
        'status',
        'notes',
        'file_path'
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    // Method constants
    const METHOD_TRANSFER = 'transfer';
    const METHOD_CASH = 'cash';

    /**
     * Get the transaction that owns the refund.
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the user who processed the refund.
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Calculate net amount (amount - fee)
     */
    public function getNetAmountAttribute()
    {
        return $this->amount - $this->fee;
    }

    /**
     * Check if refund is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Scope untuk refund yang completed.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope untuk refund yang pending.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}

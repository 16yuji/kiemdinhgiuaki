<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'booking_id',
        'financial_transaction_id',
        'applied_settlement_id',
        'type',
        'amount',
        'remaining_amount',
        'status',
        'reason',
        'created_by',
        'deducted_at',
        'cancelled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'deducted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function financialTransaction()
    {
        return $this->belongsTo(FinancialTransaction::class);
    }

    public function appliedSettlement()
    {
        return $this->belongsTo(Settlement::class, 'applied_settlement_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending_deduction')
            ->where('remaining_amount', '>', 0);
    }
}

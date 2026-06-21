<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'method',
        'status',
        'amount',
        'transaction_code',
        'gateway_response_code',
        'gateway_payload',
        'refund_amount',
        'paid_at',
        'refunded_at',
        'refund_amount',
        'refund_reason',
        'refund_note',
        'refunded_at',
        'refunded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'gateway_payload' => 'array',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
    public function refundedBy()
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }
}
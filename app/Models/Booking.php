<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Review;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'hotel_id',
        'booking_code',
        'checkin_date',
        'checkout_date',
        'guest_count',
        'contact_name',
        'contact_phone',
        'contact_email',
        'special_request',
        'total_amount',
        'status',
        'hold_expires_at',
        'checked_in_at',
        'checked_out_at',
        'cancel_reason',
        'cancelled_at',
        'no_show_reason',
        'no_show_at',
        'checkin_note',
        'checkout_note',
        'manual_review_reason',
    ];

    protected $casts = [
        'checkin_date' => 'date',
        'checkout_date' => 'date',
        'total_amount' => 'decimal:2',
        'hold_expires_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'no_show_at' => 'datetime',
        
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function roomTypes()
    {
        return $this->hasMany(BookingRoomType::class);
    }

    public function roomAssignments()
    {
        return $this->hasMany(BookingRoomAssignment::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function financialTransaction()
    {
        return $this->hasOne(FinancialTransaction::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function canBeCancelledByCustomer(): bool
    {
        return in_array($this->status, ['pending_payment', 'confirmed'], true)
            && $this->checkin_date
            && $this->checkin_date->isFuture();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}

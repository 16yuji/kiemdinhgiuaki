<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'hotel_id',
        'customer_id',
        'rating',
        'comment',
        'status',
        'hidden_by',
        'hidden_reason',
        'hidden_at',
    ];

    protected $casts = [
        'hidden_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function hiddenBy()
    {
        return $this->belongsTo(User::class, 'hidden_by');
    }

    public function reply()
    {
        return $this->hasOne(ReviewReply::class);
    }
}
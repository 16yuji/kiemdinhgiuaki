<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'owner_id',
        'gross_amount',
        'platform_fee',
        'owner_amount',
        'status',
        'note',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'owner_amount' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function settlement()
    {
        return $this->hasOne(Settlement::class);
    }

    public function ownerAdjustments()
    {
        return $this->hasMany(OwnerAdjustment::class);
    }
}
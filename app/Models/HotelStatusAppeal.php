<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelStatusAppeal extends Model
{
    protected $fillable = [
        'hotel_id',
        'owner_id',
        'status',
        'reason',
        'admin_reply',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
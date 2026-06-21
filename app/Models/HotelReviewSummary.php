<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelReviewSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'summary',
        'pros',
        'cons',
        'review_count',
        'reviews_hash',
        'generated_by',
        'generated_at',
    ];

    protected $casts = [
        'pros' => 'array',
        'cons' => 'array',
        'generated_at' => 'datetime',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'image_path',
        'is_thumbnail',
        'sort_order',
        'path',
    ];

    protected $casts = [
        'is_thumbnail' => 'boolean',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
    public function getPathAttribute(): ?string
    {
        return $this->attributes['image_path'] ?? null;
    }

    public function setPathAttribute(?string $value): void
    {
        $this->attributes['image_path'] = $value;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTypeImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_type_id',
        'image_path',
        'is_thumbnail',
        'sort_order',
        'path',
    ];

    protected $casts = [
        'is_thumbnail' => 'boolean',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
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
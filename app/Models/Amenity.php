<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'icon',
        'status',
    ];

    public function hotels()
    {
        return $this->belongsToMany(Hotel::class, 'hotel_amenity')
            ->withTimestamps();
    }

    public function roomTypes()
    {
        return $this->belongsToMany(RoomType::class, 'room_type_amenity')
            ->withTimestamps();
    }
}
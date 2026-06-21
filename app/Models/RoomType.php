<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'name',
        'description',
        'max_guests',
        'bed_type',
        'area',
        'price_per_night',
        'thumbnail',
        'status',
    ];

    protected $casts = [
        'area' => 'decimal:2',
        'price_per_night' => 'decimal:2',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function images()
    {
        return $this->hasMany(RoomTypeImage::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'room_type_amenity')
            ->withTimestamps();
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function bookingRoomTypes()
    {
        return $this->hasMany(BookingRoomType::class);
    }

    public function availableRooms()
    {
        return $this->hasMany(Room::class)->where('status', 'available');
    }
}
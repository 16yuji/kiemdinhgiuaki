<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Amenity;
use App\Models\HotelImage;
use App\Models\HotelReviewSummary;
use App\Models\Booking;
use App\Models\HotelStatusAppeal;

class Hotel extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'province',
        'district',
        'ward',
        'address',
        'latitude',
        'longitude',
        'checkin_time',
        'checkout_time',
        'thumbnail',
        'status',
        'average_rating',
        'review_count',
        'status_reason',
        'status_changed_at',
        'status_changed_by',
        'cancellation_policy',


    ];

    protected $casts = [
        'average_rating' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'checkin_time' => 'datetime:H:i:s',
        'checkout_time' => 'datetime:H:i:s',
        'status_changed_at' => 'datetime:H:i:s',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function images()
    {
        return $this->hasMany(HotelImage::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'hotel_amenity')
            ->withTimestamps();
    }

    public function roomTypes()
    {
        return $this->hasMany(RoomType::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function reviewSummary()
    {
        return $this->hasOne(HotelReviewSummary::class);
    }

    public function statusChangedBy()
    {
        return $this->belongsTo(User::class, 'status_changed_by');
    }

    public function statusAppeals()
    {
        return $this->hasMany(HotelStatusAppeal::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'gender',
        'birthday',
        'avatar',
        'google_id',
        'role',
        'status',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'locked_reason',
        'locked_at',
        'locked_by',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birthday' => 'date',
            'locked_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function partnerRequests()
    {
        return $this->hasMany(PartnerRequest::class);
    }

    public function reviewedPartnerRequests()
    {
        return $this->hasMany(PartnerRequest::class, 'reviewed_by');
    }

    public function hotels()
    {
        return $this->hasMany(Hotel::class, 'owner_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    public function financialTransactions()
    {
        return $this->hasMany(FinancialTransaction::class, 'owner_id');
    }

    public function ownerAdjustments()
    {
        return $this->hasMany(OwnerAdjustment::class, 'owner_id');
    }

    public function settlementsAsOwner()
    {
        return $this->hasMany(Settlement::class, 'owner_id');
    }

    public function settlementsAsAdmin()
    {
        return $this->hasMany(Settlement::class, 'admin_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    public function reviewReplies()
    {
        return $this->hasMany(ReviewReply::class, 'owner_id');
    }

    public function aiLogs()
    {
        return $this->hasMany(AiLog::class);
    }

    public function systemLogs()
    {
        return $this->hasMany(SystemLog::class);
    }

    public function lockedBy()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }   
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_transaction_id',
        'owner_id',
        'admin_id',
        'amount',
        'status',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'transfer_code',
        'note',
        'settled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'settled_at' => 'datetime',
    ];

    public function financialTransaction()
    {
        return $this->belongsTo(FinancialTransaction::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function appliedAdjustments()
    {
        return $this->hasMany(OwnerAdjustment::class, 'applied_settlement_id');
    }
}
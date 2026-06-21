<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'prompt',
        'response',
        'context_data',
        'status',
        'error_message',
    ];

    protected $casts = [
        'context_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
<?php

namespace App\Services;

use App\Models\SystemLog;
use Illuminate\Support\Facades\Request;

class SystemLogService
{
    public static function write(
        string $action,
        ?string $module = null,
        ?string $targetType = null,
        ?int $targetId = null,
        ?string $description = null,
        array $data = []
    ): void {
        SystemLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $module,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'description' => $description,
            'data' => $data,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
    
}
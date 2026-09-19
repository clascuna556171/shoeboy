<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(string $action, ?Model $auditable = null, ?array $details = null, ?User $user = null): AuditLog
    {
        $userId = $user?->id ?? auth()->id();

        return AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable?->getKey(),
            'details' => $details,
            'ip_address' => Request::ip(),
        ]);
    }
}

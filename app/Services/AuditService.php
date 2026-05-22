<?php

namespace App\Services;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Log a system action to the audit_trails table.
     */
    public static function log(string $action, string $description, ?array $payload = null, ?int $userId = null): AuditTrail
    {
        return AuditTrail::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'description' => $description,
            'payload' => $payload,
        ]);
    }
}

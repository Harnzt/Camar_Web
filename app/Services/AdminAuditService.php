<?php

namespace App\Services;

use App\Models\AdminActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AdminAuditService
{
    public function log(
        string $action,
        string $description,
        ?Model $target = null,
        array $oldValues = [],
        array $newValues = [],
        ?Request $request = null
    ): AdminActivityLog {
        $request ??= request();

        return AdminActivityLog::create([
            'admin_id' => auth()->id(),
            'action' => $action,
            'target_type' => $target?->getMorphClass(),
            'target_id' => $target?->getKey(),
            'description' => $description,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }
}

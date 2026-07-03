<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminActivityLog::with('admin');
        $this->scopeForUser($query, $request->user());

        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        if ($request->filled('admin_id')) {
            $query->where('admin_id', $request->admin_id);
        }

        $logs = $query->latest()->paginate(20)->withQueryString();

        return view('main_page.admin-panel.audit.index', compact('logs'));
    }

    private function scopeForUser($query, User $user): void
    {
        if ($user->isSuperAdmin()) {
            $query->whereHas('admin', fn ($adminQuery) => $adminQuery
                ->whereIn('role', ['super_admin', 'admin']));

            return;
        }

        if ($user->isAuditor()) {
            $query->whereHas('admin', fn ($adminQuery) => $adminQuery
                ->where('role', 'auditor'));

            return;
        }

        $query->whereRaw('1 = 0');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\DocumentVerification;
use App\Models\Order;
use App\Models\Project;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $managedRoles = match ($user?->role) {
            'super_admin' => ['admin'],
            'admin' => ['auditor'],
            default => [],
        };

        $stats = [
            'pending_users' => User::whereIn('role', ['buyer', 'seller'])->where('status', 'pending')->count(),
            'pending_documents' => DocumentVerification::where('status', 'pending')->count(),
            'pending_projects' => Project::where('verification_status', 'pending')->count(),
            'total_users' => User::whereIn('role', ['buyer', 'seller'])->count(),
            'managed_accounts' => User::whereIn('role', $managedRoles)->count(),
            'active_managed_accounts' => User::whereIn('role', $managedRoles)->where('status', 'verified')->count(),
            'audit_logs' => $this->scopedAuditLogs($user)->count(),
            'total_volume' => Order::whereIn('status', ['paid', 'verified', 'completed'])->sum('quantity'),
        ];

        $recentUsers = $user->hasPermission('users.verify')
            ? User::whereIn('role', ['buyer', 'seller'])
            ->latest()
            ->take(5)
            ->get()
            : collect();

        $recentProjects = $user->hasPermission('projects.verify')
            ? Project::with('seller')
            ->latest()
            ->take(5)
            ->get()
            : collect();

        $recentManagedAccounts = User::whereIn('role', $managedRoles)
            ->latest()
            ->take(5)
            ->get();

        $recentLogs = $this->scopedAuditLogs($user)
            ->latest()
            ->take(8)
            ->get();

        return view('main_page.admin-panel.dashboard', compact(
            'stats', 'recentUsers', 'recentProjects', 'recentManagedAccounts', 'recentLogs'
        ));
    }

    private function scopedAuditLogs(User $user)
    {
        $query = AdminActivityLog::with('admin');

        if ($user->isSuperAdmin()) {
            return $query->whereHas('admin', fn ($adminQuery) => $adminQuery
                ->whereIn('role', ['super_admin', 'admin']));
        }

        if ($user->isAuditor()) {
            return $query->whereHas('admin', fn ($adminQuery) => $adminQuery
                ->where('role', 'auditor'));
        }

        return $query->whereRaw('1 = 0');
    }
}

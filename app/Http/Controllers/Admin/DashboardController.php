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
    private const SUCCESSFUL_ORDER_STATUSES = ['paid', 'verified', 'completed'];

    public function index()
    {
        $user = auth()->user();
        $managedRoles = match ($user?->role) {
            'super_admin' => ['admin'],
            'admin' => ['auditor'],
            default => [],
        };

        $stats = [
            'pending_users' => User::where('role', 'buyer')->where('status', 'pending')->count(),
            'pending_documents' => DocumentVerification::where('status', 'pending')
                ->where('document_type', 'not like', 'project_%')
                ->count(),
            'pending_projects' => Project::where('verification_status', 'pending')->count(),
            'revision_projects' => Project::where('verification_status', 'revision_required')->count(),
            'managed_accounts' => User::whereIn('role', $managedRoles)->count(),
            'active_managed_accounts' => User::whereIn('role', $managedRoles)->where('status', 'verified')->count(),
        ];

        $salesChart = $user->hasPermission('transactions.view')
            ? $this->monthlySales()
            : null;

        $recentUsers = $user->hasPermission('users.verify')
            ? User::where('role', 'buyer')
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
            'stats', 'recentUsers', 'recentProjects', 'recentManagedAccounts', 'recentLogs', 'salesChart'
        ));
    }

    /**
     * Successful (paid onward) orders for the last 12 months, bucketed per month.
     * Grouped in PHP so it works on both MySQL and the SQLite test database.
     */
    private function monthlySales(): array
    {
        $start = now()->startOfMonth()->subMonths(11);

        $months = collect(range(0, 11))->mapWithKeys(fn (int $offset) => [
            $start->copy()->addMonths($offset)->format('Y-m') => [
                'label' => $start->copy()->addMonths($offset)->locale('id')->translatedFormat('M Y'),
                'transactions' => 0,
                'volume' => 0,
                'revenue' => 0.0,
            ],
        ])->all();

        Order::query()
            ->whereIn('status', self::SUCCESSFUL_ORDER_STATUSES)
            ->where('created_at', '>=', $start)
            ->get(['created_at', 'quantity', 'total_price'])
            ->each(function (Order $order) use (&$months) {
                $key = $order->created_at->format('Y-m');

                if (! isset($months[$key])) {
                    return;
                }

                $months[$key]['transactions']++;
                $months[$key]['volume'] += (int) $order->quantity;
                $months[$key]['revenue'] += (float) $order->total_price;
            });

        $series = array_values($months);

        return [
            'labels' => array_column($series, 'label'),
            'transactions' => array_column($series, 'transactions'),
            'volume' => array_column($series, 'volume'),
            'revenue' => array_column($series, 'revenue'),
            'totals' => [
                'transactions' => array_sum(array_column($series, 'transactions')),
                'volume' => array_sum(array_column($series, 'volume')),
                'revenue' => array_sum(array_column($series, 'revenue')),
            ],
        ];
    }

    private function scopedAuditLogs(User $user)
    {
        return AdminActivityLog::with('admin')->visibleTo($user);
    }
}

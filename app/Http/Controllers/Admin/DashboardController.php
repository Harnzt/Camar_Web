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
        $stats = [
            'pending_users' => User::whereIn('role', ['buyer', 'seller'])->where('status', 'pending')->count(),
            'pending_documents' => DocumentVerification::where('status', 'pending')->count(),
            'pending_projects' => Project::where('verification_status', 'pending')->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'total_users' => User::whereIn('role', ['buyer', 'seller'])->count(),
            'total_volume' => Order::whereIn('status', ['paid', 'verified', 'completed'])->sum('quantity'),
        ];

        $recentUsers = User::whereIn('role', ['buyer', 'seller'])
            ->latest()
            ->take(5)
            ->get();

        $recentProjects = Project::with('seller')
            ->latest()
            ->take(5)
            ->get();

        $recentLogs = AdminActivityLog::with('admin')
            ->latest()
            ->take(8)
            ->get();

        return view('main_page.admin-panel.dashboard', compact(
            'stats', 'recentUsers', 'recentProjects', 'recentLogs'
        ));
    }
}

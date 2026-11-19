<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive(); 

        // Menghitung jumlah isi keranjang secara global dari Session
        View::composer('*', function ($view) {
            $cartCount = count(session('cart', []));
            
            $view->with('cartCount', $cartCount);
        });

        View::composer('main_page.admin-panel.layout', function ($view) {
            $user = auth()->user();

            $view->with('adminQueueCounts', [
                'users' => $user?->hasPermission('users.verify')
                    ? User::where('role', 'buyer')->where('status', 'pending')->count()
                    : null,
                // Auditor menunggu proyek yang diajukan; admin menunggu proyek yang diminta revisi.
                'projects' => match (true) {
                    (bool) $user?->canReviewProjects() => Project::where('verification_status', 'pending')->count(),
                    (bool) $user?->canManageProjects() => Project::where('verification_status', 'revision_required')->count(),
                    default => null,
                },
                'transactions' => $user?->hasPermission('certificates.issue')
                    ? Order::whereIn('status', ['paid', 'verified'])->count()
                    : null,
            ]);
        });
    }
}
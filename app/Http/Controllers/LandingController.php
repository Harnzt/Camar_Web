<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Order; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandingController extends Controller
{
    public function index()
    {
        $project = Project::approved()->latest()->paginate(9);

        $lastTransaction = null;
        $recommendedProjects = collect(); 
        $sellerProjects = collect();
        $greetingName = '';
        $treeEquivalent = 0;
        $totalOffsetKg = 0;
        $offsetPercentage = 0;
        $totalOffsetTon = 0;
        $activeProjects = 0;
        $totalCarbonSold = 0;
        $totalStock = 0;
        $totalRevenue = 0;
        $pendingCount = 0;

        if (Auth::check()) {
            $user = Auth::user();
            if (!empty($user->company_name)) {
                $greetingName = $user->company_name;
            } else {
                $greetingName = explode(' ', $user->name)[0]; 
            }

            if (empty($greetingName)) {
                $greetingName = $user->name;
            }

            if ($user->isBuyer()) {
                $lastTransaction = $user->orders()->latest()->first();
                $recommendedProjects = Project::approved()->latest()->take(6)->get();
            }

            if ($user->isSeller()) {
                $sellerProjects = Project::where('seller_id', $user->id)->latest()->take(6)->get();
                
                $activeProjects = Project::where('seller_id', $user->id)->count();
                $totalStock = Project::where('seller_id', $user->id)->sum('stock_available');
                
                $totalRevenue = Order::whereHas('project', fn($q) => $q->where('seller_id', $user->id))
                                ->whereIn('status', ['paid', 'verified', 'completed'])->sum('total_price');
                
                $totalCarbonSold = Order::whereHas('project', fn($q) => $q->where('seller_id', $user->id))
                                ->whereIn('status', ['paid', 'verified', 'completed'])->sum('quantity');
                
                $pendingCount = Order::whereHas('project', fn($q) => $q->where('seller_id', $user->id))
                                ->where('status', 'pending')->count();
                                
                return view('main_page.landing.landing', compact(
                    'project', 'lastTransaction', 'recommendedProjects', 'sellerProjects', 'greetingName',
                    'treeEquivalent', 'totalOffsetTon', 'totalOffsetKg', 'offsetPercentage',
                    'activeProjects', 'totalCarbonSold', 'totalStock', 'totalRevenue', 'pendingCount', 'user'
                ));
            }
        }

        $user = Auth::user();
        return view('main_page.landing.landing', compact(
            'project', 'lastTransaction', 'recommendedProjects', 'sellerProjects', 'greetingName',
            'treeEquivalent', 'totalOffsetTon', 'totalOffsetKg', 'offsetPercentage',
            'activeProjects', 'totalCarbonSold', 'totalStock', 'totalRevenue', 'pendingCount', 'user'
        ));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class LandingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $project = Project::approved()->latest()->paginate(9);
        $lastTransaction = $user?->isBuyer() ? $user->orders()->latest()->first() : null;
        $recommendedProjects = $user?->isBuyer()
            ? Project::approved()->latest()->take(6)->get()
            : collect();
        $greetingName = $user?->company_name ?: ($user ? explode(' ', $user->name)[0] : '');
        $treeEquivalent = 0;
        $totalOffsetKg = 0;
        $offsetPercentage = 0;
        $totalOffsetTon = 0;

        return view('main_page.landing.landing', compact(
            'project',
            'lastTransaction',
            'recommendedProjects',
            'greetingName',
            'treeEquivalent',
            'totalOffsetTon',
            'totalOffsetKg',
            'offsetPercentage',
            'user',
        ));
    }
}

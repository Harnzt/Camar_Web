<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $approvedProjects = Project::query()->approved();

        return response()->json([
            'stats' => [
                'project_count' => (clone $approvedProjects)->count(),
                'carbon_available_ton' => (int) (clone $approvedProjects)
                    ->sum('stock_available'),
                'partner_count' => (clone $approvedProjects)
                    ->whereNotNull('seller_id')
                    ->distinct()
                    ->count('seller_id'),
            ],
            'latest_projects' => ProjectResource::collection(
                (clone $approvedProjects)
                    ->latest()
                    ->orderByDesc('id')
                    ->take(4)
                    ->get(),
            )->resolve(),
        ]);
    }
}

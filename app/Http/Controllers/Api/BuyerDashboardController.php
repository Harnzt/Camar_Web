<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmissionCalculation;
use App\Models\Order;
use App\Services\ProjectRecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuyerDashboardController extends Controller
{
    public function __construct(
        private readonly ProjectRecommendationService $recommendationService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $emission = EmissionCalculation::query()
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $successfulOrders = Order::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['paid', 'verified', 'completed']);

        $totalOffsetTon = (float) (clone $successfulOrders)->sum('quantity');
        $totalOffsetKg = $totalOffsetTon * 1000;
        $offsetPercentage = $emission && $emission->total_kg > 0
            ? min(100, ($totalOffsetKg / $emission->total_kg) * 100)
            : 0;

        $recommendations = $this->recommendationService
            ->recommend($user, $emission, 3)
            ->map(fn ($project) => [
                'id' => (string) $project->id,
                'title' => $project->name,
                'location' => $project->location,
                'category' => $project->category,
                'price_per_ton' => (float) $project->price_per_ton,
                'image_url' => $project->image_url,
                'recommendation_score' => $project->recommendation_score,
                'recommendation_reasons' => $project->recommendation_reasons,
            ])
            ->values();

        return response()->json([
            'emission' => $emission ? [
                'total_kg' => (float) $emission->total_kg,
                'total_ton' => (float) $emission->total_ton,
                'scope1_kg' => (float) $emission->scope1_kg,
                'scope2_kg' => (float) $emission->scope2_kg,
                'scope3_kg' => (float) $emission->scope3_kg,
            ] : null,
            'summary' => [
                'total_offset_kg' => $totalOffsetKg,
                'total_offset_ton' => $totalOffsetTon,
                'offset_percentage' => round($offsetPercentage, 1),
                'tree_equivalent' => $emission
                    ? (int) ceil($emission->total_kg / 21.77)
                    : 0,
                'total_transactions' => Order::where('user_id', $user->id)->count(),
                'total_spent' => (float) (clone $successfulOrders)->sum('total_price'),
            ],
            'recommended_projects' => $recommendations,
        ]);
    }
}

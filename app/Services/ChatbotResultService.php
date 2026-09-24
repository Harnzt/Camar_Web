<?php

namespace App\Services;

use App\Models\EmissionCalculation;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ChatbotResultService
{
    public function __construct(
        private readonly CarbonCalculationService $calculationService,
        private readonly ProjectRecommendationService $recommendationService
    ) {}

    public function process(User $user, array $data): array
    {
        if (! $user->isBuyer()) {
            throw new InvalidArgumentException('Perhitungan chatbot hanya tersedia untuk akun buyer.');
        }

        $result = $this->calculationService->calculate($user, $data);
        if ($result['total_kg'] <= 0) {
            throw new InvalidArgumentException(
                'Total emisi masih nol. Masukkan setidaknya satu aktivitas.'
            );
        }

        $pricePerTon = 150000;
        $totalTon = $result['total_kg'] / 1000;

        return DB::transaction(function () use ($user, $result, $totalTon, $pricePerTon): array {
            $calculation = EmissionCalculation::create([
                'user_id' => $user->id,
                'calculation_mode' => $result['mode'],
                'scope1_kg' => $result['scope1_kg'],
                'scope2_kg' => $result['scope2_kg'],
                'scope3_kg' => $result['scope3_kg'],
                'scope_details' => $result['scope_details'],
                'total_kg' => $result['total_kg'],
                'total_ton' => $totalTon,
                'estimated_cost' => $totalTon * $pricePerTon,
                'price_per_ton' => $pricePerTon,
            ]);

            $recommendations = $this->formatRecommendations(
                $this->recommendationService->recommend($user, $calculation, 3)
            );

            return [
                'calculation_id' => $calculation->id,
                'mode' => $result['mode'],
                'scope1_kg' => round($result['scope1_kg'], 2),
                'scope2_kg' => round($result['scope2_kg'], 2),
                'scope3_kg' => round($result['scope3_kg'], 2),
                'total_kg' => round($result['total_kg'], 2),
                'total_ton' => round($totalTon, 6),
                'estimated_cost' => round($totalTon * $pricePerTon),
                'recommendations' => $recommendations,
            ];
        });
    }

    public function recommendExisting(User $user): array
    {
        if (! $user->isBuyer()) {
            throw new InvalidArgumentException('Rekomendasi proyek hanya tersedia untuk akun buyer.');
        }

        $calculation = EmissionCalculation::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        return [
            'source' => $calculation ? 'latest_emission' : 'profile',
            'calculation_id' => $calculation?->id,
            'total_kg' => $calculation ? round((float) $calculation->total_kg, 2) : null,
            'recommendations' => $this->formatRecommendations(
                $this->recommendationService->recommend($user, $calculation, 3)
            ),
        ];
    }

    private function formatRecommendations(Collection $projects): array
    {
        return $projects
            ->map(fn ($project) => [
                'id' => $project->id,
                'name' => $project->name,
                'location' => $project->location,
                'price_per_ton' => (float) $project->price_per_ton,
                'score' => $project->recommendation_score,
                'reasons' => $project->recommendation_reasons,
                'url' => route('projects.show', $project->id),
            ])
            ->values()
            ->all();
    }
}

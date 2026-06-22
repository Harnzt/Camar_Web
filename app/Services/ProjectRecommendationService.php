<?php

namespace App\Services;

use App\Models\EmissionCalculation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProjectRecommendationService
{
    private const WEIGHTS = [
        'emission_match' => 35,
        'budget_match' => 25,
        'stock_coverage' => 20,
        'location_match' => 10,
        'verification' => 10,
    ];

    public function recommend(
        User $user,
        ?EmissionCalculation $emission,
        int $limit = 3,
        ?Collection $projects = null
    ): Collection {
        $projects ??= Project::query()
            ->approved()
            ->where('stock_available', '>', 0)
            ->get();

        if (!$emission) {
            return $this->initialRecommendations($user, $projects, $limit);
        }

        return $projects
            ->map(fn (Project $project) => $this->scoreProject($project, $user, $emission))
            ->sortByDesc('recommendation_score')
            ->take($limit)
            ->values();
    }

    private function initialRecommendations(User $user, Collection $projects, int $limit): Collection
    {
        if (!$user->isCompany() || blank($user->industry)) {
            return $projects
                ->shuffle()
                ->take($limit)
                ->values()
                ->each(fn (Project $project) => $this->markAsInitialRecommendation(
                    $project,
                    'random',
                    'Direkomendasikan untuk Anda',
                    'Pilihan proyek aktif dengan stok kredit yang tersedia.'
                ));
        }

        $industry = Str::lower($user->industry);

        return $projects
            ->map(function (Project $project) use ($industry) {
                $project->setAttribute(
                    'industry_match_score',
                    $this->industryMatchScore($project, $industry)
                );

                return $project;
            })
            ->sortByDesc('industry_match_score')
            ->take($limit)
            ->values()
            ->each(fn (Project $project) => $this->markAsInitialRecommendation(
                $project,
                'industry',
                'Direkomendasikan',
                "Dipilih berdasarkan profil industri {$user->industry}."
            ));
    }

    private function markAsInitialRecommendation(
        Project $project,
        string $mode,
        string $label,
        string $reason
    ): void {
        $project->setAttribute('recommendation_mode', $mode);
        $project->setAttribute('recommendation_label', $label);
        $project->setAttribute('recommendation_reasons', [$reason]);
    }

    private function industryMatchScore(Project $project, string $industry): int
    {
        $projectText = Str::lower(implode(' ', [
            $project->category,
            $project->name,
            $project->description,
            $project->methodology,
        ]));

        $industryKeywords = [
            'agriculture' => ['agriculture', 'pertanian', 'agroforestri', 'lahan', 'organik', 'hutan'],
            'energy' => ['energy', 'energi', 'surya', 'solar', 'angin', 'wind', 'hidro', 'listrik', 'biogas', 'biomassa'],
            'retail' => ['waste', 'limbah', 'sampah', 'efisiensi', 'urban', 'energi'],
            'manufacturing' => ['efisiensi', 'energi', 'limbah', 'biomassa', 'reklamasi'],
            'transportation' => ['transport', 'kendaraan', 'energi', 'efisiensi', 'biofuel'],
            'forestry' => ['forest', 'forestry', 'hutan', 'mangrove', 'reforestasi', 'konservasi'],
            'technology' => ['energi', 'efisiensi', 'renewable', 'surya', 'listrik'],
            'hospitality' => ['limbah', 'waste', 'energi', 'efisiensi', 'konservasi'],
        ];

        $matchedIndustry = collect(array_keys($industryKeywords))
            ->first(fn (string $key) => Str::contains($industry, $key));

        $keywords = $matchedIndustry
            ? $industryKeywords[$matchedIndustry]
            : collect(preg_split('/[^a-z0-9]+/i', $industry))
                ->filter(fn (?string $word) => $word && strlen($word) >= 3)
                ->values()
                ->all();

        return collect($keywords)
            ->filter(fn (string $keyword) => Str::contains($projectText, $keyword))
            ->count();
    }

    public function scoreProject(
        Project $project,
        User $user,
        ?EmissionCalculation $emission
    ): Project {
        $dominantScope = $this->dominantScope($emission);
        $neededTon = max(1, (float) ceil(((float) ($emission?->total_kg ?? 0)) / 1000));
        $targetPrice = (float) ($emission?->price_per_ton ?: 150000);

        $breakdown = [
            'emission_match' => $this->emissionMatchScore($project, $dominantScope),
            'budget_match' => $this->budgetMatchScore($project, $targetPrice),
            'stock_coverage' => $this->stockCoverageScore($project, $neededTon),
            'location_match' => $this->locationMatchScore($project, $user),
            'verification' => $this->verificationScore($project),
        ];

        $score = round(array_sum($breakdown), 1);

        $project->setAttribute('recommendation_score', $score);
        $project->setAttribute('recommendation_breakdown', $breakdown);
        $project->setAttribute('recommendation_reasons', $this->buildReasons(
            $project,
            $dominantScope,
            $neededTon,
            $targetPrice,
            $breakdown
        ));
        $project->setAttribute('dominant_scope', $dominantScope);

        return $project;
    }

    private function dominantScope(?EmissionCalculation $emission): string
    {
        if (!$emission) {
            return 'general';
        }

        $scopes = [
            'scope1' => (float) $emission->scope1_kg,
            'scope2' => (float) $emission->scope2_kg,
            'scope3' => (float) $emission->scope3_kg,
        ];

        arsort($scopes);

        return (float) reset($scopes) > 0 ? (string) array_key_first($scopes) : 'general';
    }

    private function emissionMatchScore(Project $project, string $dominantScope): float
    {
        if ($dominantScope === 'general') {
            return 17.5;
        }

        $haystack = Str::lower(implode(' ', [
            $project->category,
            $project->name,
            $project->description,
            $project->methodology,
        ]));

        $keywords = [
            'scope1' => [
                'renewable', 'energi', 'biogas', 'methane', 'metana', 'transport',
                'kendaraan', 'efisiensi', 'bahan bakar', 'clean cook',
            ],
            'scope2' => [
                'renewable', 'energi', 'surya', 'solar', 'angin', 'wind', 'hidro',
                'hydro', 'geothermal', 'listrik', 'plts',
            ],
            'scope3' => [
                'forestry', 'forest', 'hutan', 'mangrove', 'blue carbon', 'gambut',
                'peat', 'reforestasi', 'restorasi', 'konservasi', 'waste', 'limbah',
                'sampah', 'pertanian', 'nature',
            ],
        ];

        $matches = collect($keywords[$dominantScope])
            ->filter(fn (string $keyword) => Str::contains($haystack, $keyword))
            ->count();

        return match (true) {
            $matches >= 2 => 35.0,
            $matches === 1 => 24.5,
            default => 7.0,
        };
    }

    private function budgetMatchScore(Project $project, float $targetPrice): float
    {
        $price = (float) $project->price_per_ton;

        if ($price <= 0 || $targetPrice <= 0) {
            return 0;
        }

        if ($price <= $targetPrice) {
            return 25.0;
        }

        return round(max(0, 25 * (1 - (($price - $targetPrice) / $targetPrice))), 1);
    }

    private function stockCoverageScore(Project $project, float $neededTon): float
    {
        if ($neededTon <= 0) {
            return 20.0;
        }

        return round(min(1, ((float) $project->stock_available) / $neededTon) * 20, 1);
    }

    private function locationMatchScore(Project $project, User $user): float
    {
        if (!$user->address || !$project->location) {
            return 5.0;
        }

        $ignored = ['indonesia', 'jalan', 'jl', 'kota', 'kabupaten', 'provinsi'];
        $userTokens = $this->locationTokens($user->address, $ignored);
        $projectTokens = $this->locationTokens($project->location, $ignored);

        return $userTokens->intersect($projectTokens)->isNotEmpty() ? 10.0 : 2.5;
    }

    private function locationTokens(string $location, array $ignored): Collection
    {
        return collect(preg_split('/[^a-z0-9]+/i', Str::lower($location)))
            ->filter(fn (?string $token) => $token && strlen($token) >= 3)
            ->reject(fn (string $token) => in_array($token, $ignored, true))
            ->values();
    }

    private function verificationScore(Project $project): float
    {
        if (!$project->standard) {
            return 2.5;
        }

        $standard = Str::lower($project->standard);
        $recognized = [
            'verra', 'vcs', 'gold standard', 'ccb', 'srm', 'srn',
            'plan vivo', 'cdm', 'iso 14064',
        ];

        return collect($recognized)->contains(
            fn (string $keyword) => Str::contains($standard, $keyword)
        ) ? 10.0 : 6.0;
    }

    private function buildReasons(
        Project $project,
        string $dominantScope,
        float $neededTon,
        float $targetPrice,
        array $breakdown
    ): array {
        $scopeLabels = [
            'scope1' => 'emisi langsung dari bahan bakar dan kendaraan',
            'scope2' => 'emisi listrik yang dibeli',
            'scope3' => 'emisi tidak langsung dari rantai nilai',
            'general' => 'profil emisi Anda',
        ];

        $reasons = [];

        if ($breakdown['emission_match'] >= 24.5) {
            $reasons[] = "Kategori proyek cocok dengan {$scopeLabels[$dominantScope]}.";
        }

        if ((float) $project->price_per_ton <= $targetPrice) {
            $reasons[] = 'Harga per ton berada dalam estimasi anggaran offset Anda.';
        }

        if ((float) $project->stock_available >= $neededTon) {
            $reasons[] = 'Stok kredit cukup untuk menutup kebutuhan offset kalkulasi terakhir.';
        }

        if ($breakdown['location_match'] === 10.0) {
            $reasons[] = 'Lokasi proyek relevan dengan alamat atau wilayah Anda.';
        }

        if ($breakdown['verification'] === 10.0) {
            $reasons[] = "Menggunakan standar terverifikasi {$project->standard}.";
        }

        if ($reasons === []) {
            $reasons[] = 'Merupakan pilihan aktif dengan stok kredit yang masih tersedia.';
        }

        return array_slice($reasons, 0, 3);
    }
}

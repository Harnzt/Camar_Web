<?php

namespace App\Services;

use App\Models\EmissionCalculation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ChatbotInformationService
{
    public function __construct(
        private readonly ProjectRecommendationService $recommendationService
    ) {}

    /**
     * @return array{text: string, custom: array<string, mixed>}
     */
    public function answer(string $requestType, string $query, ?User $user): array
    {
        return match ($requestType) {
            'emission_explanation_request' => $this->explainLatestEmission($user),
            'project_information_request' => $this->projectInformation($query),
            'project_verification_request' => $this->projectVerification($query),
            'recommendation_explanation_request' => $this->explainRecommendation($query, $user),
            default => [
                'text' => 'Jenis informasi yang diminta belum didukung Cami.',
                'custom' => ['type' => 'information_error'],
            ],
        };
    }

    /**
     * @return array{text: string, custom: array<string, mixed>}
     */
    private function explainLatestEmission(?User $user): array
    {
        if (! $user?->isBuyer()) {
            return [
                'text' => 'Masuk sebagai buyer terlebih dahulu agar Cami dapat menjelaskan hasil emisi akunmu.',
                'custom' => ['type' => 'emission_explanation_error'],
            ];
        }

        $emission = $this->latestEmission($user);
        if (! $emission) {
            return [
                'text' => 'Akunmu belum memiliki hasil emisi tersimpan. Gunakan Kalkulator atau tulis "hitung emisi", lalu tanyakan kembali setelah hasil selesai disimpan.',
                'custom' => ['type' => 'emission_explanation_empty'],
            ];
        }

        $scopes = [
            'Scope 1' => (float) $emission->scope1_kg,
            'Scope 2' => (float) $emission->scope2_kg,
            'Scope 3' => (float) $emission->scope3_kg,
        ];
        arsort($scopes);
        $dominantScope = (string) array_key_first($scopes);
        $dominantScopeValue = (float) reset($scopes);
        $totalKg = (float) $emission->total_kg;
        $scopePercentage = $totalKg > 0 ? ($dominantScopeValue / $totalKg) * 100 : 0;
        $activities = $this->emissionActivities($emission);
        $dominantActivity = $activities->first();

        $text = 'Hasil emisi terakhirmu adalah '.$this->formatDecimal($totalKg)
            .' kg CO2e ('.$this->formatDecimal((float) $emission->total_ton, 6)." ton).\n"
            .$dominantScope.' merupakan kontribusi terbesar: '
            .$this->formatDecimal($dominantScopeValue).' kg CO2e atau sekitar '
            .$this->formatDecimal($scopePercentage, 1).'% dari total.';

        if ($dominantActivity) {
            $text .= "\nAktivitas terbesar: {$dominantActivity['label']} sebesar "
                .$this->formatDecimal($dominantActivity['value_kg'])." kg CO2e.\n"
                .$this->reductionAdvice($dominantActivity['label']);
        }

        $text .= '\nAngka ini adalah estimasi berdasarkan data aktivitas dan faktor emisi pada kalkulator CAMAR.';

        return [
            'text' => $text,
            'custom' => [
                'type' => 'emission_explanation_result',
                'calculation_id' => $emission->id,
                'total_kg' => round($totalKg, 2),
                'total_ton' => round((float) $emission->total_ton, 6),
                'dominant_scope' => Str::lower(str_replace(' ', '', $dominantScope)),
                'dominant_activity' => $dominantActivity,
                'activities' => $activities->values()->all(),
            ],
        ];
    }

    /**
     * @return array{text: string, custom: array<string, mixed>}
     */
    private function projectInformation(string $query): array
    {
        $project = $this->resolveProject($query);
        if (! $project) {
            return $this->projectNameRequired(
                'Sebutkan nama proyek yang ingin diketahui, misalnya "jelaskan proyek Mangrove Blue Carbon Riau".'
            );
        }

        $text = "Informasi {$project->name}:\n"
            .'Pengelola: '.($project->company_name ?: 'belum dicantumkan')."\n"
            .'Kategori: '.($project->category ?: 'belum dicantumkan')."\n"
            .'Lokasi: '.($project->location ?: 'belum dicantumkan')."\n"
            .'Harga: Rp '.$this->formatDecimal((float) $project->price_per_ton, 0)." per ton\n"
            .'Stok tersedia: '.$this->formatDecimal((float) $project->stock_available, 0)." ton\n"
            .'Standar tercatat: '.($project->standard ?: 'belum dicantumkan');

        if ($project->description) {
            $text .= "\nRingkasan: ".Str::limit(strip_tags($project->description), 240);
        }

        if ($project->methodology) {
            $text .= "\nMetodologi: ".Str::limit(strip_tags($project->methodology), 180);
        }

        return [
            'text' => $text,
            'custom' => [
                'type' => 'project_information_result',
                'project' => $this->projectCard($project),
                'recommendations' => [$this->projectCard($project)],
                'heading' => 'Informasi proyek',
            ],
        ];
    }

    /**
     * @return array{text: string, custom: array<string, mixed>}
     */
    private function projectVerification(string $query): array
    {
        $project = $this->resolveProject($query);
        if (! $project) {
            return $this->projectNameRequired(
                'Sebutkan nama proyek yang ingin diperiksa, misalnya "apakah proyek Mangrove Blue Carbon Riau sudah diverifikasi?".'
            );
        }

        $standard = $project->standard ?: 'belum dicantumkan';
        $reviewStatus = $project->verification_status === 'approved'
            ? 'disetujui dalam peninjauan internal CAMAR'
            : 'belum disetujui dalam peninjauan internal CAMAR';
        $year = $project->verified_year
            ? ' Tahun verifikasi yang tercatat: '.$project->verified_year.'.'
            : ' Tahun verifikasi belum dicantumkan.';

        $text = "Status {$project->name}: {$reviewStatus}. Standar yang tercatat: {$standard}.{$year} ";
        if ($project->standard) {
            $text .= 'Nama standar pada data proyek merupakan metadata proyek dan belum cukup sebagai bukti sertifikat. ';
        }
        $text .= 'CAMAR saat ini belum menyediakan nomor, dokumen, atau tautan registri sertifikasi khusus proyek ini kepada Cami, sehingga bukti sertifikasinya belum dapat dikonfirmasi melalui chatbot. Periksa halaman detail proyek atau minta dokumen kepada pengelola.';

        return [
            'text' => $text,
            'custom' => [
                'type' => 'project_verification_result',
                'project_id' => $project->id,
                'project_name' => $project->name,
                'review_status' => $project->verification_status,
                'standard' => $project->standard,
                'verified_year' => $project->verified_year,
                'evidence_available' => false,
                'url' => route('projects.show', $project->id),
                'recommendations' => [$this->projectCard($project)],
                'heading' => 'Status dan bukti proyek',
            ],
        ];
    }

    /**
     * @return array{text: string, custom: array<string, mixed>}
     */
    private function explainRecommendation(string $query, ?User $user): array
    {
        if (! $user?->isBuyer()) {
            return [
                'text' => 'Masuk sebagai buyer terlebih dahulu agar Cami dapat menjelaskan rekomendasi berdasarkan akunmu.',
                'custom' => ['type' => 'recommendation_explanation_error'],
            ];
        }

        $emission = $this->latestEmission($user);
        if (! $emission) {
            return [
                'text' => 'Belum ada hasil emisi untuk menjelaskan kecocokan proyek. Hitung emisi terlebih dahulu. Setelah itu, proyek dinilai dari kesesuaian sumber emisi (35 poin), harga (25), stok (20), lokasi (10), dan standar yang tercatat (10).',
                'custom' => ['type' => 'recommendation_explanation_empty'],
            ];
        }

        $project = $this->resolveProject($query);
        if ($project) {
            $project = $this->recommendationService->scoreProject($project, $user, $emission);
        } else {
            $project = $this->recommendationService->recommend($user, $emission, 1)->first();
        }

        if (! $project) {
            return [
                'text' => 'Belum ada proyek disetujui dengan stok tersedia yang dapat dijelaskan.',
                'custom' => ['type' => 'recommendation_explanation_empty'],
            ];
        }

        $breakdown = $project->recommendation_breakdown ?? [];
        $reasons = $project->recommendation_reasons ?? [];
        $text = $project->name.' memperoleh skor kecocokan '
            .$this->formatDecimal((float) $project->recommendation_score, 1)." dari 100.\n"
            .'Rincian: sumber emisi '.($breakdown['emission_match'] ?? 0).'/35, harga '
            .($breakdown['budget_match'] ?? 0).'/25, stok '
            .($breakdown['stock_coverage'] ?? 0).'/20, lokasi '
            .($breakdown['location_match'] ?? 0).'/10, dan standar '
            .($breakdown['verification'] ?? 0).'/10.';

        if ($reasons !== []) {
            $text .= "\nAlasan utama: ".implode(' ', $reasons);
        }

        $text .= '\nSkor ini adalah hasil aturan pembobotan CAMAR, bukan jaminan bahwa proyek tersebut satu-satunya pilihan terbaik.';

        return [
            'text' => $text,
            'custom' => [
                'type' => 'recommendation_explanation_result',
                'calculation_id' => $emission->id,
                'breakdown' => $breakdown,
                'recommendations' => [$this->projectCard($project, true)],
                'heading' => 'Alasan rekomendasi',
            ],
        ];
    }

    protected function latestEmission(User $user): ?EmissionCalculation
    {
        return EmissionCalculation::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();
    }

    /**
     * @return Collection<int, array{scope: string, label: string, value_kg: float}>
     */
    private function emissionActivities(EmissionCalculation $emission): Collection
    {
        return collect($emission->scope_details ?? [])
            ->flatMap(function ($items, string $scope): array {
                if (! is_array($items)) {
                    return [];
                }

                return collect($items)->map(fn ($item) => [
                    'scope' => $scope,
                    'label' => (string) ($item['label'] ?? 'Aktivitas'),
                    'value_kg' => round((float) ($item['value_kg'] ?? 0), 2),
                ])->all();
            })
            ->filter(fn (array $item) => $item['value_kg'] > 0)
            ->sortByDesc('value_kg')
            ->values();
    }

    private function reductionAdvice(string $activity): string
    {
        $activity = Str::lower($activity);

        return match (true) {
            Str::contains($activity, ['listrik']) => 'Prioritaskan penghematan listrik, audit peralatan, dan penggunaan sumber energi lebih rendah emisi bila tersedia.',
            Str::contains($activity, ['kendaraan', 'transportasi', 'pesawat', 'kereta']) => 'Kurangi perjalanan yang tidak perlu, tingkatkan okupansi, pilih moda lebih efisien, dan rawat kendaraan secara berkala.',
            Str::contains($activity, ['bahan bakar', 'pembakaran', 'energi rumah']) => 'Tingkatkan efisiensi pemakaian bahan bakar dan pertimbangkan peralihan ke sumber energi lebih rendah emisi.',
            Str::contains($activity, ['makanan']) => 'Kurangi makanan terbuang dan sesuaikan pola konsumsi secara bertahap dengan pilihan yang lebih rendah emisi.',
            Str::contains($activity, ['limbah']) => 'Kurangi sampah dari sumbernya, gunakan kembali barang, serta pilah bahan yang dapat didaur ulang atau dikomposkan.',
            Str::contains($activity, ['air']) => 'Kurangi pemborosan air dan segera perbaiki kebocoran untuk menekan energi pengolahan serta distribusi air.',
            Str::contains($activity, ['hotel']) => 'Gabungkan perjalanan bila memungkinkan dan pilih akomodasi yang memiliki praktik efisiensi energi yang dapat dibuktikan.',
            default => 'Mulailah mengurangi konsumsi pada aktivitas ini dan ukur kembali setelah melakukan perubahan.',
        };
    }

    protected function resolveProject(string $query): ?Project
    {
        $projects = Project::query()->approved()->get();
        $queryText = $this->normaliseText($query);
        $queryTokens = $this->meaningfulTokens($queryText);

        $ranked = $projects->map(function (Project $project) use ($queryText, $queryTokens): array {
            $name = $this->normaliseText($project->name);
            $nameTokens = $this->meaningfulTokens($name);
            $companyTokens = $this->meaningfulTokens($this->normaliseText((string) $project->company_name));
            $exactScore = $name !== '' && Str::contains($queryText, $name) ? 1000 : 0;
            $nameScore = $queryTokens->intersect($nameTokens)->count() * 20;
            $companyScore = $queryTokens->intersect($companyTokens)->count() * 5;

            return [
                'project' => $project,
                'score' => $exactScore + $nameScore + $companyScore,
            ];
        })->sortByDesc('score')->values();

        $best = $ranked->first();
        if (! $best || $best['score'] <= 0) {
            return null;
        }

        $second = $ranked->get(1);
        if ($second && $second['score'] === $best['score']) {
            return null;
        }

        return $best['project'];
    }

    private function normaliseText(string $value): string
    {
        $value = Str::lower(Str::ascii($value));

        return trim((string) preg_replace('/[^a-z0-9]+/i', ' ', $value));
    }

    /**
     * @return Collection<int, string>
     */
    private function meaningfulTokens(string $value): Collection
    {
        $ignored = [
            'apa', 'apakah', 'bagaimana', 'berapa', 'bukti', 'carbon', 'cari', 'dari',
            'detail', 'di', 'dimana', 'dokumen', 'informasi', 'ini', 'itu', 'jelaskan',
            'lokasi', 'offset', 'pengelola', 'projek', 'project', 'proyek', 'sertifikat',
            'sudah', 'status', 'standar', 'tentang', 'tertentu', 'tolong', 'verifikasi',
            'yang', 'siapa', 'memiliki', 'tersedia', 'kepada', 'saya', 'direkomendasikan',
            'rekomendasi', 'mengapa', 'kenapa', 'alasan',
        ];

        return collect(explode(' ', $value))
            ->filter(fn (string $token) => strlen($token) >= 3)
            ->reject(fn (string $token) => in_array($token, $ignored, true))
            ->unique()
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function projectCard(Project $project, bool $withRecommendation = false): array
    {
        $card = [
            'id' => $project->id,
            'name' => $project->name,
            'category' => $project->category,
            'location' => $project->location,
            'price_per_ton' => (float) $project->price_per_ton,
            'stock_available' => (int) $project->stock_available,
            'url' => route('projects.show', $project->id),
        ];

        if ($withRecommendation) {
            $card['score'] = $project->recommendation_score;
            $card['reasons'] = $project->recommendation_reasons;
        }

        return $card;
    }

    /**
     * @return array{text: string, custom: array<string, mixed>}
     */
    private function projectNameRequired(string $text): array
    {
        $projects = Project::query()
            ->approved()
            ->where('stock_available', '>', 0)
            ->orderBy('name')
            ->limit(3)
            ->get();

        if ($projects->isNotEmpty()) {
            $text .= '\nContoh proyek tersedia: '.$projects->pluck('name')->implode(', ').'.';
        }

        return [
            'text' => $text,
            'custom' => [
                'type' => 'project_name_required',
                'recommendations' => $projects->map(
                    fn (Project $project): array => $this->projectCard($project)
                )->values()->all(),
                'heading' => 'Pilih proyek yang ingin ditanyakan',
            ],
        ];
    }

    private function formatDecimal(float $value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', '.');
    }
}

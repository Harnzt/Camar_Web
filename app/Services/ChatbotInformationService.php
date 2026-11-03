<?php

namespace App\Services;

use App\Models\EmissionCalculation;
use App\Models\Order;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ChatbotInformationService
{
    public function __construct(
        private readonly ProjectRecommendationService $recommendationService,
        private readonly ?ProjectPurchaseDisclosureService $purchaseDisclosureService = null
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
            'carbon_balance_request' => $this->carbonBalance($user),
            'offset_simulation_request' => $this->simulateOffsetPurchase($query, $user),
            'project_comparison_request' => $this->compareProjects($query),
            'personal_action_plan_request' => $this->personalActionPlan($user),
            'purchase_disclosure_request' => $this->purchaseDisclosure($query),
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

        if ($project->verified_year) {
            $text .= "\nTahun verifikasi tercatat: {$project->verified_year}";
        }

        if ($project->duration_months) {
            $text .= "\nDurasi proyek: {$project->duration_months} bulan";
        }

        if ($project->area_ha) {
            $text .= "\nLuas area: ".$this->formatDecimal((float) $project->area_ha, 0).' hektare';
        }

        if ($project->co2_per_year) {
            $text .= "\nEstimasi dampak: ".$this->formatDecimal((float) $project->co2_per_year, 0).' ton CO2e per tahun';
        }

        if ($project->families_impacted) {
            $text .= "\nKeluarga terdampak: ".$this->formatDecimal((float) $project->families_impacted, 0);
        }

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

    /**
     * @return array{text: string, custom: array<string, mixed>, buttons?: array<int, array{title: string, payload: string}>}
     */
    private function purchaseDisclosure(string $query): array
    {
        $project = $this->resolveProject($query);
        if (! $project) {
            return $this->projectNameRequired(
                'Sebutkan nama proyek yang ingin diperiksa sebelum membeli. Cami akan menjelaskan harga, PPN, risiko, dan ketentuan transaksinya.'
            );
        }

        $parsedTon = $this->parseTon($query);
        if ($parsedTon !== null && (floor($parsedTon) !== $parsedTon || $parsedTon < 1)) {
            return [
                'text' => 'Jumlah pembelian harus berupa ton utuh minimal 1 ton, misalnya "cek biaya 2 ton proyek '.$project->name.'".',
                'custom' => ['type' => 'purchase_disclosure_invalid_quantity'],
            ];
        }

        $quantity = $parsedTon === null ? 1 : (int) $parsedTon;
        $disclosure = $this->purchaseDisclosureService()->forProject($project, $quantity);
        $text = "Sebelum membeli {$project->name}, periksa ringkasan ini:\n"
            .'Harga: Rp '.$this->formatDecimal($disclosure['price_per_ton'], 0)." per ton\n"
            .'Jumlah simulasi: '.$quantity." ton\n"
            .'Subtotal: Rp '.$this->formatDecimal($disclosure['subtotal'], 0)."\n"
            .'PPN 11%: Rp '.$this->formatDecimal($disclosure['tax'], 0)."\n"
            .'Biaya platform lain pada kode checkout: Rp 0'."\n"
            .'Estimasi total: Rp '.$this->formatDecimal($disclosure['estimated_total'], 0)."\n"
            .'Stok saat ini: '.$this->formatDecimal($disclosure['stock_available'], 0)." ton.\n"
            .'Risiko utama: harga dan stok dapat berubah; saldo emisi baru berkurang setelah transaksi berhasil; metadata standar perlu diperiksa bersama bukti registrinya.'."\n"
            .'Ketentuan utama: login sebagai buyer, sudah memiliki hasil emisi, membeli minimal 1 ton utuh, dan stok harus cukup. Pembayaran menggunakan Midtrans.'."\n"
            .'Kebijakan pembatalan atau pengembalian dana belum tersedia pada data yang dapat dibaca Cami. Nilai final tetap mengikuti halaman checkout.';

        return [
            'text' => $text,
            'custom' => [
                'type' => 'purchase_disclosure_result',
                'project_id' => $project->id,
                'purchase_disclosure' => $disclosure,
                'recommendations' => [$this->projectCard($project, false, $quantity)],
                'heading' => 'Periksa sebelum membeli',
            ],
        ];
    }

    /**
     * @return array{text: string, custom: array<string, mixed>, buttons?: array<int, array{title: string, payload: string}>}
     */
    private function carbonBalance(?User $user): array
    {
        if (! $user?->isBuyer()) {
            return [
                'text' => 'Masuk sebagai buyer terlebih dahulu agar Cami dapat membaca saldo emisi akunmu.',
                'custom' => ['type' => 'carbon_balance_error'],
            ];
        }

        $emission = $this->latestEmission($user);
        if (! $emission) {
            return [
                'text' => 'Akunmu belum memiliki hasil emisi tersimpan. Hitung emisi terlebih dahulu agar Cami dapat membuat saldo karbon.',
                'custom' => ['type' => 'carbon_balance_empty'],
                'buttons' => [[
                    'title' => 'Mulai hitung emisi',
                    'payload' => '/start_account_calculation',
                ]],
            ];
        }

        $position = $this->carbonPosition($user, $emission);
        $text = "Saldo karbon akunmu:\n"
            .'Total emisi terakhir: '.$this->formatDecimal($position['total_kg'])." kg CO2e\n"
            .'Offset berhasil: '.$this->formatDecimal($position['offset_kg'])." kg CO2e\n"
            .'Sisa emisi: '.$this->formatDecimal($position['remaining_kg'])." kg CO2e\n"
            .'Progres offset: '.$this->formatDecimal($position['offset_percentage'], 1).'%.';

        if ($position['remaining_kg'] > 0) {
            $text .= "\nUntuk menutup sisa tersebut melalui marketplace CAMAR, kamu memerlukan minimal {$position['required_ton']} ton kredit karena pembelian menggunakan satuan ton utuh.";
        } elseif ($position['over_offset_kg'] > 0) {
            $text .= "\nOffsetmu melebihi emisi terakhir sebesar "
                .$this->formatDecimal($position['over_offset_kg']).' kg CO2e.';
        } else {
            $text .= '\nEmisi terakhir sudah tertutup sepenuhnya.';
        }

        if ($position['pending_ton'] > 0) {
            $text .= "\nAda ".$this->formatDecimal($position['pending_ton'], 0)
                .' ton pesanan pending. Nilai ini belum mengurangi saldo sampai pembayarannya berhasil.';
        }

        return [
            'text' => $text,
            'custom' => array_merge([
                'type' => 'carbon_balance_result',
                'calculation_id' => $emission->id,
                'dashboard_url' => route('dashboard'),
            ], $position),
            'buttons' => [
                [
                    'title' => 'Rekomendasi untuk sisa emisi',
                    'payload' => 'proyek termurah untuk sisa emisi saya di dashboard',
                ],
                [
                    'title' => 'Simulasi beli 1 ton',
                    'payload' => 'simulasikan pembelian 1 ton kredit karbon',
                ],
            ],
        ];
    }

    /**
     * @return array{text: string, custom: array<string, mixed>, buttons?: array<int, array{title: string, payload: string}>}
     */
    private function simulateOffsetPurchase(string $query, ?User $user): array
    {
        if (! $user?->isBuyer()) {
            return [
                'text' => 'Masuk sebagai buyer terlebih dahulu untuk mencoba simulasi pembelian kredit karbon.',
                'custom' => ['type' => 'offset_simulation_error'],
            ];
        }

        $emission = $this->latestEmission($user);
        if (! $emission) {
            return [
                'text' => 'Simulasi memerlukan hasil emisi tersimpan. Hitung emisi terlebih dahulu.',
                'custom' => ['type' => 'offset_simulation_empty'],
            ];
        }

        $plannedTon = $this->parseTon($query);
        if ($plannedTon === null || $plannedTon <= 0) {
            return [
                'text' => 'Sebutkan jumlah kredit yang ingin disimulasikan, misalnya "simulasikan pembelian 2 ton proyek Mangrove Blue Carbon Riau".',
                'custom' => ['type' => 'offset_simulation_quantity_required'],
            ];
        }

        if (floor($plannedTon) !== $plannedTon) {
            return [
                'text' => 'Marketplace CAMAR saat ini menerima jumlah ton utuh. Gunakan angka seperti 1 ton atau 2 ton untuk simulasi.',
                'custom' => ['type' => 'offset_simulation_invalid_quantity'],
            ];
        }

        $project = $this->resolveProject($query);
        if (! $project) {
            $project = $this->recommendationService
                ->recommend($user, $emission, 1)
                ->first();
        }

        if (! $project) {
            return [
                'text' => 'Belum ada proyek disetujui dengan stok tersedia untuk disimulasikan.',
                'custom' => ['type' => 'offset_simulation_empty'],
            ];
        }

        $plannedTon = (int) $plannedTon;
        if ($plannedTon > (int) $project->stock_available) {
            return [
                'text' => "Stok {$project->name} hanya {$project->stock_available} ton, sehingga simulasi {$plannedTon} ton belum dapat dilakukan. Kurangi jumlahnya atau pilih proyek lain.",
                'custom' => [
                    'type' => 'offset_simulation_stock_error',
                    'recommendations' => [$this->projectCard($project)],
                    'heading' => 'Stok proyek belum mencukupi',
                ],
            ];
        }

        $position = $this->carbonPosition($user, $emission);
        $plannedKg = $plannedTon * 1000;
        $remainingAfterKg = max(0, $position['remaining_kg'] - $plannedKg);
        $overOffsetAfterKg = max(0, $plannedKg - $position['remaining_kg']);
        $disclosure = $this->purchaseDisclosureService()->forProject($project, $plannedTon);
        $estimatedCost = (float) $disclosure['estimated_total'];
        $card = array_merge($this->projectCard($project, false, $plannedTon), [
            'simulated_ton' => $plannedTon,
            'estimated_total_cost' => round($estimatedCost, 2),
            'remaining_after_kg' => round($remainingAfterKg, 2),
            'over_offset_after_kg' => round($overOffsetAfterKg, 2),
        ]);

        $text = "Simulasi {$plannedTon} ton dari {$project->name}:\n"
            .'Sisa emisi sebelum pembelian: '.$this->formatDecimal($position['remaining_kg'])." kg CO2e\n"
            .'Subtotal kredit: Rp '.$this->formatDecimal((float) $disclosure['subtotal'], 0)."\n"
            .'PPN 11%: Rp '.$this->formatDecimal((float) $disclosure['tax'], 0)."\n"
            .'Estimasi total: Rp '.$this->formatDecimal($estimatedCost, 0)."\n"
            .'Perkiraan sisa setelah pesanan berhasil: '.$this->formatDecimal($remainingAfterKg).' kg CO2e.';

        if ($overOffsetAfterKg > 0) {
            $text .= "\nPembelian ini akan melebihi sisa emisi sebesar "
                .$this->formatDecimal($overOffsetAfterKg).' kg CO2e.';
        }

        $text .= '\nIni hanya simulasi dan belum membuat pesanan. Saldo baru berubah setelah transaksi berstatus dibayar, diverifikasi, atau selesai.';

        return [
            'text' => $text,
            'custom' => [
                'type' => 'offset_simulation_result',
                'calculation_id' => $emission->id,
                'remaining_before_kg' => $position['remaining_kg'],
                'planned_ton' => $plannedTon,
                'planned_kg' => $plannedKg,
                'remaining_after_kg' => round($remainingAfterKg, 2),
                'over_offset_after_kg' => round($overOffsetAfterKg, 2),
                'estimated_total_cost' => round($estimatedCost, 2),
                'recommendations' => [$card],
                'heading' => 'Simulasi offset',
            ],
            'buttons' => [[
                'title' => 'Lihat saldo emisi',
                'payload' => 'berapa sisa emisi saya',
            ]],
        ];
    }

    /**
     * @return array{text: string, custom: array<string, mixed>}
     */
    private function compareProjects(string $query): array
    {
        $projects = $this->resolveProjects($query);
        if ($projects->count() < 2) {
            $examples = Project::query()
                ->approved()
                ->where('stock_available', '>', 0)
                ->orderBy('name')
                ->limit(4)
                ->get();

            return [
                'text' => 'Sebutkan dua nama proyek yang ingin dibandingkan, misalnya "bandingkan Proyek A dan Proyek B".',
                'custom' => [
                    'type' => 'project_comparison_names_required',
                    'recommendations' => $examples->map(
                        fn (Project $project): array => $this->projectCard($project)
                    )->values()->all(),
                    'heading' => 'Pilih dua proyek',
                ],
            ];
        }

        /** @var Project $first */
        $first = $projects->get(0);
        /** @var Project $second */
        $second = $projects->get(1);
        $cheaper = (float) $first->price_per_ton <= (float) $second->price_per_ton ? $first : $second;
        $largerStock = (int) $first->stock_available >= (int) $second->stock_available ? $first : $second;

        $text = "Perbandingan {$first->name} dan {$second->name}:\n"
            ."{$first->name}: Rp ".$this->formatDecimal((float) $first->price_per_ton, 0)
            ."/ton, stok {$first->stock_available} ton, lokasi ".($first->location ?: '-').'.'."\n"
            ."{$second->name}: Rp ".$this->formatDecimal((float) $second->price_per_ton, 0)
            ."/ton, stok {$second->stock_available} ton, lokasi ".($second->location ?: '-').'.'."\n"
            .$cheaper->name.' memiliki harga per ton lebih rendah atau sama, sedangkan '
            .$largerStock->name.' memiliki stok lebih besar atau sama. Pilih juga berdasarkan lokasi, metodologi, dampak, dan bukti registrinya.';

        return [
            'text' => $text,
            'custom' => [
                'type' => 'project_comparison_result',
                'recommendations' => [
                    $this->projectCard($first),
                    $this->projectCard($second),
                ],
                'heading' => 'Perbandingan proyek',
            ],
        ];
    }

    /**
     * @return array{text: string, custom: array<string, mixed>, buttons?: array<int, array{title: string, payload: string}>}
     */
    private function personalActionPlan(?User $user): array
    {
        if (! $user?->isBuyer()) {
            return [
                'text' => 'Masuk sebagai buyer agar Cami dapat membuat rencana pengurangan emisi dari hasil akunmu.',
                'custom' => ['type' => 'personal_action_plan_error'],
            ];
        }

        $emission = $this->latestEmission($user);
        if (! $emission) {
            return [
                'text' => 'Cami memerlukan hasil emisi tersimpan untuk membuat rencana tindakan pribadi. Hitung emisi terlebih dahulu.',
                'custom' => ['type' => 'personal_action_plan_empty'],
            ];
        }

        $totalKg = (float) $emission->total_kg;
        $activities = $this->emissionActivities($emission)->take(3);
        if ($activities->isEmpty()) {
            $activities = collect([
                ['scope' => 'scope1', 'label' => 'Scope 1', 'value_kg' => (float) $emission->scope1_kg],
                ['scope' => 'scope2', 'label' => 'Scope 2', 'value_kg' => (float) $emission->scope2_kg],
                ['scope' => 'scope3', 'label' => 'Scope 3', 'value_kg' => (float) $emission->scope3_kg],
            ])->filter(fn (array $item): bool => $item['value_kg'] > 0)
                ->sortByDesc('value_kg')
                ->take(3)
                ->values();
        }

        $steps = $activities->values()->map(function (array $activity, int $index) use ($totalKg): array {
            $value = (float) $activity['value_kg'];

            return [
                'rank' => $index + 1,
                'scope' => $activity['scope'],
                'label' => $activity['label'],
                'emission_kg' => round($value, 2),
                'contribution_percentage' => $totalKg > 0 ? round(($value / $totalKg) * 100, 1) : 0,
                'advice' => $this->reductionAdvice($activity['label']),
            ];
        });

        $text = "Tiga prioritas Carbon Coach berdasarkan hasil emisi terakhirmu:\n";
        foreach ($steps as $step) {
            $text .= "{$step['rank']}. {$step['label']} ({$this->formatDecimal($step['emission_kg'])} kg CO2e; {$this->formatDecimal($step['contribution_percentage'], 1)}%): {$step['advice']}\n";
        }
        $text .= 'Lakukan perubahan secara bertahap, lalu hitung ulang agar dampaknya dapat dibandingkan.';

        return [
            'text' => trim($text),
            'custom' => [
                'type' => 'personal_action_plan_result',
                'calculation_id' => $emission->id,
                'total_kg' => round($totalKg, 2),
                'steps' => $steps->all(),
            ],
            'buttons' => [[
                'title' => 'Hitung ulang emisi',
                'payload' => '/start_account_calculation',
            ]],
        ];
    }

    /**
     * @return array{total_kg: float, offset_kg: float, offset_ton: float, remaining_kg: float, offset_percentage: float, required_ton: int, pending_ton: float, over_offset_kg: float}
     */
    private function carbonPosition(User $user, EmissionCalculation $emission): array
    {
        $totalKg = max(0, (float) $emission->total_kg);
        $offsetTon = max(0, $this->successfulOffsetTon($user));
        $offsetKg = $offsetTon * 1000;
        $remainingKg = max(0, $totalKg - $offsetKg);

        return [
            'total_kg' => round($totalKg, 2),
            'offset_kg' => round($offsetKg, 2),
            'offset_ton' => round($offsetTon, 3),
            'remaining_kg' => round($remainingKg, 2),
            'offset_percentage' => $totalKg > 0 ? round(min(100, ($offsetKg / $totalKg) * 100), 1) : 0,
            'required_ton' => (int) ceil($remainingKg / 1000),
            'pending_ton' => round(max(0, $this->pendingOffsetTon($user)), 3),
            'over_offset_kg' => round(max(0, $offsetKg - $totalKg), 2),
        ];
    }

    protected function successfulOffsetTon(User $user): float
    {
        return (float) Order::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['paid', 'verified', 'completed'])
            ->sum('quantity');
    }

    protected function pendingOffsetTon(User $user): float
    {
        return (float) Order::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('quantity');
    }

    protected function parseTon(string $query): ?float
    {
        if (! preg_match('/(\d+(?:[.,]\d+)?)\s*(?:ton|tco2e)\b/iu', $query, $matches)) {
            return null;
        }

        return (float) str_replace(',', '.', $matches[1]);
    }

    /**
     * @return Collection<int, Project>
     */
    protected function resolveProjects(string $query): Collection
    {
        $projects = Project::query()->approved()->get();
        $queryText = $this->normaliseText($query);
        $queryTokens = $this->meaningfulTokens($queryText);

        return $projects->map(function (Project $project) use ($queryText, $queryTokens): array {
            $name = $this->normaliseText($project->name);
            $haystackTokens = $this->meaningfulTokens($this->normaliseText(implode(' ', [
                $project->name,
                $project->company_name,
                $project->category,
                $project->location,
            ])));
            $exactScore = $name !== '' && Str::contains($queryText, $name) ? 1000 : 0;

            return [
                'project' => $project,
                'score' => $exactScore + ($queryTokens->intersect($haystackTokens)->count() * 20),
            ];
        })->filter(fn (array $item): bool => $item['score'] > 0)
            ->sortByDesc('score')
            ->pluck('project')
            ->unique(fn (Project $project): mixed => $project->id ?? $project->name)
            ->take(2)
            ->values();
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
            'bandingkan', 'dibanding', 'versus', 'compare', 'harga', 'stok', 'dampak',
            'manfaat', 'metode', 'metodologi', 'luas', 'area', 'durasi', 'keluarga',
            'tahun', 'beli', 'pembelian', 'simulasi', 'kredit', 'dengan', 'antara',
            'biaya', 'tambahan', 'risiko', 'ketentuan', 'transaksi', 'pajak', 'ppn',
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
    private function projectCard(
        Project $project,
        bool $withRecommendation = false,
        int $quantity = 1
    ): array {
        $card = [
            'id' => $project->id,
            'name' => $project->name,
            'company_name' => $project->company_name,
            'category' => $project->category,
            'location' => $project->location,
            'price_per_ton' => (float) $project->price_per_ton,
            'stock_available' => (int) $project->stock_available,
            'standard' => $project->standard,
            'verified_year' => $project->verified_year,
            'duration_months' => $project->duration_months,
            'area_ha' => $project->area_ha,
            'co2_per_year' => $project->co2_per_year,
            'families_impacted' => $project->families_impacted,
            'description' => $project->description
                ? Str::limit(strip_tags($project->description), 220)
                : null,
            'methodology' => $project->methodology
                ? Str::limit(strip_tags($project->methodology), 180)
                : null,
            'purchase_disclosure' => $this->purchaseDisclosureService()->forProject(
                $project,
                $quantity
            ),
            'url' => route('projects.show', $project->id),
        ];

        if ($withRecommendation) {
            $card['score'] = $project->recommendation_score;
            $card['reasons'] = $project->recommendation_reasons;
            $card['recommendation_breakdown'] = $project->recommendation_breakdown;
            $card['dominant_scope'] = $project->dominant_scope;
        }

        return $card;
    }

    private function purchaseDisclosureService(): ProjectPurchaseDisclosureService
    {
        return $this->purchaseDisclosureService
            ?? app(ProjectPurchaseDisclosureService::class);
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

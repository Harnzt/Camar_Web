<?php

namespace App\Services;

use App\Models\EmissionCalculation;
use App\Models\Order;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ChatbotProjectQueryService
{
    public function __construct(
        private readonly ProjectRecommendationService $recommendationService
    ) {}

    public function answer(string $message, ?User $user = null): ?array
    {
        $mode = $this->detectMode($message);
        if ($mode === null) {
            return null;
        }

        if ($mode === 'cheapest_offset') {
            return $this->cheapestOffset($user);
        }

        $available = Project::query()
            ->approved()
            ->where('stock_available', '>', 0)
            ->where('price_per_ton', '>=', 0);

        if ($mode === 'count') {
            $count = $available->count();

            return [
                'text' => $count > 0
                    ? 'Saat ini ada '.$this->formatNumber($count).' proyek offset terverifikasi dengan stok kredit tersedia.'
                    : 'Saat ini belum ada proyek offset terverifikasi dengan stok kredit tersedia.',
                'custom' => [
                    'type' => 'project_catalog_result',
                    'mode' => $mode,
                    'count' => $count,
                ],
            ];
        }

        $projects = match ($mode) {
            'most_purchased', 'most_orders' => $this->popularProjects($mode),
            'cheapest' => $available->orderBy('price_per_ton')->orderBy('id')->limit(3)->get(),
            'most_expensive' => $available->orderByDesc('price_per_ton')->orderBy('id')->limit(3)->get(),
            'most_stock' => $available->orderByDesc('stock_available')->orderBy('id')->limit(3)->get(),
            default => $available->latest('id')->limit(3)->get(),
        };

        if ($projects->isEmpty()) {
            return [
                'text' => in_array($mode, ['most_purchased', 'most_orders'], true)
                    ? 'Belum ada pembelian berhasil untuk proyek offset yang sudah disetujui.'
                    : 'Saat ini belum ada proyek offset terverifikasi dengan stok kredit tersedia.',
                'custom' => [
                    'type' => 'project_catalog_result',
                    'mode' => $mode,
                    'recommendations' => [],
                ],
            ];
        }

        $first = $projects->first();
        $heading = match ($mode) {
            'cheapest' => 'Proyek dengan harga termurah',
            'most_expensive' => 'Proyek dengan harga tertinggi',
            'most_stock' => 'Proyek dengan stok terbanyak',
            'most_purchased' => 'Proyek paling banyak dibeli (ton)',
            'most_orders' => 'Proyek paling sering dibeli (pesanan)',
            default => 'Proyek yang tersedia',
        };
        $text = match ($mode) {
            'cheapest' => 'Proyek termurah yang tersedia saat ini adalah '.$first->name
                .' dengan harga Rp '.$this->formatNumber($first->price_per_ton).' per ton.',
            'most_expensive' => 'Proyek dengan harga tertinggi saat ini adalah '.$first->name
                .' dengan harga Rp '.$this->formatNumber($first->price_per_ton).' per ton.',
            'most_stock' => 'Proyek dengan stok kredit terbanyak saat ini adalah '.$first->name
                .' dengan stok '.$this->formatNumber($first->stock_available).' ton.',
            'most_purchased' => 'Proyek paling banyak dibeli berdasarkan total kredit terjual adalah '
                .$first->name.': '.$this->formatNumber($first->purchased_ton).' ton dari '
                .$this->formatNumber($first->purchase_count).' pesanan berhasil.',
            'most_orders' => 'Proyek paling sering dibeli berdasarkan jumlah pesanan berhasil adalah '
                .$first->name.': '.$this->formatNumber($first->purchase_count).' pesanan ('
                .$this->formatNumber($first->purchased_ton).' ton).',
            default => 'Berikut beberapa proyek offset terverifikasi yang tersedia saat ini:',
        };

        if (in_array($mode, ['most_purchased', 'most_orders'], true) && $first->stock_available <= 0) {
            $text .= ' Stok proyek ini sudah habis.';
        }

        return [
            'text' => $text,
            'custom' => [
                'type' => 'project_catalog_result',
                'mode' => $mode,
                'heading' => $heading,
                'recommendations' => $projects->map(function (Project $project) use ($mode): array {
                    $card = [
                        'id' => $project->id,
                        'name' => $project->name,
                        'category' => $project->category,
                        'location' => $project->location,
                        'price_per_ton' => (float) $project->price_per_ton,
                        'stock_available' => (int) $project->stock_available,
                        'url' => route('projects.show', $project->id),
                    ];

                    if (in_array($mode, ['most_purchased', 'most_orders'], true)) {
                        $card['purchased_ton'] = (int) $project->purchased_ton;
                        $card['purchase_count'] = (int) $project->purchase_count;
                    }

                    return $card;
                })->values()->all(),
            ],
        ];
    }

    private function cheapestOffset(?User $user): array
    {
        $custom = [
            'type' => 'project_catalog_result',
            'mode' => 'cheapest_offset',
            'heading' => 'Proyek termurah untuk sisa emisi Anda',
            'recommendations' => [],
        ];

        if (! $user?->isBuyer()) {
            return [
                'text' => 'Masuk sebagai buyer untuk mencari proyek termurah sesuai sisa emisi di dashboardmu.',
                'custom' => $custom,
            ];
        }

        $emission = EmissionCalculation::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if (! $emission) {
            return [
                'text' => 'Dashboardmu belum memiliki hasil emisi. Hitung emisi dahulu agar Cami dapat mencari proyek termurah yang cukup untuk offset.',
                'custom' => $custom,
            ];
        }

        $totalKg = (float) $emission->total_kg;
        $offsetTon = (float) Order::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['paid', 'verified', 'completed'])
            ->sum('quantity');
        $remainingKg = max(0, $totalKg - ($offsetTon * 1000));
        $requiredTon = (int) ceil($remainingKg / 1000);

        $custom += [
            'calculation_id' => $emission->id,
            'total_kg' => round($totalKg, 2),
            'already_offset_ton' => $offsetTon,
            'remaining_kg' => round($remainingKg, 2),
            'required_ton' => $requiredTon,
        ];

        if ($requiredTon === 0) {
            return [
                'text' => 'Sisa emisi pada dashboardmu sudah 0 kg CO2e. Belum perlu membeli kredit offset tambahan.',
                'custom' => $custom,
            ];
        }

        $projects = Project::query()
            ->approved()
            ->where('stock_available', '>=', $requiredTon)
            ->where('price_per_ton', '>=', 0)
            ->orderBy('price_per_ton')
            ->orderBy('id')
            ->limit(3)
            ->get();

        if ($projects->isEmpty()) {
            return [
                'text' => 'Sisa emisi di dashboardmu '.$this->formatDecimal($remainingKg)
                    .' kg CO2e membutuhkan '.$this->formatNumber($requiredTon)
                    .' ton kredit. Saat ini belum ada satu proyek disetujui dengan stok cukup untuk menutup semuanya.',
                'custom' => $custom,
            ];
        }

        $cheapest = $projects->first();
        $subtotal = $requiredTon * (float) $cheapest->price_per_ton;
        $remainingEmission = clone $emission;
        $remainingEmission->total_kg = $remainingKg;
        $projects->each(fn (Project $project) => $this->recommendationService
            ->scoreProject($project, $user, $remainingEmission));
        $custom['recommendations'] = $projects->map(fn (Project $project) => [
            'id' => $project->id,
            'name' => $project->name,
            'category' => $project->category,
            'location' => $project->location,
            'price_per_ton' => (float) $project->price_per_ton,
            'stock_available' => (int) $project->stock_available,
            'required_ton' => $requiredTon,
            'total_offset_cost' => round($requiredTon * (float) $project->price_per_ton, 2),
            'score' => $project->recommendation_score,
            'reasons' => $project->recommendation_reasons,
            'url' => route('projects.show', $project->id),
        ])->values()->all();

        return [
            'text' => 'Dashboardmu mencatat '.$this->formatDecimal($totalKg)
                .' kg CO2e dari perhitungan terakhir, dengan '.$this->formatNumber($offsetTon)
                .' ton sudah ter-offset. Sisa '.$this->formatDecimal($remainingKg)
                .' kg membutuhkan '.$this->formatNumber($requiredTon)
                .' ton kredit utuh. Proyek termurah dengan stok cukup adalah '
                .$cheapest->name.' (Rp '.$this->formatNumber($cheapest->price_per_ton)
                .' per ton); perkiraan biaya kredit Rp '.$this->formatNumber($subtotal)
                .' sebelum pajak dan biaya checkout.',
            'custom' => $custom,
        ];
    }

    private function popularProjects(string $mode): Collection
    {
        $successfulOrders = fn ($query) => $query->whereIn('status', [
            'paid', 'verified', 'completed',
        ]);

        $query = Project::query()
            ->approved()
            ->whereHas('orders', $successfulOrders)
            ->withSum(['orders as purchased_ton' => $successfulOrders], 'quantity')
            ->withCount(['orders as purchase_count' => $successfulOrders]);

        if ($mode === 'most_purchased') {
            $query->orderByDesc('purchased_ton')->orderByDesc('purchase_count');
        } else {
            $query->orderByDesc('purchase_count')->orderByDesc('purchased_ton');
        }

        return $query->orderBy('id')->limit(3)->get();
    }

    private function detectMode(string $message): ?string
    {
        $message = Str::lower($message);
        if (! Str::contains($message, ['proyek', 'projek', 'project', 'produk'])) {
            return null;
        }

        if (Str::contains($message, [
            'termurah', 'paling murah', 'proyek murah', 'produk murah',
            'harga terendah', 'harga paling rendah', 'paling hemat',
        ]) && Str::contains($message, [
            'emisi saya', 'emisi aku', 'emisi yg', 'emisi yang',
            'total emisi', 'sisa emisi', 'untuk emisi', 'offset emisi',
            'jejak karbon saya', 'jejak karbonku', 'dashboard',
            'profil', 'profile', 'offset saya', 'offsetku', 'kebutuhan offset',
        ])) {
            return 'cheapest_offset';
        }

        if (Str::contains($message, [
            'termurah', 'paling murah', 'proyek murah', 'harga terendah',
            'harga paling rendah', 'paling hemat',
        ])) {
            return 'cheapest';
        }

        if (Str::contains($message, [
            'termahal', 'paling mahal', 'proyek mahal', 'harga tertinggi',
            'harga paling tinggi',
        ])) {
            return 'most_expensive';
        }

        if (Str::contains($message, [
            'stok terbanyak', 'stok paling banyak', 'stoknya paling banyak',
            'stok terbesar', 'kredit terbanyak',
        ])) {
            return 'most_stock';
        }

        $message = preg_replace('/\bdi\s+beli\b/u', 'dibeli', $message);
        if (Str::contains($message, [
            'paling sering dibeli', 'pesanan terbanyak', 'transaksi terbanyak',
            'terpopuler', 'paling populer',
        ])) {
            return 'most_orders';
        }

        if (Str::contains($message, [
            'paling banyak dibeli', 'terbanyak dibeli', 'terlaris',
            'paling laris', 'paling banyak terjual', 'penjualan terbanyak',
        ])) {
            return 'most_purchased';
        }

        if (Str::contains($message, [
            'berapa proyek', 'berapa projek', 'berapa banyak proyek',
            'ada berapa proyek', 'jumlah proyek', 'total proyek',
        ])) {
            return 'count';
        }

        if (Str::contains($message, [
            'proyek apa saja', 'daftar proyek', 'proyek yang tersedia',
            'tampilkan proyek',
        ]) && ! Str::contains($message, [
            'cocok', 'sesuai', 'rekomendas', 'emisi', 'jejak karbon',
        ])) {
            return 'available';
        }

        return null;
    }

    private function formatNumber(float|int|string $value): string
    {
        return number_format((float) $value, 0, ',', '.');
    }

    private function formatDecimal(float $value): string
    {
        return number_format($value, 2, ',', '.');
    }
}

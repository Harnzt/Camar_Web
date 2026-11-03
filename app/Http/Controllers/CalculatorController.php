<?php

namespace App\Http\Controllers;

use App\Models\EmissionCalculation;
use App\Services\ProjectRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CalculatorController extends Controller
{
    public function __construct(
        private readonly ProjectRecommendationService $recommendationService
    ) {}

    public function show()
    {
        return view('main_page.calculator.calculator');
    }

    public function airports(Request $request)
    {
        if (! Schema::hasTable('airports_data')) {
            return response()->json([]);
        }

        $search = trim((string) $request->query('search', ''));
        $limit = min(max((int) $request->query('limit', 12), 1), 20);

        if (mb_strlen($search) < 2) {
            return response()->json([]);
        }

        $normalisedSearch = mb_strtolower($search);
        $cacheKey = 'airport_search:'.$limit.':'.md5($normalisedSearch);

        $airports = Cache::remember($cacheKey, now()->addHours(6), function () use ($search, $limit) {
            $iataSearch = strtoupper($search).'%';
            $nameSearch = '%'.$search.'%';

            return DB::table('airports_data')
                ->select('id', 'name', 'latitude_deg', 'longitude_deg', 'iata_code')
                ->whereNotNull('iata_code')
                ->where('iata_code', '<>', '')
                ->where(function ($query) use ($iataSearch, $nameSearch) {
                    $query->where('iata_code', 'like', $iataSearch)
                        ->orWhere('name', 'like', $nameSearch);
                })
                ->orderByRaw('CASE WHEN iata_code LIKE ? THEN 0 ELSE 1 END', [$iataSearch])
                ->orderBy('iata_code')
                ->limit($limit)
                ->get()
                ->map(fn ($airport) => [
                    'id' => (int) $airport->id,
                    'name' => $airport->name,
                    'latitude_deg' => (float) $airport->latitude_deg,
                    'longitude_deg' => (float) $airport->longitude_deg,
                    'iata_code' => strtoupper((string) $airport->iata_code),
                    'display' => strtoupper((string) $airport->iata_code).' - '.$airport->name,
                ])
                ->values()
                ->all();
        });

        return response()->json($airports);
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();
            $validated = $request->validate([
                'mode' => ['nullable', 'in:personal,company'],
                'total_kg' => ['required', 'numeric', 'min:0.0001'],
                'details' => ['required', 'array'],
                'details.*' => ['nullable', 'numeric', 'min:0'],
                'scope_details' => ['nullable', 'array'],
                'scope_details.scope1' => ['nullable', 'array'],
                'scope_details.scope2' => ['nullable', 'array'],
                'scope_details.scope3' => ['nullable', 'array'],
                'scope_details.scope1.*.source' => ['required_with:scope_details.scope1.*', 'string', 'max:150'],
                'scope_details.scope1.*.activity' => ['required_with:scope_details.scope1.*', 'string', 'max:255'],
                'scope_details.scope1.*.quantity' => ['required_with:scope_details.scope1.*', 'numeric', 'min:0'],
                'scope_details.scope1.*.unit' => ['required_with:scope_details.scope1.*', 'string', 'max:80'],
                'scope_details.scope1.*.emission_factor' => ['required_with:scope_details.scope1.*', 'numeric', 'min:0'],
                'scope_details.scope1.*.value_kg' => ['required_with:scope_details.scope1.*', 'numeric', 'min:0'],
                'scope_details.scope2.*.source' => ['required_with:scope_details.scope2.*', 'string', 'max:150'],
                'scope_details.scope2.*.activity' => ['required_with:scope_details.scope2.*', 'string', 'max:255'],
                'scope_details.scope2.*.quantity' => ['required_with:scope_details.scope2.*', 'numeric', 'min:0'],
                'scope_details.scope2.*.unit' => ['required_with:scope_details.scope2.*', 'string', 'max:80'],
                'scope_details.scope2.*.emission_factor' => ['required_with:scope_details.scope2.*', 'numeric', 'min:0'],
                'scope_details.scope2.*.value_kg' => ['required_with:scope_details.scope2.*', 'numeric', 'min:0'],
                'scope_details.scope3.*.source' => ['required_with:scope_details.scope3.*', 'string', 'max:150'],
                'scope_details.scope3.*.activity' => ['required_with:scope_details.scope3.*', 'string', 'max:255'],
                'scope_details.scope3.*.quantity' => ['required_with:scope_details.scope3.*', 'numeric', 'min:0'],
                'scope_details.scope3.*.unit' => ['required_with:scope_details.scope3.*', 'string', 'max:80'],
                'scope_details.scope3.*.emission_factor' => ['required_with:scope_details.scope3.*', 'numeric', 'min:0'],
                'scope_details.scope3.*.value_kg' => ['required_with:scope_details.scope3.*', 'numeric', 'min:0'],
                'scope_details.*.*.input_type' => ['nullable', 'string', 'max:100'],
                'scope_details.*.*.transport_mode' => ['nullable', 'string', 'max:80'],
                'scope_details.*.*.transport_class' => ['nullable', 'string', 'max:80'],
                'scope_details.*.*.distance_km' => ['nullable', 'numeric', 'min:0'],
                'scope_details.*.*.origin' => ['nullable', 'string', 'max:150'],
                'scope_details.*.*.origin_name' => ['nullable', 'string', 'max:255'],
                'scope_details.*.*.destination' => ['nullable', 'string', 'max:150'],
                'scope_details.*.*.destination_name' => ['nullable', 'string', 'max:255'],
                'scope_details.*.*.passengers' => ['nullable', 'numeric', 'min:0'],
                'scope_details.*.*.cabin_class' => ['nullable', 'string', 'max:80'],
            ]);

            $mode = $validated['mode'] ?? 'personal';
            $details = collect($validated['details'])
                ->map(fn ($value) => (float) ($value ?? 0));

            if ($mode === 'company') {
                $scope1Kg = $details->only(['stat', 'mobile'])->sum();
                $scope2Kg = $details->get('elec', 0);
                $scope3Kg = $details->only(['flight', 'hotel', 'train'])->sum();
                $scopeDetails = [
                    'scope1' => [
                        ['label' => 'Pembakaran stasioner', 'value_kg' => $details->get('stat', 0)],
                        ['label' => 'Kendaraan operasional', 'value_kg' => $details->get('mobile', 0)],
                    ],
                    'scope2' => [
                        ['label' => 'Konsumsi listrik', 'value_kg' => $details->get('elec', 0)],
                    ],
                    'scope3' => [
                        ['label' => 'Penerbangan Dinas Karyawan', 'value_kg' => $details->get('flight', 0)],
                        ['label' => 'Akomodasi hotel', 'value_kg' => $details->get('hotel', 0)],
                        ['label' => 'Perjalanan kereta', 'value_kg' => $details->get('train', 0)],
                    ],
                ];
            } else {
                $scope1Kg = $details->only(['energy_rt', 'vehicle'])->sum();
                $scope2Kg = $details->get('electricity', 0);
                $scope3Kg = $details->only(['flight', 'transit', 'food', 'water', 'waste'])->sum();
                $scopeDetails = [
                    'scope1' => [
                        ['label' => 'Energi rumah tangga', 'value_kg' => $details->get('energy_rt', 0)],
                        ['label' => 'Kendaraan pribadi', 'value_kg' => $details->get('vehicle', 0)],
                    ],
                    'scope2' => [
                        ['label' => 'Konsumsi listrik', 'value_kg' => $details->get('electricity', 0)],
                    ],
                    'scope3' => [
                        ['label' => 'Transportasi umum & pesawat', 'value_kg' => $details->get('transit', 0) + $details->get('flight', 0)],
                        ['label' => 'Konsumsi makanan', 'value_kg' => $details->get('food', 0)],
                        ['label' => 'Penggunaan air', 'value_kg' => $details->get('water', 0)],
                        ['label' => 'Pengelolaan limbah', 'value_kg' => $details->get('waste', 0)],
                    ],
                ];
            }

            $scopeDetails = $validated['scope_details'] ?? $scopeDetails;
            $personalFlightKg = $mode === 'personal'
                ? collect($scopeDetails['scope3'] ?? [])
                    ->filter(fn (array $item) => ($item['transport_mode'] ?? null) === 'plane'
                        || str_starts_with((string) ($item['transport_mode'] ?? ''), 'flight_')
                        || str_contains(strtolower((string) ($item['activity'] ?? '')), 'pesawat')
                        || str_contains(strtolower((string) ($item['source'] ?? '')), 'penerbangan'))
                    ->sum('value_kg')
                : 0;
            $categoryValues = $mode === 'company'
                ? [
                    'household_energy_kg' => 0,
                    'private_vehicle_kg' => 0,
                    'electricity_kg' => $details->get('elec', 0),
                    'public_transport_kg' => 0,
                    'personal_flight_kg' => 0,
                    'food_kg' => 0,
                    'water_kg' => 0,
                    'waste_kg' => 0,
                    'stationary_combustion_kg' => $details->get('stat', 0),
                    'operational_vehicle_kg' => $details->get('mobile', 0),
                    'business_flight_kg' => $details->get('flight', 0),
                    'hotel_accommodation_kg' => $details->get('hotel', 0),
                    'business_train_kg' => $details->get('train', 0),
                ]
                : [
                    'household_energy_kg' => $details->get('energy_rt', 0),
                    'private_vehicle_kg' => $details->get('vehicle', 0),
                    'electricity_kg' => $details->get('electricity', 0),
                    'public_transport_kg' => max(0, $details->get('transit', 0) - $personalFlightKg),
                    'personal_flight_kg' => $details->get('flight', 0) + $personalFlightKg,
                    'food_kg' => $details->get('food', 0),
                    'water_kg' => $details->get('water', 0),
                    'waste_kg' => $details->get('waste', 0),
                    'stationary_combustion_kg' => 0,
                    'operational_vehicle_kg' => 0,
                    'business_flight_kg' => 0,
                    'hotel_accommodation_kg' => 0,
                    'business_train_kg' => 0,
                ];
            $totalKg = $scope1Kg + $scope2Kg + $scope3Kg;
            $hargaPerTon = 150000;
            $totalTon = $totalKg / 1000;
            $estimasiBiaya = $totalTon * $hargaPerTon;

            $calculation = DB::transaction(function () use (
                $user,
                $mode,
                $scope1Kg,
                $scope2Kg,
                $scope3Kg,
                $totalKg,
                $totalTon,
                $estimasiBiaya,
                $hargaPerTon,
                $scopeDetails,
                $categoryValues
            ) {
                $calculation = EmissionCalculation::create([
                    'user_id' => $user->id,
                    'calculation_mode' => $mode,
                    'scope1_kg' => $scope1Kg,
                    'scope2_kg' => $scope2Kg,
                    'scope3_kg' => $scope3Kg,
                    'scope_details' => $scopeDetails,
                    ...$categoryValues,
                    'total_kg' => $totalKg,
                    'total_ton' => $totalTon,
                    'estimated_cost' => $estimasiBiaya,
                    'price_per_ton' => $hargaPerTon,
                ]);

                return $calculation;
            });

            $recommendedProject = $this->recommendationService
                ->recommend($user, $calculation, 1)
                ->first();
            $namaProyek = $recommendedProject ? $recommendedProject->name : 'Reforestasi Mangrove Pesisir';

            // 3. Kirim balik data untuk ditampilkan di halaman
            return response()->json([
                'success' => true,
                'data' => [
                    'total_kg' => number_format($totalKg, 2),
                    'biaya' => 'Rp '.number_format($estimasiBiaya, 0, ',', '.'),
                    'proyek' => $namaProyek,
                    'skor_rekomendasi' => $recommendedProject?->recommendation_score,
                    'alasan_rekomendasi' => $recommendedProject?->recommendation_reasons ?? [],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function clear(Request $request)
    {
        try {
            $user = $request->user();

            // Hapus semua data kalkulasi emisi milik user yang sedang login dari database
            EmissionCalculation::where('user_id', $user->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Semua riwayat kalkulasi di database berhasil dibersihkan.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data di server: '.$e->getMessage(),
            ], 500);
        }
    }
}

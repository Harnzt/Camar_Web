<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EmissionCalculation;
use App\Services\ProjectRecommendationService;

class CalculatorController extends Controller
{
    public function __construct(
        private readonly ProjectRecommendationService $recommendationService
    ) {
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $validated = $request->validate([
                'mode' => ['nullable', 'in:personal,company'],
                'total_kg' => ['required', 'numeric', 'min:0.0001'],
                'details' => ['required', 'array'],
                'details.*' => ['nullable', 'numeric', 'min:0'],
            ]);

            $mode = $validated['mode'] ?? 'personal';
            $details = collect($validated['details'])
                ->map(fn ($value) => (float) ($value ?? 0));

            if ($mode === 'company') {
                $scope1Kg = $details->only(['stat', 'mobile'])->sum();
                $scope2Kg = $details->get('elec', 0);
                $scope3Kg = $details->only(['flight', 'hotel', 'train'])->sum();
            } else {
                $scope1Kg = $details->only(['energy_rt', 'vehicle'])->sum();
                $scope2Kg = $details->get('electricity', 0);
                $scope3Kg = $details->only(['transit', 'food', 'water', 'waste'])->sum();
            }

            $totalKg = $scope1Kg + $scope2Kg + $scope3Kg;
            $hargaPerTon = 150000;
            $totalTon = $totalKg / 1000;
            $estimasiBiaya = $totalTon * $hargaPerTon;

            $calculation = EmissionCalculation::create([
                'user_id'        => $user->id,
                'scope1_kg'       => $scope1Kg,
                'scope2_kg'       => $scope2Kg,
                'scope3_kg'       => $scope3Kg,
                'total_kg'       => $totalKg,
                'total_ton'      => $totalTon,
                'estimated_cost' => $estimasiBiaya,
                'price_per_ton'  => $hargaPerTon,
            ]);

            $recommendedProject = $this->recommendationService
                ->recommend($user, $calculation, 1)
                ->first();
            $namaProyek = $recommendedProject ? $recommendedProject->name : 'Reforestasi Mangrove Pesisir';

            // 3. Kirim balik data untuk ditampilkan di halaman
            return response()->json([
                'success' => true,
                'data' => [
                    'total_kg' => number_format($totalKg, 2),
                    'biaya'    => 'Rp ' . number_format($estimasiBiaya, 0, ',', '.'),
                    'proyek'   => $namaProyek,
                    'skor_rekomendasi' => $recommendedProject?->recommendation_score,
                    'alasan_rekomendasi' => $recommendedProject?->recommendation_reasons ?? [],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function clear()
    {
        try {
            $user = Auth::user();

            // Hapus semua data kalkulasi emisi milik user yang sedang login dari database
            EmissionCalculation::where('user_id', $user->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Semua riwayat kalkulasi di database berhasil dibersihkan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menghapus data di server: ' . $e->getMessage()
            ], 500);
        }
    }
}

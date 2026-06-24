<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmissionCalculation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmissionCalculationController extends Controller
{
    public function latest(Request $request): JsonResponse
    {
        $calculation = EmissionCalculation::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->first();

        if (! $calculation) {
            return response()->json([
                'message' => 'Kalkulasi emisi belum tersedia.',
                'data' => null,
            ]);
        }

        return response()->json([
            'data' => [
                'id' => $calculation->id,
                'mode' => $calculation->calculation_mode,
                'scope1_kg' => (float) $calculation->scope1_kg,
                'scope2_kg' => (float) $calculation->scope2_kg,
                'scope3_kg' => (float) $calculation->scope3_kg,
                'scope_details' => $calculation->scope_details,
                'total_kg' => (float) $calculation->total_kg,
                'total_ton' => (float) $calculation->total_ton,
                'estimated_cost' => (float) $calculation->estimated_cost,
                'created_at' => $calculation->created_at?->toISOString(),
            ],
        ]);
    }
}

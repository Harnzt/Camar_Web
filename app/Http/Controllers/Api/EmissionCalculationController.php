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
                'category_emissions' => [
                    'household_energy_kg' => (float) $calculation->household_energy_kg,
                    'private_vehicle_kg' => (float) $calculation->private_vehicle_kg,
                    'electricity_kg' => (float) $calculation->electricity_kg,
                    'public_transport_kg' => (float) $calculation->public_transport_kg,
                    'personal_flight_kg' => (float) $calculation->personal_flight_kg,
                    'food_kg' => (float) $calculation->food_kg,
                    'water_kg' => (float) $calculation->water_kg,
                    'waste_kg' => (float) $calculation->waste_kg,
                    'stationary_combustion_kg' => (float) $calculation->stationary_combustion_kg,
                    'operational_vehicle_kg' => (float) $calculation->operational_vehicle_kg,
                    'business_flight_kg' => (float) $calculation->business_flight_kg,
                    'hotel_accommodation_kg' => (float) $calculation->hotel_accommodation_kg,
                    'business_train_kg' => (float) $calculation->business_train_kg,
                ],
                'total_kg' => (float) $calculation->total_kg,
                'total_ton' => (float) $calculation->total_ton,
                'estimated_cost' => (float) $calculation->estimated_cost,
                'created_at' => $calculation->created_at?->toISOString(),
            ],
        ]);
    }
}

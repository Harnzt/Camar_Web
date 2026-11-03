<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CATEGORY_COLUMNS = [
        'household_energy_kg',
        'private_vehicle_kg',
        'electricity_kg',
        'public_transport_kg',
        'personal_flight_kg',
        'food_kg',
        'water_kg',
        'waste_kg',
        'stationary_combustion_kg',
        'operational_vehicle_kg',
        'business_flight_kg',
        'hotel_accommodation_kg',
        'business_train_kg',
    ];

    public function up(): void
    {
        Schema::table('emission_calculations', function (Blueprint $table) {
            $table->json('scope_details')->nullable()->after('scope3_kg');
            foreach (self::CATEGORY_COLUMNS as $column) {
                $table->decimal($column, 15, 4)->default(0);
            }
        });

        if (! Schema::hasTable('emission_calculation_details')) {
            return;
        }

        DB::table('emission_calculations')
            ->select('id', 'calculation_mode')
            ->orderBy('id')
            ->chunkById(100, function ($calculations): void {
                $detailsByCalculation = DB::table('emission_calculation_details')
                    ->whereIn('emission_calculation_id', $calculations->pluck('id'))
                    ->orderBy('id')
                    ->get()
                    ->groupBy('emission_calculation_id');

                foreach ($calculations as $calculation) {
                    $details = $detailsByCalculation->get($calculation->id, collect());
                    $scopeDetails = [];
                    $categoryValues = array_fill_keys(self::CATEGORY_COLUMNS, 0.0);

                    foreach ($details as $detail) {
                        $scope = $detail->scope;
                        $valueKg = (float) ($detail->value_kg ?? $detail->emission_kg ?? 0);

                        $scopeDetails[$scope][] = [
                            'label' => $detail->activity ?: $detail->source ?: 'Aktivitas',
                            'source' => $detail->source,
                            'activity' => $detail->activity,
                            'input_type' => $detail->input_type ?? null,
                            'quantity' => $detail->quantity === null ? null : (float) $detail->quantity,
                            'unit' => $detail->unit,
                            'emission_factor' => $detail->emission_factor === null
                                ? null
                                : (float) $detail->emission_factor,
                            'value_kg' => $valueKg,
                            'transport_mode' => $detail->transport_mode ?? null,
                            'transport_class' => $detail->transport_class ?? null,
                            'distance_km' => isset($detail->distance_km) ? (float) $detail->distance_km : null,
                            'origin' => $detail->origin ?? null,
                            'origin_name' => $detail->origin_name ?? null,
                            'destination' => $detail->destination ?? null,
                            'destination_name' => $detail->destination_name ?? null,
                            'passengers' => isset($detail->passengers) ? (float) $detail->passengers : null,
                            'pax' => isset($detail->passengers) ? (float) $detail->passengers : null,
                            'cabin_class' => $detail->cabin_class ?? null,
                        ];

                        $category = $this->categoryColumn($calculation->calculation_mode, $detail);
                        if ($category !== null) {
                            $categoryValues[$category] += $valueKg;
                        }
                    }

                    DB::table('emission_calculations')
                        ->where('id', $calculation->id)
                        ->update([
                            'scope_details' => json_encode($scopeDetails),
                            ...$categoryValues,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('emission_calculations', function (Blueprint $table) {
            $table->dropColumn(['scope_details', ...self::CATEGORY_COLUMNS]);
        });
    }

    private function categoryColumn(string $mode, object $detail): ?string
    {
        $source = strtolower((string) ($detail->source ?? ''));
        $activity = strtolower((string) ($detail->activity ?? ''));

        if ($mode === 'company') {
            return match (true) {
                str_contains($source, 'stasioner') => 'stationary_combustion_kg',
                str_contains($source, 'kendaraan operasional') => 'operational_vehicle_kg',
                str_contains($source, 'listrik') => 'electricity_kg',
                str_contains($source, 'penerbangan') => 'business_flight_kg',
                str_contains($source, 'hotel') => 'hotel_accommodation_kg',
                str_contains($source, 'kereta') => 'business_train_kg',
                default => null,
            };
        }

        return match (true) {
            str_starts_with((string) ($detail->transport_mode ?? ''), 'flight_'),
            str_contains($activity, 'pesawat') && ! str_contains($activity, 'transportasi umum'),
            str_contains($source, 'penerbangan') => 'personal_flight_kg',
            str_contains($source, 'energi rumah tangga') => 'household_energy_kg',
            str_contains($source, 'kendaraan pribadi') => 'private_vehicle_kg',
            str_contains($source, 'listrik') => 'electricity_kg',
            str_contains($source, 'transportasi') => 'public_transport_kg',
            str_contains($source, 'makanan') => 'food_kg',
            str_contains($source, 'air') => 'water_kg',
            str_contains($source, 'limbah') => 'waste_kg',
            default => null,
        };
    }
};

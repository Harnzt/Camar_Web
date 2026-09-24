<?php

use App\Models\User;
use App\Services\CarbonCalculationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(Tests\TestCase::class);

test('personal calculation covers household account scopes', function () {
    $user = new User(['account_category' => 'personal']);

    $result = (new CarbonCalculationService)->calculate($user, [
        'energy_fuel' => 'lpg',
        'energy_qty' => 10,
        'vehicle_type' => 'car_petrol',
        'vehicle_fuel' => 'ron92',
        'vehicle_km' => 500,
        'electricity_kwh' => 100,
        'transit_mode' => 'train',
        'transit_km' => 100,
        'food_type' => 'beef',
        'food_kg' => 2,
        'water_m3' => 10,
        'waste_kg' => 5,
    ]);

    expect($result['mode'])->toBe('personal')
        ->and(round($result['scope1_kg'], 2))->toBe(1650.0)
        ->and(round($result['scope2_kg'], 2))->toBe(971.88)
        ->and(round($result['scope3_kg'], 2))->toBe(724.18)
        ->and(round($result['total_kg'], 2))->toBe(3346.06);
});

test('company calculation covers scope one two and three', function () {
    $user = new User(['account_category' => 'company']);

    $result = (new CarbonCalculationService)->calculate($user, [
        'stationary_fuel' => 'diesel',
        'stationary_qty' => 1000,
        'mobile_fuel' => 'ron92',
        'mobile_fuel_qty' => 100,
        'mobile_distance_fuel' => 'diesel',
        'mobile_km' => 1000,
        'electricity_grid' => 'sumatra',
        'electricity_kwh' => 1000,
        'flight_class' => 'economy',
        'flight_pax' => 2,
        'flight_km' => 1000,
        'hotel_nights' => 2,
        'hotel_rooms' => 3,
        'train_class' => 'ekonomi',
        'train_km' => 1000,
    ]);

    expect($result['mode'])->toBe('company')
        ->and(round($result['scope1_kg'], 2))->toBe(3279.5)
        ->and(round($result['scope2_kg'], 2))->toBe(879.0)
        ->and(round($result['scope3_kg'], 2))->toBe(574.41)
        ->and(round($result['total_kg'], 2))->toBe(4732.91);
});

test('company flight distance and emission match calculator route formula', function () {
    Schema::shouldReceive('hasTable')
        ->once()
        ->with('airports_data')
        ->andReturnTrue();

    $query = \Mockery::mock();
    DB::shouldReceive('table')->once()->with('airports_data')->andReturn($query);
    $query->shouldReceive('select')
        ->once()
        ->with('iata_code', 'name', 'latitude_deg', 'longitude_deg')
        ->andReturnSelf();
    $query->shouldReceive('whereIn')
        ->once()
        ->with('iata_code', ['CGK', 'DPS'])
        ->andReturnSelf();
    $query->shouldReceive('get')->once()->andReturn(collect([
        (object) [
            'name' => 'Soekarno-Hatta International Airport',
            'latitude_deg' => -6.125570,
            'longitude_deg' => 106.655998,
            'iata_code' => 'CGK',
        ],
        (object) [
            'name' => 'I Gusti Ngurah Rai International Airport',
            'latitude_deg' => -8.748409,
            'longitude_deg' => 115.167123,
            'iata_code' => 'DPS',
        ],
    ]));

    $user = new User(['account_category' => 'company']);
    $result = (new CarbonCalculationService)->calculate($user, [
        'stationary_fuel' => 'none',
        'mobile_fuel' => 'none',
        'mobile_distance_fuel' => 'none',
        'electricity_grid' => 'none',
        'flight_class' => 'economy',
        'flight_pax' => 2,
        'flight_origin' => 'Jakarta',
        'flight_destination' => 'Bandara Ngurah Rai',
        'hotel_nights' => 0,
        'hotel_rooms' => 0,
        'train_class' => 'none',
    ]);

    expect($result['details']['flight_km'])->toBe(982.61)
        ->and(round($result['details']['flight'], 2))->toBe(261.37)
        ->and(round($result['scope3_kg'], 2))->toBe(261.37)
        ->and($result['scope_details']['scope3'][0]['origin'])->toBe('CGK')
        ->and($result['scope_details']['scope3'][0]['destination'])->toBe('DPS');
});

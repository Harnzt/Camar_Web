<?php

use App\Models\EmissionCalculation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createBuyerForPurchaseGuard(): User
{
    return User::create([
        'name' => 'Buyer Uji',
        'email' => fake()->unique()->safeEmail(),
        'password' => 'password',
        'role' => 'buyer',
        'account_category' => 'personal',
        'status' => 'verified',
    ]);
}

function createProjectForPurchaseGuard(): Project
{
    return Project::create([
        'name' => 'Proyek Uji Karbon',
        'company_name' => 'PT Uji Hijau',
        'category' => 'Reforestasi',
        'price_per_ton' => 150000,
        'stock_available' => 10,
    ]);
}

test('buyer tanpa kalkulasi emisi diarahkan ke kalkulator saat menambah keranjang', function () {
    $buyer = createBuyerForPurchaseGuard();
    $project = createProjectForPurchaseGuard();

    $response = $this->actingAs($buyer)->postJson(route('cart.add'), [
        'project_id' => $project->id,
        'quantity' => 1,
    ]);

    $response
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'redirect_url' => route('calculator'),
        ]);

    expect(session('cart', []))->toBeEmpty();
});

test('buyer tanpa kalkulasi emisi tidak dapat membuat order', function () {
    $buyer = createBuyerForPurchaseGuard();

    $response = $this->actingAs($buyer)->postJson(route('orders.store'), [
        'items' => [],
    ]);

    $response
        ->assertForbidden()
        ->assertJson([
            'success' => false,
            'redirect_url' => route('calculator'),
        ]);
});

test('buyer dengan kalkulasi emisi dapat menambah proyek ke keranjang', function () {
    $buyer = createBuyerForPurchaseGuard();
    $project = createProjectForPurchaseGuard();

    EmissionCalculation::create([
        'user_id' => $buyer->id,
        'scope1_kg' => 100,
        'scope2_kg' => 50,
        'scope3_kg' => 25,
        'total_kg' => 175,
        'total_ton' => 0.175,
        'estimated_cost' => 26250,
        'price_per_ton' => 150000,
    ]);

    $response = $this->actingAs($buyer)->postJson(route('cart.add'), [
        'project_id' => $project->id,
        'quantity' => 1,
    ]);

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'cart_count' => 1,
        ]);

    expect(session('cart'))->toHaveKey((string) $project->id);
});

test('kalkulator menyimpan rincian komponen untuk setiap scope', function () {
    $buyer = createBuyerForPurchaseGuard();

    $this->actingAs($buyer)
        ->postJson('/calculator/store', [
            'mode' => 'personal',
            'total_kg' => 225,
            'details' => [
                'energy_rt' => 40,
                'vehicle' => 60,
                'electricity' => 50,
                'transit' => 20,
                'food' => 30,
                'water' => 10,
                'waste' => 15,
            ],
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    $calculation = EmissionCalculation::where('user_id', $buyer->id)->latest()->first();

    expect((float) $calculation->scope1_kg)->toBe(100.0)
        ->and((float) $calculation->scope2_kg)->toBe(50.0)
        ->and((float) $calculation->scope3_kg)->toBe(75.0)
        ->and((float) $calculation->household_energy_kg)->toBe(40.0)
        ->and((float) $calculation->private_vehicle_kg)->toBe(60.0)
        ->and((float) $calculation->electricity_kg)->toBe(50.0)
        ->and((float) $calculation->public_transport_kg)->toBe(20.0)
        ->and((float) $calculation->food_kg)->toBe(30.0)
        ->and((float) $calculation->water_kg)->toBe(10.0)
        ->and((float) $calculation->waste_kg)->toBe(15.0)
        ->and($calculation->scope_details['scope1'][0]['label'])->toBe('Energi rumah tangga')
        ->and((float) $calculation->scope_details['scope3'][3]['value_kg'])->toBe(15.0);
});

test('kalkulator menyimpan rincian transportasi dalam JSON dan emisi kategori dalam kolom', function () {
    $buyer = createBuyerForPurchaseGuard();

    $this->actingAs($buyer)
        ->postJson('/calculator/store', [
            'mode' => 'personal',
            'total_kg' => 20,
            'details' => [
                'transit' => 20,
            ],
            'scope_details' => [
                'scope3' => [[
                    'source' => 'Transportasi',
                    'activity' => 'Bus',
                    'quantity' => 240,
                    'unit' => 'penumpang-km/tahun',
                    'emission_factor' => 0.089,
                    'value_kg' => 20,
                    'input_type' => 'bus',
                    'transport_mode' => 'bus',
                    'distance_km' => 20,
                    'passengers' => 1,
                ]],
            ],
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    $calculation = EmissionCalculation::where('user_id', $buyer->id)
        ->latest()
        ->firstOrFail();

    expect((float) $calculation->public_transport_kg)->toBe(20.0)
        ->and($calculation->scope_details['scope3'][0]['transport_mode'])->toBe('bus')
        ->and($calculation->scope_details['scope3'][0]['activity'])->toBe('Bus')
        ->and((float) $calculation->scope_details['scope3'][0]['distance_km'])->toBe(20.0)
        ->and((float) $calculation->scope_details['scope3'][0]['value_kg'])->toBe(20.0);
});

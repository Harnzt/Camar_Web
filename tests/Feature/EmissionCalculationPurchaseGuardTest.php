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

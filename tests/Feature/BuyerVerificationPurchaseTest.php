<?php

use App\Models\EmissionCalculation;
use App\Models\Order;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

const PENDING_BUYER_MESSAGE = 'Akun Anda masih menunggu verifikasi admin. Anda belum dapat melakukan pembelian.';
const REJECTED_BUYER_MESSAGE = 'Verifikasi akun Anda ditolak. Silakan hubungi admin.';

function verificationBuyer(string $status, bool $withCalculation = true): User
{
    $buyer = User::create([
        'name' => "Buyer {$status}",
        'email' => "buyer-{$status}@example.test",
        'password' => 'password',
        'role' => 'buyer',
        'account_category' => 'personal',
        'status' => $status,
    ]);

    if ($withCalculation) {
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
    }

    return $buyer;
}

function verificationProject(): Project
{
    return Project::create([
        'name' => 'Proyek Verifikasi Buyer',
        'company_name' => 'PT Uji Verifikasi',
        'category' => 'Reforestasi',
        'price_per_ton' => 150000,
        'stock_available' => 10,
        'verification_status' => 'approved',
    ]);
}

dataset('unverified buyers', [
    'pending' => ['pending', PENDING_BUYER_MESSAGE],
    'rejected' => ['rejected', REJECTED_BUYER_MESSAGE],
]);

test('unverified buyer cannot create an order from the web checkout', function (string $status, string $message) {
    $buyer = verificationBuyer($status);
    $project = verificationProject();

    $this->actingAs($buyer)
        ->postJson(route('orders.store'), ['items' => [['project_id' => $project->id, 'quantity' => 1]]])
        ->assertForbidden()
        ->assertJson(['success' => false, 'message' => $message]);

    $this->actingAs($buyer)
        ->from(route('projects.show', $project->id))
        ->post(route('orders.checkout'), ['project_id' => $project->id, 'quantity' => 1])
        ->assertRedirect(route('projects.show', $project->id))
        ->assertSessionHas('warning', $message);

    $this->actingAs($buyer)
        ->from(route('cart.index'))
        ->get(route('orders.checkout.confirm'))
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('warning', $message);

    foreach (['orders.checkout.confirm.submit', 'orders.confirm'] as $routeName) {
        $this->actingAs($buyer)
            ->from(route('cart.index'))
            ->post(route($routeName), ['payment_method' => 'midtrans', 'transaction_ids' => [1]])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('warning', $message);
    }

    expect(Order::count())->toBe(0);
})->with('unverified buyers');

test('unverified buyer cannot create or pay an order through the mobile API', function (string $status, string $message) {
    $buyer = verificationBuyer($status);
    $project = verificationProject();
    Sanctum::actingAs($buyer);

    $this->postJson('/api/v1/orders', ['items' => [['project_id' => $project->id, 'quantity' => 1]]])
        ->assertForbidden()
        ->assertJsonPath('message', $message);

    $this->postJson('/api/v1/orders/charge', ['order_ids' => [1], 'payment_method' => 'qris'])
        ->assertForbidden()
        ->assertJsonPath('message', $message);

    expect(Order::count())->toBe(0);
})->with('unverified buyers');

test('verified buyer can create orders from the web and the mobile API', function () {
    $buyer = verificationBuyer('verified');
    $project = verificationProject();

    $this->actingAs($buyer)
        ->postJson(route('orders.store'), ['items' => [['project_id' => $project->id, 'quantity' => 2]]])
        ->assertOk()
        ->assertJson(['success' => true, 'redirect_url' => route('orders.checkout.confirm')]);

    Sanctum::actingAs($buyer);

    $this->postJson('/api/v1/orders', ['items' => [['project_id' => $project->id, 'quantity' => 1]]])
        ->assertCreated();

    expect(Order::where('user_id', $buyer->id)->where('status', 'pending')->count())->toBe(2);
});

test('pending buyer can still use the calculator and fill the cart', function () {
    $buyer = verificationBuyer('pending', withCalculation: false);
    $project = verificationProject();

    $this->actingAs($buyer)->get(route('calculator'))->assertOk();

    $this->actingAs($buyer)
        ->postJson('/calculator/store', [
            'mode' => 'personal',
            'total_kg' => 60,
            'details' => ['vehicle' => 60],
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->actingAs($buyer)
        ->postJson(route('cart.add'), ['project_id' => $project->id, 'quantity' => 1])
        ->assertOk()
        ->assertJson(['success' => true, 'cart_count' => 1]);

    $this->actingAs($buyer)->get(route('projects.index'))->assertOk();
});

test('cart, project page and dashboard explain why checkout is disabled', function (string $status, string $message) {
    $buyer = verificationBuyer($status);
    $project = verificationProject();

    $this->actingAs($buyer)
        ->withSession(['cart' => [$project->id => [
            'project_id' => $project->id,
            'name' => $project->name,
            'image' => null,
            'price' => $project->price_per_ton,
            'quantity' => 1,
        ]]])
        ->get(route('cart.index'))
        ->assertOk()
        ->assertSee($message)
        ->assertSee('data-purchase-blocked="1"', false);

    $this->actingAs($buyer)
        ->get(route('projects.show', $project->id))
        ->assertOk()
        ->assertSee($message)
        ->assertSee('aria-describedby="buyNowBlockedNote"', false);

    $this->actingAs($buyer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee($message);
})->with('unverified buyers');

test('verified buyer sees no verification banner and an enabled checkout', function () {
    $buyer = verificationBuyer('verified');
    $project = verificationProject();

    $this->actingAs($buyer)
        ->get(route('projects.show', $project->id))
        ->assertOk()
        ->assertDontSee('buyNowBlockedNote', false)
        ->assertDontSee(PENDING_BUYER_MESSAGE);

    $this->actingAs($buyer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('purchase-verification-notice', false);
});

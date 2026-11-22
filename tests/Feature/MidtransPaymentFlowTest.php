<?php

use App\Models\Order;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['midtrans.server_key' => 'SB-Mid-server-test-key']);
});

function midtransBuyer(string $email = 'buyer-midtrans@example.test'): User
{
    return User::create([
        'name' => 'Buyer Midtrans',
        'email' => $email,
        'password' => 'password',
        'role' => 'buyer',
        'account_category' => 'personal',
        'status' => 'verified',
    ]);
}

function midtransProject(array $attributes = []): Project
{
    return Project::create([
        'name' => 'Proyek Mangrove Midtrans',
        'company_name' => 'PT Mangrove Lestari',
        'category' => 'mangrove',
        'price_per_ton' => 100000,
        'stock_available' => 10,
        'verification_status' => 'approved',
        ...$attributes,
    ]);
}

function midtransOrder(User $buyer, Project $project, string $orderNumber, int $quantity = 3): Order
{
    $subtotal = $quantity * 100000;
    $tax = $subtotal * 0.11;

    return Order::create([
        'user_id' => $buyer->id,
        'project_id' => $project->id,
        'order_number' => $orderNumber,
        'quantity' => $quantity,
        'subtotal' => $subtotal,
        'tax' => $tax,
        'total_price' => $subtotal + $tax,
        'buyer_name' => $buyer->name,
        'buyer_email' => $buyer->email,
        'status' => 'pending',
        'payment_method' => 'midtrans',
    ]);
}

/**
 * @return array<string, string>
 */
function midtransNotificationPayload(string $orderId, string $grossAmount, string $transactionStatus = 'settlement'): array
{
    $statusCode = $transactionStatus === 'settlement' ? '200' : '202';

    return [
        'order_id' => $orderId,
        'status_code' => $statusCode,
        'gross_amount' => $grossAmount,
        'transaction_status' => $transactionStatus,
        'payment_type' => 'bank_transfer',
        'signature_key' => hash('sha512', $orderId.$statusCode.$grossAmount.config('midtrans.server_key')),
    ];
}

test('settlement webhook sent twice issues one certificate and reduces stock once', function () {
    $buyer = midtransBuyer();
    $project = midtransProject();
    $order = midtransOrder($buyer, $project, 'ORDER-1763800000-ABCD');
    $payload = midtransNotificationPayload($order->order_number, '333000.00');

    $this->postJson(route('orders.midtrans.notification'), $payload)->assertOk();

    $order->refresh();
    $firstCertificateNumber = $order->certificate_number;

    expect($order->status)->toBe('completed')
        ->and($firstCertificateNumber)->toStartWith('CAMAR-CERT-')
        ->and($order->certificate_issued_at)->not->toBeNull()
        ->and($order->certificate_issued_by)->toBeNull()
        ->and($order->payment_method)->toBe('bank_transfer')
        ->and($project->fresh()->stock_available)->toBe(7);

    $this->postJson(route('orders.midtrans.notification'), $payload)->assertOk();

    $order->refresh();

    expect($order->status)->toBe('completed')
        ->and($order->certificate_number)->toBe($firstCertificateNumber)
        ->and($project->fresh()->stock_available)->toBe(7)
        ->and(Order::whereNotNull('certificate_number')->count())->toBe(1);
});

test('cart checkout webhook settles every order of the same payment and only those', function () {
    $buyer = midtransBuyer();
    $otherBuyer = midtransBuyer('buyer-lain@example.test');
    $mangrove = midtransProject();
    $peatland = midtransProject(['name' => 'Proyek Gambut', 'stock_available' => 20]);

    $first = midtransOrder($buyer, $mangrove, 'ORDER-1763800000-1-AAA', 2);
    $second = midtransOrder($buyer, $peatland, 'ORDER-1763800000-2-BBB', 5);
    $otherUsersOrder = midtransOrder($otherBuyer, $mangrove, 'ORDER-1763800000-1-ZZZ', 1);
    $grossAmount = (string) ((int) $first->total_price + (int) $second->total_price);

    $this->postJson(route('orders.midtrans.notification'), midtransNotificationPayload($first->order_number, $grossAmount))
        ->assertOk();

    expect($first->fresh()->status)->toBe('completed')
        ->and($second->fresh()->status)->toBe('completed')
        ->and($first->fresh()->certificate_number)->not->toBe($second->fresh()->certificate_number)
        ->and($mangrove->fresh()->stock_available)->toBe(8)
        ->and($peatland->fresh()->stock_available)->toBe(15)
        ->and($otherUsersOrder->fresh()->status)->toBe('pending');
});

test('webhook for a project that is not approved marks the order paid without a certificate', function () {
    Log::spy();

    $buyer = midtransBuyer();
    $project = midtransProject(['verification_status' => 'pending']);
    $order = midtransOrder($buyer, $project, 'ORDER-1763800001-ABCD');

    $this->postJson(route('orders.midtrans.notification'), midtransNotificationPayload($order->order_number, '333000.00'))
        ->assertOk();

    $order->refresh();

    expect($order->status)->toBe('paid')
        ->and($order->certificate_number)->toBeNull()
        ->and($order->certificate_issued_at)->toBeNull()
        ->and($project->fresh()->stock_available)->toBe(7);

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message) => str_contains($message, 'belum approved'))
        ->once();
});

test('expired payment webhook marks pending orders failed without touching stock', function () {
    $buyer = midtransBuyer();
    $project = midtransProject();
    $order = midtransOrder($buyer, $project, 'ORDER-1763800002-ABCD');

    $this->postJson(route('orders.midtrans.notification'), midtransNotificationPayload($order->order_number, '333000.00', 'expire'))
        ->assertOk();

    expect($order->fresh()->status)->toBe('failed')
        ->and($order->fresh()->certificate_number)->toBeNull()
        ->and($project->fresh()->stock_available)->toBe(10);
});

test('webhook with an invalid signature changes nothing', function () {
    $buyer = midtransBuyer();
    $project = midtransProject();
    $order = midtransOrder($buyer, $project, 'ORDER-1763800003-ABCD');
    $payload = [
        ...midtransNotificationPayload($order->order_number, '333000.00'),
        'signature_key' => str_repeat('0', 128),
    ];

    $this->postJson(route('orders.midtrans.notification'), $payload)->assertForbidden();

    expect($order->fresh()->status)->toBe('pending')
        ->and($project->fresh()->stock_available)->toBe(10);
});

test('midtrans finish redirect does not change order status or stock', function () {
    $buyer = midtransBuyer();
    $project = midtransProject();
    $order = midtransOrder($buyer, $project, 'ORDER-1763800004-ABCD');

    $this->actingAs($buyer)
        ->withSession(['pending_transaction_ids' => [$order->id]])
        ->get(route('orders.midtrans.finish', [
            'order_id' => $order->order_number,
            'status_code' => '200',
            'transaction_status' => 'settlement',
        ]))
        ->assertRedirect(route('orders.success.multi', ['ids' => (string) $order->id]));

    expect($order->fresh()->status)->toBe('pending')
        ->and($order->fresh()->certificate_number)->toBeNull()
        ->and($project->fresh()->stock_available)->toBe(10);
});

test('pending and error redirects only point to the order status page', function (string $routeName) {
    $buyer = midtransBuyer();
    $project = midtransProject();
    $order = midtransOrder($buyer, $project, 'ORDER-1763800005-ABCD');

    $this->actingAs($buyer)
        ->get(route($routeName, ['order_id' => $order->order_number]))
        ->assertRedirect(route('orders.success.multi', ['ids' => (string) $order->id]));

    expect($order->fresh()->status)->toBe('pending')
        ->and($project->fresh()->stock_available)->toBe(10);
})->with(['orders.midtrans.pending', 'orders.midtrans.error']);

test('status page waits for the webhook before announcing a successful payment', function () {
    $buyer = midtransBuyer();
    $project = midtransProject();
    $order = midtransOrder($buyer, $project, 'ORDER-1763800006-ABCD');

    $this->actingAs($buyer)
        ->get(route('orders.success.multi', ['ids' => $order->id]))
        ->assertOk()
        ->assertSee('Menunggu konfirmasi pembayaran')
        ->assertDontSee('Pembayaran Berhasil!');

    $this->postJson(route('orders.midtrans.notification'), midtransNotificationPayload($order->order_number, '333000.00'))
        ->assertOk();

    $this->actingAs($buyer)
        ->get(route('orders.success', $order->id))
        ->assertOk()
        ->assertSee('Pembayaran Berhasil!')
        ->assertSee('Sertifikat digital kamu sudah terbit')
        ->assertDontSee('Menunggu konfirmasi pembayaran');
});

test('buyer certificate page names the system as issuer for automatic certificates', function () {
    $buyer = midtransBuyer();
    $order = midtransOrder($buyer, midtransProject(), 'ORDER-1763800007-ABCD');
    $order->forceFill([
        'status' => 'completed',
        'certificate_number' => 'CAMAR-CERT-20261122-AUTO01',
        'certificate_issued_at' => now(),
        'certificate_issued_by' => null,
    ])->save();

    $this->actingAs($buyer)
        ->get(route('buyer.certificates.show', $order))
        ->assertOk()
        ->assertSee('CAMAR-CERT-20261122-AUTO01')
        ->assertSee('Sistem CAMAR (otomatis)');
});

test('buyer transaction filter still shows legacy verified orders', function () {
    $buyer = midtransBuyer();
    $project = midtransProject();
    $legacyOrder = midtransOrder($buyer, $project, 'ORDER-1763800008-LEGACY');
    $legacyOrder->forceFill(['status' => 'verified'])->save();
    midtransOrder($buyer, $project, 'ORDER-1763800009-PENDING');

    $this->actingAs($buyer)
        ->get(route('buyer.transactions', ['status' => 'verified']))
        ->assertOk()
        ->assertViewHas('orders', fn ($orders) => $orders->count() === 1
            && $orders->first()->is($legacyOrder));
});

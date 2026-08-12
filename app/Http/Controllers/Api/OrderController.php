<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isBuyer() && ! $user->hasEmissionCalculation()) {
            return response()->json([
                'message' => 'Anda belum memiliki kalkulasi emisi karbon.',
            ], 403);
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.project_id' => ['required', 'integer', 'exists:projects,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'buyer_phone' => ['nullable', 'string', 'max:30'],
            'billing_address' => ['nullable', 'string', 'max:500'],
        ]);

        $orders = DB::transaction(function () use ($validated, $user) {
            $baseTime = time();
            $created = collect();

            foreach ($validated['items'] as $index => $item) {
                $project = Project::query()
                    ->approved()
                    ->lockForUpdate()
                    ->findOrFail($item['project_id']);
                $quantity = (int) $item['quantity'];

                if ($project->stock_available !== null && $quantity > $project->stock_available) {
                    abort(422, "Stok proyek \"{$project->name}\" tidak mencukupi.");
                }

                $subtotal = $quantity * $project->price_per_ton;
                $tax = round($subtotal * 0.11);
                $total = $subtotal + $tax;

                $created->push(Order::create([
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'order_number' => 'ORDER-'.$baseTime.'-'.($index + 1).'-'.Str::upper(Str::random(4)),
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total_price' => $total,
                    'buyer_name' => $user->name,
                    'buyer_email' => $user->email,
                    'buyer_phone' => $validated['buyer_phone'] ?? $user->phone ?? '-',
                    'status' => 'pending',
                    'payment_method' => $validated['payment_method'] ?? 'qris',
                ]));
            }

            return Order::query()
                ->with('project')
                ->whereIn('id', $created->pluck('id'))
                ->get();
        });

        return response()->json([
            'message' => 'Pesanan berhasil dibuat.',
            'orders' => $orders->map(fn (Order $order) => $this->orderData($order))->values(),
            'order_ids' => $orders->pluck('id')->values(),
            'total_price' => (float) $orders->sum('total_price'),
        ], 201);
    }

    public function charge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer'],
            'payment_method' => ['required', 'string', 'in:va_bca,va_bni,va_bri,va_mandiri,qris,gopay'],
        ]);

        $orders = Order::query()
            ->with('project')
            ->where('user_id', $request->user()->id)
            ->whereIn('id', $validated['order_ids'])
            ->where('status', 'pending')
            ->get();

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'Pesanan tidak ditemukan atau sudah dibayar.'], 404);
        }

        $grossAmount = (int) $orders->sum('total_price');
        $orderId = $orders->first()->order_number;

        $itemDetails = [];
        foreach ($orders as $order) {
            $itemDetails[] = [
                'id'       => 'project-' . $order->id,
                'price'    => (int) $order->project->price_per_ton,
                'quantity' => $order->quantity,
                'name'     => substr($order->project->name ?? 'Proyek', 0, 45),
            ];
            $itemDetails[] = [
                'id'       => 'tax-' . $order->id,
                'price'    => (int) $order->tax,
                'quantity' => 1,
                'name'     => 'PPN 11% - ' . substr($order->project->name ?? 'Proyek', 0, 30),
            ];
        }

        $serverKey = config('midtrans.server_key');
        $isProduction = config('midtrans.is_production', false);
        $baseUrl = $isProduction ? 'https://api.midtrans.com/v2/charge' : 'https://api.sandbox.midtrans.com/v2/charge';

        $payload = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $request->user()->name,
                'email'      => $request->user()->email,
                'phone'      => $request->user()->phone ?? '',
            ],
        ];

        $paymentMethod = $validated['payment_method'];

        if (in_array($paymentMethod, ['va_bca', 'va_bni', 'va_bri'])) {
            $payload['payment_type'] = 'bank_transfer';
            $payload['bank_transfer'] = [
                'bank' => str_replace('va_', '', $paymentMethod),
            ];
        } elseif ($paymentMethod === 'va_mandiri') {
            $payload['payment_type'] = 'echannel';
            $payload['echannel'] = [
                'bill_info1' => 'Payment For:',
                'bill_info2' => 'Camar Carbon',
            ];
        } elseif ($paymentMethod === 'qris') {
            $payload['payment_type'] = 'qris';
        } elseif ($paymentMethod === 'gopay') {
            $payload['payment_type'] = 'gopay';
        }

        $response = \Illuminate\Support\Facades\Http::timeout(30)
            ->withBasicAuth($serverKey, '')
            ->post($baseUrl, $payload);

        if (!$response->successful()) {
            return response()->json([
                'message' => 'Gagal menghubungi server pembayaran Midtrans.',
                'error' => $response->body()
            ], 500);
        }

        $resData = $response->json();

        // Update local orders with chosen payment method
        foreach ($orders as $order) {
            $order->update(['payment_method' => $paymentMethod]);
        }

        // Return unified data to mobile app
        $responseData = [
            'status_code' => $resData['status_code'] ?? '200',
            'order_id' => $resData['order_id'] ?? $orderId,
            'gross_amount' => $resData['gross_amount'] ?? $grossAmount,
            'payment_type' => $resData['payment_type'] ?? '',
        ];

        if (isset($resData['va_numbers']) && count($resData['va_numbers']) > 0) {
            $responseData['va_number'] = $resData['va_numbers'][0]['va_number'];
            $responseData['bank'] = $resData['va_numbers'][0]['bank'];
        } elseif (isset($resData['biller_code']) && isset($resData['bill_key'])) {
            $responseData['va_number'] = $resData['bill_key']; // Bill Key functions as VA for Mandiri
            $responseData['biller_code'] = $resData['biller_code'];
            $responseData['bank'] = 'mandiri';
        } elseif (isset($resData['actions'])) {
            foreach ($resData['actions'] as $action) {
                if ($action['name'] === 'generate-qr-code') {
                    $responseData['qr_code_url'] = $action['url'];
                }
            }
        }

        return response()->json([
            'message' => 'Charge berhasil',
            'payment_info' => $responseData,
        ]);
    }

    public function buyerTransactions(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with('project')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'transactions' => $orders->map(fn (Order $order) => $this->orderData($order))->values(),
        ]);
    }

    private function orderData(Order $order): array
    {
        return [
            'id' => (string) $order->id,
            'code' => $order->order_number,
            'project_id' => (string) $order->project_id,
            'project_name' => $order->project?->name ?? 'Proyek',
            'category' => $order->project?->category ?? '-',
            'quantity_ton' => (float) $order->quantity,
            'subtotal' => (float) $order->subtotal,
            'tax' => (float) $order->tax,
            'total_price' => (float) $order->total_price,
            'payment_method' => $order->payment_method,
            'status' => $order->status,
            'created_at' => $order->created_at?->toISOString(),
        ];
    }
}

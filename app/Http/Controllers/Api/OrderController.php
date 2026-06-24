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

    public function confirm(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer'],
            'payment_method' => ['required', 'string', 'max:50'],
        ]);

        $orders = DB::transaction(function () use ($validated, $request) {
            $orders = Order::query()
                ->with('project')
                ->where('user_id', $request->user()->id)
                ->whereIn('id', $validated['order_ids'])
                ->lockForUpdate()
                ->get();

            abort_if($orders->count() !== count($validated['order_ids']), 404, 'Sebagian pesanan tidak ditemukan.');

            foreach ($orders as $order) {
                if (! in_array($order->status, ['paid', 'verified', 'completed'], true)) {
                    if ($order->project && $order->project->stock_available !== null) {
                        abort_if(
                            $order->quantity > $order->project->stock_available,
                            422,
                            "Stok proyek \"{$order->project->name}\" tidak mencukupi.",
                        );

                        $order->project->decrement('stock_available', $order->quantity);
                    }

                    $order->update([
                        'status' => 'paid',
                        'payment_method' => $validated['payment_method'],
                    ]);
                }
            }

            return $orders->fresh(['project']);
        });

        return response()->json([
            'message' => 'Pembayaran berhasil dikonfirmasi.',
            'orders' => $orders->map(fn (Order $order) => $this->orderData($order))->values(),
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

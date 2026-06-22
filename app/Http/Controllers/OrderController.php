<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /* ═══════════════════════════════════════════════════════════
     |  CHECKOUT (Buy Now — Single project langsung dari halaman detail)
     ═══════════════════════════════════════════════════════════ */
    public function checkout(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $project  = Project::approved()->findOrFail($request->project_id);
        $quantity = (int) $request->quantity;
        $subtotal = $quantity * $project->price_per_ton;
        $tax      = $subtotal * 0.11;
        $total    = $subtotal + $tax;

        return view('main_page.projects.orders', compact(
            'project', 'quantity', 'subtotal', 'tax', 'total'
        ));
    }

    /* ═══════════════════════════════════════════════════════════
     |  STORE — Dipanggil via AJAX (Bisa menangani Keranjang Belanja & Buy Now)
     ═══════════════════════════════════════════════════════════ */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu.',
            ], 401);
        }

        $user = Auth::user();

        if ($user->isBuyer() && !$user->hasEmissionCalculation()) {
            session()->flash(
                'warning',
                'Hitung emisi karbon Anda terlebih dahulu sebelum melakukan pembelian.'
            );

            return response()->json([
                'success'      => false,
                'message'      => 'Anda belum memiliki kalkulasi emisi karbon.',
                'redirect_url' => route('calculator'),
            ], 403);
        }

        $orderIds = [];

        DB::beginTransaction();
        try {
            // ── ALUR A: CHECKOUT DARI KERANJANG BELANJA (ARRAY ITEMS) ──
            if ($request->has('items') && is_array($request->items)) {
                $request->validate([
                    'items'              => 'required|array|min:1',
                    'items.*.project_id' => 'required|exists:projects,id',
                    'items.*.quantity'   => 'required|integer|min:1',
                ]);

                $baseTime = time();

                foreach ($request->items as $index => $item) {
                    $project  = Project::approved()->findOrFail($item['project_id']);
                    $quantity = (int) $item['quantity'];
                    $subtotal = $quantity * $project->price_per_ton;
                    $tax      = round($subtotal * 0.11);
                    $total    = $subtotal + $tax;

                    if ($project->stock_available !== null && $quantity > $project->stock_available) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Stok proyek \"{$project->name}\" tidak mencukupi.",
                        ], 422);
                    }

                    // 🔥 FIX: Menghapus 'order_code' agar tidak eror Unknown Column
                    $order = Order::create([
                        'user_id'        => Auth::id(),
                        'project_id'     => $project->id,
                        // order_number diisi format unik gabungan timestamp agar aman untuk Midtrans
                        'order_number'   => 'ORDER-' . $baseTime . '-' . ($index + 1) . '-' . Str::upper(Str::random(3)),
                        'quantity'       => $quantity,
                        'subtotal'       => $subtotal,
                        'tax'            => $tax,
                        'total_price'    => $total,
                        'buyer_name'     => Auth::user()->name,
                        'buyer_email'    => Auth::user()->email,
                        'buyer_phone'    => Auth::user()->phone ?? '-',
                        'status'         => 'pending',
                        'payment_method' => 'midtrans',
                    ]);

                    $orderIds[] = $order->id;
                }

                // Hapus item dari session cart setelah berhasil dipindahkan ke DB
                $cart = session()->get('cart', []);
                foreach ($request->items as $item) {
                    if (isset($cart[$item['project_id']])) {
                        unset($cart[$item['project_id']]);
                    }
                }
                session()->put('cart', $cart);

            } else {
                // ── ALUR B: CHECKOUT LANGSUNG / BUY NOW (SINGLE ITEM) ──
                $projectId = $request->input('id') ?? $request->input('project_id');
                $qtyInput  = $request->input('qty') ?? $request->input('quantity');

                $request->merge(['proj_id' => $projectId, 'qty_val' => $qtyInput]);
                $request->validate([
                    'proj_id' => 'required|exists:projects,id',
                    'qty_val' => 'required|integer|min:1',
                ]);

                $project  = Project::approved()->findOrFail($projectId);
                $quantity = (int) $qtyInput;
                $subtotal = $quantity * $project->price_per_ton;
                $tax      = round($subtotal * 0.11);
                $total    = $subtotal + $tax;

                if ($project->stock_available !== null && $quantity > $project->stock_available) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok proyek tidak mencukupi.',
                    ], 422);
                }

                // 🔥 FIX: Menghapus 'order_code' agar tidak eror Unknown Column
                $order = Order::create([
                    'user_id'        => Auth::id(),
                    'project_id'     => $project->id,
                    'order_number'   => 'ORDER-' . time() . '-' . Str::upper(Str::random(4)),
                    'quantity'       => $quantity,
                    'subtotal'       => $subtotal,
                    'tax'            => $tax,
                    'total_price'    => $total,
                    'total_amount'   => $total,
                    'buyer_name'     => Auth::user()->name,
                    'buyer_email'    => Auth::user()->email,
                    'buyer_phone'    => Auth::user()->phone ?? '-',
                    'status'         => 'pending',
                    'payment_method' => 'midtrans',
                ]);

                $orderIds[] = $order->id;
            }

            DB::commit();

            session()->put('pending_transaction_ids', $orderIds);

            return response()->json([
                'success'      => true,
                'message'      => 'Pesanan berhasil dibuat!',
                'redirect_url' => route('orders.checkout.confirm'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ═══════════════════════════════════════════════════════════
     |  CHECKOUT CONFIRM PAGE — Halaman Ringkasan & Pemicu Snap Token
     ═══════════════════════════════════════════════════════════ */
    public function checkoutConfirm()
    {
        $ids = session()->get('pending_transaction_ids', []);

        if (empty($ids)) {
            return redirect()->route('cart.index')->with('error', 'Sesi checkout tidak valid. Silakan ulangi.');
        }

        $orders = Order::with('project')
            ->whereIn('id', $ids)
            ->where('user_id', Auth::id())
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Transaksi tidak ditemukan.');
        }

        $grandTotal = $orders->sum('total_price');
        $itemDetails = [];

        foreach ($orders as $order) {
            $itemDetails[] = [
                'id'       => 'project-' . $order->id,
                'price'    => (int) $order->project->price_per_ton,
                'quantity' => $order->quantity,
                'name'     => substr($order->project->name, 0, 45),
            ];
            $itemDetails[] = [
                'id'       => 'tax-' . $order->id,
                'price'    => (int) $order->tax,
                'quantity' => 1,
                'name'     => 'PPN 11% - ' . substr($order->project->name, 0, 30),
            ];
        }

        // Generate Midtrans Snap Token secara dinamis menggunakan gabungan ID order
        $snapToken = null;
        try {
            $user = Auth::user();
            // Gunakan order_number dari item pertama sebagai referensi pengenal utama di dashboard Midtrans
            $midtransOrderId = $orders->first()->order_number;
            $snapToken = $this->getMidtransSnapToken($midtransOrderId, (int) $grandTotal, $itemDetails, $user);
        } catch (\Exception $e) {
            \Log::error('Midtrans snap token error: ' . $e->getMessage());
        }

        // Mapping ke array agar blade view yang lama tidak crash
        $transactions = $orders->map(function ($ord) {
            return [
                'id'            => $ord->id,
                'name'          => $ord->project->name ?? 'Proyek',
                'image'         => $ord->project->image ?? 'placeholder.jpg',
                'category'      => $ord->project->category ?? '-',
                'quantity'      => $ord->quantity,
                'price_per_ton' => $ord->project->price_per_ton ?? $ord->total_price,
                'subtotal'      => $ord->subtotal,
                'tax'           => $ord->tax,
                'total'         => $ord->total_price,
            ];
        })->toArray();

        return view('main_page.projects.order', compact('transactions', 'snapToken'));
    }

    /* ─────────────────────────────────────────────────────────
     |  Helper Rest API: Request Snap Token ke Midtrans Endpoint
     |───────────────────────────────────────────────────────── */
    private function getMidtransSnapToken(string $orderId, int $grossAmount, array $itemDetails, $user): string
    {
        $serverKey = config('midtrans.server_key');
        $isProduction = config('midtrans.is_production', false);

        $snapUrl = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $payload = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'item_details'     => $itemDetails,
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone ?? '',
            ],
            'callbacks' => [
                'finish'  => route('orders.midtrans.finish'),
                'error'   => route('orders.midtrans.error'),
                'pending' => route('orders.midtrans.pending'),
            ],
        ];

        $response = \Http::withBasicAuth($serverKey, '')
            ->post($snapUrl, $payload);

        if (!$response->successful()) {
            throw new \Exception('Midtrans Rest API Error: ' . $response->body());
        }

        return $response->json('token');
    }

    /* ═══════════════════════════════════════════════════════════
     |  CONFIRM — Form Fallback Manual jika Webhook/Snap bermasalah
     ═══════════════════════════════════════════════════════════ */
    public function confirm(Request $request)
    {
        $request->validate([
            'payment_method'    => 'required|in:transfer,ewallet,qris,midtrans',
            'transaction_ids'   => 'nullable|array',
            'transaction_ids.*' => 'integer'
        ]);

        if ($request->has('transaction_ids') && !empty($request->transaction_ids)) {
            $ids = $request->transaction_ids;

            DB::beginTransaction();
            try {
                Order::whereIn('id', $ids)
                    ->where('user_id', Auth::id())
                    ->update([
                        'payment_method' => $request->payment_method,
                        'status'         => 'paid'
                    ]);

                foreach ($ids as $orderId) {
                    $order = Order::find($orderId);
                    if ($order && $order->project) {
                        $order->project->decrement('stock_available', $order->quantity);
                    }
                }

                DB::commit();
                session()->forget('pending_transaction_ids');

                return redirect()->route('orders.success.multi', ['ids' => implode(',', $ids)])
                    ->with('success', 'Semua pesanan Anda berhasil dikonfirmasi!');

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
            }
        }

        return redirect()->route('orders.index')->with('error', 'Aksi pemesanan tidak valid.');
    }

    /* ═══════════════════════════════════════════════════════════
     |  MIDTRANS WEBHOOK — Mengubah status pesanan otomatis
     ═══════════════════════════════════════════════════════════ */
    public function midtransNotification(Request $request)
    {
        $serverKey = config('midtrans.server_key');
        $payload   = $request->all();
        $orderIdWebhook = $payload['order_id'] ?? null;

        if (!$orderIdWebhook) {
            return response()->json(['message' => 'Missing order_id'], 400);
        }

        $signatureKey = hash('sha512', $orderIdWebhook . $payload['status_code'] . $payload['gross_amount'] . $serverKey);

        if ($signatureKey !== $payload['signature_key']) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transactionStatus = $payload['transaction_status'];

        $newStatus = match (true) {
            $transactionStatus === 'capture' || $transactionStatus === 'settlement' => 'paid',
            in_array($transactionStatus, ['deny', 'cancel', 'expire']) => 'failed',
            default => null,
        };

        if ($newStatus) {
            // Cari data order berdasarkan order_number dari webhook midtrans
            $mainOrder = Order::where('order_number', $orderIdWebhook)->first();
            
            if ($mainOrder) {
                // Ambil semua order pending milik user ini yang dibuat di waktu yang sama/berdekatan jika multi-item
                $orderParts = explode('-', $orderIdWebhook);
                $baseTimestamp = isset($orderParts[1]) ? $orderParts[1] : null;

                if ($baseTimestamp && str_contains($orderIdWebhook, 'ORDER-')) {
                    $queryOrders = Order::where('order_number', 'LIKE', "ORDER-{$baseTimestamp}-%")
                                        ->where('status', 'pending');
                } else {
                    $queryOrders = Order::where('id', $mainOrder->id);
                }

                $ordersToUpdate = $queryOrders->get();

                foreach ($ordersToUpdate as $order) {
                    $order->update([
                        'status'         => $newStatus,
                        'payment_method' => $payload['payment_type'] ?? 'midtrans',
                    ]);

                    if ($newStatus === 'paid' && $order->project) {
                        $order->project->decrement('stock_available', $order->quantity);
                    }
                }
            }
        }

        return response()->json(['message' => 'OK']);
    }

    /* ═══════════════════════════════════════════════════════════
     |  REDIRECT CALLBACKS FROM POPUP MIDTRANS
     ═══════════════════════════════════════════════════════════ */
    public function midtransFinish(Request $request)
    {
        $ids = session()->get('pending_transaction_ids', []);
        
        if (!empty($ids)) {
            Order::whereIn('id', $ids)->update([
                'status'         => 'paid',
            ]);
            
            $orders = Order::whereIn('id', $ids)->get();
            foreach ($orders as $order) {
                if ($order->project) {
                    $order->project->decrement('stock_available', $order->quantity);
                }
            }
            
            session()->forget('pending_transaction_ids');
            return redirect()->route('orders.success.multi', ['ids' => implode(',', $ids)])
                ->with('success', 'Pembayaran sukses, terima kasih!');
        }

        return redirect()->route('orders.index');
    }

    public function midtransError(Request $request)
    {
        return redirect()->route('cart.index')->with('error', 'Pembayaran dibatalkan atau gagal.');
    }

    public function midtransPending(Request $request)
    {
        $ids = session()->get('pending_transaction_ids', []);
        return redirect()->route('orders.success.multi', ['ids' => implode(',', $ids ?? [])])
            ->with('info', 'Pembayaran Anda sedang menunggu penyelesaian.');
    }

    /* ═══════════════════════════════════════════════════════════
     |  SUCCESS INTERFACES
     ═══════════════════════════════════════════════════════════ */
    public function success($id)
    {
        $order = Order::with('project')->findOrFail($id);
        if ($order->user_id !== Auth::id()) abort(403);
        
        $transactions = collect([$order]);
        return view('main_page.projects.success', compact('transactions'));
    }

    public function successMulti(Request $request)
    {
        $ids = explode(',', $request->query('ids', ''));
        $transactions = Order::with('project')
            ->whereIn('id', $ids)
            ->where('user_id', Auth::id())
            ->get();

        if ($transactions->isEmpty()) return redirect()->route('orders.index');

        return view('main_page.projects.success', compact('transactions'));
    }

    /* ═══════════════════════════════════════════════════════════
     |  RIWAYAT BELANJA
     ═══════════════════════════════════════════════════════════ */
    public function show($id)
    {
        $order = Order::with('project')->findOrFail($id);
        if ($order->user_id !== Auth::id()) abort(403);

        $project  = $order->project;
        $quantity = $order->quantity;
        $subtotal = $order->subtotal;
        $tax      = $order->tax;
        $total    = $order->total_price;

        $transaction = $order;

        return view('main_page.orders.show', compact(
            'transaction', 'project', 'quantity', 'subtotal', 'tax', 'total'
        ));
    }

    public function index()
    {
        $transactions = Order::with('project')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('main_page.projects.order', compact('transactions'));
    }
}

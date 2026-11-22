<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Project;
use App\Services\CertificateService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
                // Gunakan status 'pending' agar pesanan tidak langsung dianggap lunas
                // Hanya update payment_method, jangan kurangi stok di sini
                Order::whereIn('id', $ids)
                    ->where('user_id', Auth::id())
                    ->update([
                        'payment_method' => $request->payment_method,
                        'status'         => 'pending' 
                    ]);

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
     |  MIDTRANS WEBHOOK — Satu-satunya jalur yang mengubah status
     |  pesanan, stok, dan menerbitkan sertifikat.
     ═══════════════════════════════════════════════════════════ */
    public function midtransNotification(Request $request, CertificateService $certificates): JsonResponse
    {
        $payload = $request->all();
        $orderIdWebhook = $payload['order_id'] ?? null;

        if (! $orderIdWebhook) {
            return response()->json(['message' => 'Missing order_id'], 400);
        }

        $expectedSignature = hash(
            'sha512',
            $orderIdWebhook.($payload['status_code'] ?? '').($payload['gross_amount'] ?? '').config('midtrans.server_key')
        );

        if (! is_string($payload['signature_key'] ?? null) || ! hash_equals($expectedSignature, $payload['signature_key'])) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $newStatus = match ($payload['transaction_status'] ?? null) {
            'capture', 'settlement' => 'paid',
            'deny', 'cancel', 'expire' => 'failed',
            default => null,
        };

        if ($newStatus === null) {
            return response()->json(['message' => 'OK']);
        }

        DB::transaction(function () use ($orderIdWebhook, $newStatus, $payload, $certificates) {
            $mainOrder = Order::query()->where('order_number', $orderIdWebhook)->first();

            if (! $mainOrder) {
                Log::warning('Midtrans webhook: order tidak ditemukan.', ['order_id' => $orderIdWebhook]);

                return;
            }

            // Notifikasi ulang untuk order yang sudah diproses tidak lagi menemukan baris 'pending'.
            $orders = $this->midtransOrderGroupQuery($mainOrder)
                ->where('status', 'pending')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                return;
            }

            if ($newStatus === 'paid' && (int) round((float) $orders->sum('total_price')) !== (int) round((float) ($payload['gross_amount'] ?? 0))) {
                Log::warning('Midtrans webhook: gross_amount berbeda dengan total pesanan.', [
                    'order_id' => $orderIdWebhook,
                    'gross_amount' => $payload['gross_amount'] ?? null,
                    'orders_total' => $orders->sum('total_price'),
                ]);
            }

            foreach ($orders as $order) {
                $order->update([
                    'status' => $newStatus,
                    'payment_method' => $payload['payment_type'] ?? 'midtrans',
                ]);

                if ($newStatus !== 'paid') {
                    continue;
                }

                $project = Project::query()->lockForUpdate()->find($order->project_id);
                $order->setRelation('project', $project);

                $project?->decrement('stock_available', $order->quantity);

                if ($certificates->canIssueFor($order)) {
                    $certificates->issueFor($order);
                } else {
                    Log::warning('Sertifikat tidak diterbitkan otomatis karena proyek belum approved.', [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'project_id' => $order->project_id,
                        'verification_status' => $project?->verification_status,
                    ]);
                }
            }
        });

        return response()->json(['message' => 'OK']);
    }

    /**
     * Checkout keranjang membuat beberapa order (ORDER-{timestamp}-{n}-...) yang
     * dibayar dalam satu transaksi Midtrans memakai order_number order pertama.
     *
     * @return Builder<Order>
     */
    private function midtransOrderGroupQuery(Order $mainOrder): Builder
    {
        $query = Order::query()->where('user_id', $mainOrder->user_id);

        if (preg_match('/^ORDER-(\d+)-/', $mainOrder->order_number, $matches) === 1) {
            return $query->where('order_number', 'like', "ORDER-{$matches[1]}-%");
        }

        return $query->whereKey($mainOrder->id);
    }

    /* ═══════════════════════════════════════════════════════════
     |  REDIRECT CALLBACKS FROM POPUP MIDTRANS
     |  Hanya mengarahkan ke halaman status; tidak mengubah data pesanan.
     ═══════════════════════════════════════════════════════════ */
    public function midtransFinish(Request $request): RedirectResponse
    {
        $ids = $this->redirectOrderIds($request);
        session()->forget('pending_transaction_ids');

        return $this->redirectToOrderStatus($ids);
    }

    public function midtransError(Request $request): RedirectResponse
    {
        return $this->redirectToOrderStatus($this->redirectOrderIds($request))
            ->with('error', 'Pembayaran dibatalkan atau gagal.');
    }

    public function midtransPending(Request $request): RedirectResponse
    {
        return $this->redirectToOrderStatus($this->redirectOrderIds($request))
            ->with('info', 'Pembayaran Anda sedang menunggu penyelesaian.');
    }

    /**
     * @return array<int, int>
     */
    private function redirectOrderIds(Request $request): array
    {
        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->merge(session()->get('pending_transaction_ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id);

        if ($ids->isNotEmpty()) {
            return Order::query()
                ->where('user_id', Auth::id())
                ->whereIn('id', $ids)
                ->pluck('id')
                ->all();
        }

        $midtransOrderId = $request->query('order_id');

        if (is_string($midtransOrderId) && $midtransOrderId !== '') {
            $mainOrder = Order::query()
                ->where('user_id', Auth::id())
                ->where('order_number', $midtransOrderId)
                ->first();

            if ($mainOrder) {
                return $this->midtransOrderGroupQuery($mainOrder)->pluck('id')->all();
            }
        }

        return [];
    }

    /**
     * @param  array<int, int>  $ids
     */
    private function redirectToOrderStatus(array $ids): RedirectResponse
    {
        if (empty($ids)) {
            return redirect()->route('buyer.transactions');
        }

        return redirect()->route('orders.success.multi', ['ids' => implode(',', $ids)]);
    }

    /* ═══════════════════════════════════════════════════════════
     |  SUCCESS INTERFACES — Status selalu dibaca dari database
     ═══════════════════════════════════════════════════════════ */
    public function success($id)
    {
        $order = Order::with('project')->findOrFail($id);
        if ($order->user_id !== Auth::id()) abort(403);

        $transactions = collect([$order]);
        $paymentState = $this->paymentState($transactions);

        return view('main_page.projects.success', compact('transactions', 'paymentState'));
    }

    public function successMulti(Request $request)
    {
        $ids = explode(',', $request->query('ids', ''));
        $transactions = Order::with('project')
            ->whereIn('id', $ids)
            ->where('user_id', Auth::id())
            ->get();

        if ($transactions->isEmpty()) return redirect()->route('orders.index');

        $paymentState = $this->paymentState($transactions);

        return view('main_page.projects.success', compact('transactions', 'paymentState'));
    }

    /**
     * @param  Collection<int, Order>  $orders
     * @return 'confirmed'|'failed'|'waiting'
     */
    private function paymentState(Collection $orders): string
    {
        if ($orders->every(fn (Order $order) => in_array($order->status, Order::PAID_STATUSES, true))) {
            return 'confirmed';
        }

        if ($orders->every(fn (Order $order) => in_array($order->status, ['failed', 'cancelled'], true))) {
            return 'failed';
        }

        return 'waiting';
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

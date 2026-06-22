<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AdminAuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionManagementController extends Controller
{
    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'project.seller', 'statusUpdater']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q
                ->where('order_number', 'like', "%{$search}%")
                ->orWhere('buyer_name', 'like', "%{$search}%")
                ->orWhereHas('project', fn ($project) => $project->where('name', 'like', "%{$search}%")));
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('main_page.admin-panel.transactions.index', compact('orders'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['pending', 'paid', 'verified', 'completed', 'cancelled', 'refunded']),
            ],
            'admin_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        if (in_array($validated['status'], ['cancelled', 'refunded'], true)
            && blank($validated['admin_notes'])) {
            return back()->withErrors(['admin_notes' => 'Catatan wajib diisi untuk pembatalan atau pengembalian dana.']);
        }

        $old = $order->only(['status', 'status_updated_by', 'status_updated_at', 'admin_notes']);

        $order->update([
            'status' => $validated['status'],
            'status_updated_by' => auth()->id(),
            'status_updated_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ]);

        $this->audit->log(
            'order.status.updated',
            "Status transaksi {$order->order_number} diubah menjadi {$validated['status']}.",
            $order,
            $old,
            $order->only(array_keys($old))
        );

        return back()->with('success', 'Status transaksi berhasil diperbarui.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AdminAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TransactionManagementController extends Controller
{
    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function index(Request $request)
    {
        $query = Order::with(['user', 'project.seller', 'statusUpdater', 'certificateIssuer']);

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
        $user = $request->user();
        $canManageTransactions = $user?->hasPermission('transactions.manage') ?? false;
        $canIssueCertificates = $user?->hasPermission('certificates.issue') ?? false;

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['pending', 'paid', 'verified', 'completed', 'cancelled', 'refunded']),
            ],
            'admin_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        if (! $canManageTransactions && ! $canIssueCertificates) {
            abort(403, 'Anda tidak memiliki izin untuk memperbarui transaksi.');
        }

        if (! $canManageTransactions && ! in_array($validated['status'], ['verified', 'completed'], true)) {
            return back()->withErrors([
                'status' => 'Auditor hanya dapat memverifikasi transaksi dan menerbitkan sertifikat.',
            ]);
        }

        if (in_array($validated['status'], ['verified', 'completed'], true) && ! $canIssueCertificates) {
            return back()->withErrors([
                'status' => 'Penerbitan sertifikat harus dilakukan oleh Auditor Pemerintah.',
            ]);
        }

        if ($validated['status'] === 'verified' && $order->status !== 'paid') {
            return back()->withErrors([
                'status' => 'Transaksi hanya dapat diverifikasi setelah pembayaran berhasil.',
            ]);
        }

        if ($validated['status'] === 'completed' && $order->status !== 'verified') {
            return back()->withErrors([
                'status' => 'Sertifikat hanya dapat diterbitkan setelah transaksi berstatus verified.',
            ]);
        }

        if (in_array($validated['status'], ['verified', 'completed'], true)) {
            if (! $order->project || $order->project->verification_status !== 'approved') {
                return back()->withErrors([
                    'status' => 'Proyek harus berstatus approved sebelum sertifikat dapat diproses.',
                ]);
            }
        }

        if (in_array($validated['status'], ['cancelled', 'refunded'], true)
            && blank($validated['admin_notes'])) {
            return back()->withErrors(['admin_notes' => 'Catatan wajib diisi untuk pembatalan atau pengembalian dana.']);
        }

        $old = $order->only([
            'status',
            'status_updated_by',
            'status_updated_at',
            'admin_notes',
            'certificate_number',
            'certificate_issued_at',
            'certificate_issued_by',
        ]);

        $updates = [
            'status' => $validated['status'],
            'status_updated_by' => auth()->id(),
            'status_updated_at' => now(),
            'admin_notes' => $validated['admin_notes'],
        ];

        if ($validated['status'] === 'completed') {
            $updates['certificate_number'] = $order->certificate_number ?: $this->generateCertificateNumber($order);
            $updates['certificate_issued_at'] = $order->certificate_issued_at ?: now();
            $updates['certificate_issued_by'] = auth()->id();
        }

        $order->update($updates);

        $this->audit->log(
            $validated['status'] === 'completed' ? 'certificate.issued' : 'order.status.updated',
            $validated['status'] === 'completed'
                ? "Sertifikat {$order->certificate_number} diterbitkan untuk transaksi {$order->order_number}."
                : "Status transaksi {$order->order_number} diubah menjadi {$validated['status']}.",
            $order,
            $old,
            $order->only(array_keys($old))
        );

        return back()->with(
            'success',
            $validated['status'] === 'completed'
                ? 'Sertifikat berhasil diterbitkan untuk transaksi ini.'
                : 'Status transaksi berhasil diperbarui.'
        );
    }

    private function generateCertificateNumber(Order $order): string
    {
        do {
            $number = 'CAMAR-CERT-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
        } while (Order::where('certificate_number', $number)->whereKeyNot($order->id)->exists());

        return $number;
    }
}

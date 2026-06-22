<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    /**
     * Tampilkan halaman riwayat transaksi.
     */
    public function index(Request $request)
    {
        $query = Transaction::query()->with('items');

        // ── Filter: Search ──
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('payment_method', 'like', "%{$search}%");
            });
        }

        // ── Filter: Status ──
        if ($status = $request->get('status')) {
            $allowed = ['success', 'pending', 'failed', 'refunded'];
            if (in_array($status, $allowed)) {
                $query->where('status', $status);
            }
        }

        // ── Filter: Date Range ──
        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // ── Sort ──
        $allowedCols = ['transaction_id', 'created_at', 'amount'];
        $sortCol = in_array($request->get('sort'), $allowedCols) ? $request->get('sort') : 'created_at';
        $sortDir = $request->get('dir') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortCol, $sortDir);

        // ── Paginate ──
        $transactions = $query->paginate(15)->withQueryString();

        // ── Stats (keseluruhan, bukan per-filter, agar card summary selalu akurat) ──
        $stats = $this->getStats();

        return view('transactions.index', compact('transactions', 'stats'));
    }

    /**
     * Return detail satu transaksi (untuk modal, JSON).
     */
    public function show(Request $request, Transaction $transaction)
    {
        $transaction->load('items');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($transaction);
        }

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Export transaksi ke CSV (respects filters).
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Transaction::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('payment_method', 'like', "%{$search}%");
            });
        }
        if ($status = $request->get('status')) {
            $allowed = ['success', 'pending', 'failed', 'refunded'];
            if (in_array($status, $allowed)) {
                $query->where('status', $status);
            }
        }
        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $filename = 'transaksi_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // BOM for Excel compatibility
            fputs($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, [
                'ID Transaksi',
                'Tanggal',
                'Pelanggan',
                'Email',
                'Metode Pembayaran',
                'Nominal',
                'Status',
            ]);

            // Data rows — chunk to avoid memory exhaustion
            $query->orderBy('created_at', 'desc')
                  ->chunk(500, function ($transactions) use ($handle) {
                      foreach ($transactions as $tx) {
                          fputcsv($handle, [
                              $tx->transaction_id,
                              $tx->created_at->format('d/m/Y H:i'),
                              $tx->customer_name,
                              $tx->customer_email,
                              $tx->payment_method,
                              $tx->amount,
                              $tx->status,
                          ]);
                      }
                  });

            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Stats summary untuk summary cards.
     */
    private function getStats(): array
    {
        $counts = Transaction::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalAmount = Transaction::where('status', 'success')->sum('amount');

        return [
            'total'        => array_sum($counts),
            'success'      => $counts['success']  ?? 0,
            'pending'      => $counts['pending']   ?? 0,
            'failed'       => $counts['failed']    ?? 0,
            'refunded'     => $counts['refunded']  ?? 0,
            'total_amount' => $totalAmount,
        ];
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Read-only: status pembayaran berasal dari webhook Midtrans dan sertifikat
 * diterbitkan otomatis oleh CertificateService.
 */
class TransactionManagementController extends Controller
{
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
}

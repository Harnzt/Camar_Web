<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Order; // 🔥 MENGGUNAKAN ORDER SEBAGAI PENGGANTI TRANSACTION
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SellerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ── Stat Cards (Menggunakan Model Order) ─────────────────────────
        $totalRevenue = Order::whereHas('project', fn($q) => $q->where('seller_id', $user->id))
            ->whereIn('status', ['paid', 'verified', 'completed'])
            ->sum('total_price');

        // 🔥 offset_ton diganti menjadi quantity sesuai field di model Order
        $totalCarbonSold = Order::whereHas('project', fn($q) => $q->where('seller_id', $user->id))
            ->whereIn('status', ['paid', 'verified', 'completed'])
            ->sum('quantity');

        $activeProjects = Project::where('seller_id', $user->id)
            ->where('stock_available', '>', 0)
            ->count();

        $totalStock = Project::where('seller_id', $user->id)
            ->sum('stock_available');

        // ── Grafik Penjualan Bulanan (Menggunakan Model Order) ──────
        $monthlySales = Order::whereHas('project', fn($q) => $q->where('seller_id', $user->id))
            ->whereIn('status', ['paid', 'verified', 'completed'])
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('SUM(quantity) as carbon_sold'), // 🔥 Menggunakan quantity
                DB::raw('COUNT(*) as trx_count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Isi bulan yang kosong dengan 0
        $chartLabels  = [];
        $chartRevenue = [];
        $chartCarbon  = [];

        for ($i = 11; $i >= 0; $i--) {
            $date  = now()->subMonths($i);
            $label = $date->format('M Y');
            $chartLabels[] = $label;

            $found = $monthlySales->first(
                fn($r) => $r->year == $date->year && $r->month == $date->month
            );

            $chartRevenue[] = $found ? (float) $found->revenue     : 0;
            $chartCarbon[]  = $found ? (float) $found->carbon_sold  : 0;
        }

        // ── Proyek Milik Seller (Menggunakan Relasi orders) ────────────────
        // 🔥 Asumsi: Di model Project, Anda memiliki relasi bernama orders() atau ubah ke orders jika sebelumnya bernama transactions
        $projects = Project::where('seller_id', $user->id)
            ->withCount('orders') 
            ->withSum(['orders as revenue_sum' => fn($q) =>
                $q->whereIn('status', ['paid', 'verified', 'completed'])
            ], 'total_price')
            ->withSum(['orders as carbon_sum' => fn($q) =>
                $q->whereIn('status', ['paid', 'verified', 'completed'])
            ], 'quantity') // 🔥 Menggunakan quantity
            ->latest()
            ->get();

        // ── Transaksi/Order Masuk Terbaru (Menggunakan Model Order) ────────
        $recentSales = Order::whereHas('project', fn($q) => $q->where('seller_id', $user->id))
            ->with(['project', 'user']) // 🔥 Relasi buyer diganti ke user sesuai model Order Anda
            ->latest()
            ->take(8)
            ->get();

        // ── Summary Pending (Menggunakan Model Order) ──────────────────────
        $pendingCount  = Order::whereHas('project', fn($q) => $q->where('seller_id', $user->id))
            ->where('status', 'pending')->count();
            
        $pendingAmount = Order::whereHas('project', fn($q) => $q->where('seller_id', $user->id))
            ->where('status', 'pending')->sum('total_price');

        return view('main_page.dashboard-seller.dashboard-seller', compact(
            'totalRevenue',
            'totalCarbonSold',
            'activeProjects',
            'totalStock',
            'chartLabels',
            'chartRevenue',
            'chartCarbon',
            'projects',
            'recentSales',
            'pendingCount',
            'pendingAmount',
        ));
    }

    public function destroy($id)
    {
        $project = Project::where('seller_id', Auth::id())->findOrFail($id);

        if ($project->image && file_exists(public_path('images/' . $project->image))) {
            unlink(public_path('images/' . $project->image));
        }

        $project->delete();

        return redirect()->back()->with('success', 'Proyek berhasil dihapus permanen dari portofolio Anda.');
    }

    public function create()
    {
        $user = Auth::user();
        
        // 🔥 Menggunakan Model Order
        $pendingCount  = Order::whereHas('project', fn($q) => $q->where('seller_id', $user->id))
            ->where('status', 'pending')->count();
        $pendingAmount = Order::whereHas('project', fn($q) => $q->where('seller_id', $user->id))
            ->where('status', 'pending')->sum('total_price');

        $chartLabels = []; $chartRevenue = []; $chartCarbon = [];

        return view('main_page.dashboard-seller.create', compact('pendingCount', 'pendingAmount', 'chartLabels', 'chartRevenue', 'chartCarbon'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'category'        => 'required',
            'location'        => 'required',
            'price_per_ton'   => 'required|numeric|min:0',
            'stock_available' => 'required|numeric|min:0',
            'description'     => 'required',
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
        }

        Project::create([
            'seller_id'         => Auth::id(),
            'company_name'      => Auth::user()->company_name ?? 'Mitra CAMAR', 
            'name'              => $request->name,
            'category'          => $request->category,
            'standard'          => $request->standard,
            'location'          => $request->location,
            'price_per_ton'     => $request->price_per_ton,
            'stock_available'   => $request->stock_available,
            'co2_per_year'      => $request->co2_per_year,
            'area_ha'           => $request->area_ha,
            'families_impacted' => $request->families_impacted,
            'duration_months'   => $request->duration_months,
            'description'       => $request->description,
            'methodology'       => $request->methodology,
            'image'             => $imageName,
            'verification_status' => 'pending',
            'submitted_at'      => now(),
        ]);

        return redirect()->route('seller.dashboard')->with('success', 'Proyek berhasil diajukan dan menunggu verifikasi admin.');
    }

    public function edit($id)
    {
        $project = Project::where('seller_id', Auth::id())->findOrFail($id);

        $user = Auth::user();
        
        // 🔥 Menggunakan Model Order
        $pendingCount  = Order::whereHas('project', fn($q) => $q->where('seller_id', $user->id))->where('status', 'pending')->count();
        $pendingAmount = Order::whereHas('project', fn($q) => $q->where('seller_id', $user->id))->where('status', 'pending')->sum('total_price');
        
        $chartLabels = []; $chartRevenue = []; $chartCarbon = [];

        return view('main_page.dashboard-seller.edit', compact('project', 'pendingCount', 'pendingAmount', 'chartLabels', 'chartRevenue', 'chartCarbon'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'category'        => 'required',
            'location'        => 'required',
            'price_per_ton'   => 'required|numeric|min:0',
            'stock_available' => 'required|numeric|min:0',
            'description'     => 'required',
        ]);

        $project = Project::where('seller_id', Auth::id())->findOrFail($id);

        if ($request->hasFile('image')) {
            if ($project->image && file_exists(public_path('images/' . $project->image))) {
                unlink(public_path('images/' . $project->image));
            }
            
            $imageName = time() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            $project->image = $imageName;
        }

        $project->update([
            'name'              => $request->name,
            'category'          => $request->category,
            'standard'          => $request->standard,
            'location'          => $request->location,
            'price_per_ton'     => $request->price_per_ton,
            'stock_available'   => $request->stock_available,
            'co2_per_year'      => $request->co2_per_year,
            'area_ha'           => $request->area_ha,
            'families_impacted' => $request->families_impacted,
            'duration_months'   => $request->duration_months,
            'description'       => $request->description,
            'methodology'       => $request->methodology,
            'verification_status' => 'pending',
            'reviewed_by'       => null,
            'reviewed_at'       => null,
            'rejection_reason'  => null,
            'admin_notes'       => null,
            'submitted_at'      => now(),
        ]);

        return redirect()->route('seller.dashboard')->with('success', 'Data proyek diperbarui dan diajukan kembali untuk verifikasi.');
    }

    public function transactions(Request $request)
    {
        $user = Auth::user();

        // Query dasar: ambil Order yang proyeknya dibuat oleh seller ini
        $query = Order::whereHas('project', function($q) use ($user) {
            $q->where('seller_id', $user->id);
        })->with(['project', 'user']); // eager load relasi proyek dan buyer

        // Filter 1: Berdasarkan Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter 2: Pencarian (Nama Proyek atau Nama Pembeli)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'LIKE', "%{$search}%")
                  ->orWhereHas('project', function($pq) use ($search) {
                      $pq->where('name', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Urutkan dari transaksi terbaru dan batasi dengan paginasi
        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        // Hitung ringkasan statistik khusus transaksi seller ini
        $stats = [
            'total_count' => Order::whereHas('project', fn($q) => $q->where('seller_id', $user->id))->count(),
            'paid_count'  => Order::whereHas('project', fn($q) => $q->where('seller_id', $user->id))->whereIn('status', ['paid', 'verified', 'completed'])->count(),
            'pending_count' => Order::whereHas('project', fn($q) => $q->where('seller_id', $user->id))->where('status', 'pending')->count(),
        ];

        return view('main_page.dashboard-seller.transactions', compact('orders', 'stats'));
    }
}

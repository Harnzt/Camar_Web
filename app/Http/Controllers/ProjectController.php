<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\EmissionCalculation;
use App\Services\ProjectRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectRecommendationService $recommendationService
    ) {
    }

    /**
     * Halaman list semua proyek (Katalog Adaptif)
     */
    public function index()
    {
        $user = Auth::user();
        $emission = null;

        if ($user && $user->isBuyer()) {
            // Ambil data kalkulasi terakhir user dari database
            $emission = EmissionCalculation::where('user_id', $user->id)
                ->latest()
                ->first();
        }

        $projects = Project::approved()->latest()->paginate(9);

        // 2. Sediakan wadah kosong untuk rekomendasi proyek agar tidak memicu undefined variable
        $recommendedProjects = collect();

        if ($user?->isBuyer()) {
            $recommendedProjects = $this->recommendationService
                ->recommend($user, $emission, 3);
        }

        // 4. Pastikan memanggil view 'index', bukan 'projects' lagi
        return view('main_page.projects.projects', compact('projects', 'recommendedProjects', 'user', 'emission'));
    }
    
    /**
     * Halaman detail satu proyek
     */
    public function show($id)
    {
        // 1. Ambil data (pakai find biasa dulu agar tidak langsung 404 kalau kosong)
        $project = Project::approved()->find($id);

        if (!$project) {
            abort(404);
        }

        // 3. Arahkan ke file resources/views/main_page/projects/show.blade.php
        return view('main_page.projects.show', compact('project'));
    }

    /**
     * Watchlist toggle (AJAX) — simpan ke session
     */
    public function toggleWatchlist(Request $request)
    {
        $request->validate(['project_id' => 'required|exists:projects,id']);

        $id        = $request->project_id;
        $watchlist = session()->get('watchlist', []);

        if (in_array($id, $watchlist)) {
            $watchlist = array_values(array_diff($watchlist, [$id]));
            $watching  = false;
        } else {
            $watchlist[] = $id;
            $watching    = true;
        }

        session()->put('watchlist', $watchlist);

        return response()->json([
            'success'   => true,
            'watching'  => $watching,
        ]);
    }

    /**
     * Checkout (Buy Now)
     */
    public function checkout(Request $request)
    {
        $user = Auth::user();

        if ($user?->isBuyer() && !$user->hasEmissionCalculation()) {
            return redirect()
                ->route('calculator')
                ->with(
                    'warning',
                    'Hitung emisi karbon Anda terlebih dahulu sebelum melakukan pembelian.'
                );
        }

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $project  = Project::findOrFail($request->project_id);
        $quantity = (int) $request->quantity;

        // Simpan order sementara ke session
        session()->put('checkout', [
            'project'  => $project,
            'quantity' => $quantity,
            'total'    => $project->price_per_ton * $quantity,
        ]);

        // Arahkan ke halaman checkout
       return redirect()->route('orders.checkout.view', ['id' => $project->id]);
    }

    // public function boot(): void
    // {
    //     // 🔥 2. TAMBAHKAN BARIS INI AGAR PAGINATION OTOMATIS PAKAI CSS BOOTSTRAP 5
    //     Paginator::useBootstrapFive(); 
    // }
}

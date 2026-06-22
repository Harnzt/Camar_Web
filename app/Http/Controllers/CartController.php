<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $total = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
        return view('main_page.projects.cart', compact('cart', 'total'));
    }
    public function mergeSessionCart()
    {
        $cart = session()->get('cart', []);
        
        // Jika session keranjang kosong, tidak perlu memproses apapun
        if (empty($cart)) {
            return;
        }// fungsi ini cukup dibiarkan kosong agar tidak memicu error saat dipanggil AuthController.
    }

    public function add(Request $request)
    {
        $user = Auth::user();

        if ($user?->isBuyer() && !$user->hasEmissionCalculation()) {
            session()->flash(
                'warning',
                'Hitung emisi karbon Anda terlebih dahulu sebelum membeli atau menambahkan proyek ke keranjang.'
            );

            return response()->json([
                'success'      => false,
                'message'      => 'Anda belum memiliki kalkulasi emisi karbon.',
                'redirect_url' => route('calculator'),
            ], 403);
        }

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $project = Project::approved()->findOrFail($request->project_id);
        $cart    = session()->get('cart', []);
        $pid     = $request->project_id;

        if (isset($cart[$pid])) {
            $cart[$pid]['quantity'] += $request->quantity;
        } else {
            $cart[$pid] = [
                'project_id' => $pid,
                'name'       => $project->name,
                'image'      => $project->image,
                'price'      => $project->price_per_ton,
                'quantity'   => $request->quantity,
            ];
        }

        session()->put('cart', $cart);

        return response()->json([
            'success'    => true,
            'cart_count' => count($cart),
            'message'    => 'Berhasil ditambahkan ke keranjang.',
        ]);
    }

    public function update(Request $request)
    {
        $cart = session()->get('cart', []);
        $id   = $request->id;

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = max(1, (int) $request->quantity);
            session()->put('cart', $cart);
        }

        return response()->json(['success' => true]);
    }

    public function remove(Request $request)
    {
        $cart = session()->get('cart', []);
        unset($cart[$request->id]);
        session()->put('cart', $cart);

        return response()->json(['success' => true]);
    }

    public function clear()
    {
        session()->forget('cart');
        return response()->json(['success' => true]);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akun yang belum diverifikasi admin tetap boleh menjelajah, menghitung emisi,
 * dan mengisi keranjang, tetapi tidak boleh membuat pesanan atau membayar.
 */
class EnsureBuyerIsVerified
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $message = $request->user()?->purchaseBlockedReason();

        if ($message === null) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 403);
        }

        return back()->with('warning', $message);
    }
}

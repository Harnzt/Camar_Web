<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RejectLegacySellerSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isSeller()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'loginError' => 'Akun seller tidak lagi dapat digunakan untuk login. Silakan hubungi admin.',
            ]);
        }

        return $next($request);
    }
}

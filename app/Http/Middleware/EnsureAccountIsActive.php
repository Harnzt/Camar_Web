<?php

namespace App\Http\Middleware;

use App\Models\AdminLoginLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Login already rejects suspended accounts; this closes the gap for
 * sessions that were opened before the account was suspended.
 */
class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isSuspended()) {
            return $next($request);
        }

        if ($user->isAdministrator()) {
            AdminLoginLog::query()
                ->where('admin_id', $user->id)
                ->whereKey($request->session()->get('admin_login_log_id'))
                ->whereNull('logged_out_at')
                ->update(['logged_out_at' => now()]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = 'Akun Anda sedang dinonaktifkan. Hubungi super admin.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 401);
        }

        return redirect()->route('login')->withErrors(['loginError' => $message]);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $permissions = array_filter(explode('|', $permission));

        abort_unless(
            $request->user() && collect($permissions)->contains(
                fn (string $item) => $request->user()->hasPermission($item)
            ),
            403,
            'Anda tidak memiliki izin untuk melakukan tindakan ini.'
        );

        return $next($request);
    }
}

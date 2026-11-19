<?php

namespace App\Services;

use App\Models\AdminLoginLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountSessionService
{
    /**
     * Force every active session and API token of the account to end,
     * so a suspended or deleted account loses access immediately.
     *
     * @return int Number of web sessions removed.
     */
    public function terminate(User $user): int
    {
        $removedSessions = 0;

        if (config('session.driver') === 'database' && Schema::hasTable(config('session.table', 'sessions'))) {
            $removedSessions = DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->getKey())
                ->delete();
        }

        $user->tokens()->delete();

        AdminLoginLog::query()
            ->where('admin_id', $user->getKey())
            ->whereNull('logged_out_at')
            ->update(['logged_out_at' => now()]);

        return $removedSessions;
    }
}

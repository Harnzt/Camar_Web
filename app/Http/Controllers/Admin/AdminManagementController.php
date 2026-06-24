<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminManagementController extends Controller
{
    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));

        $admins = User::query()
            ->whereIn('role', ['admin', 'super_admin'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                });
            })
            ->with('latestAdminLogin')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('main_page.admin-panel.admins.index', compact('admins'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'super_admin'])],
            'password' => ['required', Password::min(8)],
        ]);

        $admin = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'account_category' => 'personal',
            'status' => 'verified',
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        $this->audit->log(
            'admin.created',
            "Akun {$admin->role} {$admin->email} dibuat.",
            $admin,
            [],
            $admin->only(['name', 'email', 'role', 'status'])
        );

        return back()->with('success', 'Akun administrator berhasil dibuat.');
    }

    public function update(Request $request, User $admin)
    {
        $this->ensureAdministrator($admin);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($admin->id)],
            'role' => ['required', Rule::in(['admin', 'super_admin'])],
        ]);

        if ($admin->isSuperAdmin() && $validated['role'] !== 'super_admin' && $this->isLastSuperAdmin($admin)) {
            return back()->withErrors(['role' => 'Super admin terakhir tidak dapat diturunkan menjadi admin.']);
        }

        $old = $admin->only(['name', 'email', 'role']);
        $admin->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);
        $admin->save();

        $this->audit->log(
            'admin.updated',
            "Akun administrator {$admin->email} diperbarui.",
            $admin,
            $old,
            $admin->only(array_keys($old))
        );

        return back()->with('success', 'Akun administrator berhasil diperbarui.');
    }

    public function updateStatus(Request $request, User $admin)
    {
        $this->ensureAdministrator($admin);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['verified', 'suspended'])],
        ]);

        if ($admin->is(auth()->user()) && $validated['status'] === 'suspended') {
            return back()->withErrors(['status' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        }

        if ($validated['status'] === 'suspended' && $admin->isSuperAdmin() && $this->isLastActiveSuperAdmin($admin)) {
            return back()->withErrors(['status' => 'Super admin aktif terakhir tidak dapat dinonaktifkan.']);
        }

        $old = $admin->only(['status', 'suspended_at', 'suspension_reason']);
        $admin->update([
            'status' => $validated['status'],
            'suspended_at' => $validated['status'] === 'suspended' ? now() : null,
            'suspension_reason' => $validated['status'] === 'suspended'
                ? 'Dinonaktifkan oleh super admin.'
                : null,
        ]);

        $this->audit->log(
            'admin.status.updated',
            "Status akun {$admin->email} diubah menjadi {$validated['status']}.",
            $admin,
            $old,
            $admin->only(array_keys($old))
        );

        return back()->with('success', 'Status administrator berhasil diperbarui.');
    }

    public function updatePassword(Request $request, User $admin)
    {
        $this->ensureAdministrator($admin);

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $admin->update(['password' => Hash::make($validated['password'])]);

        $this->audit->log(
            'admin.password.updated',
            "Password akun {$admin->email} diperbarui.",
            $admin
        );

        return back()->with('success', 'Password administrator berhasil diperbarui.');
    }

    public function loginLogs(User $admin): JsonResponse
    {
        $this->ensureAdministrator($admin);

        $logs = $admin->adminLoginLogs()
            ->latest('logged_in_at')
            ->paginate(10)
            ->through(fn ($log) => [
                'id' => $log->id,
                'logged_in_at' => $log->logged_in_at?->format('d M Y H:i:s'),
                'logged_out_at' => $log->logged_out_at?->format('d M Y H:i:s'),
                'ip_address' => $log->ip_address ?: '-',
                'device' => $this->deviceLabel($log->user_agent),
            ]);

        return response()->json([
            'admin' => $admin->only(['id', 'name', 'email']),
            'logs' => $logs,
        ]);
    }

    public function destroy(User $admin)
    {
        $this->ensureAdministrator($admin);

        if ($admin->is(auth()->user())) {
            return back()->withErrors(['delete' => 'Anda tidak dapat menghapus akun sendiri.']);
        }

        if ($admin->isSuperAdmin() && $this->isLastSuperAdmin($admin)) {
            return back()->withErrors(['delete' => 'Super admin terakhir tidak dapat dihapus.']);
        }

        $snapshot = $admin->only(['name', 'email', 'role', 'status']);
        $this->audit->log(
            'admin.deleted',
            "Akun administrator {$admin->email} dihapus.",
            $admin,
            $snapshot
        );
        $admin->delete();

        return back()->with('success', 'Akun administrator berhasil dihapus.');
    }

    private function ensureAdministrator(User $admin): void
    {
        abort_unless($admin->isAdministrator(), 404);
    }

    private function isLastSuperAdmin(User $admin): bool
    {
        return User::query()
            ->where('role', 'super_admin')
            ->whereKeyNot($admin->id)
            ->doesntExist();
    }

    private function isLastActiveSuperAdmin(User $admin): bool
    {
        return User::query()
            ->where('role', 'super_admin')
            ->where('status', 'verified')
            ->whereKeyNot($admin->id)
            ->doesntExist();
    }

    private function deviceLabel(?string $userAgent): string
    {
        if (!$userAgent) {
            return '-';
        }

        $browser = str_contains($userAgent, 'Edg/') ? 'Microsoft Edge'
            : (str_contains($userAgent, 'Chrome/') ? 'Google Chrome'
            : (str_contains($userAgent, 'Firefox/') ? 'Mozilla Firefox'
            : (str_contains($userAgent, 'Safari/') ? 'Safari' : 'Browser lain')));

        $platform = str_contains($userAgent, 'Windows') ? 'Windows'
            : (str_contains($userAgent, 'Android') ? 'Android'
            : (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') ? 'iOS'
            : (str_contains($userAgent, 'Macintosh') ? 'macOS' : 'Perangkat lain')));

        return "{$browser} · {$platform}";
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminManagementController extends Controller
{
    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function index()
    {
        $admins = User::whereIn('role', ['admin', 'super_admin'])
            ->latest()
            ->paginate(15);

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
        abort_unless($admin->isAdministrator(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($admin->id)],
            'role' => ['required', Rule::in(['admin', 'super_admin'])],
            'status' => ['required', Rule::in(['verified', 'suspended'])],
            'password' => ['nullable', Password::min(8)],
        ]);

        if ($admin->is(auth()->user()) && $validated['status'] === 'suspended') {
            return back()->withErrors(['status' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        }

        $old = $admin->only(['name', 'email', 'role', 'status']);
        $admin->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'status' => $validated['status'],
            'suspended_at' => $validated['status'] === 'suspended' ? now() : null,
            'suspension_reason' => $validated['status'] === 'suspended'
                ? 'Dinonaktifkan oleh super admin.'
                : null,
        ]);

        if (!blank($validated['password'] ?? null)) {
            $admin->password = Hash::make($validated['password']);
        }

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
}

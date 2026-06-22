<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AdminAuditService;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function __construct(private readonly AdminAuditService $audit)
    {
    }

    public function index()
    {
        $roles = Role::with('permissions')->orderBy('id')->get();
        $permissions = Permission::orderBy('name')->get();

        return view('main_page.admin-panel.roles.index', compact('roles', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        abort_if($role->slug === 'super_admin', 403, 'Permission super admin tidak dapat dibatasi.');

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $old = $role->permissions()->pluck('slug')->all();
        $role->permissions()->sync($validated['permissions'] ?? []);
        $new = $role->permissions()->pluck('slug')->all();

        $this->audit->log(
            'role.permissions.updated',
            "Permission role {$role->name} diperbarui.",
            $role,
            ['permissions' => $old],
            ['permissions' => $new]
        );

        return back()->with('success', 'Permission role berhasil diperbarui.');
    }
}

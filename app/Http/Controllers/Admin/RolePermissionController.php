<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\AdminAuditService;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    public function __construct(private readonly AdminAuditService $audit) {}

    /**
     * Only administrator roles use panel permissions; buyer and seller never
     * reach the admin panel, so they are not shown or editable here.
     */
    private const EDITABLE_ROLES = ['admin', 'auditor'];

    public function index()
    {
        $roles = Role::with('permissions')
            ->whereIn('slug', self::EDITABLE_ROLES)
            ->orderBy('id')
            ->get();
        $superAdminRole = Role::where('slug', 'super_admin')->first();
        $permissions = Permission::orderBy('name')->get();

        return view('main_page.admin-panel.roles.index', compact('roles', 'superAdminRole', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        abort_if($role->slug === 'super_admin', 403, 'Permission super admin tidak dapat dibatasi.');
        abort_unless(in_array($role->slug, self::EDITABLE_ROLES, true), 404);

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

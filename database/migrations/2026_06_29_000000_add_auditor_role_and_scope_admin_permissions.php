<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('buyer','seller','auditor','admin','super_admin') NOT NULL DEFAULT 'buyer'");
        }

        $now = now();

        DB::table('roles')->updateOrInsert(
            ['slug' => 'auditor'],
            [
                'name' => 'Auditor',
                'description' => 'Petugas verifikasi dokumen akun dan proyek',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        DB::table('roles')->where('slug', 'admin')->update([
            'description' => 'Pengelola akun auditor',
            'updated_at' => $now,
        ]);

        DB::table('roles')->where('slug', 'super_admin')->update([
            'description' => 'Pengelola akun admin dan audit administrator',
            'updated_at' => $now,
        ]);

        $this->syncRolePermissions('super_admin', [
            'admin.dashboard',
            'admins.manage',
            'audit.view',
        ]);

        $this->syncRolePermissions('admin', [
            'admin.dashboard',
            'admins.manage',
        ]);

        $this->syncRolePermissions('auditor', [
            'admin.dashboard',
            'users.verify',
            'documents.verify',
            'projects.verify',
            'audit.view',
        ]);
    }

    public function down(): void
    {
        $this->syncRolePermissions('super_admin', DB::table('permissions')->pluck('slug')->all());

        $this->syncRolePermissions('admin', [
            'admin.dashboard',
            'users.verify',
            'documents.verify',
            'projects.verify',
            'transactions.manage',
        ]);

        DB::table('role_permissions')
            ->where('role_id', DB::table('roles')->where('slug', 'auditor')->value('id'))
            ->delete();
        DB::table('roles')->where('slug', 'auditor')->delete();
        DB::table('users')->where('role', 'auditor')->update(['role' => 'admin']);

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('buyer','seller','admin','super_admin') NOT NULL DEFAULT 'buyer'");
        }
    }

    private function syncRolePermissions(string $roleSlug, array $permissionSlugs): void
    {
        $roleId = DB::table('roles')->where('slug', $roleSlug)->value('id');

        if (!$roleId) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('slug', $permissionSlugs)
            ->pluck('id');

        DB::table('role_permissions')->where('role_id', $roleId)->delete();

        foreach ($permissionIds as $permissionId) {
            DB::table('role_permissions')->insert([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }
    }
};

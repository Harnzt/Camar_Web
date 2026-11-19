<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Payment status now comes only from Midtrans, so "transactions.manage" has no
 * action left behind it. It is replaced by a read-only "transactions.view"
 * permission that lets admins see transactions and the sales charts.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('permissions')->updateOrInsert(
            ['slug' => 'transactions.view'],
            [
                'name' => 'Lihat Transaksi & Grafik Penjualan',
                'description' => 'Melihat daftar transaksi dan grafik penjualan tanpa mengubah status',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $viewPermissionId = DB::table('permissions')->where('slug', 'transactions.view')->value('id');

        foreach (['admin', 'auditor', 'super_admin'] as $roleSlug) {
            $roleId = DB::table('roles')->where('slug', $roleSlug)->value('id');

            if ($roleId) {
                DB::table('role_permissions')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission_id' => $viewPermissionId,
                ]);
            }
        }

        $managePermissionId = DB::table('permissions')->where('slug', 'transactions.manage')->value('id');

        if ($managePermissionId) {
            DB::table('role_permissions')->where('permission_id', $managePermissionId)->delete();
            DB::table('permissions')->where('id', $managePermissionId)->delete();
        }

        DB::table('roles')->where('slug', 'admin')->update([
            'description' => 'Mengelola proyek dan akun auditor, serta memantau transaksi',
            'updated_at' => $now,
        ]);

        DB::table('roles')->where('slug', 'auditor')->update([
            'description' => 'Memverifikasi akun pembeli dan menerbitkan sertifikat. Proyek hanya dipantau.',
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        $now = now();

        DB::table('permissions')->updateOrInsert(
            ['slug' => 'transactions.manage'],
            ['name' => 'Kelola Status Transaksi', 'description' => null, 'created_at' => $now, 'updated_at' => $now]
        );

        $viewPermissionId = DB::table('permissions')->where('slug', 'transactions.view')->value('id');

        if ($viewPermissionId) {
            DB::table('role_permissions')->where('permission_id', $viewPermissionId)->delete();
            DB::table('permissions')->where('id', $viewPermissionId)->delete();
        }
    }
};

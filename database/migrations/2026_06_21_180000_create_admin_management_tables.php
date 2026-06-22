<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('buyer','seller','admin','super_admin') NOT NULL DEFAULT 'buyer'");
            DB::statement("ALTER TABLE users MODIFY status ENUM('pending','verified','rejected','suspended') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'verified_by')) {
                $table->foreignId('verified_by')->nullable()->after('status')
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }
            if (!Schema::hasColumn('users', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('verified_at');
            }
            if (!Schema::hasColumn('users', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable()->after('rejection_reason');
            }
            if (!Schema::hasColumn('users', 'suspension_reason')) {
                $table->text('suspension_reason')->nullable()->after('suspended_at');
            }
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(true);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('document_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->string('document_path');
            $table->enum('status', ['pending', 'approved', 'rejected', 'revision_required'])
                ->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'document_type']);
        });

        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->nullableMorphs('target');
            $table->text('description');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['action', 'created_at']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->enum('verification_status', [
                'pending', 'approved', 'rejected', 'revision_required',
            ])->default('approved')->after('seller_id');
            $table->foreignId('reviewed_by')->nullable()->after('verification_status')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('rejection_reason')->nullable()->after('reviewed_at');
            $table->text('admin_notes')->nullable()->after('rejection_reason');
            $table->timestamp('submitted_at')->nullable()->after('admin_notes');
            $table->index('verification_status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('status_updated_by')->nullable()->after('status')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('status_updated_at')->nullable()->after('status_updated_by');
            $table->text('admin_notes')->nullable()->after('status_updated_at');
            $table->index('status');
        });

        $now = now();
        $roles = [
            ['name' => 'Buyer', 'slug' => 'buyer', 'description' => 'Pembeli kredit karbon'],
            ['name' => 'Seller', 'slug' => 'seller', 'description' => 'Penyedia proyek karbon'],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Petugas operasional dan verifikasi'],
            ['name' => 'Super Admin', 'slug' => 'super_admin', 'description' => 'Pengelola administrator dan seluruh sistem'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert([...$role, 'is_system' => true, 'created_at' => $now, 'updated_at' => $now]);
        }

        $permissions = [
            ['name' => 'Lihat Dashboard Admin', 'slug' => 'admin.dashboard'],
            ['name' => 'Verifikasi Akun', 'slug' => 'users.verify'],
            ['name' => 'Verifikasi Dokumen', 'slug' => 'documents.verify'],
            ['name' => 'Verifikasi Proyek', 'slug' => 'projects.verify'],
            ['name' => 'Kelola Status Transaksi', 'slug' => 'transactions.manage'],
            ['name' => 'Kelola Admin', 'slug' => 'admins.manage'],
            ['name' => 'Kelola Role dan Permission', 'slug' => 'permissions.manage'],
            ['name' => 'Lihat Audit Log', 'slug' => 'audit.view'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert([
                ...$permission,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $adminRoleId = DB::table('roles')->where('slug', 'admin')->value('id');
        $superAdminRoleId = DB::table('roles')->where('slug', 'super_admin')->value('id');
        $operationalPermissions = DB::table('permissions')
            ->whereIn('slug', [
                'admin.dashboard',
                'users.verify',
                'documents.verify',
                'projects.verify',
                'transactions.manage',
            ])
            ->pluck('id');

        foreach ($operationalPermissions as $permissionId) {
            DB::table('role_permissions')->insert([
                'role_id' => $adminRoleId,
                'permission_id' => $permissionId,
            ]);
        }

        foreach (DB::table('permissions')->pluck('id') as $permissionId) {
            DB::table('role_permissions')->insert([
                'role_id' => $superAdminRoleId,
                'permission_id' => $permissionId,
            ]);
        }

        DB::table('projects')->update([
            'verification_status' => 'approved',
            'submitted_at' => DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)'),
        ]);

        DB::table('users')
            ->where('status', 'verified')
            ->update(['verified_at' => DB::raw('COALESCE(updated_at, CURRENT_TIMESTAMP)')]);

        DB::table('users')
            ->whereNotNull('documents')
            ->orderBy('id')
            ->each(function ($user) use ($now) {
                $documents = json_decode($user->documents, true);
                if (!is_array($documents)) {
                    return;
                }

                foreach ($documents as $type => $path) {
                    if (!$path) {
                        continue;
                    }

                    DB::table('document_verifications')->insertOrIgnore([
                        'user_id' => $user->id,
                        'document_type' => $type,
                        'document_path' => $path,
                        'status' => $user->status === 'verified' ? 'approved' : 'pending',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['status_updated_by']);
            $table->dropIndex(['status']);
            $table->dropColumn(['status_updated_by', 'status_updated_at', 'admin_notes']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropIndex(['verification_status']);
            $table->dropColumn([
                'verification_status', 'reviewed_by', 'reviewed_at',
                'rejection_reason', 'admin_notes', 'submitted_at',
            ]);
        });

        Schema::dropIfExists('admin_activity_logs');
        Schema::dropIfExists('document_verifications');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'verified_by', 'verified_at', 'rejection_reason',
                'suspended_at', 'suspension_reason',
            ]);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('buyer','seller') NOT NULL DEFAULT 'buyer'");
            DB::statement("ALTER TABLE users MODIFY status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending'");
        }
    }
};

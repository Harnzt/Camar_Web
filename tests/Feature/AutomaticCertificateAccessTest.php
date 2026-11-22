<?php

use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

function accessTestUser(string $role, string $status = 'verified'): User
{
    return User::create([
        'name' => ucfirst(str_replace('_', ' ', $role)).' Akses',
        'email' => "{$role}-{$status}-akses@example.test",
        'password' => 'password',
        'role' => $role,
        'account_category' => 'personal',
        'status' => $status,
    ]);
}

function rolePermissionSlugs(string $roleSlug): array
{
    return Role::where('slug', $roleSlug)->firstOrFail()->permissions()->pluck('slug')->all();
}

test('manual certificate route and permission no longer exist', function () {
    expect(Route::has('admin.transactions.update'))->toBeFalse()
        ->and(Permission::where('slug', 'certificates.issue')->exists())->toBeFalse()
        ->and(rolePermissionSlugs('auditor'))->not->toContain('certificates.issue');

    $this->actingAs(accessTestUser('super_admin'))
        ->get(route('admin.roles.index'))
        ->assertOk()
        ->assertDontSee('Terbitkan Sertifikat');
});

test('rolling back the migration restores certificates.issue for auditor and super admin', function () {
    $migration = require database_path('migrations/2026_11_22_155400_remove_certificates_issue_permission.php');

    $migration->down();

    expect(rolePermissionSlugs('auditor'))->toContain('certificates.issue')
        ->and(rolePermissionSlugs('super_admin'))->toContain('certificates.issue');

    $migration->up();

    expect(Permission::where('slug', 'certificates.issue')->exists())->toBeFalse();
});

test('auditor keeps dashboard, project review, transactions and audit log access', function () {
    $auditor = accessTestUser('auditor');
    $project = Project::create([
        'name' => 'Proyek Menunggu Auditor',
        'company_name' => 'PT Uji',
        'category' => 'mangrove',
        'price_per_ton' => 100000,
        'stock_available' => 10,
        'verification_status' => 'pending',
        'submitted_at' => now(),
    ]);

    expect(rolePermissionSlugs('auditor'))->toContain('admin.dashboard', 'projects.verify', 'transactions.view', 'audit.view');

    $this->actingAs($auditor)->get(route('admin.dashboard'))->assertOk()->assertSee('Penjualan 12 bulan terakhir');
    $this->actingAs($auditor)->get(route('admin.projects.index'))->assertOk();
    $this->actingAs($auditor)->get(route('admin.projects.show', $project))->assertOk();
    $this->actingAs($auditor)->get(route('admin.transactions.index'))->assertOk();
    $this->actingAs($auditor)->get(route('admin.audit.index'))->assertOk();
    $this->actingAs($auditor)->get(route('admin.audit.export'))->assertOk();
});

test('super admin can still verify buyer accounts and open the add project form', function () {
    $superAdmin = accessTestUser('super_admin');
    $buyer = accessTestUser('buyer', 'pending');

    $this->actingAs($superAdmin)->get(route('admin.users.index'))->assertOk();
    $this->actingAs($superAdmin)->get(route('admin.users.show', $buyer))->assertOk();
    $this->actingAs($superAdmin)->get(route('admin.projects.create'))->assertOk();

    $this->actingAs($superAdmin)
        ->patch(route('admin.users.status', $buyer), ['status' => 'verified'])
        ->assertRedirect();

    expect($buyer->fresh()->status)->toBe('verified');
});

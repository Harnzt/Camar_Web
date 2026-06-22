<?php

use App\Models\AdminActivityLog;
use App\Models\DocumentVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAdministrativeUser(string $role): User
{
    return User::create([
        'name' => ucfirst(str_replace('_', ' ', $role)),
        'email' => $role . '@example.test',
        'password' => 'password',
        'role' => $role,
        'account_category' => 'personal',
        'status' => 'verified',
    ]);
}

test('buyer cannot access admin dashboard', function () {
    $buyer = User::create([
        'name' => 'Buyer',
        'email' => 'buyer-admin-test@example.test',
        'password' => 'password',
        'role' => 'buyer',
        'account_category' => 'personal',
        'status' => 'verified',
    ]);

    $this->actingAs($buyer)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admin can access dashboard and review a document', function () {
    $admin = createAdministrativeUser('admin');
    $buyer = User::create([
        'name' => 'Buyer Review',
        'email' => 'buyer-review@example.test',
        'password' => 'password',
        'role' => 'buyer',
        'account_category' => 'personal',
        'status' => 'pending',
    ]);
    $document = DocumentVerification::create([
        'user_id' => $buyer->id,
        'document_type' => 'npwp',
        'document_path' => 'documents/example.pdf',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();

    $this->actingAs($admin)
        ->patch(route('admin.documents.update', $document), [
            'status' => 'approved',
            'notes' => 'Dokumen valid.',
        ])
        ->assertRedirect();

    expect($document->fresh()->status)->toBe('approved')
        ->and(AdminActivityLog::where('action', 'document.reviewed')->exists())->toBeTrue();
});

test('regular admin cannot manage administrator accounts', function () {
    $admin = createAdministrativeUser('admin');

    $this->actingAs($admin)
        ->get(route('admin.admins.index'))
        ->assertForbidden();
});

test('super admin can create an admin account', function () {
    $superAdmin = createAdministrativeUser('super_admin');

    $this->actingAs($superAdmin)
        ->post(route('admin.admins.store'), [
            'name' => 'Admin Operasional',
            'email' => 'operasional@example.test',
            'role' => 'admin',
            'password' => 'Password123!',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'email' => 'operasional@example.test',
        'role' => 'admin',
        'status' => 'verified',
    ]);
});

test('administrator can view the complete public landing page', function () {
    $admin = createAdministrativeUser('admin');

    $this->actingAs($admin)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Wujudkan Masa Depan Hijau')
        ->assertSee('Layanan Kami');
});

<?php

use App\Models\AdminActivityLog;
use App\Models\DocumentVerification;
use App\Models\User;
use App\Models\AdminLoginLog;
use Illuminate\Support\Facades\Hash;
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

test('super admin can edit status password and delete another admin', function () {
    $superAdmin = createAdministrativeUser('super_admin');
    $admin = createAdministrativeUser('admin');

    $this->actingAs($superAdmin)
        ->patch(route('admin.admins.update', $admin), [
            'name' => 'Admin Baru',
            'email' => 'admin-baru@example.test',
            'role' => 'admin',
        ])
        ->assertRedirect();

    $this->actingAs($superAdmin)
        ->patch(route('admin.admins.status', $admin), ['status' => 'suspended'])
        ->assertRedirect();

    $this->actingAs($superAdmin)
        ->patch(route('admin.admins.password', $admin), [
            'password' => 'PasswordBaru123!',
            'password_confirmation' => 'PasswordBaru123!',
        ])
        ->assertRedirect();

    $admin->refresh();
    expect($admin->name)->toBe('Admin Baru')
        ->and($admin->status)->toBe('suspended')
        ->and(Hash::check('PasswordBaru123!', $admin->password))->toBeTrue();

    $this->actingAs($superAdmin)
        ->delete(route('admin.admins.destroy', $admin))
        ->assertRedirect();

    expect($admin->fresh()->trashed())->toBeTrue();
});

test('administrator login and logout are recorded', function () {
    $admin = createAdministrativeUser('admin');

    $this->post(route('login.process'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    $log = AdminLoginLog::where('admin_id', $admin->id)->first();
    expect($log)->not->toBeNull()
        ->and($log->logged_out_at)->toBeNull();

    $this->post(route('logout'))->assertRedirect(route('login'));

    expect($log->fresh()->logged_out_at)->not->toBeNull();
});

test('super admin cannot delete own account', function () {
    $superAdmin = createAdministrativeUser('super_admin');

    $this->actingAs($superAdmin)
        ->delete(route('admin.admins.destroy', $superAdmin))
        ->assertSessionHasErrors('delete');

    expect($superAdmin->fresh()->trashed())->toBeFalse();
});

test('administrator can view the complete public landing page', function () {
    $admin = createAdministrativeUser('admin');

    $this->actingAs($admin)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Wujudkan Masa Depan Hijau')
        ->assertSee('Layanan Kami');
});

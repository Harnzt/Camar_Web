<?php

use App\Models\AdminActivityLog;
use App\Models\DocumentVerification;
use App\Models\Project;
use App\Models\User;
use App\Models\AdminLoginLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

test('admin can access dashboard and review an account document', function () {
    $reviewer = createAdministrativeUser('admin');
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

    $this->actingAs($reviewer)
        ->get(route('admin.dashboard'))
        ->assertOk();

    $this->actingAs($reviewer)
        ->patch(route('admin.documents.update', $document), [
            'status' => 'approved',
            'notes' => 'Dokumen valid.',
        ])
        ->assertRedirect();

    expect($document->fresh()->status)->toBe('approved')
        ->and($buyer->fresh()->status)->toBe('verified')
        ->and($buyer->fresh()->verified_by)->toBe($reviewer->id)
        ->and(AdminActivityLog::where('action', 'document.reviewed')->exists())->toBeTrue();
});

test('account status follows rejected document review', function () {
    $reviewer = createAdministrativeUser('admin');
    $seller = User::create([
        'name' => 'Seller Review',
        'email' => 'seller-review@example.test',
        'password' => 'password',
        'role' => 'seller',
        'account_category' => 'company',
        'status' => 'pending',
    ]);
    $document = DocumentVerification::create([
        'user_id' => $seller->id,
        'document_type' => 'nib',
        'document_path' => 'documents/nib.pdf',
        'status' => 'pending',
    ]);

    $this->actingAs($reviewer)
        ->patch(route('admin.documents.update', $document), [
            'status' => 'rejected',
            'notes' => 'Nomor dokumen tidak sesuai.',
        ])
        ->assertRedirect();

    $seller->refresh();

    expect($document->fresh()->status)->toBe('rejected')
        ->and($seller->status)->toBe('rejected')
        ->and($seller->rejection_reason)->toBe('Nomor dokumen tidak sesuai.');
});

test('admin decides account verification and auditor no longer can', function () {
    $reviewer = createAdministrativeUser('admin');
    $buyer = User::create([
        'name' => 'Buyer Decision',
        'email' => 'buyer-decision@example.test',
        'password' => 'password',
        'role' => 'buyer',
        'account_category' => 'personal',
        'status' => 'pending',
    ]);
    $document = DocumentVerification::create([
        'user_id' => $buyer->id,
        'document_type' => 'npwp',
        'document_path' => 'documents/npwp.pdf',
        'status' => 'pending',
    ]);

    $this->actingAs(createAdministrativeUser('auditor'))
        ->patch(route('admin.users.status', $buyer), [
            'status' => 'verified',
            'reason' => 'Data dan dokumen sesuai.',
        ])
        ->assertForbidden();

    $this->actingAs($reviewer)
        ->patch(route('admin.users.status', $buyer), [
            'status' => 'verified',
            'reason' => 'Data dan dokumen sesuai.',
        ])
        ->assertRedirect();

    $buyer->refresh();
    $document->refresh();

    expect($buyer->status)->toBe('verified')
        ->and($buyer->verified_by)->toBe($reviewer->id)
        ->and($document->status)->toBe('approved')
        ->and($document->reviewed_by)->toBe($reviewer->id);
});

test('user verification detail shows documents as read only and decision form separately', function () {
    $reviewer = createAdministrativeUser('admin');
    $buyer = User::create([
        'name' => 'Buyer Readonly',
        'email' => 'buyer-readonly@example.test',
        'password' => 'password',
        'role' => 'buyer',
        'account_category' => 'personal',
        'status' => 'pending',
    ]);
    DocumentVerification::create([
        'user_id' => $buyer->id,
        'document_type' => 'npwp',
        'document_path' => 'documents/npwp.pdf',
        'status' => 'pending',
    ]);

    $this->actingAs($reviewer)
        ->get(route('admin.users.show', $buyer))
        ->assertOk()
        ->assertSee('Informasi Pengguna')
        ->assertSee('Dokumen Pengguna')
        ->assertSee('Verifikasi Akun')
        ->assertSee('Unduh dokumen')
        ->assertDontSee('Catatan pemeriksaan')
        ->assertDontSee('inline-review');
});

test('auditor can see project documents on project review page', function () {
    $auditor = createAdministrativeUser('auditor');
    $seller = User::create([
        'name' => 'Seller With Documents',
        'email' => 'seller-with-documents@example.test',
        'password' => 'password',
        'role' => 'seller',
        'account_category' => 'company',
        'status' => 'verified',
    ]);
    $project = Project::create([
        'seller_id' => $seller->id,
        'company_name' => 'Mitra CAMAR',
        'name' => 'Solar Audit Project',
        'category' => 'solar',
        'location' => 'Bandung',
        'price_per_ton' => 150000,
        'stock_available' => 80,
        'description' => 'Proyek panel surya.',
        'verification_status' => 'pending',
        'submitted_at' => now(),
    ]);

    DocumentVerification::create([
        'user_id' => $seller->id,
        'document_type' => "project_{$project->id}_verification_certificate",
        'document_path' => "project-documents/{$seller->id}/{$project->id}/certificate.pdf",
        'status' => 'pending',
    ]);

    $this->actingAs($auditor)
        ->get(route('admin.projects.show', $project))
        ->assertOk()
        ->assertSee('Dokumen Proyek')
        ->assertSee('Sertifikat Verifikasi')
        ->assertSee('certificate.pdf');
});

test('regular admin manages auditors only, not other admins', function () {
    $admin = createAdministrativeUser('admin');
    $otherAdmin = User::create([
        'name' => 'Admin Lain',
        'email' => 'admin-lain@example.test',
        'password' => 'password',
        'role' => 'admin',
        'account_category' => 'personal',
        'status' => 'verified',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.admins.index'))
        ->assertOk()
        ->assertSee('Kelola Auditor')
        ->assertDontSee('admin-lain@example.test');

    $this->actingAs($admin)
        ->patch(route('admin.admins.status', $otherAdmin), ['status' => 'suspended'])
        ->assertNotFound();

    expect($otherAdmin->fresh()->status)->toBe('verified');
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
        ->assertNotFound();

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

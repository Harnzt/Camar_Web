<?php

use App\Models\AdminActivityLog;
use App\Models\DocumentVerification;
use App\Models\Order;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function panelUser(string $role, string $status = 'verified', ?string $email = null): User
{
    return User::create([
        'name' => ucfirst(str_replace('_', ' ', $role)).' Panel',
        'email' => $email ?? $role.'-panel@example.test',
        'password' => 'password',
        'role' => $role,
        'account_category' => 'personal',
        'status' => $status,
    ]);
}

function panelProject(array $attributes = []): Project
{
    return Project::create([
        'company_name' => 'Mitra CAMAR',
        'name' => 'Proyek Mangrove Panel',
        'category' => 'mangrove',
        'location' => 'Demak',
        'price_per_ton' => 100000,
        'stock_available' => 100,
        'description' => 'Proyek rehabilitasi mangrove.',
        'verification_status' => 'approved',
        'submitted_at' => now(),
        ...$attributes,
    ]);
}

function panelOrder(Project $project, string $status, array $attributes = []): Order
{
    static $sequence = 0;
    $sequence++;

    return Order::create([
        'project_id' => $project->id,
        'order_number' => 'ORD-PANEL-'.$sequence,
        'quantity' => 2,
        'total_price' => 200000,
        'buyer_name' => 'Pembeli Panel',
        'status' => $status,
        ...$attributes,
    ]);
}

// ---------------------------------------------------------------
// Status akun & sesi
// ---------------------------------------------------------------

test('suspended administrator with an open session is logged out on the next request', function () {
    $auditor = panelUser('auditor');

    $this->actingAs($auditor)->get(route('admin.dashboard'))->assertOk();

    $auditor->forceFill(['status' => 'suspended'])->save();

    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('loginError');

    $this->assertGuest();
});

test('suspending an administrator deletes their stored sessions', function () {
    $superAdmin = panelUser('super_admin');
    $admin = panelUser('admin');

    DB::table('sessions')->insert([
        'id' => 'session-of-admin',
        'user_id' => $admin->id,
        'payload' => base64_encode('x'),
        'last_activity' => now()->timestamp,
    ]);
    config(['session.driver' => 'database']);

    $this->actingAs($superAdmin)
        ->patch(route('admin.admins.status', $admin), ['status' => 'suspended'])
        ->assertRedirect();

    expect(DB::table('sessions')->where('user_id', $admin->id)->exists())->toBeFalse()
        ->and($admin->fresh()->suspension_reason)->toContain('Super Admin');
});

// ---------------------------------------------------------------
// Permission
// ---------------------------------------------------------------

test('super admin bypasses permission checks', function () {
    $superAdmin = panelUser('super_admin');

    expect($superAdmin->hasPermission('permissions.manage'))->toBeTrue()
        ->and($superAdmin->hasPermission('anything.at.all'))->toBeTrue();

    $this->actingAs($superAdmin)->get(route('admin.roles.index'))->assertOk();
    $this->actingAs($superAdmin)->get(route('admin.users.index'))->assertOk();
});

test('permission checks reuse one query per user instance', function () {
    $auditor = panelUser('auditor');

    DB::enableQueryLog();
    $auditor->hasPermission('users.verify');
    $auditor->hasPermission('audit.view');
    $auditor->hasPermission('admins.manage');
    $auditor->hasAnyPermission('transactions.view', 'certificates.issue');

    $permissionQueries = collect(DB::getQueryLog())
        ->filter(fn ($entry) => str_contains($entry['query'], 'permissions'));

    expect($permissionQueries)->toHaveCount(1)
        ->and($auditor->hasPermission('certificates.issue'))->toBeTrue()
        ->and($auditor->hasPermission('users.verify'))->toBeFalse()
        ->and($auditor->hasPermission('admins.manage'))->toBeFalse();
});

// ---------------------------------------------------------------
// Audit log
// ---------------------------------------------------------------

test('super admin sees audit logs from every administrator role', function () {
    $superAdmin = panelUser('super_admin');
    $auditor = panelUser('auditor');
    $admin = panelUser('admin');

    AdminActivityLog::create(['admin_id' => $auditor->id, 'action' => 'certificate.issued', 'description' => 'Log dari auditor']);
    AdminActivityLog::create(['admin_id' => $admin->id, 'action' => 'admin.created', 'description' => 'Log dari admin']);

    $this->actingAs($superAdmin)
        ->get(route('admin.audit.index'))
        ->assertOk()
        ->assertSee('Log dari auditor')
        ->assertSee('Log dari admin');
});

test('auditor only sees auditor activity in the audit log', function () {
    $auditor = panelUser('auditor');
    $admin = panelUser('admin');

    AdminActivityLog::create(['admin_id' => $auditor->id, 'action' => 'user.status.updated', 'description' => 'Log dari auditor']);
    AdminActivityLog::create(['admin_id' => $admin->id, 'action' => 'admin.created', 'description' => 'Log dari admin']);

    $this->actingAs($auditor)
        ->get(route('admin.audit.index'))
        ->assertOk()
        ->assertSee('Log dari auditor')
        ->assertDontSee('Log dari admin');
});

test('audit log can be filtered by actor, target and date range', function () {
    $superAdmin = panelUser('super_admin');
    $auditor = panelUser('auditor');
    $admin = panelUser('admin');
    $project = panelProject();

    $old = AdminActivityLog::create(['admin_id' => $auditor->id, 'action' => 'project.reviewed', 'description' => 'Log lama auditor', 'target_type' => Project::class, 'target_id' => $project->id]);
    $old->forceFill(['created_at' => now()->subDays(10)])->save();
    AdminActivityLog::create(['admin_id' => $auditor->id, 'action' => 'project.reviewed', 'description' => 'Log baru auditor', 'target_type' => Project::class, 'target_id' => $project->id]);
    AdminActivityLog::create(['admin_id' => $admin->id, 'action' => 'admin.created', 'description' => 'Log admin', 'target_type' => User::class, 'target_id' => $auditor->id]);

    $this->actingAs($superAdmin)
        ->get(route('admin.audit.index', [
            'admin_id' => $auditor->id,
            'target_type' => Project::class,
            'date_from' => now()->subDays(2)->toDateString(),
            'date_to' => now()->toDateString(),
        ]))
        ->assertOk()
        ->assertSee('Log baru auditor')
        ->assertDontSee('Log lama auditor')
        ->assertDontSee('Log admin');
});

test('audit log export streams a sanitized CSV and records the export', function () {
    $superAdmin = panelUser('super_admin');
    AdminActivityLog::create(['admin_id' => $superAdmin->id, 'action' => 'admin.created', 'description' => '=HYPERLINK("http://evil")']);

    $response = $this->actingAs($superAdmin)->get(route('admin.audit.export'));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('text/csv');

    $csv = $response->streamedContent();
    expect($csv)->toContain('Waktu;Pelaku')
        ->and($csv)->not->toContain('Nilai Sebelum')
        ->and($csv)->not->toContain('Nilai Sesudah')
        ->and($csv)->toContain("'=HYPERLINK")
        ->and(AdminActivityLog::where('action', 'audit.exported')->exists())->toBeTrue();
});

// ---------------------------------------------------------------
// Badge antrean & dashboard
// ---------------------------------------------------------------

test('sidebar queue badges follow each role responsibility', function () {
    panelUser('buyer', 'pending', 'pending-buyer@example.test');
    $project = panelProject();
    panelProject(['name' => 'Proyek Diajukan', 'verification_status' => 'pending']);
    panelProject(['name' => 'Proyek Direvisi 1', 'verification_status' => 'revision_required']);
    panelProject(['name' => 'Proyek Direvisi 2', 'verification_status' => 'revision_required']);
    panelOrder($project, 'paid');
    panelOrder($project, 'verified');
    panelOrder($project, 'pending');

    // Auditor: proyek menunggu verifikasi + transaksi menunggu sertifikat.
    $this->actingAs(panelUser('auditor'))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('id="nav-badge-projects" data-count="1"', false)
        ->assertSee('id="nav-badge-transactions" data-count="2"', false)
        ->assertDontSee('id="nav-badge-users"', false);

    // Admin: akun pembeli menunggu + proyek yang diminta revisi.
    $this->actingAs(panelUser('admin'))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('id="nav-badge-users" data-count="1"', false)
        ->assertSee('id="nav-badge-projects" data-count="2"', false)
        ->assertDontSee('id="nav-badge-transactions"', false);
});

test('dashboard sales charts are shown to auditor and admin', function () {
    $project = panelProject();
    panelOrder($project, 'paid', ['quantity' => 5, 'total_price' => 500000]);
    panelOrder($project, 'pending', ['quantity' => 9, 'total_price' => 900000]);

    foreach (['auditor', 'admin'] as $role) {
        $this->actingAs(panelUser($role))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Penjualan 12 bulan terakhir')
            ->assertSee('Rp 500.000')
            ->assertViewHas('salesChart', fn ($chart) => $chart['totals']['volume'] === 5
                && $chart['totals']['transactions'] === 1);
    }
});

test('dashboard explains an empty sales chart instead of drawing zeros', function () {
    $this->actingAs(panelUser('admin'))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Belum ada transaksi lunas dalam 12 bulan terakhir')
        ->assertDontSee('id="chart-revenue"', false);
});

test('admin can view transactions read-only but cannot process certificates', function () {
    $admin = panelUser('admin');
    $order = panelOrder(panelProject(), 'paid');

    $this->actingAs($admin)
        ->get(route('admin.transactions.index'))
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee('Mode pemantauan')
        ->assertDontSee('Verifikasi transaksi');

    $this->actingAs($admin)
        ->patch(route('admin.transactions.update', $order), ['status' => 'verified'])
        ->assertForbidden();

    expect($order->fresh()->status)->toBe('paid');
});

// ---------------------------------------------------------------
// Role & Permission, audit log
// ---------------------------------------------------------------

test('role and permission page only lists administrator roles', function () {
    $superAdmin = panelUser('super_admin');
    $buyerRole = Role::where('slug', 'buyer')->firstOrFail();

    $this->actingAs($superAdmin)
        ->get(route('admin.roles.index'))
        ->assertOk()
        ->assertSee('Simpan permission Admin')
        ->assertSee('Simpan permission Auditor Pemerintah')
        ->assertDontSee('Simpan permission Buyer')
        ->assertDontSee('Simpan permission Seller');

    $this->actingAs($superAdmin)
        ->put(route('admin.roles.update', $buyerRole), ['permissions' => []])
        ->assertNotFound();
});

test('audit log no longer shows change details', function () {
    $superAdmin = panelUser('super_admin');
    AdminActivityLog::create([
        'admin_id' => $superAdmin->id,
        'action' => 'admin.updated',
        'description' => 'Akun diperbarui',
        'old_values' => ['name' => 'Nama Lama Rahasia'],
        'new_values' => ['name' => 'Nama Baru'],
    ]);

    foreach ([route('admin.audit.index'), route('admin.dashboard')] as $url) {
        $this->actingAs($superAdmin)
            ->get($url)
            ->assertOk()
            ->assertSee('Akun diperbarui')
            ->assertDontSee('Detail Perubahan')
            ->assertDontSee('Nama Lama Rahasia');
    }
});

// ---------------------------------------------------------------
// Pratinjau dokumen & proyek baca-saja
// ---------------------------------------------------------------

test('admin can preview an account document inline', function () {
    Storage::fake('private');
    Storage::disk('private')->put('documents/1/npwp.pdf', '%PDF-1.4 test');

    $buyer = panelUser('buyer', 'pending', 'buyer-doc@example.test');
    $document = DocumentVerification::create([
        'user_id' => $buyer->id,
        'document_type' => 'npwp',
        'document_path' => 'documents/1/npwp.pdf',
        'status' => 'pending',
    ]);

    $response = $this->actingAs(panelUser('admin'))->get(route('admin.documents.preview', $document));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toBe('application/pdf')
        ->and($response->headers->get('content-disposition'))->toStartWith('inline')
        ->and($response->headers->get('x-content-type-options'))->toBe('nosniff');
});

test('documents that cannot be rendered inline are download only', function () {
    Storage::fake('private');
    Storage::disk('private')->put('documents/1/data.docx', 'binary');

    $buyer = panelUser('buyer', 'pending', 'buyer-docx@example.test');
    $document = DocumentVerification::create([
        'user_id' => $buyer->id,
        'document_type' => 'akta',
        'document_path' => 'documents/1/data.docx',
        'status' => 'pending',
    ]);

    $this->actingAs(panelUser('admin'))
        ->get(route('admin.documents.preview', $document))
        ->assertStatus(415);
});

test('auditor can open project documents but not account documents', function () {
    Storage::fake('private');
    Storage::disk('private')->put('project-documents/map.png', 'png');
    Storage::disk('private')->put('documents/1/ktp.png', 'png');

    $auditor = panelUser('auditor');
    $buyer = panelUser('buyer', 'pending', 'buyer-ktp@example.test');
    $project = panelProject();

    $projectDocument = DocumentVerification::create([
        'user_id' => $buyer->id,
        'document_type' => "project_{$project->id}_location_map",
        'document_path' => 'project-documents/map.png',
        'status' => 'pending',
    ]);
    $accountDocument = DocumentVerification::create([
        'user_id' => $buyer->id,
        'document_type' => 'ktp',
        'document_path' => 'documents/1/ktp.png',
        'status' => 'pending',
    ]);

    $this->actingAs($auditor)->get(route('admin.documents.preview', $projectDocument))->assertOk();
    $this->actingAs($auditor)->get(route('admin.documents.download', $projectDocument))->assertOk();
    $this->actingAs($auditor)->get(route('admin.documents.preview', $accountDocument))->assertForbidden();
});

// ---------------------------------------------------------------
// Alur proyek: admin mengajukan, auditor memutuskan
// ---------------------------------------------------------------

function projectFormData(array $overrides = []): array
{
    return [
        'name' => 'Mangrove Pesisir Demak',
        'company_name' => 'Mitra CAMAR',
        'category' => 'mangrove',
        'location' => 'Demak',
        'standard' => 'VCS',
        'duration_months' => 12,
        'price_per_ton' => 100000,
        'stock_available' => 100,
        'co2_per_year' => 500,
        'verified_year' => 2025,
        'description' => 'Rehabilitasi mangrove pesisir.',
        'methodology' => 'VM0033',
        ...$overrides,
    ];
}

test('project created by admin waits for the auditor and stays off the public catalog', function () {
    $this->actingAs(panelUser('admin'))
        ->post(route('admin.projects.store'), projectFormData())
        ->assertRedirect(route('admin.projects.index'));

    $project = Project::where('name', 'Mangrove Pesisir Demak')->firstOrFail();

    expect($project->verification_status)->toBe('pending')
        ->and($project->reviewed_by)->toBeNull()
        ->and(Project::approved()->whereKey($project->id)->exists())->toBeFalse();

    $this->get(route('projects.show', $project->id))->assertDontSee('Mangrove Pesisir Demak');
});

test('auditor decides project status and admin cannot approve its own project', function () {
    $project = panelProject(['verification_status' => 'pending']);
    $admin = panelUser('admin');

    $this->actingAs($admin)
        ->get(route('admin.projects.show', $project))
        ->assertOk()
        ->assertSee('Status Verifikasi')
        ->assertDontSee('Simpan Keputusan');

    $this->actingAs($admin)
        ->patch(route('admin.projects.update', $project), ['verification_status' => 'approved'])
        ->assertForbidden();

    $auditor = panelUser('auditor');
    $this->actingAs($auditor)
        ->get(route('admin.projects.show', $project))
        ->assertOk()
        ->assertSee('Keputusan Auditor');

    $this->actingAs($auditor)
        ->patch(route('admin.projects.update', $project), ['verification_status' => 'approved'])
        ->assertRedirect();

    $project->refresh();
    expect($project->verification_status)->toBe('approved')
        ->and($project->reviewed_by)->toBe($auditor->id);
});

test('admin fixing a project after revision resubmits it to the auditor', function () {
    $project = panelProject(['verification_status' => 'revision_required', 'admin_notes' => 'Lengkapi metodologi.']);
    $admin = panelUser('admin');

    $this->actingAs($admin)
        ->get(route('admin.projects.show', $project))
        ->assertSee('Lengkapi metodologi.')
        ->assertSee('Perbaiki');

    $this->actingAs($admin)
        ->put(route('admin.projects.manage.update', $project), projectFormData(['name' => $project->name, 'methodology' => 'VM0033 v2.1']))
        ->assertRedirect();

    expect($project->fresh()->verification_status)->toBe('pending');
});

test('adding stock or changing methodology on a live project sends it back to the auditor', function (array $change) {
    $project = panelProject(projectFormData(['verification_status' => 'approved']));

    $this->actingAs(panelUser('admin'))
        ->put(route('admin.projects.manage.update', $project), projectFormData($change))
        ->assertRedirect();

    expect($project->fresh()->verification_status)->toBe('pending');
})->with([
    'stok ditambah' => [['stock_available' => 150]],
    'metodologi berubah' => [['methodology' => 'VM0007']],
    'tahun verifikasi berubah' => [['verified_year' => 2024]],
]);

test('price, description and lower stock edits keep a live project approved', function () {
    $project = panelProject(projectFormData(['verification_status' => 'approved']));

    $this->actingAs(panelUser('admin'))
        ->put(route('admin.projects.manage.update', $project), projectFormData([
            'price_per_ton' => 125000,
            'description' => 'Deskripsi baru.',
            'stock_available' => 80,
        ]))
        ->assertRedirect();

    $project->refresh();
    expect($project->verification_status)->toBe('approved')
        ->and((int) $project->stock_available)->toBe(80);
});

// ---------------------------------------------------------------
// Transaksi: pembayaran dari Midtrans, panel hanya sertifikat
// ---------------------------------------------------------------

test('auditor verifies a paid order and then issues its certificate', function () {
    $auditor = panelUser('auditor');
    $order = panelOrder(panelProject(), 'paid');

    $this->actingAs($auditor)
        ->patch(route('admin.transactions.update', $order), ['status' => 'verified'])
        ->assertSessionHasNoErrors();

    $this->actingAs($auditor)
        ->patch(route('admin.transactions.update', $order), ['status' => 'completed'])
        ->assertSessionHasNoErrors();

    $order->refresh();
    expect($order->status)->toBe('completed')
        ->and($order->certificate_number)->toStartWith('CAMAR-CERT-');
});

test('payment statuses cannot be changed manually from the panel', function () {
    $order = panelOrder(panelProject(), 'pending');

    $this->actingAs(panelUser('super_admin'))
        ->patch(route('admin.transactions.update', $order), ['status' => 'paid'])
        ->assertSessionHasErrors('status');

    $this->actingAs(panelUser('super_admin', 'verified', 'super2@example.test'))
        ->patch(route('admin.transactions.update', $order), ['status' => 'cancelled', 'admin_notes' => 'x'])
        ->assertSessionHasErrors('status');

    expect($order->fresh()->status)->toBe('pending');
});

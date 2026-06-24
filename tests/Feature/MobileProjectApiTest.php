<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createMobileApiProject(array $attributes = []): Project
{
    return Project::create(array_merge([
        'name' => 'Proyek Mangrove Mobile',
        'company_name' => 'PT Hijau Mobile',
        'category' => 'Blue Carbon',
        'location' => 'Banyuwangi',
        'duration_months' => 24,
        'price_per_ton' => 150000,
        'stock_available' => 500,
        'co2_per_year' => 1200,
        'description' => 'Restorasi mangrove untuk penyerapan karbon.',
        'verification_status' => 'approved',
    ], $attributes));
}

test('project catalog only returns approved projects and supports search', function () {
    $approved = createMobileApiProject();
    createMobileApiProject([
        'name' => 'Proyek Belum Disetujui',
        'verification_status' => 'pending',
    ]);

    $this->getJson('/api/v1/projects?search=Mangrove')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', (string) $approved->id)
        ->assertJsonPath('0.title', 'Proyek Mangrove Mobile')
        ->assertJsonPath('0.duration_months', 24)
        ->assertJsonPath('0.price_per_ton', 150000)
        ->assertJsonPath('0.stock_available', 500);
});

test('home api returns latest approved projects and aggregate stats', function () {
    $seller = User::factory()->create([
        'role' => 'seller',
        'account_category' => 'company',
        'status' => 'verified',
    ]);

    createMobileApiProject([
        'seller_id' => $seller->id,
        'name' => 'Proyek Lama',
        'stock_available' => 200,
        'created_at' => now()->subDay(),
    ]);
    $latest = createMobileApiProject([
        'seller_id' => $seller->id,
        'name' => 'Proyek Terbaru',
        'stock_available' => 300,
        'created_at' => now(),
    ]);
    createMobileApiProject([
        'verification_status' => 'pending',
        'stock_available' => 999,
    ]);

    $this->getJson('/api/v1/home')
        ->assertOk()
        ->assertJsonPath('stats.project_count', 2)
        ->assertJsonPath('stats.carbon_available_ton', 500)
        ->assertJsonPath('stats.partner_count', 1)
        ->assertJsonPath('latest_projects.0.id', (string) $latest->id)
        ->assertJsonCount(2, 'latest_projects');
});

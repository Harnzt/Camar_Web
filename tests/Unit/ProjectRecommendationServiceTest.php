<?php

use App\Models\EmissionCalculation;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectRecommendationService;

test('scope 2 calculation strongly recommends a verified renewable energy project', function () {
    $user = new User([
        'name' => 'Buyer',
        'address' => 'Bandung, Jawa Barat',
    ]);

    $emission = new EmissionCalculation([
        'scope1_kg' => 100,
        'scope2_kg' => 5000,
        'scope3_kg' => 200,
        'total_kg' => 5300,
        'price_per_ton' => 250000,
    ]);

    $solar = new Project([
        'name' => 'Pembangkit Listrik Tenaga Surya Jawa Barat',
        'category' => 'Renewable Energy',
        'location' => 'Jawa Barat, Indonesia',
        'standard' => 'Verra VCS',
        'price_per_ton' => 200000,
        'stock_available' => 100,
        'description' => 'Proyek PLTS untuk menghasilkan listrik bersih.',
    ]);

    $scored = app(ProjectRecommendationService::class)
        ->scoreProject($solar, $user, $emission);

    expect($scored->dominant_scope)->toBe('scope2')
        ->and($scored->recommendation_score)->toBe(100.0)
        ->and($scored->recommendation_breakdown['emission_match'])->toBe(35.0)
        ->and($scored->recommendation_breakdown['verification'])->toBe(10.0);
});

test('recommendations are ordered by weighted score', function () {
    $user = new User([
        'name' => 'Buyer',
        'address' => 'Jakarta',
    ]);

    $emission = new EmissionCalculation([
        'scope1_kg' => 100,
        'scope2_kg' => 8000,
        'scope3_kg' => 200,
        'total_kg' => 8300,
        'price_per_ton' => 250000,
    ]);

    $solar = new Project([
        'name' => 'PLTS Komunitas',
        'category' => 'Renewable Energy',
        'location' => 'Jakarta, Indonesia',
        'standard' => 'Gold Standard',
        'price_per_ton' => 200000,
        'stock_available' => 100,
        'description' => 'Energi surya untuk listrik komunitas.',
    ]);

    $forest = new Project([
        'name' => 'Konservasi Hutan',
        'category' => 'Forestry & Nature-Based',
        'location' => 'Kalimantan, Indonesia',
        'standard' => null,
        'price_per_ton' => 400000,
        'stock_available' => 2,
        'description' => 'Perlindungan hutan tropis.',
    ]);

    $recommended = app(ProjectRecommendationService::class)
        ->recommend($user, $emission, 2, collect([$forest, $solar]));

    expect($recommended->first()->name)->toBe('PLTS Komunitas')
        ->and($recommended->first()->recommendation_score)
        ->toBeGreaterThan($recommended->last()->recommendation_score);
});

test('company without emission receives industry recommendations without compatibility score', function () {
    $user = new User([
        'name' => 'Buyer Perusahaan',
        'role' => 'buyer',
        'account_category' => 'company',
        'industry' => 'energy',
    ]);

    $solar = new Project([
        'name' => 'Pembangkit Listrik Tenaga Surya',
        'category' => 'Renewable Energy',
        'stock_available' => 100,
        'description' => 'Energi surya dan listrik bersih.',
    ]);

    $forest = new Project([
        'name' => 'Konservasi Hutan',
        'category' => 'Forestry',
        'stock_available' => 100,
        'description' => 'Perlindungan hutan tropis.',
    ]);

    $recommended = app(ProjectRecommendationService::class)
        ->recommend($user, null, 2, collect([$forest, $solar]));

    expect($recommended->first()->name)->toBe('Pembangkit Listrik Tenaga Surya')
        ->and($recommended->first()->recommendation_mode)->toBe('industry')
        ->and($recommended->first()->recommendation_label)
        ->toBe('Direkomendasikan')
        ->and($recommended->first()->getAttribute('recommendation_score'))->toBeNull();
});

test('personal buyer without emission receives random initial recommendations without score', function () {
    $user = new User([
        'name' => 'Buyer Individu',
        'role' => 'buyer',
        'account_category' => 'personal',
    ]);

    $projects = collect([
        new Project(['name' => 'Proyek A', 'stock_available' => 10]),
        new Project(['name' => 'Proyek B', 'stock_available' => 10]),
        new Project(['name' => 'Proyek C', 'stock_available' => 10]),
    ]);

    $recommended = app(ProjectRecommendationService::class)
        ->recommend($user, null, 2, $projects);

    expect($recommended)->toHaveCount(2)
        ->and($recommended->every(
            fn (Project $project) => $project->recommendation_mode === 'random'
                && $project->getAttribute('recommendation_score') === null
        ))->toBeTrue();
});

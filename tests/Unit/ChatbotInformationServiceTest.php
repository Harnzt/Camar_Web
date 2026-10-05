<?php

use App\Models\EmissionCalculation;
use App\Models\Project;
use App\Models\User;
use App\Services\ChatbotInformationService;
use App\Services\ProjectRecommendationService;

uses(Tests\TestCase::class);

function informationServiceWith(
    ?EmissionCalculation $emission,
    ?Project $project
): ChatbotInformationService {
    return new class(app(ProjectRecommendationService::class), $emission, $project) extends ChatbotInformationService
    {
        public function __construct(
            ProjectRecommendationService $recommendationService,
            private readonly ?EmissionCalculation $emission,
            private readonly ?Project $project
        ) {
            parent::__construct($recommendationService);
        }

        protected function latestEmission(User $user): ?EmissionCalculation
        {
            return $this->emission;
        }

        protected function resolveProject(string $query): ?Project
        {
            return $this->project;
        }
    };
}

function informationServiceBuyer(): User
{
    $buyer = new User([
        'role' => 'buyer',
        'account_category' => 'company',
        'address' => 'Riau, Indonesia',
    ]);
    $buyer->id = 7;

    return $buyer;
}

function informationServiceEmission(): EmissionCalculation
{
    $emission = new EmissionCalculation([
        'scope1_kg' => 100,
        'scope2_kg' => 1200,
        'scope3_kg' => 200,
        'scope_details' => [
            'scope1' => [['label' => 'Kendaraan operasional', 'value_kg' => 100]],
            'scope2' => [['label' => 'Konsumsi listrik', 'value_kg' => 1200]],
            'scope3' => [['label' => 'Perjalanan kereta', 'value_kg' => 200]],
        ],
        'total_kg' => 1500,
        'total_ton' => 1.5,
        'price_per_ton' => 300000,
    ]);
    $emission->id = 9;

    return $emission;
}

function informationServiceProject(): Project
{
    $project = new Project([
        'name' => 'Mangrove Blue Carbon Riau',
        'company_name' => 'Riau Mangrove Project',
        'category' => 'Blue Carbon',
        'location' => 'Riau, Indonesia',
        'standard' => 'Verra VCS',
        'verified_year' => 2025,
        'verification_status' => 'approved',
        'price_per_ton' => 255000,
        'stock_available' => 69,
        'description' => 'Restorasi ekosistem mangrove di pesisir Riau.',
        'methodology' => 'Restorasi dan perlindungan mangrove.',
    ]);
    $project->id = 4;

    return $project;
}

test('it explains the dominant scope and activity from a saved emission', function () {
    $answer = informationServiceWith(
        informationServiceEmission(),
        informationServiceProject()
    )->answer(
        'emission_explanation_request',
        'jelaskan hasil emisi saya',
        informationServiceBuyer()
    );

    expect($answer['custom']['type'])->toBe('emission_explanation_result')
        ->and($answer['custom']['dominant_scope'])->toBe('scope2')
        ->and($answer['custom']['dominant_activity']['label'])->toBe('Konsumsi listrik')
        ->and($answer['custom']['dominant_activity']['value_kg'])->toBe(1200.0)
        ->and($answer['text'])->toContain('penghematan listrik');
});

test('it returns current project information without overstating certification', function () {
    $service = informationServiceWith(
        informationServiceEmission(),
        informationServiceProject()
    );

    $information = $service->answer(
        'project_information_request',
        'jelaskan Mangrove Blue Carbon Riau',
        null
    );
    $verification = $service->answer(
        'project_verification_request',
        'apakah Mangrove Blue Carbon Riau memiliki sertifikat',
        null
    );

    expect($information['custom']['project']['location'])->toBe('Riau, Indonesia')
        ->and($information['custom']['project']['price_per_ton'])->toBe(255000.0)
        ->and($verification['custom']['review_status'])->toBe('approved')
        ->and($verification['custom']['standard'])->toBe('Verra VCS')
        ->and($verification['custom']['evidence_available'])->toBeFalse()
        ->and($verification['text'])->toContain('belum dapat dikonfirmasi');
});

test('it explains recommendation using the configured weighted scoring', function () {
    $answer = informationServiceWith(
        informationServiceEmission(),
        informationServiceProject()
    )->answer(
        'recommendation_explanation_request',
        'mengapa Mangrove Blue Carbon Riau direkomendasikan',
        informationServiceBuyer()
    );

    expect($answer['custom']['type'])->toBe('recommendation_explanation_result')
        ->and($answer['custom']['breakdown'])->toMatchArray([
            'emission_match' => 7.0,
            'budget_match' => 25.0,
            'stock_coverage' => 20.0,
            'location_match' => 10.0,
            'verification' => 10.0,
        ])
        ->and($answer['text'])->toContain('aturan pembobotan CAMAR');
});

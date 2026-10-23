<?php

use App\Models\EmissionCalculation;
use App\Models\Project;
use App\Models\User;
use App\Services\ChatbotInformationService;
use App\Services\ProjectRecommendationService;

uses(Tests\TestCase::class);

function informationServiceWith(
    ?EmissionCalculation $emission,
    ?Project $project,
    float $offsetTon = 0,
    float $pendingTon = 0,
    ?\Illuminate\Support\Collection $projects = null
): ChatbotInformationService {
    return new class(app(ProjectRecommendationService::class), $emission, $project, $offsetTon, $pendingTon, $projects) extends ChatbotInformationService
    {
        public function __construct(
            ProjectRecommendationService $recommendationService,
            private readonly ?EmissionCalculation $emission,
            private readonly ?Project $project,
            private readonly float $offsetTon,
            private readonly float $pendingTon,
            private readonly ?\Illuminate\Support\Collection $projects
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

        protected function successfulOffsetTon(User $user): float
        {
            return $this->offsetTon;
        }

        protected function pendingOffsetTon(User $user): float
        {
            return $this->pendingTon;
        }

        protected function resolveProjects(string $query): \Illuminate\Support\Collection
        {
            return $this->projects ?? collect();
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
        'duration_months' => 48,
        'area_ha' => 700,
        'co2_per_year' => 12000,
        'families_impacted' => 240,
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

test('it calculates remaining emissions from successful orders and excludes pending orders', function () {
    $answer = informationServiceWith(
        informationServiceEmission(),
        informationServiceProject(),
        1,
        2
    )->answer(
        'carbon_balance_request',
        'berapa sisa emisi saya',
        informationServiceBuyer()
    );

    expect($answer['custom']['type'])->toBe('carbon_balance_result')
        ->and($answer['custom']['total_kg'])->toBe(1500.0)
        ->and($answer['custom']['offset_kg'])->toBe(1000.0)
        ->and($answer['custom']['remaining_kg'])->toBe(500.0)
        ->and($answer['custom']['required_ton'])->toBe(1)
        ->and($answer['custom']['pending_ton'])->toBe(2.0)
        ->and($answer['buttons'][0]['payload'])->toBe('proyek termurah untuk sisa emisi saya di dashboard')
        ->and($answer['text'])->toContain('belum mengurangi saldo');
});

test('it simulates a purchase without changing the saved carbon balance', function () {
    $answer = informationServiceWith(
        informationServiceEmission(),
        informationServiceProject(),
        1
    )->answer(
        'offset_simulation_request',
        'simulasikan pembelian 1 ton proyek Mangrove Blue Carbon Riau',
        informationServiceBuyer()
    );

    expect($answer['custom']['type'])->toBe('offset_simulation_result')
        ->and($answer['custom']['remaining_before_kg'])->toBe(500.0)
        ->and($answer['custom']['remaining_after_kg'])->toBe(0.0)
        ->and($answer['custom']['over_offset_after_kg'])->toBe(500.0)
        ->and($answer['custom']['estimated_total_cost'])->toBe(283050.0)
        ->and($answer['custom']['recommendations'][0]['purchase_disclosure']['tax'])->toBe(28050.0)
        ->and($answer['text'])->toContain('PPN 11%')
        ->and($answer['text'])->toContain('hanya simulasi');
});

test('it discloses price fees risks and transaction terms before purchase', function () {
    $answer = informationServiceWith(
        informationServiceEmission(),
        informationServiceProject()
    )->answer(
        'purchase_disclosure_request',
        'cek biaya 2 ton proyek Mangrove Blue Carbon Riau',
        informationServiceBuyer()
    );

    expect($answer['custom']['type'])->toBe('purchase_disclosure_result')
        ->and($answer['custom']['purchase_disclosure']['quantity_ton'])->toBe(2)
        ->and($answer['custom']['purchase_disclosure']['subtotal'])->toBe(510000.0)
        ->and($answer['custom']['purchase_disclosure']['tax'])->toBe(56100.0)
        ->and($answer['custom']['purchase_disclosure']['estimated_total'])->toBe(566100.0)
        ->and($answer['custom']['purchase_disclosure']['risks'])->toHaveCount(3)
        ->and($answer['custom']['purchase_disclosure']['terms'])->toHaveCount(4)
        ->and($answer['text'])->toContain('Risiko utama')
        ->and($answer['text'])->toContain('Ketentuan utama');
});

test('it compares two projects using current project records', function () {
    $first = informationServiceProject();
    $second = new Project([
        'name' => 'Hutan Lestari Kalimantan',
        'company_name' => 'Mitra Hutan',
        'category' => 'Forestry',
        'location' => 'Kalimantan, Indonesia',
        'standard' => 'Gold Standard',
        'verification_status' => 'approved',
        'price_per_ton' => 210000,
        'stock_available' => 120,
    ]);
    $second->id = 8;

    $answer = informationServiceWith(
        informationServiceEmission(),
        $first,
        projects: collect([$first, $second])
    )->answer(
        'project_comparison_request',
        'bandingkan Mangrove Blue Carbon Riau dan Hutan Lestari Kalimantan',
        null
    );

    expect($answer['custom']['type'])->toBe('project_comparison_result')
        ->and($answer['custom']['recommendations'])->toHaveCount(2)
        ->and($answer['text'])->toContain('Hutan Lestari Kalimantan memiliki harga per ton lebih rendah');
});

test('it creates a prioritized personal reduction plan from saved activities', function () {
    $answer = informationServiceWith(
        informationServiceEmission(),
        informationServiceProject()
    )->answer(
        'personal_action_plan_request',
        'buatkan rencana pengurangan emisi',
        informationServiceBuyer()
    );

    expect($answer['custom']['type'])->toBe('personal_action_plan_result')
        ->and($answer['custom']['steps'])->toHaveCount(3)
        ->and($answer['custom']['steps'][0]['label'])->toBe('Konsumsi listrik')
        ->and($answer['custom']['steps'][0]['contribution_percentage'])->toBe(80.0)
        ->and($answer['text'])->toContain('Carbon Coach');
});

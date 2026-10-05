<?php

use App\Models\User;
use App\Services\ChatbotInformationService;
use Illuminate\Support\Facades\Http;

function informationBuyer(string $accountCategory = 'personal'): User
{
    $user = new User([
        'role' => 'buyer',
        'account_category' => $accountCategory,
        'status' => 'verified',
    ]);
    $user->id = 77;

    return $user;
}

test('Rasa project information request is completed using the application service', function () {
    config(['services.rasa.webhook_url' => 'http://rasa.test/webhooks/rest/webhook']);
    Http::fake([
        'http://rasa.test/*' => Http::response([[
            'custom' => [
                'type' => 'project_information_request',
                'query' => 'di mana proyek Mangrove Blue Carbon Riau',
            ],
        ]]),
    ]);
    $informationService = Mockery::mock(ChatbotInformationService::class);
    $informationService->shouldReceive('answer')
        ->once()
        ->with(
            'project_information_request',
            'di mana proyek Mangrove Blue Carbon Riau',
            null
        )->andReturn([
            'text' => 'Lokasi proyek berada di Riau.',
            'custom' => [
                'type' => 'project_information_result',
                'project' => [
                    'name' => 'Mangrove Blue Carbon Riau',
                    'location' => 'Riau, Indonesia',
                ],
            ],
        ]);
    $this->app->instance(ChatbotInformationService::class, $informationService);

    $this->postJson('/chatbot/send', [
        'message' => 'di mana proyek Mangrove Blue Carbon Riau',
    ])->assertOk()
        ->assertJsonPath('data.0.custom.type', 'project_information_result')
        ->assertJsonPath('data.0.custom.project.name', 'Mangrove Blue Carbon Riau')
        ->assertJsonPath('data.0.custom.project.location', 'Riau, Indonesia');
});

test('Rasa verification request preserves the distinction between approval and evidence', function () {
    config(['services.rasa.webhook_url' => 'http://rasa.test/webhooks/rest/webhook']);
    Http::fake([
        'http://rasa.test/*' => Http::response([[
            'custom' => [
                'type' => 'project_verification_request',
                'query' => 'apakah proyek Mangrove Blue Carbon Riau memiliki sertifikat',
            ],
        ]]),
    ]);
    $informationService = Mockery::mock(ChatbotInformationService::class);
    $informationService->shouldReceive('answer')->once()->andReturn([
        'text' => 'Disetujui CAMAR, tetapi bukti sertifikasi belum dapat dikonfirmasi.',
        'custom' => [
            'type' => 'project_verification_result',
            'review_status' => 'approved',
            'standard' => 'Verra VCS',
            'evidence_available' => false,
        ],
    ]);
    $this->app->instance(ChatbotInformationService::class, $informationService);

    $this->postJson('/chatbot/send', [
        'message' => 'apakah proyek Mangrove Blue Carbon Riau memiliki sertifikat',
    ])->assertOk()
        ->assertJsonPath('data.0.custom.type', 'project_verification_result')
        ->assertJsonPath('data.0.custom.review_status', 'approved')
        ->assertJsonPath('data.0.custom.standard', 'Verra VCS')
        ->assertJsonPath('data.0.custom.evidence_available', false);
});

test('Rasa latest emission request is completed for the authenticated buyer', function () {
    $buyer = informationBuyer();
    config(['services.rasa.webhook_url' => 'http://rasa.test/webhooks/rest/webhook']);
    Http::fake([
        'http://rasa.test/*' => Http::response([[
            'custom' => ['type' => 'emission_explanation_request'],
        ]]),
    ]);
    $informationService = Mockery::mock(ChatbotInformationService::class);
    $informationService->shouldReceive('answer')
        ->once()
        ->withArgs(fn (string $type, string $query, ?User $user) => $type === 'emission_explanation_request'
            && $query === 'jelaskan hasil emisi saya'
            && $user?->id === 77)
        ->andReturn([
            'text' => 'Scope 2 dan konsumsi listrik adalah penyumbang terbesar.',
            'custom' => [
                'type' => 'emission_explanation_result',
                'total_kg' => 1500,
                'dominant_scope' => 'scope2',
                'dominant_activity' => [
                    'label' => 'Konsumsi listrik',
                    'value_kg' => 1200,
                ],
            ],
        ]);
    $this->app->instance(ChatbotInformationService::class, $informationService);

    $this->actingAs($buyer)
        ->postJson('/chatbot/send', ['message' => 'jelaskan hasil emisi saya'])
        ->assertOk()
        ->assertJsonPath('data.0.custom.type', 'emission_explanation_result')
        ->assertJsonPath('data.0.custom.total_kg', 1500)
        ->assertJsonPath('data.0.custom.dominant_scope', 'scope2')
        ->assertJsonPath('data.0.custom.dominant_activity.label', 'Konsumsi listrik');
});

test('recommendation explanation request is not intercepted as a new recommendation', function () {
    $buyer = informationBuyer('company');
    config(['services.rasa.webhook_url' => 'http://rasa.test/webhooks/rest/webhook']);
    Http::fake([
        'http://rasa.test/*' => Http::response([[
            'custom' => [
                'type' => 'recommendation_explanation_request',
                'query' => 'mengapa proyek mangrove direkomendasikan',
            ],
        ]]),
    ]);
    $informationService = Mockery::mock(ChatbotInformationService::class);
    $informationService->shouldReceive('answer')->once()->andReturn([
        'text' => 'Skor dihitung dari lima kriteria CAMAR.',
        'custom' => [
            'type' => 'recommendation_explanation_result',
            'breakdown' => [
                'emission_match' => 35,
                'budget_match' => 25,
                'stock_coverage' => 20,
                'location_match' => 10,
                'verification' => 10,
            ],
        ],
    ]);
    $this->app->instance(ChatbotInformationService::class, $informationService);

    $this->actingAs($buyer)
        ->postJson('/chatbot/send', [
            'message' => 'mengapa proyek mangrove direkomendasikan',
        ])->assertOk()
        ->assertJsonPath('data.0.custom.type', 'recommendation_explanation_result')
        ->assertJsonPath('data.0.custom.breakdown.emission_match', 35)
        ->assertJsonPath('data.0.custom.breakdown.verification', 10);

    Http::assertSentCount(1);
});

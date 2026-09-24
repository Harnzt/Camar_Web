<?php

use App\Models\User;
use App\Services\ChatbotProjectQueryService;
use App\Services\ChatbotResultService;
use Illuminate\Support\Facades\Http;

function chatbotBuyer(string $accountCategory = 'personal'): User
{
    $user = new User([
        'role' => 'buyer',
        'account_category' => $accountCategory,
        'status' => 'verified',
    ]);
    $user->id = 42;

    return $user;
}

test('website sends the authenticated buyer account type to Rasa', function () {
    config(['services.rasa.webhook_url' => 'http://rasa.test/webhooks/rest/webhook']);
    Http::fake([
        'http://rasa.test/*' => Http::response([
            ['recipient_id' => 'user', 'text' => 'Akun perusahaan terdeteksi.'],
        ]),
    ]);
    $user = chatbotBuyer('company');

    $this->actingAs($user)
        ->postJson('/chatbot/send', ['message' => 'hitung emisi'])
        ->assertOk()
        ->assertJsonPath('status', 'success');

    Http::assertSent(function ($request) use ($user) {
        $metadata = $request['metadata'];

        return $request['sender'] === 'user_'.$user->id
            && $metadata['authenticated'] === true
            && $metadata['role'] === 'buyer'
            && $metadata['account_type'] === 'company';
    });
});

test('new chat sessions have separate Rasa conversation trackers', function () {
    config(['services.rasa.webhook_url' => 'http://rasa.test/webhooks/rest/webhook']);
    Http::fake(['http://rasa.test/*' => Http::response([['text' => 'Halo!']])]);
    $this->actingAs(chatbotBuyer());

    $this->postJson('/chatbot/send', [
        'message' => 'halo',
        'conversation_id' => 'session_one_123',
    ])->assertOk();
    $this->postJson('/chatbot/send', [
        'message' => 'halo',
        'conversation_id' => 'session_two_456',
    ])->assertOk();

    $senders = Http::recorded()->map(fn ($entry) => $entry[0]['sender'])->all();
    expect($senders)->toBe(['user_42_session_one_123', 'user_42_session_two_456']);

    $this->postJson('/chatbot/send', [
        'message' => 'halo',
        'conversation_id' => 'invalid id!',
    ])->assertUnprocessable();
    Http::assertSentCount(2);
});

test('website turns the final Rasa activity data into a calculated result', function () {
    config(['services.rasa.webhook_url' => 'http://rasa.test/webhooks/rest/webhook']);
    Http::fake([
        'http://rasa.test/*' => Http::response([[
            'recipient_id' => 'user',
            'text' => 'Data aktivitas sudah lengkap.',
            'custom' => [
                'type' => 'calculation_request',
                'account_type' => 'personal',
                'data' => ['electricity_kwh' => 100],
            ],
        ]]),
    ]);

    $resultService = Mockery::mock(ChatbotResultService::class);
    $resultService->shouldReceive('process')
        ->once()
        ->withArgs(fn (User $user, array $data) => $user->id === 42
            && $data === ['electricity_kwh' => 100])
        ->andReturn([
            'calculation_id' => 7,
            'mode' => 'personal',
            'scope1_kg' => 0.0,
            'scope2_kg' => 971.88,
            'scope3_kg' => 0.0,
            'total_kg' => 971.88,
            'total_ton' => 0.97188,
            'estimated_cost' => 145782,
            'recommendations' => [],
        ]);
    $this->app->instance(ChatbotResultService::class, $resultService);

    $this->actingAs(chatbotBuyer())
        ->postJson('/chatbot/send', ['message' => '100'])
        ->assertOk()
        ->assertJsonPath('data.0.custom.type', 'calculation_result')
        ->assertJsonPath('data.0.custom.mode', 'personal')
        ->assertJsonPath('data.0.custom.total_kg', 971.88)
        ->assertJsonPath('data.0.text', "Hasil estimasi emisi akun individu:\nScope 1: 0,00 kg CO2e\nScope 2: 971,88 kg CO2e\nScope 3: 0,00 kg CO2e\nTotal: 971,88 kg CO2e (0,971880 ton)\nEstimasi biaya offset: Rp 145.782\nBelum ada proyek offset yang tersedia untuk direkomendasikan.");
});

test('website accepts the older Rasa activity format and hides its ready message', function () {
    config(['services.rasa.webhook_url' => 'http://rasa.test/webhooks/rest/webhook']);
    Http::fake([
        'http://rasa.test/*' => Http::response([
            ['text' => 'Data aktivitas sudah lengkap dan siap dihitung oleh CAMAR.'],
            ['custom' => [
                'type' => 'calculation_request',
                'data' => ['energy_fuel' => 'none', 'electricity_kwh' => 100],
            ]],
            ['text' => 'Pilih bantuan yang kamu butuhkan:'],
        ]),
    ]);

    $resultService = Mockery::mock(ChatbotResultService::class);
    $resultService->shouldReceive('process')
        ->once()
        ->withArgs(fn (User $user, array $data) => $user->id === 42
            && $data === ['energy_fuel' => 'none', 'electricity_kwh' => 100])
        ->andReturn([
            'calculation_id' => 7,
            'mode' => 'personal',
            'scope1_kg' => 0.0,
            'scope2_kg' => 971.88,
            'scope3_kg' => 0.0,
            'total_kg' => 971.88,
            'total_ton' => 0.97188,
            'estimated_cost' => 145782,
            'recommendations' => [],
        ]);
    $this->app->instance(ChatbotResultService::class, $resultService);

    $this->actingAs(chatbotBuyer())
        ->postJson('/chatbot/send', ['message' => '15'])
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.custom.type', 'calculation_result')
        ->assertJsonPath('data.0.custom.total_kg', 971.88)
        ->assertJsonPath('data.1.text', 'Pilih bantuan yang kamu butuhkan:');
});

test('website rejects older Rasa activity data for a different account type', function () {
    config(['services.rasa.webhook_url' => 'http://rasa.test/webhooks/rest/webhook']);
    Http::fake([
        'http://rasa.test/*' => Http::response([[
            'custom' => [
                'type' => 'calculation_request',
                'data' => ['stationary_fuel' => 'diesel', 'stationary_qty' => 100],
            ],
        ]]),
    ]);

    $resultService = Mockery::mock(ChatbotResultService::class);
    $resultService->shouldNotReceive('process');
    $this->app->instance(ChatbotResultService::class, $resultService);

    $this->actingAs(chatbotBuyer('personal'))
        ->postJson('/chatbot/send', ['message' => '100'])
        ->assertOk()
        ->assertJsonPath('data.0.custom.type', 'calculation_error');
});

test('website rejects activity data from a stale account form', function () {
    config(['services.rasa.webhook_url' => 'http://rasa.test/webhooks/rest/webhook']);
    Http::fake([
        'http://rasa.test/*' => Http::response([[
            'recipient_id' => 'user',
            'custom' => [
                'type' => 'calculation_request',
                'account_type' => 'company',
                'data' => ['electricity_kwh' => 100],
            ],
        ]]),
    ]);

    $resultService = Mockery::mock(ChatbotResultService::class);
    $resultService->shouldNotReceive('process');
    $this->app->instance(ChatbotResultService::class, $resultService);

    $this->actingAs(chatbotBuyer('personal'))
        ->postJson('/chatbot/send', ['message' => '100'])
        ->assertOk()
        ->assertJsonPath('data.0.custom.type', 'calculation_error');
});

test('website shows recommendations from the latest saved emission without recalculating', function () {
    config(['services.rasa.webhook_url' => 'http://rasa.test/webhooks/rest/webhook']);
    Http::fake();

    $resultService = Mockery::mock(ChatbotResultService::class);
    $resultService->shouldNotReceive('process');
    $resultService->shouldReceive('recommendExisting')
        ->once()
        ->withArgs(fn (User $user) => $user->id === 42)
        ->andReturn([
            'source' => 'latest_emission',
            'calculation_id' => 7,
            'total_kg' => 971.88,
            'recommendations' => [['id' => 3, 'name' => 'Proyek Surya']],
        ]);
    $this->app->instance(ChatbotResultService::class, $resultService);

    $this->actingAs(chatbotBuyer())
        ->postJson('/chatbot/send', ['message' => 'carikan proyek sesuai emisi saya saat ini'])
        ->assertOk()
        ->assertJsonPath('data.0.custom.type', 'project_recommendation_result')
        ->assertJsonPath('data.0.custom.source', 'latest_emission')
        ->assertJsonPath('data.0.custom.recommendations.0.name', 'Proyek Surya')
        ->assertJsonPath('data.0.text', 'Berdasarkan hasil emisi terakhirmu sebesar 971,88 kg CO2e/tahun, berikut proyek yang sesuai:');

    Http::assertNothingSent();
});

test('recommendation button shows initial projects when the buyer has no saved emission', function () {
    config(['services.rasa.webhook_url' => 'http://rasa.test/webhooks/rest/webhook']);
    Http::fake();

    $resultService = Mockery::mock(ChatbotResultService::class);
    $resultService->shouldNotReceive('process');
    $resultService->shouldReceive('recommendExisting')->once()->andReturn([
        'source' => 'profile',
        'calculation_id' => null,
        'total_kg' => null,
        'recommendations' => [['id' => 3, 'name' => 'Proyek Surya', 'score' => null]],
    ]);
    $this->app->instance(ChatbotResultService::class, $resultService);

    $this->actingAs(chatbotBuyer())
        ->postJson('/chatbot/send', ['message' => '/ask_project_recommendation'])
        ->assertOk()
        ->assertJsonPath('data.0.custom.type', 'project_recommendation_result')
        ->assertJsonPath('data.0.custom.source', 'profile')
        ->assertJsonPath('data.0.custom.calculation_id', null)
        ->assertJsonPath('data.0.custom.recommendations.0.name', 'Proyek Surya');

    Http::assertNothingSent();
});

test('website does not expose project recommendations to a guest', function () {
    config(['services.rasa.webhook_url' => 'http://rasa.test/webhooks/rest/webhook']);
    Http::fake();

    $resultService = Mockery::mock(ChatbotResultService::class);
    $resultService->shouldNotReceive('recommendExisting');
    $this->app->instance(ChatbotResultService::class, $resultService);

    $this->postJson('/chatbot/send', ['message' => 'rekomendasi proyek'])
        ->assertOk()
        ->assertJsonPath('data.0.custom.type', 'project_recommendation_error');

    Http::assertNothingSent();
});

test('website still handles a recommendation request returned by Rasa', function () {
    config(['services.rasa.webhook_url' => 'http://rasa.test/webhooks/rest/webhook']);
    Http::fake([
        'http://rasa.test/*' => Http::response([[
            'custom' => ['type' => 'project_recommendation_request'],
        ]]),
    ]);

    $resultService = Mockery::mock(ChatbotResultService::class);
    $resultService->shouldReceive('recommendExisting')->once()->andReturn([
        'source' => 'profile',
        'calculation_id' => null,
        'total_kg' => null,
        'recommendations' => [],
    ]);
    $this->app->instance(ChatbotResultService::class, $resultService);

    $this->actingAs(chatbotBuyer())
        ->postJson('/chatbot/send', ['message' => 'tolong bantu saya'])
        ->assertOk()
        ->assertJsonPath('data.0.custom.type', 'project_recommendation_result');

    Http::assertSentCount(1);
});

test('catalog questions use current project data without contacting Rasa', function () {
    Http::fake();

    $projectQueryService = Mockery::mock(ChatbotProjectQueryService::class);
    $projectQueryService->shouldReceive('answer')
        ->once()
        ->with('proyek apa yang paling murah?', null)
        ->andReturn([
            'text' => 'Proyek termurah adalah Surya Uji dengan harga Rp 120.000 per ton.',
            'custom' => [
                'type' => 'project_catalog_result',
                'mode' => 'cheapest',
                'heading' => 'Proyek dengan harga termurah',
                'recommendations' => [['id' => 3, 'name' => 'Surya Uji', 'price_per_ton' => 120000]],
            ],
        ]);
    $this->app->instance(ChatbotProjectQueryService::class, $projectQueryService);

    $this->postJson('/chatbot/send', ['message' => 'proyek apa yang paling murah?'])
        ->assertOk()
        ->assertJsonPath('data.0.custom.type', 'project_catalog_result')
        ->assertJsonPath('data.0.custom.mode', 'cheapest')
        ->assertJsonPath('data.0.custom.recommendations.0.price_per_ton', 120000);

    Http::assertNothingSent();
});

test('cheapest offset question uses the authenticated buyer profile', function () {
    Http::fake();

    $projectQueryService = Mockery::mock(ChatbotProjectQueryService::class);
    $projectQueryService->shouldReceive('answer')
        ->once()
        ->withArgs(fn (string $message, ?User $user) => $message === 'produk termurah untuk offset emisi saya di dashboard'
            && $user?->id === 42)
        ->andReturn([
            'text' => 'Proyek termurah dengan stok cukup adalah Surya Uji.',
            'custom' => [
                'type' => 'project_catalog_result',
                'mode' => 'cheapest_offset',
                'required_ton' => 2,
                'recommendations' => [['id' => 3, 'name' => 'Surya Uji']],
            ],
        ]);
    $this->app->instance(ChatbotProjectQueryService::class, $projectQueryService);

    $this->actingAs(chatbotBuyer())
        ->postJson('/chatbot/send', ['message' => 'produk termurah untuk offset emisi saya di dashboard'])
        ->assertOk()
        ->assertJsonPath('data.0.custom.mode', 'cheapest_offset')
        ->assertJsonPath('data.0.custom.required_ton', 2);

    Http::assertNothingSent();
});

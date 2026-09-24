<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ChatbotProjectQueryService;
use App\Services\ChatbotResultService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class ChatbotController extends Controller
{
    public function __construct(
        private readonly ChatbotResultService $resultService,
        private readonly ChatbotProjectQueryService $projectQueryService
    ) {}

    public function send(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'conversation_id' => ['nullable', 'string', 'regex:/^[A-Za-z0-9_-]{8,64}$/'],
        ]);

        $userMessage = trim($validated['message']);
        $user = $request->user();

        try {
            $projectAnswer = $this->projectQueryService->answer($userMessage, $user);
            if ($projectAnswer !== null) {
                return response()->json([
                    'status' => 'success',
                    'data' => [$projectAnswer],
                ]);
            }
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'success',
                'data' => [[
                    'text' => 'Informasi proyek belum dapat ditampilkan. Silakan coba kembali.',
                    'custom' => ['type' => 'project_catalog_error'],
                ]],
            ]);
        }

        if ($this->isRecommendationRequest($userMessage)) {
            return response()->json([
                'status' => 'success',
                'data' => [$this->completeRecommendation(
                    ['custom' => ['type' => 'project_recommendation_request']],
                    $user
                )],
            ]);
        }

        $senderId = $user ? 'user_'.$user->id : 'guest_'.session()->getId();
        if (! empty($validated['conversation_id'])) {
            $senderId .= '_'.$validated['conversation_id'];
        }
        try {
            $response = Http::acceptJson()
                ->asJson()
                ->connectTimeout(3)
                ->timeout(20)
                ->post(config('services.rasa.webhook_url'), [
                    'sender' => $senderId,
                    'message' => $userMessage,
                    'metadata' => [
                        'authenticated' => (bool) $user,
                        'role' => $user?->role,
                        'account_type' => $user?->account_category ?? 'guest',
                    ],
                ]);

            if ($response->successful()) {
                $messages = $response->json();
                if (! is_array($messages)) {
                    throw new \UnexpectedValueException('Format respons Rasa tidak valid.');
                }

                return response()->json([
                    'status' => 'success',
                    'data' => $this->completeResponses($messages, $user),
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal terhubung ke Rasa server.',
            ], 500);

        } catch (ConnectionException $exception) {
            $previous = $exception->getPrevious();

            $context = $previous instanceof \GuzzleHttp\Exception\ConnectException
                ? $previous->getHandlerContext()
                : [];

            $isTimeout = (int) ($context['errno'] ?? 0) === 28
                || str_contains($exception->getMessage(), 'cURL error 28');

            Log::warning('Permintaan ke Rasa gagal.', [
                'failure_type' => $isTimeout ? 'timeout' : 'connection_failed',
                'endpoint' => config('services.rasa.webhook_url'),
                'curl_errno' => $context['errno'] ?? null,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $isTimeout
                    ? 'Respons chatbot terlalu lama. Silakan coba lagi sebentar.'
                    : 'Chatbot belum dapat dihubungi. Silakan coba lagi sebentar.',
            ], $isTimeout ? 504 : 503);
        }
    }

    private function completeResponses(array $messages, ?User $user): array
    {
        $hasCalculation = collect($messages)->contains(
            fn ($message) => is_array($message)
                && ($message['custom']['type'] ?? null) === 'calculation_request'
        );

        return collect($messages)
            ->reject(fn ($message) => $hasCalculation
                && is_array($message)
                && ($message['text'] ?? null) === 'Data aktivitas sudah lengkap dan siap dihitung oleh CAMAR.')
            ->map(function ($message) use ($user) {
                $custom = is_array($message) ? ($message['custom'] ?? null) : null;
                if (is_array($custom) && ($custom['type'] ?? null) === 'project_recommendation_request') {
                    return $this->completeRecommendation($message, $user);
                }

                if (! is_array($custom) || ($custom['type'] ?? null) !== 'calculation_request') {
                    return $message;
                }

                if (! $user || ! is_array($custom['data'] ?? null)) {
                    $message['text'] = 'Sesi akun tidak ditemukan. Silakan masuk kembali.';
                    $message['custom'] = ['type' => 'calculation_error'];

                    return $message;
                }

                $declaredType = $custom['account_type'] ?? null;
                $dataType = $this->activityAccountType($custom['data']);
                if (
                    ($declaredType ?? $dataType) !== $user->account_category
                    || ($dataType !== null && $dataType !== $user->account_category)
                ) {
                    $message['text'] = 'Tipe akun berubah saat pengisian. Silakan mulai perhitungan kembali.';
                    $message['custom'] = ['type' => 'calculation_error'];

                    return $message;
                }

                try {
                    $result = $this->resultService->process($user, $custom['data']);
                    $message['text'] = $this->resultText($result);
                    $message['custom'] = array_merge(
                        ['type' => 'calculation_result'],
                        $result
                    );
                } catch (InvalidArgumentException $exception) {
                    $message['text'] = $exception->getMessage();
                    $message['custom'] = ['type' => 'calculation_error'];
                } catch (Throwable $exception) {
                    report($exception);
                    $message['text'] = 'Hasil belum dapat disimpan. Silakan coba kembali.';
                    $message['custom'] = ['type' => 'calculation_error'];
                }

                return $message;
            })->values()->all();
    }

    private function completeRecommendation(array $message, ?User $user): array
    {
        if (! $user || ! $user->isBuyer()) {
            $message['text'] = 'Masuk sebagai buyer terlebih dahulu untuk melihat rekomendasi proyekmu.';
            $message['custom'] = ['type' => 'project_recommendation_error'];

            return $message;
        }

        try {
            $result = $this->resultService->recommendExisting($user);
            $text = $result['source'] === 'latest_emission'
                ? 'Berdasarkan hasil emisi terakhirmu sebesar '
                    .$this->formatNumber($result['total_kg']).' kg CO2e/tahun, berikut proyek yang sesuai:'
                : 'Kamu belum memiliki hasil emisi tersimpan. Berikut pilihan proyek awal yang tersedia. Hitung emisi untuk rekomendasi yang lebih sesuai.';

            if (empty($result['recommendations'])) {
                $text .= "\nBelum ada proyek offset terverifikasi dengan stok tersedia saat ini.";
            }

            $message['text'] = $text;
            $message['custom'] = array_merge(['type' => 'project_recommendation_result'], $result);
        } catch (Throwable $exception) {
            report($exception);
            $message['text'] = 'Rekomendasi proyek belum dapat ditampilkan. Silakan coba kembali.';
            $message['custom'] = ['type' => 'project_recommendation_error'];
        }

        return $message;
    }

    private function isRecommendationRequest(string $message): bool
    {
        $message = Str::lower($message);

        return $message === '/ask_project_recommendation'
            || (Str::contains($message, 'proyek') && Str::contains($message, [
                'rekomendas', 'carikan', 'cocok', 'sesuai', 'saran', 'pilih', 'relevan',
            ]));
    }

    private function activityAccountType(array $data): ?string
    {
        $hasPersonalFields = array_key_exists('energy_fuel', $data);
        $hasCompanyFields = array_key_exists('stationary_fuel', $data);

        if ($hasPersonalFields === $hasCompanyFields) {
            return null;
        }

        return $hasPersonalFields ? 'personal' : 'company';
    }

    private function resultText(array $result): string
    {
        $mode = $result['mode'] === 'company' ? 'perusahaan' : 'individu';

        $text = "Hasil estimasi emisi akun {$mode}:\n"
            .'Scope 1: '.$this->formatNumber($result['scope1_kg'])." kg CO2e\n"
            .'Scope 2: '.$this->formatNumber($result['scope2_kg'])." kg CO2e\n"
            .'Scope 3: '.$this->formatNumber($result['scope3_kg'])." kg CO2e\n"
            .'Total: '.$this->formatNumber($result['total_kg']).' kg CO2e '
            .'('.$this->formatNumber($result['total_ton'], 6)." ton)\n"
            .'Estimasi biaya offset: Rp '.$this->formatNumber($result['estimated_cost'], 0);

        if (empty($result['recommendations'])) {
            $text .= "\nBelum ada proyek offset yang tersedia untuk direkomendasikan.";
        }

        return $text;
    }

    private function formatNumber(float|int $value, int $decimals = 2): string
    {
        return number_format((float) $value, $decimals, ',', '.');
    }
}

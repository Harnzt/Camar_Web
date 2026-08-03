<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    protected function systemPrompt(): string
    {
        return <<<PROMPT
        Kamu adalah Cami, asisten virtual resmi CAMAR (Carbon Market).
        Jawab dalam Bahasa Indonesia yang ramah, ringkas, akurat, dan mudah dipahami.
        Fokus bantuanmu mencakup carbon offset, kredit karbon, kalkulator emisi,
        cara membeli proyek bagi buyer, cara mendaftarkan dan mengelola proyek
        bagi seller, status verifikasi, transaksi, serta penggunaan aplikasi CAMAR.

        Jangan mengarang data proyek, harga, status akun, transaksi, kebijakan,
        atau sertifikasi. Jika informasi spesifik pengguna tidak tersedia di
        percakapan, arahkan pengguna membuka halaman terkait atau menghubungi
        dukungan CAMAR. Jangan meminta kata sandi, token, OTP, atau data pembayaran.
        Beri peringatan bahwa jawaban bukan nasihat hukum atau investasi bila relevan.
        PROMPT;
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'nullable|string|max:100',
        ]);

        $userMessage = trim($validated['message']);
        $sessionId = $validated['session_id'] ?? 'anonymous';


        $rateKey = 'cami_rate_'.$sessionId;

        $count = Cache::get($rateKey, 0);

        if ($count >= 20) {
            return response()->json([
                'reply' => 'Terlalu banyak permintaan. Silakan coba lagi sebentar.',
            ], 429);
        }

        Cache::put($rateKey, $count + 1, now()->addMinute());

        $apiKey = config('services.gemini.api_key');

        $model = 'gemini-3.5-flash-lite';

        if (!$apiKey) {
            return response()->json([
                'reply' => 'GEMINI_API_KEY belum diatur pada file .env',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Conversation History
        |--------------------------------------------------------------------------
        */

        $historyKey = 'cami_history_'.$sessionId;

        $history = Cache::get($historyKey, []);

        $contents = [];

        $lastRole = null;

        foreach ($history as $turn) {

            $role = $turn['role'] == 'user'
                ? 'user'
                : 'model';

            if ($lastRole == $role) {
                continue;
            }

            $contents[] = [
                'role' => $role,
                'parts' => [
                    [
                        'text' => $turn['text']
                    ]
                ]
            ];

            $lastRole = $role;
        }

        if ($lastRole == 'user') {
            array_pop($contents);
        }

        $contents[] = [
            'role' => 'user',
            'parts' => [
                [
                    'text' => $userMessage
                ]
            ]
        ];

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                    [
                        'system_instruction' => [
                            'parts' => [
                                [
                                    'text' => $this->systemPrompt()
                                ]
                            ]
                        ],

                        'contents' => $contents,

                        'generationConfig' => [
                            'temperature' => 0.7,
                            'maxOutputTokens' => 512,
                        ]
                    ]
                );

            if (!$response->successful()) {

                Log::error('Gemini Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                $error = $response->json('error.message')
                    ?? $response->body();

                return response()->json([
                    'reply' => "Layanan Cami sedang bermasalah ({$response->status()}): {$error}"
                ], 502);
            }

            $reply =
                $response->json('candidates.0.content.parts.0.text')
                ?? 'Maaf, saya belum dapat memberikan jawaban saat ini.';

            // Hapus sintaks Markdown
            $reply = preg_replace('/\*\*(.*?)\*\*/', '$1', $reply);
            $reply = preg_replace('/\*(.*?)\*/', '$1', $reply);
            $reply = preg_replace('/__(.*?)__/', '$1', $reply);
            $reply = preg_replace('/`(.*?)`/', '$1', $reply);
            $reply = preg_replace('/^#{1,6}\s*/m', '', $reply);

            // Rapikan baris kosong
            $reply = preg_replace("/\n{3,}/", "\n\n", $reply);

            $reply = trim($reply);
            
            $history[] = [
                'role' => 'user',
                'text' => $userMessage,
            ];

            $history[] = [
                'role' => 'model',
                'text' => $reply,
            ];

            $history = array_slice($history, -10);

            Cache::put($historyKey, $history, now()->addHours(2));

            return response()->json([
                'reply' => trim($reply)
            ]);

        } catch (\Throwable $e) {

            Log::error($e);

            return response()->json([
                'reply' => 'Maaf, Cami belum dapat dihubungi. Silakan coba lagi sebentar.',
            ], 503);
        }
    }
}

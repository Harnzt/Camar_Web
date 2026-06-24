<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentVerification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:buyer,seller'],
            'account_category' => ['required', 'in:company,personal'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'confirm_password' => ['required', 'same:password'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'name' => ['nullable', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'industry' => ['nullable', 'string', 'max:100'],
            'documents' => ['nullable', 'array'],
            'documents.npwp' => ['nullable', 'string', 'max:255'],
            'documents.akta' => ['nullable', 'string', 'max:255'],
            'documents.nib' => ['nullable', 'string', 'max:255'],
            'documents.iso' => ['nullable', 'string', 'max:255'],
            'documents.gold_standard' => ['nullable', 'string', 'max:255'],
            'documents.vcs' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['account_category'] === 'personal') {
            validator($validated, [
                'name' => ['required', 'string', 'max:100'],
                'job_title' => ['required', 'string', 'max:100'],
                'documents.npwp' => ['required', 'string', 'max:255'],
            ])->validate();
        }

        if ($validated['account_category'] === 'company') {
            validator($validated, [
                'company_name' => ['required', 'string', 'max:255'],
                'full_name' => ['required', 'string', 'max:100'],
                'position' => ['required', 'string', 'max:100'],
                'industry' => ['required', 'string', 'max:100'],
                'documents.npwp' => ['required', 'string', 'max:255'],
                'documents.akta' => ['required', 'string', 'max:255'],
                'documents.nib' => ['required', 'string', 'max:255'],
            ])->validate();
        }

        if ($validated['role'] === 'seller') {
            $hasGold = filled(Arr::get($validated, 'documents.gold_standard'));
            $hasVcs = filled(Arr::get($validated, 'documents.vcs'));

            if (! $hasGold && ! $hasVcs) {
                throw ValidationException::withMessages([
                    'documents.gold_standard' => ['Seller wajib upload minimal 1 sertifikat (Gold Standard atau VCS).'],
                ]);
            }
        }

        $documents = collect($validated['documents'] ?? [])
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value, $key) => "mobile-documents/pending/{$key}/".basename($value))
            ->all();

        $user = User::create([
            'name' => $validated['account_category'] === 'company'
                ? $validated['full_name']
                : $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'company_name' => $validated['company_name'] ?? null,
            'industry' => $validated['industry'] ?? null,
            'position' => $validated['position'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'role' => $validated['role'],
            'account_category' => $validated['account_category'],
            'status' => 'pending',
            'documents' => $documents ?: null,
        ]);

        foreach ($documents as $type => $path) {
            DocumentVerification::updateOrCreate(
                ['user_id' => $user->id, 'document_type' => $type],
                ['document_path' => $path, 'status' => 'pending'],
            );
        }

        return response()->json([
            'message' => 'Akun berhasil dibuat dan menunggu verifikasi admin.',
            'user' => $this->userData($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::query()
            ->where('email', $validated['email'])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau kata sandi tidak sesuai.'],
            ]);
        }

        if ($user->isSuspended()) {
            return response()->json([
                'message' => 'Akun Anda sedang dinonaktifkan. Hubungi administrator.',
            ], 403);
        }

        $token = $user->createToken(
            $validated['device_name'] ?? 'camar-mobile',
            ["role:{$user->role}"],
            now()->addDays(30),
        );

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at?->toISOString(),
            'user' => $this->userData($user),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userData($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'account_category' => $user->account_category,
            'status' => $user->status,
            'company_name' => $user->company_name,
            'profile_photo_url' => $user->profile_photo_url,
        ];
    }
}

<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function mobileApiUser(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'password' => Hash::make('secret123'),
        'role' => 'buyer',
        'account_category' => 'personal',
        'status' => 'verified',
    ], $attributes));
}

test('mobile user can login, fetch profile, and logout', function () {
    $user = mobileApiUser();

    $login = $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'secret123',
        'device_name' => 'android-emulator',
    ]);

    $login
        ->assertOk()
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('user.role', 'buyer')
        ->assertJsonStructure(['token', 'token_type', 'expires_at', 'user']);

    $token = $login->json('token');

    $this->withToken($token)
        ->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('user.email', $user->email);

    $this->withToken($token)
        ->postJson('/api/v1/logout')
        ->assertOk()
        ->assertJsonPath('message', 'Logout berhasil.');

    expect($user->tokens()->count())->toBe(0);
    $this->app['auth']->forgetGuards();

    $this->withToken($token)
        ->getJson('/api/v1/me')
        ->assertUnauthorized();
});

test('mobile login rejects invalid credentials', function () {
    $user = mobileApiUser();

    $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

test('suspended mobile user cannot login', function () {
    $user = mobileApiUser(['status' => 'suspended']);

    $this->postJson('/api/v1/login', [
        'email' => $user->email,
        'password' => 'secret123',
    ])
        ->assertForbidden()
        ->assertJsonPath(
            'message',
            'Akun Anda sedang dinonaktifkan. Hubungi administrator.',
        );
});

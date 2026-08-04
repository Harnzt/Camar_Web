<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('web registration rejects a personal seller account', function () {
    $this->post(route('register.process'), [
        'role' => 'seller',
        'account_category' => 'personal',
    ])
        ->assertSessionHasErrors([
            'account_category' => 'Seller hanya dapat mendaftar sebagai perusahaan.',
        ]);
});

test('mobile registration API rejects a personal seller account', function () {
    $this->postJson('/api/v1/register', [
        'role' => 'seller',
        'account_category' => 'personal',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('account_category')
        ->assertJsonPath(
            'errors.account_category.0',
            'Seller hanya dapat mendaftar sebagai perusahaan.',
        );
});

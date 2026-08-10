<?php

use App\Core\Infra\External\GoogleAuthenticationService;
use Revolution\Google\Sheets\Facades\Sheets;
use Tests\TestCase;

uses(TestCase::class);

it('configures Google Sheets with the OAuth refresh token only once', function () {
    config([
        'google.auth_type' => 'oauth',
        'google.oauth.refresh_token' => 'refresh-token-001',
    ]);

    Sheets::shouldReceive('setAccessToken')
        ->once()
        ->with([
            'access_token' => '',
            'refresh_token' => 'refresh-token-001',
            'created' => 0,
            'expires_in' => 0,
        ]);

    $service = new GoogleAuthenticationService;

    $service->authenticate();
    $service->authenticate();

    expect(true)->toBeTrue();
});

it('does not configure OAuth when the service account mode is active', function () {
    config(['google.auth_type' => 'service']);

    Sheets::shouldReceive('setAccessToken')->never();

    (new GoogleAuthenticationService)->authenticate();

    expect(true)->toBeTrue();
});

it('fails clearly when OAuth mode has no refresh token', function () {
    config([
        'google.auth_type' => 'oauth',
        'google.oauth.refresh_token' => '',
    ]);

    expect(fn (): mixed => (new GoogleAuthenticationService)->authenticate())
        ->toThrow(RuntimeException::class, 'refresh token OAuth do Google');
});

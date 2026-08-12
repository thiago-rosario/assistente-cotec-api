<?php

use App\Core\Infra\External\GoogleDriveAuthenticationService;
use Illuminate\Support\Env;
use Revolution\Google\Client\GoogleApiClient;
use Revolution\Google\Sheets\Facades\Sheets;
use Tests\TestCase;

uses(TestCase::class);

it('keeps Google Sheets service account authentication enabled when Drive uses OAuth', function () {
    $googleConfig = googleConfigForEnvironment([
        'GOOGLE_SERVICE_ENABLED' => 'true',
        'GOOGLE_DRIVE_AUTH_TYPE' => 'oauth',
    ]);

    expect($googleConfig['service']['enable'])->toBeTrue()
        ->and($googleConfig['auth_type'])->toBe('oauth');
});

it('keeps service account mode working when Drive auth type is service', function () {
    $googleConfig = googleConfigForEnvironment([
        'GOOGLE_SERVICE_ENABLED' => 'true',
        'GOOGLE_DRIVE_AUTH_TYPE' => 'service',
    ]);

    expect($googleConfig['service']['enable'])->toBeTrue()
        ->and($googleConfig['auth_type'])->toBe('service');
});

it('configures the isolated Drive client with the OAuth refresh token only once', function () {
    config([
        'google.auth_type' => 'oauth',
        'google.oauth.refresh_token' => 'refresh-token-001',
    ]);

    Sheets::shouldReceive('setAccessToken')->never();

    $client = Mockery::mock(GoogleApiClient::class);
    $client->shouldReceive('setAccessToken')
        ->once()
        ->with([
            'access_token' => '',
            'refresh_token' => 'refresh-token-001',
            'created' => 0,
            'expires_in' => 0,
        ]);
    $client->shouldReceive('isAccessTokenExpired')->once()->andReturnTrue();
    $client->shouldReceive('fetchAccessTokenWithRefreshToken')->once();
    $client->shouldReceive('make')->once()->with('drive')->andReturn($drive = new stdClass);

    $service = new GoogleDriveAuthenticationService($client);

    $service->authenticate();
    $service->authenticate();

    expect($service->drive())->toBe($drive);
});

it('uses the Drive client without configuring OAuth in service account mode', function () {
    config(['google.auth_type' => 'service']);

    Sheets::shouldReceive('setAccessToken')->never();

    $client = Mockery::mock(GoogleApiClient::class);
    $client->shouldReceive('setAccessToken')->never();
    $client->shouldReceive('make')->once()->with('drive')->andReturn($drive = new stdClass);

    expect((new GoogleDriveAuthenticationService($client))->drive())->toBe($drive);
});

it('fails clearly when OAuth mode has no refresh token', function () {
    config([
        'google.auth_type' => 'oauth',
        'google.oauth.refresh_token' => '',
    ]);

    expect(fn (): mixed => (new GoogleDriveAuthenticationService)->authenticate())
        ->toThrow(RuntimeException::class, 'refresh token OAuth do Google');
});

/**
 * @param  array<string, string>  $environment
 * @return array<string, mixed>
 */
function googleConfigForEnvironment(array $environment): array
{
    $original = [];

    foreach ($environment as $key => $value) {
        $original[$key] = [
            'environment' => getenv($key),
            'env' => array_key_exists($key, $_ENV) ? $_ENV[$key] : null,
            'server' => array_key_exists($key, $_SERVER) ? $_SERVER[$key] : null,
        ];

        putenv($key.'='.$value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    Env::enablePutenv();

    try {
        return require base_path('config/google.php');
    } finally {
        foreach ($original as $key => $values) {
            if ($values['environment'] === false) {
                putenv($key);
            } else {
                putenv($key.'='.$values['environment']);
            }

            if ($values['env'] === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $values['env'];
            }

            if ($values['server'] === null) {
                unset($_SERVER[$key]);
            } else {
                $_SERVER[$key] = $values['server'];
            }
        }

        Env::enablePutenv();
    }
}

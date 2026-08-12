<?php

declare(strict_types=1);

namespace App\Core\Infra\External;

use Revolution\Google\Client\GoogleApiClient;
use RuntimeException;

final class GoogleDriveAuthenticationService
{
    private bool $authenticated = false;

    public function __construct(
        private ?GoogleApiClient $client = null,
    ) {}

    public function authenticate(): void
    {
        if ($this->authenticated) {
            return;
        }

        if ($this->authenticationType() !== 'oauth') {
            $this->authenticated = true;

            return;
        }

        $refreshToken = trim((string) config('google.oauth.refresh_token'));

        if ($refreshToken === '') {
            throw new RuntimeException(
                'O refresh token OAuth do Google não está configurado.',
            );
        }

        $client = $this->client();
        $client->setAccessToken([
            'access_token' => '',
            'refresh_token' => $refreshToken,
            'created' => 0,
            'expires_in' => 0,
        ]);

        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken();
        }

        $this->authenticated = true;
    }

    public function drive(): object
    {
        $this->authenticate();

        $drive = $this->client()->make('drive');

        if (! is_object($drive)) {
            throw new RuntimeException('Não foi possível criar o serviço do Google Drive.');
        }

        return $drive;
    }

    private function client(): GoogleApiClient
    {
        return $this->client ??= new GoogleApiClient($this->clientConfig());
    }

    /**
     * @return array<string, mixed>
     */
    private function clientConfig(): array
    {
        $config = (array) config('google');

        if ($this->authenticationType() === 'oauth') {
            $config['service'] = array_merge(
                (array) ($config['service'] ?? []),
                ['enable' => false],
            );
        }

        return $config;
    }

    private function authenticationType(): string
    {
        return strtolower(trim((string) config('google.auth_type', 'service')));
    }
}

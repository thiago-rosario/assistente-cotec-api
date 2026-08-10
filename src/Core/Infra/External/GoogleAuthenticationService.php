<?php

declare(strict_types=1);

namespace App\Core\Infra\External;

use Revolution\Google\Sheets\Facades\Sheets;
use RuntimeException;

final class GoogleAuthenticationService
{
    private bool $authenticated = false;

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

        Sheets::setAccessToken([
            'access_token' => '',
            'refresh_token' => $refreshToken,
            'created' => 0,
            'expires_in' => 0,
        ]);

        $this->authenticated = true;
    }

    private function authenticationType(): string
    {
        return strtolower(trim((string) config('google.auth_type', 'service')));
    }
}

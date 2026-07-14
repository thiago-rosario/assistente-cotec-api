<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Service;

use App\Core\Conversation\Application\Interfaces\Service\GreetingMessageMatcherServiceInterface;
use Illuminate\Support\Str;

final class GreetingMessageMatcherService implements GreetingMessageMatcherServiceInterface
{
    private const array Greetings = [
        'oi',
        'oie',
        'ola',
        'alo',
        'opa',
        'e ai',
        'eae',
        'salve',
        'hello',
        'hi',
        'bom dia',
        'boa tarde',
        'boa noite',
        'oi bom dia',
        'oi boa tarde',
        'oi boa noite',
        'ola bom dia',
        'ola boa tarde',
        'ola boa noite',
        'tudo bem',
        'tudo bom',
        'td bem',
        'td bom',
    ];

    public function matches(string $message): bool
    {
        $normalizedMessage = $this->normalize($message);

        $normalizedLines = collect(preg_split('/\R/', $message) ?: [])
            ->map(fn (string $line): string => $this->normalize($line))
            ->reject(fn (string $line): bool => $this->isEmptyOrTimestamp($line))
            ->unique()
            ->values();

        if ($normalizedLines->count() === 1) {
            $normalizedMessage = $normalizedLines->first();
        }

        return preg_match('/^oi+$/', $normalizedMessage) === 1
            || in_array($normalizedMessage, self::Greetings, true);
    }

    private function normalize(string $message): string
    {
        return Str::of($message)
            ->replaceMatches('/[\x{200E}\x{200F}]/u', '')
            ->trim()
            ->lower()
            ->ascii()
            ->replaceMatches('/[.!?,;:]+/', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }

    private function isEmptyOrTimestamp(string $message): bool
    {
        return $message === '' || preg_match('/^\d{1,2}\s\d{2}$/', $message) === 1;
    }
}

<?php

declare(strict_types=1);

namespace App\Core\Application\Service;

use App\Core\Application\Interfaces\Service\MunicipalityExtractorServiceInterface;
use Illuminate\Support\Str;

class MunicipalityExtractorService implements MunicipalityExtractorServiceInterface
{
    private const array Patterns = [
        '/(?:munic[ií]pio|cidade)\s+(?:de\s+|do\s+|da\s+)?([\p{L}\s\'-]+?)(?:[?.!,;:]|$)/iu',
        '/\bobras?\b.+\bem\s+([\p{L}\s\'-]+?)(?:[?.!,;:]|$)/iu',
    ];

    public function extract(string $message): ?string
    {
        foreach (self::Patterns as $pattern) {
            if (preg_match($pattern, $message, $matches) !== 1) {
                continue;
            }

            $municipality = Str::of($matches[1])
                ->replaceMatches('/\s+/', ' ')
                ->trim()
                ->toString();

            if ($municipality !== '') {
                return $municipality;
            }
        }

        return null;
    }
}

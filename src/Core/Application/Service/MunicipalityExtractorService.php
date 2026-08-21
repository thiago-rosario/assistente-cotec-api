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
        '/\bcadernos?\s+t[eé]cnicos?\s+(?:de\s+|do\s+|da\s+|em\s+)?([\p{L}\s\'-]+?)(?:[?.!,;:]|$)/iu',
        '/\b(?:consultar|consulta|buscar|busca|pesquisar|pesquisa|procurar|procura)\b\s+(?:(?:o|a|os|as|do|da|dos|das)\s+)?(?:cadernos?\s+t[eé]cnicos?\s+(?:de\s+|do\s+|da\s+|em\s+)?|munic[ií]pio\s+(?:de\s+|do\s+|da\s+)?|cidade\s+(?:de\s+|do\s+|da\s+)?|em\s+|de\s+|do\s+|da\s+|sobre\s+)?([\p{L}\s\'-]+?)(?:[?.!,;:]|$)/iu',
    ];

    private const array LeadingGreetingPatterns = [
        '/^\s*(?:oi+|oie|ol[aá]|al[oô]|opa|e\s+a[ií]|eae|salve|hello|hi)\b[,\s.!?;:-]*/iu',
        '/^\s*(?:bom\s+dia|boa\s+tarde|boa\s+noite)\b[,\s.!?;:-]*/iu',
        '/^\s*(?:tudo\s+bem|tudo\s+bom|td\s+bem|td\s+bom)\b[,\s.!?;:-]*/iu',
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

            if ($municipality !== '' && $this->isValidStandaloneCandidate($municipality)) {
                return $municipality;
            }
        }

        $municipality = $this->cleanStandaloneMessage($message);

        return $municipality !== '' && $this->isValidStandaloneCandidate($municipality)
            ? $municipality
            : null;
    }

    private function cleanStandaloneMessage(string $message): string
    {
        $municipality = Str::of($message)
            ->replaceMatches('/[\x{200E}\x{200F}]/u', '')
            ->replaceMatches('/\R+/', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();

        do {
            $previousMunicipality = $municipality;

            foreach (self::LeadingGreetingPatterns as $pattern) {
                $municipality = Str::of($municipality)
                    ->replaceMatches($pattern, '')
                    ->trim()
                    ->toString();
            }
        } while ($municipality !== $previousMunicipality);

        return Str::of($municipality)
            ->replaceMatches('/^[,.;:!?\-\s]+|[,.;:!?\-\s]+$/u', '')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }

    private function isValidStandaloneCandidate(string $municipality): bool
    {
        if (preg_match('/^[\p{L}\s\'-]{3,60}$/u', $municipality) !== 1) {
            return false;
        }

        $normalizedMunicipality = Str::of($municipality)
            ->lower()
            ->ascii()
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();

        $words = Str::of($normalizedMunicipality)
            ->explode(' ')
            ->filter()
            ->values();

        if ($words->count() > 4) {
            return false;
        }

        return preg_match(
            '/(?:^|\s)(?:forca|levantamento\s+de\s+terreno|processo)(?:\s|$)/i',
            $normalizedMunicipality,
        ) !== 1;
    }
}

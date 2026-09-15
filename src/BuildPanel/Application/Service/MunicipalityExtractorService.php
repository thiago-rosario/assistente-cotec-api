<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Service;

use App\BuildPanel\Application\Interfaces\Service\MunicipalityExtractorServiceInterface;
use App\BuildPanel\Domain\Repository\TechnicalNotebookRepositoryInterface;
use App\Contract\Infra\Repository\SheetRepository\FindContractRecordsGoogleSheetRepository;
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

    public function __construct(
        private readonly TechnicalNotebookRepositoryInterface $notebooks,
        private readonly FindContractRecordsGoogleSheetRepository $contracts,
    ) {}

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
                return $this->matchAvailableMunicipality($municipality);
            }
        }

        $municipality = $this->cleanStandaloneMessage($message);

        return $municipality !== '' && $this->isValidStandaloneCandidate($municipality)
            ? $this->matchAvailableMunicipality($municipality)
            : null;
    }

    private function matchAvailableMunicipality(string $municipality): ?string
    {
        $municipalities = [];

        foreach ($this->notebooks->all() as $notebook) {
            $municipalities[] = $notebook->municipality;
        }

        foreach ($this->contracts->findAll() as $contract) {
            $municipalities = [...$municipalities, ...$contract->municipalities];
        }

        $availableMunicipalities = [];

        foreach ($municipalities as $availableMunicipality) {
            $normalized = $this->normalizeMunicipality($availableMunicipality);

            if ($normalized !== '') {
                $availableMunicipalities[$normalized] ??= trim($availableMunicipality);
            }
        }

        $normalizedMunicipality = $this->normalizeMunicipality($municipality);

        if (isset($availableMunicipalities[$normalizedMunicipality])) {
            return $availableMunicipalities[$normalizedMunicipality];
        }

        if (strlen($normalizedMunicipality) < 5) {
            return null;
        }

        $match = null;

        foreach ($availableMunicipalities as $normalized => $availableMunicipality) {
            if (strlen($normalized) < 5 || levenshtein($normalizedMunicipality, $normalized) !== 1) {
                continue;
            }

            if ($match !== null) {
                return null;
            }

            $match = $availableMunicipality;
        }

        return $match;
    }

    private function normalizeMunicipality(string $municipality): string
    {
        return Str::of($municipality)->ascii()->lower()->replaceMatches('/\s+/', ' ')->trim()->toString();
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
            ->trim();

        $words = $normalizedMunicipality
            ->explode(' ')
            ->filter()
            ->values();

        if ($words->count() > 4) {
            return false;
        }

        return $words->count() <= 4;
    }
}

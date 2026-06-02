<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\TechnicalNotebookEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class SearchTechnicalNotebookGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<TechnicalNotebookEntity>  $technicalNotebooks
     * @return list<TechnicalNotebookEntity>
     */
    public function search(array $technicalNotebooks, string $term): array
    {
        $normalizedTerm = $this->normalize($term);

        return array_values(array_filter(
            $technicalNotebooks,
            fn (TechnicalNotebookEntity $technicalNotebook): bool => $this->matchesSearchTerm($technicalNotebook, $normalizedTerm),
        ));
    }

    private function matchesSearchTerm(TechnicalNotebookEntity $technicalNotebook, string $normalizedTerm): bool
    {
        foreach ($technicalNotebook->toSearchableArray() as $value) {
            if ($value !== null && str_contains($this->normalize($value), $normalizedTerm)) {
                return true;
            }
        }

        return false;
    }
}

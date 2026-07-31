<?php

declare(strict_types=1);

namespace App\BuildPanel\Infra\Repository\SheetRepository;

use App\BuildPanel\Domain\Entity\TechnicalNotebookEntity;
use App\BuildPanel\Infra\Trait\HandlesGoogleSheetRows;
use App\BuildPanel\Infra\Trait\SearchableEntityMatcherTrait;

class SearchTechnicalNotebookGoogleSheetRepository
{
    use HandlesGoogleSheetRows;
    use SearchableEntityMatcherTrait;

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
}

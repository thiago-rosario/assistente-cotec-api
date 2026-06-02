<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\NotebookEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;
use App\Core\Infra\Trait\SearchableEntityMatcherTrait;

class SearchNotebookGoogleSheetRepository
{
    use HandlesGoogleSheetRows;
    use SearchableEntityMatcherTrait;

    /**
     * @param  list<NotebookEntity>  $notebooks
     * @return list<NotebookEntity>
     */
    public function search(array $notebooks, string $term): array
    {
        $normalizedTerm = $this->normalize($term);

        return array_values(array_filter(
            $notebooks,
            fn (NotebookEntity $notebook): bool => $this->matchesSearchTerm($notebook, $normalizedTerm),
        ));
    }
}

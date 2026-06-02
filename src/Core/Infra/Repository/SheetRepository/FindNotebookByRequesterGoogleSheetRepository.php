<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\NotebookEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindNotebookByRequesterGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<NotebookEntity>  $notebooks
     * @return list<NotebookEntity>
     */
    public function findByRequester(array $notebooks, string $requester): array
    {
        $normalizedRequester = $this->normalize($requester);

        return array_values(array_filter(
            $notebooks,
            fn (NotebookEntity $notebook): bool => $notebook->requester !== null
                && $this->normalize($notebook->requester) === $normalizedRequester,
        ));
    }
}

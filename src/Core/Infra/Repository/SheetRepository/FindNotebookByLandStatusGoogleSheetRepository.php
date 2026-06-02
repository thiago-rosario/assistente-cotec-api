<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\NotebookEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindNotebookByLandStatusGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<NotebookEntity>  $notebooks
     * @return list<NotebookEntity>
     */
    public function findByLandStatus(array $notebooks, string $status): array
    {
        $normalizedStatus = $this->normalize($status);

        return array_values(array_filter(
            $notebooks,
            fn (NotebookEntity $notebook): bool => $notebook->landStatus !== null
                && $this->normalize($notebook->landStatus) === $normalizedStatus,
        ));
    }
}

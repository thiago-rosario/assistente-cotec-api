<?php

declare(strict_types=1);

namespace App\Core\Infra\Repository\SheetRepository;

use App\Core\Domain\Entity\ConstructionDemandEntity;
use App\Core\Infra\Trait\HandlesGoogleSheetRows;

class FindConstructionDemandByProcessGoogleSheetRepository
{
    use HandlesGoogleSheetRows;

    /**
     * @param  list<ConstructionDemandEntity>  $constructionDemands
     */
    public function findByProcess(array $constructionDemands, string $process): ?ConstructionDemandEntity
    {
        $normalizedProcess = $this->normalize($process);

        foreach ($constructionDemands as $constructionDemand) {
            if ($constructionDemand->process !== null && $this->normalize($constructionDemand->process) === $normalizedProcess) {
                return $constructionDemand;
            }
        }

        return null;
    }
}

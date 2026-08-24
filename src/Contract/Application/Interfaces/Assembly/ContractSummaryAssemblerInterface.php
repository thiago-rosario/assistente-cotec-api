<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Assembly;

use App\Contract\Application\DTO\ContractExecutionDeadlineOutputDTO;
use App\Contract\Application\DTO\ContractReadjustmentOutputDTO;
use App\Contract\Application\DTO\ContractSummaryOutputDTO;
use App\Contract\Application\DTO\MunicipalityContractReferenceDTO;
use App\Contract\Application\DTO\ValueAdditiveOutputDTO;
use App\Contract\Domain\Entity\ContractEntity;

interface ContractSummaryAssemblerInterface
{
    /**
     * @param  list<ValueAdditiveOutputDTO>  $valueAdditives
     * @param  list<ContractReadjustmentOutputDTO>  $readjustments
     * @param  list<ContractExecutionDeadlineOutputDTO>  $executionDeadlines
     */
    public function assemble(
        ContractEntity $contract,
        MunicipalityContractReferenceDTO $reference,
        ?string $municipality,
        array $valueAdditives,
        array $readjustments,
        array $executionDeadlines,
    ): ContractSummaryOutputDTO;
}

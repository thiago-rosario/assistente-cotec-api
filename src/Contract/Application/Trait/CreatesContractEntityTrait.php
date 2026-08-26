<?php

declare(strict_types=1);

namespace App\Contract\Application\Trait;

use App\Contract\Application\DTO\ContractExecutionDeadlineOutputDTO;
use App\Contract\Application\DTO\ContractReadjustmentOutputDTO;
use App\Contract\Application\DTO\ValueAdditiveOutputDTO;
use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;

trait CreatesContractEntityTrait
{
    /**
     * @param array{
     *     contractNumber: ContractNumberValueObject,
     *     company: ?string,
     *     seiProcess: ?string,
     *     municipalities: list<string>,
     *     valueAdditives: list<ValueAdditiveOutputDTO>,
     *     readjustments: list<ContractReadjustmentOutputDTO>,
     *     executionDeadlines: list<ContractExecutionDeadlineOutputDTO>
     * } $contractData
     */
    private function contractEntity(
        array $contractData,
        ?MunicipalityValueObject $municipality,
    ): ContractEntity {
        $deadline = $contractData['executionDeadlines'][0] ?? null;

        return new ContractEntity(
            contractNumber: $contractData['contractNumber']->value,
            company: $contractData['company'],
            seiProcess: $contractData['seiProcess'],
            municipalities: $municipality === null
                ? $contractData['municipalities']
                : [$municipality->value],
            validityEndDate: $deadline?->validityEndDate,
            executionDeadline: $deadline?->executionEndDate?->format('d/m/Y'),
        );
    }
}

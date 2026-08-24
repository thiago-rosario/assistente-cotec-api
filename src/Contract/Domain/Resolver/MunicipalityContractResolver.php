<?php

declare(strict_types=1);

namespace App\Contract\Domain\Resolver;

use App\Contract\Application\DTO\MunicipalityContractReferenceDTO;
use App\Contract\Domain\Repository\ValueAdditiveRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;

class MunicipalityContractResolver
{
    public function __construct(
        private readonly ValueAdditiveRepositoryInterface $repository,
    ) {}

    /**
     * @return list<MunicipalityContractReferenceDTO>
     */
    public function resolve(MunicipalityValueObject $municipality): array
    {
        /** @var array<string, MunicipalityContractReferenceDTO> $referencesByContractNumber */
        $referencesByContractNumber = [];

        foreach ($this->repository->findByMunicipality($municipality) as $valueAdditive) {
            $contractNumber = new ContractNumberValueObject($valueAdditive->contractNumber);
            $company = $valueAdditive->company === null ? null : trim($valueAdditive->company);
            $company = $company === '' ? null : $company;

            if (! isset($referencesByContractNumber[$contractNumber->value])) {
                $referencesByContractNumber[$contractNumber->value] = new MunicipalityContractReferenceDTO(
                    contractNumber: $contractNumber,
                    company: $company,
                );

                continue;
            }

            $reference = $referencesByContractNumber[$contractNumber->value];

            if ($reference->company === null && $company !== null) {
                $referencesByContractNumber[$contractNumber->value] = new MunicipalityContractReferenceDTO(
                    contractNumber: $reference->contractNumber,
                    company: $company,
                );
            }
        }

        return array_values($referencesByContractNumber);
    }
}

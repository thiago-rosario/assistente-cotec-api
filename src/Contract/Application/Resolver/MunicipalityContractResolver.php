<?php

declare(strict_types=1);

namespace App\Contract\Application\Resolver;

use App\Contract\Application\DTO\MunicipalityContractReferenceDTO;
use App\Contract\Application\Interfaces\Resolver\MunicipalityContractResolverInterface;
use App\Contract\Domain\Repository\ContractRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;

/**
 * Resolves municipality contracts from the contract tracking movement sheets.
 *
 * The contract repository is the canonical source for municipality-to-contract
 * discovery. Detail repositories remain responsible for their own records.
 */
class MunicipalityContractResolver implements MunicipalityContractResolverInterface
{
    public function __construct(
        private readonly ContractRepositoryInterface $repository,
    ) {}

    /**
     * @return list<MunicipalityContractReferenceDTO>
     */
    public function resolve(MunicipalityValueObject $municipality): array
    {
        /** @var array<string, MunicipalityContractReferenceDTO> $referencesByContractNumber */
        $referencesByContractNumber = [];

        foreach ($this->repository->findByMunicipality($municipality) as $contract) {
            $contractNumber = new ContractNumberValueObject($contract->contractNumber);
            $company = $contract->company === null ? null : trim($contract->company);
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

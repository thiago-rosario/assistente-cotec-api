<?php

declare(strict_types=1);

namespace App\Contract\Infra\Repository\Gateway;

use App\Contract\Domain\Entity\ValueAdditiveEntity;
use App\Contract\Domain\Repository\ValueAdditiveRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;
use App\Contract\Infra\Repository\SheetRepository\FindValueAdditivesByContractNumberGoogleSheetRepository;
use App\Contract\Infra\Repository\SheetRepository\FindValueAdditivesByMunicipalityAndContractNumberGoogleSheetRepository;
use App\Contract\Infra\Repository\SheetRepository\FindValueAdditivesByMunicipalityGoogleSheetRepository;

final readonly class ValueAdditiveGoogleSheetGatewayRepository implements ValueAdditiveRepositoryInterface
{
    public function __construct(
        private FindValueAdditivesByMunicipalityGoogleSheetRepository $findByMunicipalityRepository,
        private FindValueAdditivesByContractNumberGoogleSheetRepository $findByContractNumberRepository,
        private FindValueAdditivesByMunicipalityAndContractNumberGoogleSheetRepository $findByMunicipalityAndContractNumberRepository,
    ) {}

    /**
     * @return list<ValueAdditiveEntity>
     */
    public function findByMunicipality(MunicipalityValueObject $municipality): array
    {
        return $this->findByMunicipalityRepository->findByMunicipality($municipality);
    }

    /**
     * @return list<ValueAdditiveEntity>
     */
    public function findByContractNumber(ContractNumberValueObject $contractNumber): array
    {
        return $this->findByContractNumberRepository->findByContractNumber($contractNumber);
    }

    /**
     * @return list<ValueAdditiveEntity>
     */
    public function findByMunicipalityAndContractNumber(
        MunicipalityValueObject $municipality,
        ContractNumberValueObject $contractNumber,
    ): array {
        return $this->findByMunicipalityAndContractNumberRepository->findByMunicipalityAndContractNumber(
            $municipality,
            $contractNumber,
        );
    }
}

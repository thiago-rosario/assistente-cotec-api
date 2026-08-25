<?php

declare(strict_types=1);

namespace App\Contract\Infra\Repository\Gateway;

use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\Repository\ContractRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;
use App\Contract\Infra\Repository\SheetRepository\FindContractByContractNumberGoogleSheetRepository;
use App\Contract\Infra\Repository\SheetRepository\FindContractBySeiProcessGoogleSheetRepository;
use App\Contract\Infra\Repository\SheetRepository\FindContractsByCompanyGoogleSheetRepository;
use App\Contract\Infra\Repository\SheetRepository\FindContractsByMunicipalityGoogleSheetRepository;

final readonly class ContractGoogleSheetGatewayRepository implements ContractRepositoryInterface
{
    public function __construct(
        private FindContractByContractNumberGoogleSheetRepository $findByContractNumberRepository,
        private FindContractBySeiProcessGoogleSheetRepository $findBySeiProcessRepository,
        private FindContractsByMunicipalityGoogleSheetRepository $findByMunicipalityRepository,
        private FindContractsByCompanyGoogleSheetRepository $findByCompanyRepository,
    ) {}

    public function findByContractNumber(ContractNumberValueObject $contractNumber): ?ContractEntity
    {
        return $this->findByContractNumberRepository->findByContractNumber($contractNumber);
    }

    public function findBySeiProcess(string $seiProcess): ?ContractEntity
    {
        return $this->findBySeiProcessRepository->findBySeiProcess($seiProcess);
    }

    /**
     * @return list<ContractEntity>
     */
    public function findByMunicipality(MunicipalityValueObject $municipality): array
    {
        return $this->findByMunicipalityRepository->findByMunicipality($municipality);
    }

    /**
     * @return list<ContractEntity>
     */
    public function findByCompany(string $company): array
    {
        return $this->findByCompanyRepository->findByCompany($company);
    }
}

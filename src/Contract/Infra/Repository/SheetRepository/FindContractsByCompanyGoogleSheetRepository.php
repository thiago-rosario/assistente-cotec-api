<?php

declare(strict_types=1);

namespace App\Contract\Infra\Repository\SheetRepository;

use App\Contract\Application\Interfaces\Parser\ContractSearchValueParserInterface;
use App\Contract\Domain\Entity\ContractEntity;

final class FindContractsByCompanyGoogleSheetRepository
{
    public function __construct(
        private readonly FindContractRecordsGoogleSheetRepository $recordsRepository,
        private readonly ContractSearchValueParserInterface $searchValueParser,
    ) {}

    /**
     * @return list<ContractEntity>
     */
    public function findByCompany(string $company): array
    {
        $normalizedCompany = $this->searchValueParser->parse($company);
        $contracts = $this->recordsRepository->findAll();

        return array_values(array_filter(
            $contracts,
            fn (ContractEntity $contract): bool => $contract->company !== null
                && $this->searchValueParser->parse($contract->company) === $normalizedCompany,
        ));
    }
}

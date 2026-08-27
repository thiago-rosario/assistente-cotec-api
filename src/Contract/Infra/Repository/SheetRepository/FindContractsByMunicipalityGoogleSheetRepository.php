<?php

declare(strict_types=1);

namespace App\Contract\Infra\Repository\SheetRepository;

use App\Contract\Application\Interfaces\Parser\ContractSearchValueParserInterface;
use App\Contract\Domain\Entity\ContractEntity;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;

final readonly class FindContractsByMunicipalityGoogleSheetRepository
{
    public function __construct(
        private FindContractRecordsGoogleSheetRepository $recordsRepository,
        private ContractSearchValueParserInterface $searchValueParser,
    ) {}

    /**
     * @return list<ContractEntity>
     */
    public function findByMunicipality(MunicipalityValueObject $municipality): array
    {
        $normalizedMunicipality = $this->searchValueParser->parse($municipality->value);
        $contracts = $this->recordsRepository->findAll();

        return array_values(array_filter(
            $contracts,
            fn (ContractEntity $contract): bool => collect($contract->municipalities)
                ->contains(fn (string $candidate): bool => $this->searchValueParser->parse($candidate) === $normalizedMunicipality),
        ));
    }
}

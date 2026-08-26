<?php

declare(strict_types=1);

namespace App\Contract\Infra\Repository\SheetRepository;

use App\Contract\Application\Interfaces\Adapter\ContractSheetAdapterInterface;
use App\Contract\Application\Interfaces\Mapper\ContractSheetMapperInterface;
use App\Contract\Application\Interfaces\Parser\ContractSearchValueParserInterface;
use App\Contract\Domain\Entity\ContractEntity;

final class FindContractBySeiProcessGoogleSheetRepository
{
    public function __construct(
        private readonly ContractSheetAdapterInterface $adapter,
        private readonly ContractSheetMapperInterface $mapper,
        private readonly ContractSearchValueParserInterface $searchValueParser,
    ) {}

    public function findBySeiProcess(string $seiProcess): ?ContractEntity
    {
        $normalizedSeiProcess = $this->searchValueParser->parse($seiProcess);
        $contracts = $this->adapter->map('contracts', $this->mapper->map(...));

        foreach ($contracts as $contract) {
            if ($contract->seiProcess !== null
                && $this->searchValueParser->parse($contract->seiProcess) === $normalizedSeiProcess) {
                return $contract;
            }
        }

        return null;
    }
}

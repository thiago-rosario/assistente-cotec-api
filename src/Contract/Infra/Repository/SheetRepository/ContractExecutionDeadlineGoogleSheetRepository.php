<?php

declare(strict_types=1);

namespace App\Contract\Infra\Repository\SheetRepository;

use App\Contract\Application\Interfaces\Adapter\ContractSheetAdapterInterface;
use App\Contract\Application\Interfaces\Mapper\ContractExecutionDeadlineSheetMapperInterface;
use App\Contract\Domain\Entity\ContractExecutionDeadlineEntity;
use App\Contract\Domain\Repository\ContractExecutionDeadlineRepositoryInterface;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;

final class ContractExecutionDeadlineGoogleSheetRepository implements ContractExecutionDeadlineRepositoryInterface
{
    public function __construct(
        private readonly ContractSheetAdapterInterface $adapter,
        private readonly ContractExecutionDeadlineSheetMapperInterface $mapper,
    ) {}

    /**
     * @return list<ContractExecutionDeadlineEntity>
     */
    public function findByContractNumber(ContractNumberValueObject $contractNumber): array
    {
        $deadlines = $this->adapter->map('execution-deadlines', $this->mapper->map(...));

        return array_values(array_filter(
            $deadlines,
            fn (ContractExecutionDeadlineEntity $deadline): bool => (new ContractNumberValueObject($deadline->contractNumber))
                ->equals($contractNumber),
        ));
    }
}

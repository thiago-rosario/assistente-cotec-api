<?php

declare(strict_types=1);

namespace App\Contract\Infra\Repository\SheetRepository;

use App\Contract\Application\Interfaces\Adapter\ContractSheetAdapterInterface;
use App\Contract\Application\Interfaces\Mapper\ValueAdditiveSheetMapperInterface;
use App\Contract\Application\Interfaces\Parser\ContractSearchValueParserInterface;
use App\Contract\Domain\Entity\ValueAdditiveEntity;
use App\Contract\Domain\ValueObject\ContractNumberValueObject;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;

final class FindValueAdditivesByMunicipalityAndContractNumberGoogleSheetRepository
{
    public function __construct(
        private readonly ContractSheetAdapterInterface $adapter,
        private readonly ValueAdditiveSheetMapperInterface $mapper,
        private readonly ContractSearchValueParserInterface $searchValueParser,
    ) {}

    /**
     * @return list<ValueAdditiveEntity>
     */
    public function findByMunicipalityAndContractNumber(
        MunicipalityValueObject $municipality,
        ContractNumberValueObject $contractNumber,
    ): array {
        $normalizedMunicipality = $this->searchValueParser->parse($municipality->value);
        $valueAdditives = $this->adapter->map('value-additives', $this->mapper->map(...));

        return array_values(array_filter(
            $valueAdditives,
            fn (ValueAdditiveEntity $valueAdditive): bool => $valueAdditive->contractNumber === $contractNumber->value
                && $this->searchValueParser->parse($valueAdditive->municipality) === $normalizedMunicipality,
        ));
    }
}

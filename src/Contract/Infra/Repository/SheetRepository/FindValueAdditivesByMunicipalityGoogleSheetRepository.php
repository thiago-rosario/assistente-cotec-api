<?php

declare(strict_types=1);

namespace App\Contract\Infra\Repository\SheetRepository;

use App\Contract\Application\Interfaces\Adapter\ContractSheetAdapterInterface;
use App\Contract\Application\Interfaces\Mapper\ValueAdditiveSheetMapperInterface;
use App\Contract\Application\Interfaces\Parser\ContractSearchValueParserInterface;
use App\Contract\Domain\Entity\ValueAdditiveEntity;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;

final class FindValueAdditivesByMunicipalityGoogleSheetRepository
{
    public function __construct(
        private readonly ContractSheetAdapterInterface $adapter,
        private readonly ValueAdditiveSheetMapperInterface $mapper,
        private readonly ContractSearchValueParserInterface $searchValueParser,
    ) {}

    /**
     * @return list<ValueAdditiveEntity>
     */
    public function findByMunicipality(MunicipalityValueObject $municipality): array
    {
        $normalizedMunicipality = $this->searchValueParser->parse($municipality->value);
        $valueAdditives = $this->adapter->map('value-additives', $this->mapper->map(...));

        return array_values(array_filter(
            $valueAdditives,
            fn (ValueAdditiveEntity $valueAdditive): bool => $this->searchValueParser->parse(
                $valueAdditive->municipality,
            ) === $normalizedMunicipality,
        ));
    }
}

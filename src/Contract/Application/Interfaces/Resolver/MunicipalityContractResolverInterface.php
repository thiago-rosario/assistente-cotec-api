<?php

declare(strict_types=1);

namespace App\Contract\Application\Interfaces\Resolver;

use App\Contract\Application\DTO\MunicipalityContractReferenceDTO;
use App\Contract\Domain\ValueObject\MunicipalityValueObject;

interface MunicipalityContractResolverInterface
{
    /**
     * @return list<MunicipalityContractReferenceDTO>
     */
    public function resolve(MunicipalityValueObject $municipality): array;
}

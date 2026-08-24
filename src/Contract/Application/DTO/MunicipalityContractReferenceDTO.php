<?php

declare(strict_types=1);

namespace App\Contract\Application\DTO;

use App\Contract\Domain\ValueObject\ContractNumberValueObject;

readonly class MunicipalityContractReferenceDTO
{
    public function __construct(
        public ContractNumberValueObject $contractNumber,
        public ?string $company,
    ) {}
}

<?php

declare(strict_types=1);

namespace App\Contract\Application\DTO;

readonly class ContractReadjustmentsByContractOutputDTO
{
    /**
     * @param  list<ContractReadjustmentOutputDTO>  $data
     */
    public function __construct(
        public string $contractNumber,
        public ?string $company,
        public int $total,
        public array $data,
    ) {}
}

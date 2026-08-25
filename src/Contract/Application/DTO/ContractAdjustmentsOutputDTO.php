<?php

declare(strict_types=1);

namespace App\Contract\Application\DTO;

use App\Contract\Enum\ContractSearchTypeEnum;

readonly class ContractAdjustmentsOutputDTO
{
    /**
     * @param  list<ContractReadjustmentsByContractOutputDTO>  $data
     */
    public function __construct(
        public string                 $searchTerm,
        public ContractSearchTypeEnum $searchType,
        public int                    $total,
        public array                  $data,
    ) {}
}

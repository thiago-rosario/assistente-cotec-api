<?php

declare(strict_types=1);

namespace App\Contract\Application\DTO;

use App\Contract\Enum\ContractSearchTypeEnum;

readonly class FindContractSummaryOutputDTO
{
    /**
     * @param  list<ContractExtractDTO>  $data
     */
    public function __construct(
        public string $searchTerm,
        public ContractSearchTypeEnum $searchType,
        public int $total,
        public array $data,
    ) {}
}

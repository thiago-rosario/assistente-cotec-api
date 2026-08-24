<?php

declare(strict_types=1);

namespace App\Contract\Application\DTO;

use App\Contract\Enum\ContractSearchType;

readonly class SearchContractOutputDTO
{
    /**
     * @param  list<ContractSummaryOutputDTO>  $data
     */
    public function __construct(
        public string $searchTerm,
        public ContractSearchType $searchType,
        public int $total,
        public array $data,
    ) {}
}

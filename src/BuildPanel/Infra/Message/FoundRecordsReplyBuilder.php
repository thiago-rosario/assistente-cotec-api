<?php

declare(strict_types=1);

namespace App\BuildPanel\Infra\Message;

class FoundRecordsReplyBuilder
{
    public function __construct(
        private readonly TechnicalNotebookReplyBuilder $technicalNotebookReplyBuilder,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @param  array{term: string|null, total: int, data: list<array<string, mixed>>}  $result
     */
    public function build(array $filters, array $result): string
    {
        return $this->technicalNotebookReplyBuilder->build($filters, $result);
    }
}

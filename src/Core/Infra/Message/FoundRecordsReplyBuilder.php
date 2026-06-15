<?php

declare(strict_types=1);

namespace App\Core\Infra\Message;

class FoundRecordsReplyBuilder
{
    public function __construct(
        private readonly TechnicalNotebookReplyBuilder $technicalNotebookReplyBuilder,
        private readonly GenericRecordsReplyBuilder $genericRecordsReplyBuilder,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @param  array{term: string|null, total: int, data: list<array<string, mixed>>}  $result
     */
    public function build(string $intent, array $filters, array $result): string
    {
        if ($intent === 'search_technical_notebook') {
            return $this->technicalNotebookReplyBuilder->build($filters, $result);
        }

        return $this->genericRecordsReplyBuilder->build($intent, $result);
    }
}

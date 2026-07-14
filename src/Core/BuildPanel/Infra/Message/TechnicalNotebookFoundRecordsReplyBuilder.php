<?php

declare(strict_types=1);

namespace App\Core\BuildPanel\Infra\Message;

use App\Core\Conversation\Application\Interfaces\Service\WhatsappFoundRecordsReplyBuilderInterface;
use App\Core\Conversation\Enum\WhatsappMessageIntentEnum;

class TechnicalNotebookFoundRecordsReplyBuilder implements WhatsappFoundRecordsReplyBuilderInterface
{
    public function __construct(
        private readonly TechnicalNotebookReplyBuilder $technicalNotebookReplyBuilder,
    ) {}

    public function supports(string $intent): bool
    {
        return $intent === WhatsappMessageIntentEnum::SEARCH_TECHNICAL_NOTEBOOK->value;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array{term: string|null, total: int, data: list<array<string, mixed>>}  $result
     */
    public function build(array $filters, array $result): string
    {
        return $this->technicalNotebookReplyBuilder->build($filters, $result);
    }
}

<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Interfaces\Service;

interface WhatsappFoundRecordsReplyBuilderInterface
{
    public function supports(string $intent): bool;

    /**
     * @param  array<string, mixed>  $filters
     * @param  array{term: string|null, total: int, data: list<array<string, mixed>>}  $result
     */
    public function build(array $filters, array $result): string;
}

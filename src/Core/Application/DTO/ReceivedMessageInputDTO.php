<?php

declare(strict_types=1);

namespace App\Core\Application\DTO;

readonly class ReceivedMessageInputDTO
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public ?string $message = null,
        public ?string $phone = null,
        public ?string $senderName = null,
        public ?string $receivedAt = null,
        public ?string $source = null,
        public ?string $externalId = null,
        public array $metadata = [],
        public ?ReceivedMessageDocumentInputDTO $document = null,
        public ?string $caption = null,
    ) {}
}

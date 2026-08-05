<?php

declare(strict_types=1);

namespace App\Core\Application\DTO;

readonly class ReceivedMessageDocumentInputDTO
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public string $originalFileName,
        public string $mimeType,
        public int $sizeBytes,
        public ?string $contentBase64 = null,
        public ?string $temporaryPath = null,
        public array $metadata = [],
    ) {}
}

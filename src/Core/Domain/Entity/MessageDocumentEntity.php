<?php

declare(strict_types=1);

namespace App\Core\Domain\Entity;

class MessageDocumentEntity
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        private string $originalFileName,
        private string $mimeType,
        private int $sizeBytes,
        private ?string $contentBase64 = null,
        private ?string $temporaryPath = null,
        private array $metadata = [],
    ) {}

    public function originalFileName(): string
    {
        return $this->originalFileName;
    }

    public function mimeType(): string
    {
        return $this->mimeType;
    }

    public function sizeBytes(): int
    {
        return $this->sizeBytes;
    }

    public function contentBase64(): ?string
    {
        return $this->contentBase64;
    }

    public function temporaryPath(): ?string
    {
        return $this->temporaryPath;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    public function hasContentReference(): bool
    {
        return filled($this->contentBase64) || filled($this->temporaryPath);
    }
}

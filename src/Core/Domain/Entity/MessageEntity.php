<?php

declare(strict_types=1);

namespace App\Core\Domain\Entity;

use Illuminate\Support\Str;

class MessageEntity
{
    public function __construct(
        private readonly ?string $content,
        private readonly ?string $phone = null,
        private readonly ?MessageDocumentEntity $document = null,
        private readonly ?string $externalId = null,
        private readonly ?string $caption = null,
        private readonly ?string $receivedAt = null,
        /**
         * @var array<string, mixed>
         */
        private readonly array $metadata = [],
    ) {}

    public function content(): string
    {
        return $this->content ?? '';
    }

    public function externalId(): ?string
    {
        return $this->externalId;
    }

    public function caption(): ?string
    {
        return $this->caption;
    }

    public function receivedAt(): ?string
    {
        return $this->receivedAt;
    }

    public function document(): ?MessageDocumentEntity
    {
        return $this->document;
    }

    public function hasDocument(): bool
    {
        return $this->document !== null;
    }

    public function hasSupportedContent(): bool
    {
        return $this->hasTextContent() || $this->hasDocument();
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    public function normalizedPhone(): ?string
    {
        if ($this->phone === null || trim($this->phone) === '') {
            return null;
        }

        return trim($this->phone);
    }

    public function hasTextContent(): bool
    {
        return trim($this->content) !== '';
    }

    public function normalizedContent(): string
    {
        return Str::of($this->content)
            ->replaceMatches('/[\x{200E}\x{200F}]/u', '')
            ->trim()
            ->lower()
            ->ascii()
            ->replaceMatches('/[.!?,;:]+/', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }
}

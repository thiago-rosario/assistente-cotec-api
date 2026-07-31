<?php

declare(strict_types=1);

namespace App\Core\Domain\Entity;

use Illuminate\Support\Str;

class MessageEntity
{
    public function __construct(
        private readonly string $content,
        private readonly ?string $phone = null,
    ) {}

    public function content(): string
    {
        return $this->content;
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

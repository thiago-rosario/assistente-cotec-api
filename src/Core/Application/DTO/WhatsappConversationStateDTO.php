<?php

declare(strict_types=1);

namespace App\Core\Application\DTO;

readonly class WhatsappConversationStateDTO
{
    public function __construct(
        public string $route,
        public ?string $municipality = null,
        public ?int $contractOption = null,
    ) {}
}

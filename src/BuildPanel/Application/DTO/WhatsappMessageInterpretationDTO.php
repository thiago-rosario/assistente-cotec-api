<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\DTO;

readonly class WhatsappMessageInterpretationDTO
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public string $intent,
        public array $filters = [],
    ) {}
}

<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces;

interface WhatsappMessageInterpretationParserInterface
{
    /**
     * @param  array<string, mixed>|string  $interpretation
     * @return array{intent: string, filters: array<string, mixed>}
     */
    public function parse(array|string $interpretation): array;
}

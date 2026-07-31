<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Interfaces\Parser;

use App\BuildPanel\Application\DTO\WhatsappMessageInterpretationDTO;

interface WhatsappMessageInterpretationParserInterface
{
    /**
     * @param  array<string, mixed>|string  $interpretation
     */
    public function parse(array|string $interpretation): WhatsappMessageInterpretationDTO;
}

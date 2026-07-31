<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Interfaces\Service;

use App\BuildPanel\Application\DTO\WhatsappMessageInterpretationDTO;

interface DirectWhatsappMessageInterpreterServiceInterface
{
    public function interpret(string $message): ?WhatsappMessageInterpretationDTO;
}

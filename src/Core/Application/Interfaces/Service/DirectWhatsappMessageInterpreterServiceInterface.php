<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Service;

use App\Core\Application\DTO\WhatsappMessageInterpretationDTO;

interface DirectWhatsappMessageInterpreterServiceInterface
{
    public function interpret(string $message): ?WhatsappMessageInterpretationDTO;
}

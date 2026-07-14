<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Interfaces\Service;

use App\Core\Conversation\Application\DTO\WhatsappMessageInterpretationDTO;

interface ResolveWhatsappMessageInterpretationServiceInterface
{
    public function __invoke(string $message): WhatsappMessageInterpretationDTO;
}

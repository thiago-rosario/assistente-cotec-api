<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Interfaces\Service;

use App\BuildPanel\Application\DTO\WhatsappMessageInterpretationDTO;

interface ResolveWhatsappMessageInterpretationServiceInterface
{
    public function __invoke(string $message): WhatsappMessageInterpretationDTO;
}

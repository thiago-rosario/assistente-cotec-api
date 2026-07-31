<?php

declare(strict_types=1);

namespace App\BuildPanel\Application\Service;

use App\BuildPanel\Application\DTO\WhatsappMessageInterpretationDTO;
use App\BuildPanel\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;
use App\BuildPanel\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\BuildPanel\Domain\Resolver\WhatsappMessageIntentResolver;
use App\BuildPanel\Enum\WhatsappMessageIntentEnum;

class ResolveWhatsappMessageInterpretationService implements ResolveWhatsappMessageInterpretationServiceInterface
{
    public function __construct(
        private readonly DirectWhatsappMessageInterpreterServiceInterface $directInterpreter,
        private readonly WhatsappMessageIntentResolver $resolver,
    ) {}

    public function __invoke(string $message): WhatsappMessageInterpretationDTO
    {
        $interpretation = $this->directInterpreter->interpret($message) ?? new WhatsappMessageInterpretationDTO(
            intent: WhatsappMessageIntentEnum::UNKNOWN->value,
        );

        return $this->resolver->resolve($interpretation);
    }
}

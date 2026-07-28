<?php

declare(strict_types=1);

namespace App\Core\Application\Service;

use App\Core\Application\DTO\WhatsappMessageInterpretationDTO;
use App\Core\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;
use App\Core\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\Core\Domain\Resolver\WhatsappMessageIntentResolver;
use App\Core\Enum\WhatsappMessageIntentEnum;

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

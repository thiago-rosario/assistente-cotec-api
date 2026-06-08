<?php

declare(strict_types=1);

namespace App\Core\Application\Service;

use App\Core\Application\DTO\WhatsappMessageInterpretationDTO;
use App\Core\Application\Interfaces\Parser\WhatsappMessageInterpretationParserInterface;
use App\Core\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;
use App\Core\Application\Interfaces\Service\InterpretWhatsappMessageWithAiServiceInterface;
use App\Core\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\Core\Domain\Resolver\WhatsappMessageIntentResolver;

class ResolveWhatsappMessageInterpretationService implements ResolveWhatsappMessageInterpretationServiceInterface
{
    public function __construct(
        private readonly DirectWhatsappMessageInterpreterServiceInterface $directInterpreter,
        private readonly InterpretWhatsappMessageWithAiServiceInterface $aiInterpreter,
        private readonly WhatsappMessageInterpretationParserInterface $parser,
        private readonly WhatsappMessageIntentResolver $resolver,
    ) {}

    public function __invoke(string $message): WhatsappMessageInterpretationDTO
    {
        $interpretation = $this->directInterpreter->interpret($message) ?? $this->parser->parse(
            ($this->aiInterpreter)($message),
        );

        return $this->resolver->resolve($interpretation);
    }
}

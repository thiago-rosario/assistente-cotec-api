<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Service;

use App\Core\Conversation\Application\DTO\WhatsappMessageInterpretationDTO;
use App\Core\Conversation\Application\Interfaces\Parser\WhatsappMessageInterpretationParserInterface;
use App\Core\Conversation\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\InterpretWhatsappMessageWithAiServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\Core\Conversation\Domain\Resolver\WhatsappMessageIntentResolver;

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

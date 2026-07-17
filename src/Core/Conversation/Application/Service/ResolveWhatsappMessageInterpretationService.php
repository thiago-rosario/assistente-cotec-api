<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Service;

use App\Core\Conversation\Application\DTO\WhatsappMessageInterpretationDTO;
use App\Core\Conversation\Application\Interfaces\Parser\WhatsappMessageInterpretationParserInterface;
use App\Core\Conversation\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\InterpretWhatsappMessageWithAiServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\Core\Conversation\Domain\Resolver\WhatsappMessageIntentResolver;
use App\Core\Conversation\Enum\ConversationStateEnum;
use App\Core\Conversation\Enum\MainMenuOptionEnum;
use App\Core\Conversation\Enum\WhatsappMessageIntentEnum;

class ResolveWhatsappMessageInterpretationService implements ResolveWhatsappMessageInterpretationServiceInterface
{
    public function __construct(
        private readonly DirectWhatsappMessageInterpreterServiceInterface $directInterpreter,
        private readonly InterpretWhatsappMessageWithAiServiceInterface $aiInterpreter,
        private readonly WhatsappMessageInterpretationParserInterface $parser,
        private readonly WhatsappMessageIntentResolver $resolver,
    ) {}

    public function __invoke(string $message, ?ConversationStateEnum $state = null): WhatsappMessageInterpretationDTO
    {
        $menuInterpretation = $this->interpretMainMenuOption($message, $state);

        if ($menuInterpretation !== null) {
            return $menuInterpretation;
        }

        $interpretation = $this->directInterpreter->interpret($message) ?? $this->parser->parse(
            ($this->aiInterpreter)($message),
        );

        return $this->resolver->resolve($interpretation);
    }

    private function interpretMainMenuOption(string $message, ?ConversationStateEnum $state): ?WhatsappMessageInterpretationDTO
    {
        if ($state !== ConversationStateEnum::MainMenu) {
            return null;
        }

        return match (MainMenuOptionEnum::tryFrom(trim($message))) {
            MainMenuOptionEnum::BuildPanelConsultation => new WhatsappMessageInterpretationDTO(
                intent: WhatsappMessageIntentEnum::OPEN_BUILD_PANEL->value,
            ),
            default => null,
        };
    }
}

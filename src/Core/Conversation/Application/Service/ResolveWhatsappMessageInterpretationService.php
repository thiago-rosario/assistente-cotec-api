<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Service;

use App\Core\Conversation\Application\DTO\WhatsappMessageInterpretationDTO;
use App\Core\Conversation\Application\Interfaces\Parser\WhatsappMessageInterpretationParserInterface;
use App\Core\Conversation\Application\Interfaces\Service\DirectWhatsappMessageInterpreterServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\InterpretWhatsappMessageWithAiServiceInterface;
use App\Core\Conversation\Application\Interfaces\Service\ResolveWhatsappMessageInterpretationServiceInterface;
use App\Core\Conversation\Domain\Resolver\WhatsappMessageIntentResolver;
use App\Core\Conversation\Enum\ConversationState;
use App\Core\Conversation\Enum\MainMenuOption;
use App\Core\Conversation\Enum\WhatsappMessageIntentEnum;

class ResolveWhatsappMessageInterpretationService implements ResolveWhatsappMessageInterpretationServiceInterface
{
    public function __construct(
        private readonly DirectWhatsappMessageInterpreterServiceInterface $directInterpreter,
        private readonly InterpretWhatsappMessageWithAiServiceInterface $aiInterpreter,
        private readonly WhatsappMessageInterpretationParserInterface $parser,
        private readonly WhatsappMessageIntentResolver $resolver,
    ) {}

    public function __invoke(string $message, ?ConversationState $state = null): WhatsappMessageInterpretationDTO
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

    private function interpretMainMenuOption(string $message, ?ConversationState $state): ?WhatsappMessageInterpretationDTO
    {
        if ($state !== ConversationState::MainMenu) {
            return null;
        }

        return match (MainMenuOption::fromInput($message)) {
            MainMenuOption::BuildPanelConsultation => new WhatsappMessageInterpretationDTO(
                intent: WhatsappMessageIntentEnum::OPEN_BUILD_PANEL->value,
            ),
            default => null,
        };
    }
}

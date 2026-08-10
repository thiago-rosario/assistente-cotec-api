<?php

declare(strict_types=1);

namespace App\Core\Application\Service;

use App\BuildPanel\Application\Interfaces\Service\WhatsappMessageResponseFormatterInterface;
use App\Core\Application\Interfaces\Message\WhatsappMainMenuMessageBuilderInterface;
use App\Core\Application\Interfaces\Service\WhatsappMainMenuServiceInterface;
use App\Core\Domain\Entity\MessageEntity;
use App\Core\Domain\Repository\WhatsappConversationStateRepositoryInterface;
use App\Core\Enum\WhatsappConversationState;
use App\Core\Enum\WhatsappMenuOption;
use App\TechnicalInspectionReport\Application\Interfaces\Service\TechnicalInspectionReportWhatsappConversationFlowServiceInterface;

class WhatsappMainMenuService implements WhatsappMainMenuServiceInterface
{
    public function __construct(
        private readonly WhatsappConversationStateRepositoryInterface $conversationStates,
        private readonly WhatsappMessageResponseFormatterInterface $responseFormatter,
        private readonly WhatsappMainMenuMessageBuilderInterface $messages,
        private readonly TechnicalInspectionReportWhatsappConversationFlowServiceInterface $technicalInspectionReportFlow,
    ) {}

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function show(MessageEntity $message): array
    {
        $this->conversationStates->put($message, WhatsappConversationState::MainMenu);

        return $this->messages->mainMenu();
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function handleOption(MessageEntity $message, WhatsappMenuOption $menuOption): array
    {
        return match ($menuOption) {
            WhatsappMenuOption::BuildPanel => $this->startBuildPanel($message),
            WhatsappMenuOption::TechnicalInspectionReport => $this->startTechnicalInspectionReport($message),
            WhatsappMenuOption::AssistantInfo => $this->assistantInfo($message),
            WhatsappMenuOption::End => $this->endConversation($message),
            WhatsappMenuOption::Invalid => $this->invalidMenuOption($message),
        };
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    public function endConversation(MessageEntity $message): array
    {
        $this->conversationStates->forget($message);

        return $this->messages->conversationEnded();
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    private function startBuildPanel(MessageEntity $message): array
    {
        $this->conversationStates->put($message, WhatsappConversationState::BuildPanel);

        return $this->responseFormatter->greeting();
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    private function startTechnicalInspectionReport(MessageEntity $message): array
    {
        return $this->technicalInspectionReportFlow->start($message);
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    private function assistantInfo(MessageEntity $message): array
    {
        $this->conversationStates->put($message, WhatsappConversationState::MainMenu);

        return $this->messages->assistantInfo();
    }

    /**
     * @return array{reply: string, intent: string, total: int, data: list<array<string, mixed>>, filters: array<string, mixed>}
     */
    private function invalidMenuOption(MessageEntity $message): array
    {
        $this->conversationStates->put($message, WhatsappConversationState::MainMenu);

        return $this->messages->invalidMenuOption();
    }
}

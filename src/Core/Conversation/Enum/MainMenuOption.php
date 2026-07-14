<?php

declare(strict_types=1);

namespace App\Core\Conversation\Enum;

enum MainMenuOption: string
{
    case BuildPanelConsultation = '1';
    case TravelReportSubmission = '2';
    case TravelReportConsultation = '3';
    case AssistantInformation = '4';
    case CloseAttendance = '0';

    public static function fromInput(string $input): ?self
    {
        return self::tryFrom(trim($input));
    }

    public function nextState(): ConversationState
    {
        return match ($this) {
            self::BuildPanelConsultation => ConversationState::BuildPanelConsultation,
            self::TravelReportSubmission => ConversationState::TravelReportLogin,
            self::TravelReportConsultation => ConversationState::TravelReportConsultation,
            self::AssistantInformation => ConversationState::AssistantInformation,
            self::CloseAttendance => ConversationState::Closed,
            default => ConversationState::Unknown,
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Core\Conversation\Enum;

enum MainMenuOptionEnum: string
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

    public function nextState(): ConversationStateEnum
    {
        return match ($this) {
            self::BuildPanelConsultation => ConversationStateEnum::BuildPanelConsultation,
            self::TravelReportSubmission => ConversationStateEnum::TravelReportLogin,
            self::TravelReportConsultation => ConversationStateEnum::TravelReportConsultation,
            self::AssistantInformation => ConversationStateEnum::AssistantInformation,
            self::CloseAttendance => ConversationStateEnum::Closed,
            default => ConversationStateEnum::Unknown,
        };
    }
}

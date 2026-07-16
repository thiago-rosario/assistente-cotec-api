<?php

declare(strict_types=1);

namespace App\Core\Conversation\Domain\Resolver;

use App\Core\Conversation\Enum\MainMenuOptionEnum;
use App\Core\Conversation\Enum\ConversationStateEnum;

class MainMenuStateResolver
{
    public function resolve(MainMenuOptionEnum $optionEnum): ConversationStateEnum
    {
        return match ($optionEnum) {
            MainMenuOptionEnum::BuildPanelConsultation =>
            ConversationStateEnum::BuildPanelConsultation,

            MainMenuOptionEnum::TravelReportSubmission =>
            ConversationStateEnum::TravelReportLogin,

            MainMenuOptionEnum::TravelReportConsultation =>
            ConversationStateEnum::TravelReportConsultation,

            MainMenuOptionEnum::AssistantInformation =>
            ConversationStateEnum::AssistantInformation,

            MainMenuOptionEnum::CloseAttendance =>
            ConversationStateEnum::Closed,
        };
    }
}

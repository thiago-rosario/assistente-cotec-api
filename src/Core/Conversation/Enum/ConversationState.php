<?php

declare(strict_types=1);

namespace App\Core\Conversation\Enum;

enum ConversationState: string
{
    case MainMenu = 'main_menu';
    case BuildPanelConsultation = 'build_panel_consultation';
    case TravelReportLogin = 'travel_report_login';
    case TravelReportConsultation = 'travel_report_consultation';
    case AssistantInformation = 'assistant_information';
    case GoBack = 'go_back';
    case Closed = 'close_conversation';
    case Unknown = 'unknown';
}

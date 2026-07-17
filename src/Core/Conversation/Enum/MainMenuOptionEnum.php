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
}

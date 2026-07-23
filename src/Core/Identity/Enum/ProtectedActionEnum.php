<?php

declare(strict_types=1);

namespace App\Core\Identity\Enum;

enum ProtectedActionEnum: string
{
    case BuildPanelConsultation = 'build_panel_consultation';
    case TravelReportSubmission = 'travel_report_submission';
}

<?php

declare(strict_types=1);

namespace App\Core\Enum;

enum WhatsappConversationState: string
{
    case BuildPanel = 'build_panel';

    case MainMenu = 'main_menu';

    case TechnicalInspectionReportMenu = 'technical_inspection_report_menu';

    case TechnicalInspectionReportAwaitingMunicipality = 'technical_inspection_report_awaiting_municipality';

    case TechnicalInspectionReportAwaitingSeiDecision = 'technical_inspection_report_awaiting_sei_decision';

    case TechnicalInspectionReportAwaitingSeiProcess = 'technical_inspection_report_awaiting_sei_process';

    case TechnicalInspectionReportAwaitingInspectionDate = 'technical_inspection_report_awaiting_inspection_date';

    case TechnicalInspectionReportAwaitingResponsible = 'technical_inspection_report_awaiting_responsible';

    case TechnicalInspectionReportAwaitingDocument = 'technical_inspection_report_awaiting_document';

    case TechnicalInspectionReportAwaitingConfirmation = 'technical_inspection_report_awaiting_confirmation';

    case TechnicalInspectionReportProcessing = 'technical_inspection_report_processing';

    case TechnicalInspectionReportCompleted = 'technical_inspection_report_completed';

    case TechnicalInspectionReportRecoverableFailure = 'technical_inspection_report_recoverable_failure';

    public function isTechnicalInspectionReport(): bool
    {
        return str_starts_with($this->value, 'technical_inspection_report_');
    }
}

<?php

declare(strict_types=1);

namespace App\Core\Enum;

enum CodeExceptionEnum: int
{
    case MessageNotContent = 1000;
    case OpenAIEmptyResponse = 1001;
    case GoogleSheetRead = 1002;

    case GoogleSpreadsheetIdRequired = 1003;
    case GoogleSheetIdRequired = 1004;
    case GoogleSheetGidInvalid = 1005;
    case GoogleSheetNameRequired = 1006;
    case GoogleSheetNotConfigured = 1007;

    case SearchTechnicalNotebookError = 1008;
}

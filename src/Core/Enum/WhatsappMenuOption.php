<?php

declare(strict_types=1);

namespace App\Core\Enum;

enum WhatsappMenuOption: string
{
    case End = '0';

    case BuildPanel = '1';

    case TechnicalInspectionReport = '2';

    case AssistantInfo = '3';

    case Invalid = 'invalid';
}

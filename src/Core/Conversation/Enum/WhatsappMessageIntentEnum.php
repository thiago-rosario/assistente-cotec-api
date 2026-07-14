<?php

declare(strict_types=1);

namespace App\Core\Conversation\Enum;

enum WhatsappMessageIntentEnum: string
{
    case UNKNOWN = 'unknown';

    case OPEN_BUILD_PANEL = 'open_build_panel';

    case SEARCH_TECHNICAL_NOTEBOOK = 'search_technical_notebook';
}

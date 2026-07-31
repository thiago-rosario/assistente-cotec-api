<?php

declare(strict_types=1);

namespace App\Core\Enum;

enum WhatsappConversationState: string
{
    case BuildPanel = 'build_panel';

    case MainMenu = 'main_menu';
}

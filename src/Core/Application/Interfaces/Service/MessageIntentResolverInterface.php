<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Service;

use App\Core\Domain\Entity\MessageEntity;
use App\Core\Enum\WhatsappMenuOption;

interface MessageIntentResolverInterface
{
    public function menuOption(MessageEntity $message): ?WhatsappMenuOption;

    public function isMainMenuRequest(MessageEntity $message): bool;
}

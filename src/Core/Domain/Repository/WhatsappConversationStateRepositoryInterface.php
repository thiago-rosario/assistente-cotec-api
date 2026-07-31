<?php

declare(strict_types=1);

namespace App\Core\Domain\Repository;

use App\Core\Domain\Entity\MessageEntity;
use App\Core\Enum\WhatsappConversationState;

interface WhatsappConversationStateRepositoryInterface
{
    public function get(MessageEntity $message): ?WhatsappConversationState;

    public function put(MessageEntity $message, WhatsappConversationState $state): void;

    public function forget(MessageEntity $message): void;
}

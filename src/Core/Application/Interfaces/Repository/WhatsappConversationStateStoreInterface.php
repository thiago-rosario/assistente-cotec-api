<?php

declare(strict_types=1);

namespace App\Core\Application\Interfaces\Repository;

use App\Core\Application\DTO\WhatsappConversationStateDTO;

interface WhatsappConversationStateStoreInterface
{
    public function get(?string $phone): ?WhatsappConversationStateDTO;

    public function put(?string $phone, WhatsappConversationStateDTO $state): void;

    public function forget(?string $phone): void;
}

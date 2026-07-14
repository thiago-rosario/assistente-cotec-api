<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Interfaces\Repository;

use App\Core\Conversation\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Conversation\Enum\ConversationState;

interface ConversationStateRepositoryInterface
{
    public function get(ReceivedMessageInputDTO $input): ?ConversationState;

    public function put(ReceivedMessageInputDTO $input, ConversationState $state): void;

    public function forget(ReceivedMessageInputDTO $input): void;
}

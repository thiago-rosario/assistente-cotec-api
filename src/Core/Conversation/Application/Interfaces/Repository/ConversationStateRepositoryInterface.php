<?php

declare(strict_types=1);

namespace App\Core\Conversation\Application\Interfaces\Repository;

use App\Core\Conversation\Application\DTO\ReceivedMessageInputDTO;
use App\Core\Conversation\Enum\ConversationStateEnum;

interface ConversationStateRepositoryInterface
{
    public function get(ReceivedMessageInputDTO $input): ?ConversationStateEnum;

    public function put(ReceivedMessageInputDTO $input, ConversationStateEnum $state): void;

    public function forget(ReceivedMessageInputDTO $input): void;
}
